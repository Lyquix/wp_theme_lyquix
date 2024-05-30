<?php

/**
 * modules.php - Lyquix theme modules
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
//  Instead add directories under /php/custom/modules and use .php files with the same name

namespace lqx\modules;

// Load modules only if the feature is enabled
if (get_theme_mod('feat_modules', '1') === '1') {
	// Get directories under php/modules
	$modules = array_merge(
		glob(get_template_directory() . '/php/modules/*'),
		glob(get_template_directory() . '/php/custom/modules/*')
	);
	$modules = array_filter($modules, 'is_dir');
	$modules = array_map('basename', $modules);

	// Load each module once
	foreach ($modules as $module) {
		require_once get_template_directory() . '/php/modules/' . $module . '/' . $module . '.php';
	}

	// Set the Style Preset values for the Lyquix modules
	add_filter('acf/load_field', function ($field) {
		$field_keys = [
			// CTA
			[
				'user' => 'field_658c719dc8329', // style field
				'choice' => 'field_6580708228a61' // style_name field
			],
			// Popups
			[
				'user' => 'field_658be723381f2', // style field
				'choice' => 'field_658c740a049fa' // style_name field
			],
			// Modals
			[
				'user' => 'field_65c11d4eadade', // style field
				'choice' => 'field_65c11d5f554f8' // style_name field
			]
		];
		foreach ($field_keys as $k) {
			if ($field['key'] == $k['user']) {
				$choice_field = get_field_object($k['choice']);

				// Add an empty choice
				$field['choices'] = ['' => 'Select'];

				// Because of the structure with the groups at the top of modules we need an extra level of going up
				$top_field_key = get_field_object($choice_field['parent'])['parent'] ?? '';

				if ($top_field_key) {
					while (have_rows($top_field_key, 'option')) {
						the_row();
						$rows = get_sub_field($choice_field['parent'], 'option');
						if ($rows) foreach ($rows as $row) {
							$value = $row[$choice_field['name']];
							$field['choices'][$value] = $value;
						}
					}
				}
			}
		}
		return $field;
	});
}

/**
 * Load a rendered for a module based on available overrides
 *
 * @param string $module_name
 * 		The name of the module
 *
 * @return string
 * 		The path to the renderer file
 */
function get_renderer($module_name) {
	$dir = get_stylesheet_directory() . '/php/custom/modules/' . $module_name . '/';
	$filename = 'default.php';

	if (file_exists($dir . $filename)) {
		return $dir . $filename;
	} else {
		return get_stylesheet_directory() . '/php/modules/' . $module_name . '/' . $filename;
	}
}

/**
 * Get the template file for a module based on available overrides
 *
 * @param string $module_name
 * 		The name of the module
 * @param string $sub_template
 * 		The name of the sub-template
 * 		Default: null
 *
 * @return string
 */
function get_template($module_name, $sub_template = null) {
	$dir = get_stylesheet_directory() . '/php/custom/modules/' . $module_name . '/';
	$filename = 'default';
	if ($sub_template) {
		$filename = $sub_template;
	}
	$filename .= '.tmpl.php';

	if (file_exists($dir . $filename)) {
		return $dir . $filename;
	} else {
		return get_stylesheet_directory() . '/php/modules/' . $module_name . '/' . $filename;
	}
}
