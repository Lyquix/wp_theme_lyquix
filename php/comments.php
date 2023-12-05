<?php

/**
 * comments.php - Disable all comment functionality
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
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

namespace lqx\comments;

// Disable comments post type support
function disable_comments_post_type_support() {
	$post_types = get_post_types();
	foreach ($post_types as $post_type) {
		if (post_type_supports($post_type, 'comments')) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}
}
add_action('init', '\lqx\comments\disable_comments_post_type_support');

// Remove comments from the admin menu and admin bar
function remove_comments_admin_menu() {
	remove_menu_page('edit-comments.php');
	remove_submenu_page('options-general.php', 'options-discussion.php');
}
add_action('admin_menu', '\lqx\comments\remove_comments_admin_menu');

function remove_comments_admin_bar() {
	if (is_admin_bar_showing()) {
		remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	}
}
add_action('wp_before_admin_bar_render', '\lqx\comments\remove_comments_admin_bar');

// Remove comments from the "Right Now" dashboard widget
function remove_comments_dashboard_widget() {
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', '\lqx\comments\remove_comments_dashboard_widget');

// Disable comment-related REST API endpoints
function disable_comments_rest_api_endpoints($endpoints) {
	if (isset($endpoints['/wp/v2/comments'])) {
		unset($endpoints['/wp/v2/comments']);
	}
	if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
		unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
	}
	return $endpoints;
}
add_filter('rest_endpoints', '\lqx\comments\disable_comments_rest_api_endpoints');

// Disable XML-RPC methods related to comments
function disable_xmlrpc_comment_methods($methods) {
	unset($methods['wp.getComments']);
	unset($methods['wp.getComment']);
	unset($methods['wp.deleteComment']);
	unset($methods['wp.editComment']);
	unset($methods['wp.newComment']);
	return $methods;
}
add_filter('xmlrpc_methods', '\lqx\comments\disable_xmlrpc_comment_methods');

// Disable comments RSS feed
function disable_comments_rss() {
	wp_die('No comments are available.');
}
add_action('do_feed_comments', '\lqx\comments\disable_comments_rss', 1);

// Disable comments in the admin
function disable_comments_admin() {
	// Hide the existing comments
	add_filter('comments_array', '__return_empty_array', 10);
	// Remove the comments metabox from the dashboard
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	// Disable support for comments in the admin
	remove_post_type_support('post', 'comments');
	remove_post_type_support('page', 'comments');
}
add_action('admin_init', '\lqx\comments\disable_comments_admin');
