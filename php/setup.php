<?php
/**
 * setup.php - Theme initial setup
 *
 * @version     2.2.2
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

function lqx_setup() {
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
	// Register menu locations
	register_nav_menus(array(
		'primary-menu' => __('Primary Menu', 'lyquix'),
		'secondary-menu' => __('Secondary Menu', 'lyquix'),
		'tertiary-menu' => __('Tertiary Menu', 'lyquix'),
		'utility-menu' => __('Utility Menu', 'lyquix'),
		'footer-menu' => __('Footer Menu', 'lyquix'),
		'hidden-menu' => __('Hidden Menu', 'lyquix'),
	));
	// Hide PHP upgrade alert from dashboard
	add_action('admin_head', function(){echo '<style>#dashboard_php_nag {display:none;}</style>';});
}
