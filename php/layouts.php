<?php

/**
 * layouts.php - Lyquix layout Gutenberg blocks
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

namespace lqx\layouts;

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

if (get_theme_mod('feat_gutenberg_layout_blocks', '1') === '1') {
	// Register the Lyquix Layouts blocks category
	add_filter('block_categories_all', function ($categories) {
		$categories[] = [
			'slug'  => 'lqx-layout-blocks',
			'title' => 'Lyquix Layouts'
		];

		return $categories;
	});


	/**
	 * Register ACF blocks
	 */
	add_action('init', function () {
		// Use glob to find 'block.json' files in the 'blocks' directory
		$matches = array_merge(glob(__DIR__ . '/layouts/*/block.json'));

		// Check if any matches were found
		if (!empty($matches)) {
			foreach ($matches as $match) {
				// Get the directory name for each match
				register_block_type(dirname($match));
			}
		}
	});

	// ACF Inner Blocks should wrap
	add_filter('acf/blocks/wrap_frontend_innerblocks', function ($wrap, $name) {
		if (str_contains($name, 'lqx/')) {
			return false;
		}
		return true;
	}, 10, 2);
}
