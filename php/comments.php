<?php

/**
 * comments.php - Disable all comment functionality
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

if (get_theme_mod('feat_disable_comments', '1') === '1') {
	// Disable comments post type support
	add_action('init', function () {
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if (post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	});

	// Remove comments from the admin menu and admin bar
	add_action('admin_menu', function () {
		remove_menu_page('edit-comments.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');
	});

	// Remove comments from the admin bar
	add_action('wp_before_admin_bar_render', function () {
		if (is_admin_bar_showing()) {
			remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
		}
	});

	// Remove comments from the "Right Now" dashboard widget
	add_action('wp_dashboard_setup', function () {
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	});

	// Disable comment-related REST API endpoints
	add_filter('rest_endpoints', function ($endpoints) {
		if (isset($endpoints['/wp/v2/comments'])) {
			unset($endpoints['/wp/v2/comments']);
		}
		if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
			unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
		}
		return $endpoints;
	});

	// Disable XML-RPC methods related to comments
	add_filter('xmlrpc_methods', function ($methods) {
		unset($methods['wp.getComments']);
		unset($methods['wp.getComment']);
		unset($methods['wp.deleteComment']);
		unset($methods['wp.editComment']);
		unset($methods['wp.newComment']);
		return $methods;
	});

	// Disable comments RSS feed
	add_action('do_feed_comments', function () {
		wp_die('No comments are available.');
	}, 1);

	// Disable comments in the admin
	add_action('admin_init', function () {
		// Hide the existing comments
		add_filter('comments_array', '__return_empty_array', 10);
		// Remove the comments metabox from the dashboard
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
		// Disable support for comments in the admin
		remove_post_type_support('post', 'comments');
		remove_post_type_support('page', 'comments');
	});
}
