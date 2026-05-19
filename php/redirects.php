<?php

/**
 * redirects.php - Redirects manager sidebar/metabox panel (requires Redirection plugin)
 *
 * @version     3.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//    "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

namespace lqx\redirects;

// Check if feature is enabled
if (get_theme_mod('feat_redirects', '1') == '1') {

	// Only proceed if the Redirection plugin is active
	if (!defined('REDIRECTION_VERSION')) return;

	// REST API endpoints
	add_action('rest_api_init', function () {
		// GET: fetch redirect chains for a post
		register_rest_route('lqx/v1', '/redirects', [
			'methods' => 'GET',
			'callback' => function ($request) {
				$post_id = intval($request->get_param('post_id'));

				$post = get_post($post_id);
				if (!$post) {
					return new \WP_Error('post_not_found', 'Post not found', ['status' => 404]);
				}

				return rest_ensure_response(get_redirects_for_post($post_id));
			},
			'permission_callback' => function ($request) {
				$post_id = intval($request->get_param('post_id'));
				return $post_id && current_user_can('edit_post', $post_id);
			},
			'args' => [
				'post_id' => [
					'required' => true,
					'type' => 'integer',
					'sanitize_callback' => 'absint'
				]
			]
		]);

		// POST: create a new redirect pointing to a post
		register_rest_route('lqx/v1', '/redirects', [
			'methods' => 'POST',
			'callback' => function ($request) {
				$post_id    = intval($request->get_param('post_id'));
				$source_url = trim(sanitize_text_field($request->get_param('source_url')));

				$post = get_post($post_id);
				if (!$post) {
					return new \WP_Error('post_not_found', 'Post not found', ['status' => 404]);
				}

				if (empty($source_url)) {
					return new \WP_Error('invalid_url', 'Source URL is required', ['status' => 400]);
				}

				// Ensure source URL starts with /
				if ($source_url[0] !== '/') {
					$source_url = '/' . $source_url;
				}

				// Target is the post's permalink path
				$permalink   = get_permalink($post_id);
				$parsed      = parse_url($permalink);
				$target_path = $parsed['path'] ?? '/';

				// Resolve the group ID from plugin options, fall back to first enabled group
				$options  = red_get_options();
				$group_id = isset($options['last_group_id']) && $options['last_group_id'] > 0
					? intval($options['last_group_id'])
					: 0;

				if (!$group_id || \Red_Group::get($group_id) === false) {
					global $wpdb;
					$group_id = intval($wpdb->get_var(
						"SELECT id FROM {$wpdb->prefix}redirection_groups WHERE status='enabled' ORDER BY id LIMIT 1"
					));
				}

				if (!$group_id) {
					return new \WP_Error('no_group', 'No redirect group found in Redirection plugin', ['status' => 500]);
				}

				$result = \Red_Item::create([
					'url'         => $source_url,
					'action_data' => ['url' => $target_path],
					'group_id'    => $group_id,
					'match_type'  => 'url',
					'action_type' => 'url',
					'action_code' => 301,
					'regex'       => false,
				]);

				if (is_wp_error($result)) {
					return new \WP_Error('create_failed', $result->get_error_message(), ['status' => 500]);
				}

				// Return the refreshed redirect list so the UI can update in one round-trip
				return rest_ensure_response(get_redirects_for_post($post_id));
			},
			'permission_callback' => function ($request) {
				$post_id = intval($request->get_param('post_id'));
				return $post_id && current_user_can('edit_post', $post_id);
			},
			'args' => [
				'post_id' => [
					'required' => true,
					'type' => 'integer',
					'sanitize_callback' => 'absint'
				],
				'source_url' => [
					'required' => true,
					'type' => 'string',
					'sanitize_callback' => 'sanitize_text_field'
				]
			]
		]);
	});

	/**
	 * Normalize a URL path: strip trailing slash except for root "/"
	 */
	function normalize_path($path) {
		$p = rtrim($path, '/');
		return $p === '' ? '/' : $p;
	}

	/**
	 * Main entry point: returns chains of redirects pointing to the given post
	 */
	function get_redirects_for_post($post_id) {
		global $wpdb;

		$table = $wpdb->prefix . 'redirection_items';

		// Verify table exists
		if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
			return ['error' => 'Redirection plugin table not found', 'chains' => [], 'post_path' => ''];
		}

		$permalink = get_permalink($post_id);
		$parsed    = parse_url($permalink);
		$post_path = normalize_path($parsed['path'] ?? '/');

		$items  = collect_redirect_items([$post_path]);
		$chains = build_chains($items, $post_path);

		return [
			'post_path' => $post_path,
			'chains'    => $chains,
		];
	}

	/**
	 * Iteratively collect all redirect items whose destination path matches
	 * any of the given target paths, then recurse on the sources found,
	 * building a complete picture of all redirect chains leading to the post.
	 *
	 * Only URL-type, non-regex, URL-match redirects are considered.
	 */
	function collect_redirect_items($initial_paths) {
		global $wpdb;

		$table      = $wpdb->prefix . 'redirection_items';
		$all_items  = [];
		$found_ids  = [];
		$queue      = $initial_paths;
		$depth      = 0;

		$home_parsed = parse_url(home_url());
		$domain      = $home_parsed['host'] ?? '';

		while (!empty($queue) && $depth < 10) {
			$next_queue = [];

			foreach ($queue as $target_path) {
				// Broad LIKE patterns — PHP filter below verifies the exact path match.
				// Using prefix-style LIKE covers: exact, trailing slash, and query string variants.
				// e.g. target "/blog/hello-world" matches stored "/blog/hello-world/" or "/blog/hello-world/?utm=..."
				$like_rel = $wpdb->esc_like($target_path) . '%';
				$like_abs = '%' . $wpdb->esc_like('://' . $domain . $target_path) . '%';

				// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
				$sql = $wpdb->prepare(
					"SELECT id, url, action_data, action_type, action_code, status, regex, last_count AS hits, title
					 FROM {$table}
					 WHERE match_type  = 'url'
					   AND action_type = 'url'
					   AND regex       = 0
					   AND (
					       action_data LIKE %s
					    OR action_data LIKE %s
					   )",
					$like_rel,
					$like_abs
				);
				// phpcs:enable

				$rows = $wpdb->get_results($sql, ARRAY_A);

				foreach ($rows as $row) {
					$id = intval($row['id']);
					if (in_array($id, $found_ids)) continue;

					// PHP-side verify: action_data path must exactly match target_path
					$action_parsed = parse_url($row['action_data']);
					$action_path   = normalize_path($action_parsed['path'] ?? $row['action_data']);
					if ($action_path !== $target_path) continue;

					$found_ids[] = $id;
					$row['id']          = $id;
					$row['action_code'] = intval($row['action_code']);
					$row['hits']        = intval($row['hits']);
					$row['regex']       = (bool)$row['regex'];
					$all_items[] = $row;

					// Enqueue the source path for the next level
					$src_parsed = parse_url($row['url']);
					$src_path   = normalize_path($src_parsed['path'] ?? $row['url']);
					if (!in_array($src_path, $next_queue)) {
						$next_queue[] = $src_path;
					}
				}
			}

			$queue = $next_queue;
			$depth++;
		}

		return $all_items;
	}

	/**
	 * Build chains from a flat list of redirect items.
	 *
	 * Each chain is an array of items ordered from the most-remote source
	 * to the item that directly redirects to the post. Only full chains are
	 * emitted (starting at the root with no further predecessors), so each
	 * unique chain appears exactly once.
	 *
	 * Example: /oldest → /old → /post produces one chain:
	 *   [/oldest → /old → /post]
	 */
	function build_chains($items, $post_path) {
		// Resolve each item's destination and source paths
		foreach ($items as &$item) {
			$dp = parse_url($item['action_data']);
			$item['_dest'] = normalize_path($dp['path'] ?? $item['action_data']);

			$sp = parse_url($item['url']);
			$item['_src']  = normalize_path($sp['path'] ?? $item['url']);
		}
		unset($item);

		// Map: destination path → [items redirecting to that path]
		$by_dest = [];
		foreach ($items as $item) {
			$by_dest[$item['_dest']][] = $item;
		}

		$chains  = [];
		$visited = [];
		traverse_chains($post_path, $by_dest, [], $chains, $visited, 0);

		// Strip internal keys before returning
		return array_map(function ($chain) {
			return array_map(function ($item) {
				unset($item['_dest'], $item['_src']);
				return $item;
			}, $chain);
		}, $chains);
	}

	/**
	 * Recursive traversal to collect chains ending at $target.
	 * A chain is only emitted when no further predecessors exist (i.e., at the
	 * root of the chain), so each unique chain is represented exactly once at
	 * its full length.
	 */
	function traverse_chains($target, $by_dest, $current_chain, &$chains, &$visited, $depth) {
		if ($depth >= 10 || !isset($by_dest[$target])) return;

		$current_ids = array_column($current_chain, 'id');

		foreach ($by_dest[$target] as $item) {
			if (in_array($item['id'], $current_ids)) continue; // cycle guard

			// Prepend: new item is the most-remote hop so far → oldest-first order
			$new_chain = array_merge([$item], $current_chain);

			$key = implode(',', array_column($new_chain, 'id'));
			if (in_array($key, $visited)) continue;
			$visited[] = $key;

			$count_before = count($chains);

			// Continue looking for even deeper sources
			traverse_chains($item['_src'], $by_dest, $new_chain, $chains, $visited, $depth + 1);

			// Only emit this chain if the recursion found no deeper chains,
			// meaning this item is the root (no predecessors) or depth limit was hit.
			if (count($chains) === $count_before) {
				$chains[] = $new_chain;
			}
		}
	}

	// Classic Editor: Add meta box for all public post types
	add_action('add_meta_boxes', function ($post_type, $post) {
		// Skip if block editor is active — the Gutenberg sidebar panel handles this
		if (function_exists('use_block_editor_for_post') && use_block_editor_for_post($post)) return;

		$post_types = get_post_types(['public' => true], 'names');
		foreach ($post_types as $type) {
			add_meta_box(
				'lqx_redirects_meta_box',
				__('Redirects', 'lyquix'),
				function ($post) {
					?>
					<div id="lqx-redirects-metabox">
						<p><?php _e('Loading redirects…', 'lyquix'); ?></p>
					</div>
					<?php
				},
				$type,
				'side',
				'low'
			);
		}
	}, 10, 2);

	// Classic Editor: Enqueue scripts
	add_action('admin_enqueue_scripts', function ($hook) {
		if (!in_array($hook, ['post.php', 'post-new.php'])) return;

		// Only load for classic editor
		if (function_exists('use_block_editor_for_post') && isset($_GET['post'])) {
			$post = get_post($_GET['post']);
			if ($post && use_block_editor_for_post($post)) return;
		}

		wp_enqueue_script(
			'lqx-redirects-metabox',
			get_template_directory_uri() . '/js/redirects-metabox.js',
			[],
			filemtime(get_template_directory() . '/js/redirects-metabox.js'),
			true
		);

		global $post;
		wp_localize_script('lqx-redirects-metabox', 'lqxRedirects', [
			'nonce'   => wp_create_nonce('wp_rest'),
			'postId'  => $post ? $post->ID : 0,
			'restUrl' => rest_url('lqx/v1/redirects')
		]);
	});

	// Gutenberg: Enqueue block editor assets
	add_action('enqueue_block_editor_assets', function () {
		wp_enqueue_script(
			'lqx-redirects-sidebar',
			get_template_directory_uri() . '/js/redirects-sidebar.js',
			['wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element', 'wp-api-fetch'],
			filemtime(get_template_directory() . '/js/redirects-sidebar.js'),
			true
		);

		// Add type="module" to the script tag
		add_filter('script_loader_tag', function ($tag, $handle, $src) {
			if ('lqx-redirects-sidebar' === $handle) {
				$tag = str_replace(' src', ' type="module" src', $tag);
			}
			return $tag;
		}, 10, 3);
	});

}
