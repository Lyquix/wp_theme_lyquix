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
//  Instead add directories under /php/custom/blocks and use block.json files

namespace lqx\blocks;

// Process overrides for settings presets
function process_overrides($settings) {
	$processed = [];
	foreach ($settings as $key => $value) {
		// Check if the key ends in _override_group
		if (substr($key, -15) == '_override_group') {
			// Extract the key name by removing _override_group
			$original_key = substr($key, 0, -15);

			// Check if the override is set to true
			if ($value[$original_key . '_override'] === true) {
				// Get the override value
				if ($value[$original_key]) {
					$processed[$original_key] = $value[$original_key];
				}
			}
		} elseif ($value) {
			// Check if $value is an array
			if (is_array($value)) {
				// Recursively process the overrides
				$processed[$key] = process_overrides($value);
			} else {
				$processed[$key] = $value;
			}
		}
	}

	return $processed;
}

// Recursively removes any keys from $settings that are null or ''
function remove_empty_settings($settings) {
	foreach ($settings as $key => $value) {
		if (is_array($value)) {
			$value = remove_empty_settings($value);
			if (!count($value)) {
				unset($settings[$key]);
			} else {
				$settings[$key] = $value;
			}
		} elseif ($value === null || $value === '') {
			unset($settings[$key]);
		}
	}

	return $settings;
}

// Recursively merge seetings arrays while skipping null and '' values
function merge_settings($settings, $override) {
	foreach ($override as $key => $value) {
		if (is_array($value)) {
			if (isset($settings[$key])) {
				$settings[$key] = merge_settings($settings[$key], $value);
			} else {
				$settings[$key] = $value;
			}
		} else {
			$settings[$key] = $value;
		}
	}

	return $settings;
}

// Get the settings for a block
function get_settings($block, $post_id = null) {
	// If $block is a string, convert it to an array
	if(is_string($block)) {
		$block = [
			'name' => $block,
			'anchor' => '',
			'className' => ''
		];
	}

	// For some reason get_field doesn't work when passing the post ID for the current post
	if($post_id === get_the_ID()) $post_id = null;

	// Get the block name by removing the lqx/ prefix
	$block_name = substr($block['name'], 4);

	// Initialize the settings array
	$settings = [
		'global' => get_field($block_name . '_block_global', 'option'),
		'styles' => get_field($block_name . '_block_styles', 'option'),
		'presets' => get_field($block_name . '_block_presets', 'option'),
		'local' => [
			'user' => get_field($block_name . '_block_user', $post_id),
			'admin' => get_field($block_name . '_block_admin', $post_id)
		],
		'processed' => array_merge([
			'anchor' => isset($block['anchor']) ? esc_attr($block['anchor']) : '',
			'class' => isset($block['className']) ? $block['className'] : '',
			'hash' => 'id-' . md5(json_encode([get_the_ID(), $block, random_int(1000, 9999)])) // Generate a unique hash for the block
		], get_field($block_name . '_block_global', 'option'))
	];

	// Check for user settings
	if ($settings['local']['user'] !== null) {
		// Style presets
		if ($settings['local']['user']['style']) $settings['processed']['class'] .= ' ' . $settings['local']['user']['style'];

		// Check for settings presets
		if (isset($settings['local']['user']['preset']) && $settings['local']['user']['preset'] !== '') {
			foreach ($settings['presets'] as $preset) {
				if ($preset['preset_name'] == $settings['local']['user']['preset']) {
					// Process the overrides
					$settings['processed'] = merge_settings($settings['processed'], remove_empty_settings(process_overrides($preset[$block_name . '_block_admin'])));
					break;
				}
			}
		}
	}

	// Check for admin settings
	if (!empty($settings['local']['admin'])) {
		// Process the overrides
		$settings['processed'] = merge_settings($settings['processed'], remove_empty_settings(process_overrides($settings['local']['admin'])));
	}

	return $settings;
}

function get_content($block, $post_id = null) {
	// If $block is a string, convert it to an array
	if(is_string($block)) $block = ['name' => $block];

	// Remove the lqx/ prefix from the block name
	$block_name = substr($block['name'], 4);

	// For some reason get_field doesn't work when passing the post ID for the current post
	if($post_id === get_the_ID()) $post_id = null;

	return get_field($block_name . '_block_content', $post_id);
}

// Register the Lyquix Modules block category
add_filter('block_categories_all', function ($categories) {
	$categories[] = [
		'slug'  => 'lqx-module-blocks',
		'title' => 'Lyquix Modules'
	];

	return $categories;
});

// Register ACF blocks
add_action('init', function () {
	// Use glob to find 'block.json' files in the 'blocks' directory
	$matches = array_merge(glob(__DIR__ . '/blocks/*/block.json'), glob(__DIR__ . 'custom/blocks/*/block.json'));

	// Check if any matches were found
	if (!empty($matches)) {
		foreach ($matches as $match) {
			// Get the directory name for each match
			register_block_type(dirname($match));
		}
	}
});

// Set the Style Preset values for the Lyquix Modules
add_filter('acf/load_field', function ($field) {
	$field_keys = [
		// Accordion
		[ // sstyle and style_name fields
			'user' => 'field_656c9b99e9e1f',
			'choice' => 'field_656e7cb6b285f'
		],
		[ // preset and preset_name fields
			'user' => 'field_656c9bb1e9e20',
			'choice' => 'field_656d01578aa30'
		],
		// Banner
		[ // style and style_name fields
			'user' => 'field_657727e6739ff',
			'choice' => 'field_65806108a3e5d'
		],
		[ // preset and preset_name fields
			'user' => 'field_657727e673a6d',
			'choice' => 'field_656d01578aa30'
		],
		// Hero
		[ // style and style_name fields
			'user' => 'field_657217f48ca53',
			'choice' => 'field_657761228bf9d'
		],
		[ // preset and preset_name fields
			'user' => 'field_657218058ca54',
			'choice' => 'field_657766304a9cc'
		],
		// Gallery
		[ // style and style_name fields
			'user' => 'field_6577582aed940',
			'choice' => 'field_65806199e7ed0'
		],
		[ // preset and preset_name fields
			'user' => 'field_6577582aed9a8',
			'choice' => 'field_658061d6e7ed2'
		],
		// Tabs
		[ //  style and style_name fields
			'user' => 'field_656f866617343',
			'choice' => 'field_656f879ccf606'
		],
		[ // preset and preset_name fields
			'user' => 'field_656f866617344',
			'choice' => 'field_656f87fcef854'
		]
	];

	foreach ($field_keys as $k) {
		if ($field['key'] == $k['user']) {
			$choice_field = get_field_object($k['choice']);

			// Add an empty choice
			$field['choices'][''] = 'Select';

			while (have_rows($choice_field['parent'], 'option')) {
				the_row();
				$value = get_sub_field($k['choice'], 'option');
				$field['choices'][$value] = $value;
			}
		}
	}
	return $field;
});

