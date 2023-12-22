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
//  Instead create a file called menus.php in /php/custom/ and add custom
//  menus to the $menus array in that file

namespace lqx\menus;

function add_menu_positions() {
	// Array of menu locations
	$menus = [
		// Header Menus
		'Top Menu',
		'Main Menu',
		'Utility Menu',
		'Logged-In Menu',

		// Footer menus
		'Bottom Menu',
		'Footer Menu',

		// Other
		'Hidden Menu',
	];

	// Add custom menu positions to $menus array
	if (file_exists(get_template_directory() . '/php/custom/menus.php')) {
		require get_template_directory() . '/php/custom/menus.php';
	}

	// Register menu locations
	foreach ($menus as $menu) {
		register_nav_menu(preg_replace('/[^a-z0-9]+/', '-', strtolower($menu)), __($menu, 'lyquix'));
	}
}

add_action('after_setup_theme', '\lqx\menus\add_menu_positions');
