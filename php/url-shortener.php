<?php

/**
 * url-shortener.php - URL shortener for post URLs in the editor sidebar
 *
 * @version     3.2.0
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

namespace lqx\url_shortener;

// Check if feature is enabled
if (get_theme_mod('feat_url_shortener', '0') == '1') {

	// Register post meta on init
	add_action('init', function () {
		$post_types = get_post_types(['public' => true], 'names');
		foreach ($post_types as $post_type) {
			register_post_meta($post_type, '_short_url', [
				'show_in_rest' => true,
				'single' => true,
				'type' => 'string',
				'default' => '',
				'auth_callback' => function () {
					return current_user_can('edit_posts');
				}
			]);
			register_post_meta($post_type, '_short_url_alias', [
				'show_in_rest' => false,
				'single' => true,
				'type' => 'string',
				'default' => ''
			]);
			register_post_meta($post_type, '_short_url_target', [
				'show_in_rest' => false,
				'single' => true,
				'type' => 'string',
				'default' => ''
			]);
		}
	});

	/**
	 * Create a short URL via the configured provider
	 *
	 * @param string $long_url The URL to shorten
	 * @return array|null Array with 'short_url' and 'alias' keys, or null on failure
	 */
	function create_short_url($long_url) {
		$provider = get_theme_mod('url_shortener_provider', 'yourls');

		if ($provider === 'yourls') {
			return create_yourls_url($long_url);
		} elseif ($provider === 'tinyurl') {
			return create_tinyurl_url($long_url);
		} elseif ($provider === 'bitly') {
			return create_bitly_url($long_url);
		}

		return null;
	}

	/**
	 * Update the redirect target for an existing short URL alias
	 *
	 * @param string $alias The short URL alias/keyword
	 * @param string $new_long_url The new target URL
	 * @return bool True on success, false on failure
	 */
	function update_short_url($alias, $new_long_url) {
		$provider = get_theme_mod('url_shortener_provider', 'yourls');

		if ($provider === 'yourls') {
			return update_yourls_url($alias, $new_long_url);
		} elseif ($provider === 'tinyurl') {
			return update_tinyurl_url($alias, $new_long_url);
		} elseif ($provider === 'bitly') {
			return update_bitly_url($alias, $new_long_url);
		}

		return false;
	}

	/**
	 * Create a short URL via YOURLS
	 */
	function create_yourls_url($long_url) {
		$base_url = rtrim(get_theme_mod('url_shortener_yourls_url', ''), '/');
		$api_key = get_theme_mod('url_shortener_yourls_api_key', '');

		if (empty($base_url) || empty($api_key)) return null;

		$response = wp_remote_post($base_url . '/yourls-api.php', [
			'body' => [
				'action' => 'shorturl',
				'url' => $long_url,
				'format' => 'json',
				'signature' => $api_key
			],
			'timeout' => 15
		]);

		if (is_wp_error($response)) {
			error_log('URL Shortener: YOURLS create error - ' . $response->get_error_message());
			return null;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['shorturl']) && !empty($body['url']['keyword'])) {
			return [
				'short_url' => $body['shorturl'],
				'alias' => $body['url']['keyword']
			];
		}

		error_log('URL Shortener: YOURLS create unexpected response - ' . wp_remote_retrieve_body($response));
		return null;
	}

	/**
	 * Update a YOURLS short URL redirect target
	 */
	function update_yourls_url($alias, $new_long_url) {
		$base_url = rtrim(get_theme_mod('url_shortener_yourls_url', ''), '/');
		$api_key = get_theme_mod('url_shortener_yourls_api_key', '');

		if (empty($base_url) || empty($api_key)) return false;

		$response = wp_remote_post($base_url . '/yourls-api.php', [
			'body' => [
				'action' => 'update',
				'shorturl' => $alias,
				'url' => $new_long_url,
				'format' => 'json',
				'signature' => $api_key
			],
			'timeout' => 15
		]);

		if (is_wp_error($response)) {
			error_log('URL Shortener: YOURLS update error - ' . $response->get_error_message());
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['statusCode']) && $body['statusCode'] == 200) {
			return true;
		}

		error_log('URL Shortener: YOURLS update unexpected response - ' . wp_remote_retrieve_body($response));
		return false;
	}

	/**
	 * Create a short URL via TinyURL
	 */
	function create_tinyurl_url($long_url) {
		$api_key = get_theme_mod('url_shortener_tinyurl_api_key', '');

		if (empty($api_key)) return null;

		$response = wp_remote_post('https://api.tinyurl.com/create', [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json'
			],
			'body' => wp_json_encode([
				'url' => $long_url,
				'domain' => 'tinyurl.com'
			]),
			'timeout' => 15
		]);

		if (is_wp_error($response)) {
			error_log('URL Shortener: TinyURL create error - ' . $response->get_error_message());
			return null;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['data']['tiny_url']) && !empty($body['data']['alias'])) {
			return [
				'short_url' => $body['data']['tiny_url'],
				'alias' => $body['data']['alias']
			];
		}

		error_log('URL Shortener: TinyURL create unexpected response - ' . wp_remote_retrieve_body($response));
		return null;
	}

	/**
	 * Update a TinyURL short URL redirect target
	 */
	function update_tinyurl_url($alias, $new_long_url) {
		$api_key = get_theme_mod('url_shortener_tinyurl_api_key', '');

		if (empty($api_key)) return false;

		$response = wp_remote_request('https://api.tinyurl.com/change', [
			'method' => 'PATCH',
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json'
			],
			'body' => wp_json_encode([
				'alias' => $alias,
				'domain' => 'tinyurl.com',
				'url' => $new_long_url
			]),
			'timeout' => 15
		]);

		if (is_wp_error($response)) {
			error_log('URL Shortener: TinyURL update error - ' . $response->get_error_message());
			return false;
		}

		$code = wp_remote_retrieve_response_code($response);

		if ($code >= 200 && $code < 300) {
			return true;
		}

		error_log('URL Shortener: TinyURL update unexpected response - ' . wp_remote_retrieve_body($response));
		return false;
	}

	/**
	 * Create a short URL via Bitly
	 */
	function create_bitly_url($long_url) {
		$api_key = get_theme_mod('url_shortener_bitly_api_key', '');

		if (empty($api_key)) return null;

		$response = wp_remote_post('https://api-ssl.bitly.com/v4/shorten', [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json'
			],
			'body' => wp_json_encode([
				'long_url' => $long_url
			]),
			'timeout' => 15
		]);

		if (is_wp_error($response)) {
			error_log('URL Shortener: Bitly create error - ' . $response->get_error_message());
			return null;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!empty($body['link']) && !empty($body['id'])) {
			return [
				'short_url' => $body['link'],
				'alias' => $body['id']
			];
		}

		error_log('URL Shortener: Bitly create unexpected response - ' . wp_remote_retrieve_body($response));
		return null;
	}

	/**
	 * Update a Bitly short URL redirect target
	 */
	function update_bitly_url($alias, $new_long_url) {
		$api_key = get_theme_mod('url_shortener_bitly_api_key', '');

		if (empty($api_key)) return false;

		$response = wp_remote_request('https://api-ssl.bitly.com/v4/bitlinks/' . $alias, [
			'method' => 'PATCH',
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json'
			],
			'body' => wp_json_encode([
				'long_url' => $new_long_url
			]),
			'timeout' => 15
		]);

		if (is_wp_error($response)) {
			error_log('URL Shortener: Bitly update error - ' . $response->get_error_message());
			return false;
		}

		$code = wp_remote_retrieve_response_code($response);

		if ($code >= 200 && $code < 300) {
			return true;
		}

		error_log('URL Shortener: Bitly update unexpected response - ' . wp_remote_retrieve_body($response));
		return false;
	}

	// Generate or update short URL on post save
	add_action('save_post', function ($post_id, $post) {
		// Skip autosaves and revisions
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (wp_is_post_revision($post_id)) return;

		// Only process published posts
		if ($post->post_status !== 'publish') return;

		// Only process public post types
		$public_post_types = get_post_types(['public' => true], 'names');
		if (!in_array($post->post_type, $public_post_types)) return;

		$permalink = get_permalink($post_id);
		$short_url = get_post_meta($post_id, '_short_url', true);
		$alias = get_post_meta($post_id, '_short_url_alias', true);
		$stored_target = get_post_meta($post_id, '_short_url_target', true);

		if (empty($short_url)) {
			// Create a new short URL
			$result = create_short_url($permalink);
			if ($result) {
				update_post_meta($post_id, '_short_url', $result['short_url']);
				update_post_meta($post_id, '_short_url_alias', $result['alias']);
				update_post_meta($post_id, '_short_url_target', $permalink);
			}
		} elseif ($stored_target !== $permalink) {
			// Permalink changed, update the short URL target
			$success = update_short_url($alias, $permalink);
			if ($success) {
				update_post_meta($post_id, '_short_url_target', $permalink);
			}
		}
	}, 10, 2);

	// Classic Editor: Add meta box for all public post types
	add_action('add_meta_boxes', function ($post_type, $post) {
		// Skip if block editor is active — the Gutenberg sidebar panel handles this
		if (function_exists('use_block_editor_for_post') && use_block_editor_for_post($post)) return;

		$post_types = get_post_types(['public' => true], 'names');
		foreach ($post_types as $post_type) {
			add_meta_box(
				'lqx_url_shortener_meta_box',
				__('Short URL', 'lyquix'),
				function ($post) {
					$short_url = get_post_meta($post->ID, '_short_url', true);
					$qr_enabled = get_theme_mod('feat_qr_generator', '1') == '1' ? '1' : '0';
					?>
					<div id="lqx-url-shortener-metabox"
						data-short-url="<?php echo esc_attr($short_url); ?>"
						data-qr-enabled="<?php echo esc_attr($qr_enabled); ?>">
						<p><?php _e('Publish the post to generate a short URL.', 'lyquix'); ?></p>
					</div>
					<?php
				},
				$post_type,
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

		$deps = ['jquery'];
		if (get_theme_mod('feat_qr_generator', '1') == '1') {
			$deps[] = 'qr-code-generator';
		}

		wp_enqueue_script(
			'lqx-url-shortener-metabox',
			get_template_directory_uri() . '/js/url-shortener-metabox.js',
			$deps,
			filemtime(get_template_directory() . '/js/url-shortener-metabox.js'),
			true
		);
	});

	// Gutenberg: Enqueue block editor assets
	add_action('enqueue_block_editor_assets', function () {
		$deps = ['wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element'];
		if (get_theme_mod('feat_qr_generator', '1') == '1') {
			$deps[] = 'qr-code-generator';
		}

		wp_enqueue_script(
			'lqx-url-shortener-sidebar',
			get_template_directory_uri() . '/js/url-shortener-sidebar.js',
			$deps,
			filemtime(get_template_directory() . '/js/url-shortener-sidebar.js'),
			true
		);

		wp_localize_script('lqx-url-shortener-sidebar', 'lqxUrlShortener', [
			'qrEnabled' => get_theme_mod('feat_qr_generator', '1') == '1'
		]);

		// Add type="module" to the script
		add_filter('script_loader_tag', function ($tag, $handle, $src) {
			if ('lqx-url-shortener-sidebar' === $handle) {
				$tag = str_replace(' src', ' type="module" src', $tag);
			}
			return $tag;
		}, 10, 3);
	});

}
