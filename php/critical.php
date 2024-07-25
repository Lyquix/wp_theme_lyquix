<?php

/**
 * critical.php - Generate configuration for critical path CSS
 *
 * @version     3.0.0
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

namespace lqx\critical;

function rest_route() {
	$templates = [];

	// Add pages
	$pages = get_pages(['post_status' => 'publish']);
	foreach ($pages as $page) {
		$templates[] = [
			'type' => 'page',
			'slug' => $page->post_name,
			'url' => get_permalink($page)
		];
	}

	// Sample blog post
	$posts = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
	if (!empty($posts)) {
		$templates[] = [
			'type' => 'post',
			'url' => get_permalink($posts[0])
		];
	}

	// Custom post types created with ACF
	$post_types = get_post_types(['_builtin' => false], 'objects');
	foreach ($post_types as $post_type) {
		if (in_array($post_type->name, [
			'acf-field-group',
			'acf-field',
			'acf-taxonomy',
			'acf-post-type',
			'acf-ui-options-page'
		])) continue;

		$custom_posts = get_posts([
			'post_type' => $post_type->name,
			'numberposts' => 1,
			'post_status' => 'publish'
		]);
		if (!empty($custom_posts)) {
			$templates[] = [
				'type' => $post_type->name,
				'url' => get_permalink($custom_posts[0])
			];
		}
	}

	return [
		'viewports' => [
			[
				'width' => 320,
				'height' => 720
			],
			[
				'width' => 480,
				'height' => 1080
			],
			[
				'width' => 720,
				'height' => 1080
			],
			[
				'width' => 1080,
				'height' => 1080
			],
			[
				'width' => 1620,
				'height' => 1080
			]
		],
		'templates' => $templates
	];
}

// Register a REST API endpoint to get the alerts from site options
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/critical', [
		'methods' => 'GET',
		'callback' => '\lqx\critical\rest_route',
		'permission_callback' => '__return_true',
	]);
});
