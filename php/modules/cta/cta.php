<?php

/**
 * cta.php - Lyquix CTA module
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

namespace lqx\modules\cta;

// Set the Style Preset values for the Lyquix Modules
add_filter('acf/load_field', function ($field) {
	// Field keys
	$user = 'field_658c719dc8329'; // style field
	$choice = 'field_65c11d5f554f8'; // style_name field

	if ($field['key'] == $user) {
		$choice_field = get_field_object($choice);

		// Add an empty choice
		$field['choices'][''] = 'Select';

		while (have_rows($choice_field['parent'], 'option')) {
			the_row();
			$value = get_sub_field($choice, 'option');
			$field['choices'][$value] = $value;

		}
	}
	return $field;
});


if (file_exists(get_stylesheet_directory() . '/php/custom/modules/cta/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/modules/cta/render.php';
} else {
	require_once get_stylesheet_directory() . '/php/modules/cta/render.php';
}
