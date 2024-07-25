<?php
/**
 * critical.php - Generate configuration for critical path CSS
 *
 * @version     2.5.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

function lqx_critical_rest_route() {
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
				'width' => 1600,
				'height' => 1080
			],
			[
				'width' => 1280,
				'height' => 1080
			],
			[
				'width' => 960,
				'height' => 1080
			],
			[
				'width' => 640,
				'height' => 960
			],
			[
				'width' => 320,
				'height' => 720
			]
		],
		'templates' => $templates
	];
}

// Register a REST API endpoint to get the alerts from site options
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v2', '/critical', [
		'methods' => 'GET',
		'callback' => 'lqx_critical_rest_route',
		'permission_callback' => '__return_true',
	]);
});
