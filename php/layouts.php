<?php

/**
 * layouts.php - Lyquix layout Gutenberg blocks
 *
 * @version     3.4.0
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

namespace lqx\layouts;

/**
 * Get Tailwind CSS classes from ACF fields
 * 		- Fields must start with 'tailwind_'
 *
 * @return string - The Tailwind CSS classes
 * 		- Example: 'text-center bg-blue-500'
 */
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

if (get_theme_mod('feat_layout_blocks', '1') === '1') {
	// Register the Lyquix Layouts blocks category
	add_filter('block_categories_all', function ($categories) {
		array_splice($categories, 1, 0, [[
			'slug'  => 'lqx-layout-blocks',
			'title' => 'Lyquix Layout Blocks'
		]]);

		return $categories;
	});


	//Register ACF blocks
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

	// Legacy: the Cluster block was registered as 'lqx/luster' (typo) until 3.5.1.
	// No known content uses the old name, but render any stray legacy block as the Cluster block.
	add_filter('render_block_data', function ($parsed_block) {
		if (($parsed_block['blockName'] ?? '') === 'lqx/luster') $parsed_block['blockName'] = 'lqx/cluster';
		return $parsed_block;
	}, 5);

	// ACF Inner Blocks should wrap
	add_filter('acf/blocks/wrap_frontend_innerblocks', function ($wrap, $name) {
		if (str_contains($name, 'lqx/')) {
			return false;
		}
		return true;
	}, 10, 2);

	// Populate each layout block's Style dropdown from its Styles repeater on the
	// Site Settings > Layout page (field name: <block>_block_styles, one tab per block)
	$layout_style_fields = [
		'box' => 'field_fee77bc4d8220',
		'center' => 'field_25af950c75b24',
		'cluster' => 'field_708fdfc23fd3a',
		'container' => 'field_d9cf4850f29d6',
		'cover' => 'field_89920973daf15',
		'frame' => 'field_e2c5691d5bffa',
		'grid' => 'field_16bd134a8f00d',
		'icon' => 'field_91fd844e76ab0',
		'imposter' => 'field_2cc7d1523268c',
		'reel' => 'field_24cebc6d20341',
		'sidebar' => 'field_e2d506c15ac88',
		'stack' => 'field_37ab950d92e58',
		'switcher' => 'field_a4da1ecb0b542'
	];
	foreach ($layout_style_fields as $layout_block => $style_field_key) {
		add_filter('acf/load_field/key=' . $style_field_key, function ($field) use ($layout_block) {
			// Only needed in the block editor to populate the style dropdown
			if (!is_admin() && !(defined('REST_REQUEST') && REST_REQUEST)) return $field;

			static $choices = [];
			if (!isset($choices[$layout_block])) {
				$choices[$layout_block] = ['' => 'Select'];
				if (function_exists('have_rows') && have_rows($layout_block . '_block_styles', 'option')) {
					while (have_rows($layout_block . '_block_styles', 'option')) {
						the_row();
						$value = get_sub_field('style_name');
						if ($value) $choices[$layout_block][$value] = $value;
					}
				}
			}

			$field['choices'] = $choices[$layout_block];
			return $field;
		});
	}
}
