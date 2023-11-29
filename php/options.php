<?php

/**
 * options.php - Site Options pages
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
//  If you need to add sub pages to the Site Options page, create a file called
//  options.php in the php/custom folder, and add the sub pages there.

if (function_exists('acf_add_options_page') && function_exists('acf_add_options_sub_page')) {
	// Add Site Options page
	acf_add_options_page([
		'page_title'    => 'Site Options',
		'menu_title'    => 'Site Options',
		'menu_slug'     => 'site-options',
		'capability'    => 'edit_posts',
		'redirect'      => false
	]);

	acf_add_options_sub_page([
		'page_title'    => 'Modules - Site Settings',
		'menu_title'    => 'Modules',
		'parent_slug'   => 'site-options',
	]);

	/**
	 * TODO
	 * Alert bar
	 * Filters
	 * Popups
	 */

	// Add option pages to $site_options array
	if (file_exists(get_template_directory() . '/php/custom/options.php')) {
		require get_template_directory() . '/php/custom/options.php';

		if(count($sub_pages)) {
			// Add custom option pages
			foreach($sub_pages as $sub_page) {
				acf_add_options_sub_page([
					'page_title'    => $sub_page['page_title'],
					'menu_title'    => $sub_page['menu_title'],
					'parent_slug'   => 'site-options',
				]);
			}
		}
	}
}