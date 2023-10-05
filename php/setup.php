<?php

/**
 * setup.php - Theme initial setup
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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

namespace lqx\setup;

function theme_setup() {
	// Theme Features Support
	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption']);
	add_theme_support('customize-selective-refresh-widgets');

	// Load theme styles into editor
	add_editor_style('css/editor.css');

	// Remove unnecessary wptexturize filter
	add_filter('run_wptexturize', '__return_false');

	// Disable srcset on images
	add_filter('max_srcset_image_width', (function () {
		return 1;
	})());

	// Hide PHP upgrade alert from dashboard
	add_action('admin_head', function () {
		echo '<style>#dashboard_php_nag {display:none;}</style>';
	});

	// Allow SVGs in WP Uploads
	add_filter('upload_mimes', function ($mimes) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	});

	// Disable wpautop
	remove_filter( 'the_content', 'wpautop' );
	remove_filter( 'the_excerpt', 'wpautop' );
}

add_action('after_setup_theme', 'lqx\setup\theme_setup');
