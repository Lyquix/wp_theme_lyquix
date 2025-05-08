<?php

/**
 * critical.php - Generate configuration for critical path CSS
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

namespace lqx\critical;

function slug_path($page)
{
	$slug_path = $page->post_name;
	$parent_id = $page->post_parent;

	// Traverse up the hierarchy to include all parent slugs
	while ($parent_id) {
		$parent = get_post($parent_id);
		$slug_path = $parent->post_name . '/' . $slug_path;
		$parent_id = $parent->post_parent;
	}

	return $slug_path;
}

function rest_route()
{
	$templates = [];
	$exclude_types = get_theme_mod('exclude_types_critical_path_css', '[]');
	if (is_string($exclude_types)) $exclude_types = json_decode($exclude_types, true);
	if (!is_array($exclude_types)) $exclude_types = [];

	// Add pages
	if (!in_array('page', $exclude_types)) {
		$pages = get_pages(['post_status' => 'publish']);
		$exclude_pages = get_theme_mod('exclude_pages_critical_path_css', '[]');
		if (is_string($exclude_pages)) $exclude_pages = json_decode($exclude_pages, true);
		if (!is_array($exclude_pages)) $exclude_pages = [];
		foreach ($pages as $page) {
			$slug_path = slug_path($page);
			if (in_array($slug_path, $exclude_pages)) continue;
			$templates[] = [
				'type' => 'page',
				'slug' => $slug_path,
				'url' => get_permalink($page)
			];
		}
	}

	// Sample blog post
	if (!in_array('post', $exclude_types)) {
		$posts = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
		if (!empty($posts)) {
			$templates[] = [
				'type' => 'post',
				'url' => get_permalink($posts[0])
			];
		}
	}

	// Custom post types created with ACF
	$post_types = get_post_types(['_builtin' => false, 'public' => true], 'objects');
	foreach ($post_types as $post_type) {
		if (in_array($post_type->name, $exclude_types)) continue;
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

	$viewports = [];
	foreach (
		json_decode(
			get_theme_mod(
				'viewports_critical_path_css',
				'{"xs":{"width":320,"height":720},
				"sm":{"width":480,"height":1080},
				"md":{"width":720,"height":1080},
				"lg":{"width":1080,"height":1080},
				"xl":{"width":1620,"height":1080}}'
			),
			true
		) as $viewport
	) {
		$viewports[] = $viewport;
	}


	return [
		'viewports' => $viewports,
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
