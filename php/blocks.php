<?php

/**
 * blocks.php - Lyquix Gutenberg blocks
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
//  Instead add directories under /php/custom/blocks with and use
//  block.json files

namespace lqx\blocks;

/**
 * Register new blocks categories
 */

add_filter('block_categories_all', function ($categories) {
	$categories[] = [
		'slug'  => 'lqx-module-blocks',
		'title' => 'Lyquix Modules'
	];

	$categories[] = [
		'slug'  => 'lqx-layout-blocks',
		'title' => 'Lyquix Layouts'
	];

	return $categories;
});

// Load Lyquix blocks common functions
require_once(get_stylesheet_directory() . '/php/blocks/layout/layout.php');
require_once(get_stylesheet_directory() . '/php/blocks/module/module.php');

/**
 * Register ACF blocks
 */
add_action('init', function () {
	// Use glob to find 'block.json' files in the 'blocks' directory
	$matches = array_merge(glob(__DIR__ . '/blocks/**/*/block.json'), glob(__DIR__ . 'custom/blocks/**/*/block.json'));

	// Check if any matches were found
	if (!empty($matches)) {
		foreach ($matches as $match) {
			// Get the directory name for each match
			register_block_type(dirname($match));
		}
	}
});

/**
 * ACF Inner Blocks should wrap
 */
add_filter('acf/blocks/wrap_frontend_innerblocks', function ($wrap, $name) {
	if (str_contains($name, 'lqx/')) {
		return false;
	}
	return true;
}, 10, 2);


/**
 * Set the Style Preset values for the Lyquix Modules
 */
add_filter('acf/load', function ($field) {
	$field_keys = [
		[ // Accordion Style Presets
			'user' => 'field_656c9b99e9e1f',
			'choice' => 'field_656e7cb6b285f'
		],
		[ // Accordion Settings Presets
			'user' => 'field_656c9bb1e9e20',
			'choice' => 'field_656d01578aa30'
		]
	];

	foreach ($field_keys as $k) {
		if ($field['key'] == $k['user']) {
			$choice_field = get_field_object($k['choice']);
			error_log(json_encode([$field, $choice_field], JSON_PRETTY_PRINT), 3, __DIR__ . '/blocks.log');
			while (have_rows($choice_field['parent'], 'option')) {
				the_row();
				$value = get_sub_field($k['choice'], 'option');
				$field['choices'][$value] = $value;
			}
		}
	}
	return $field;
});

