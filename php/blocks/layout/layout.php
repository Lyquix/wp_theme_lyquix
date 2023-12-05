<?php

/**
 * layout.php - Lyquix layout blocks common functions
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

namespace lqx\blocks\layout;

// Get a list of used Tailwind classes
function get_tailwind_classes() {
	// Initialize the results array
	$classes = [];

	// Define a callback function to use with array_walk_recursive
	$callback = function ($value, $key) use (&$classes) {
		if (is_string($value) && str_starts_with($value, 'tailwind_')) {
			// Remove the prefix and add to the results array
			$classes[] = str_replace('tailwind_', '', $value);
		} elseif (is_string($key) && str_starts_with($key, 'tailwind_') && $value) {
			$classes[] = str_replace('tailwind_', '', $key) . $value;
		}
	};

	// Get the fields
	$fields = get_fields();

	// Apply the callback to each element of the array
	if ($fields) array_walk_recursive($fields, $callback);

	return implode(' ', $classes);
}
