<?php

/**
 * qr-code.php - QR code generator for post URLs in the editor sidebar
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

namespace lqx\qr_code;

// Check if feature is enabled
if (get_theme_mod('feat_qr_generator', '1') == '1') {
	// Enqueue the QR code library on admin screens
	add_action('admin_enqueue_scripts', function ($hook) {
		if (!in_array($hook, ['post.php', 'post-new.php'])) return;

		wp_enqueue_script(
			'qr-code-generator',
			get_template_directory_uri() . '/js/qr-code-generator.js',
			[],
			filemtime(get_template_directory() . '/js/qr-code-generator.js'),
			true
		);
	});

	// Classic Editor: Add meta box for all public post types
	add_action('add_meta_boxes', function ($post_type, $post) {
		// Skip if block editor is active — the Gutenberg sidebar panel handles this
		if (function_exists('use_block_editor_for_post') && use_block_editor_for_post($post)) return;

		$post_types = get_post_types(['public' => true], 'names');
		foreach ($post_types as $post_type) {
			add_meta_box(
				'lqx_qr_code_meta_box',
				__('QR Code', 'lyquix'),
				function ($post) {
					$permalink = get_permalink($post->ID);
					$url = ($post->post_status === 'auto-draft') ? '' : $permalink;
					?>
					<div id="lqx-qr-code-metabox" data-url="<?php echo esc_attr($url); ?>">
						<p><?php _e('Please save the post as a draft to generate its QR code.', 'lyquix'); ?></p>
					</div>
					<?php
				},
				$post_type,
				'side',
				'low'
			);
		}
	}, 10, 2);

	// Classic Editor: Enqueue the classic editor script
	add_action('admin_enqueue_scripts', function ($hook) {
		if (!in_array($hook, ['post.php', 'post-new.php'])) return;

		// Only load for classic editor
		if (function_exists('use_block_editor_for_post') && isset($_GET['post'])) {
			$post = get_post($_GET['post']);
			if ($post && use_block_editor_for_post($post)) return;
		}

		wp_enqueue_script(
			'lqx-qr-generator-metabox',
			get_template_directory_uri() . '/js/qr-code-metabox.js',
			['jquery', 'qr-code-generator'],
			filemtime(get_template_directory() . '/js/qr-code-metabox.js'),
			true
		);
	});

	// Gutenberg: Enqueue block editor assets
	add_action('enqueue_block_editor_assets', function () {
		wp_enqueue_script(
			'lqx-qr-code-sidebar',
			get_template_directory_uri() . '/js/qr-code-sidebar.js',
			['wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element', 'qr-code-generator'],
			filemtime(get_template_directory() . '/js/qr-code-sidebar.js'),
			true
		);

		// Add type="module" to the script
		add_filter('script_loader_tag', function ($tag, $handle, $src) {
			if ('lqx-qr-code-sidebar' === $handle) {
				$tag = str_replace(' src', ' type="module" src', $tag);
			}
			return $tag;
		}, 10, 3);
	});
}