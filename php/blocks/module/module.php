<?php

/**
 * module.php - Lyquix module blocks common functions
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

namespace lqx\blocks\module;

// Process overrides for settings presets
function process_overrides($settings) {
	$processed = [];
	foreach ($settings as $key => $value) {
		// Check if the key ends in _group
		if (substr($key, -6) == '_group') {
			// Extract the key name by removing _group
			$sub_key = substr($key, 0, -6);

			// Check if the override is set to true
			if ($value[$sub_key . '_override'] === true) {
				// Get the override value
				$sub_value = $value[substr($key, 0, -6)];

				if ($sub_value) {
					$processed[$sub_key] = $sub_value;
				}
			}
		} elseif ($value) {
			$processed[$key] = $value;
		}
	}

	return $processed;
}

// Get the settings for a block
function get_settings($block, $post_id) {
	// Get the block name by removing the lqx/ prefix
	$block_name = substr($block['name'], 4);

	// Initialize the settings array
	$settings = [
		'global' => get_field($block_name . '_block_global', 'option'),
		'styles' => get_field($block_name . '_block_styles', 'option'),
		'presets' => get_field($block_name . '_block_presets', 'option'),
		'local' => [
			'user' => get_field($block_name . '_block_user'),
			'admin' => get_field($block_name . '_block_admin')
		],
		'processed' => array_merge([
			'anchor' => $block['anchor'] ? esc_attr($block['anchor']) : '',
			'class' => $block['className'] ? $block['className'] : '',
			'hash' => 'block_' . md5(json_encode([$post_id, $block])) // Generate a unique hash for the block
		], get_field($block_name . '_block_global', 'option'))
	];

	// Check for user settings
	if ($settings['local']['user'] !== null) {
		// Style presets
		if ($settings['local']['user']['style']) $settings['processed']['class'] .= ' ' . $settings['local']['user']['style'];

		// Check for settings presets
		if ($settings['local']['user']['presets'] !== '') {
			foreach ($settings['presets'] as $preset) {
				if ($preset['preset_name'] == $settings['local']['user']['presets']) {
					// Process the overrides
					$settings['processed'] = array_merge($settings['processed'], process_overrides($preset[$block_name . '_block_admin']));
					break;
				}
			}
		}
	}

	// Check for admin settings
	if ($settings['local']['admin'] !== null) {
		// Process the overrides
		$settings['processed'] = array_merge($settings['processed'], process_overrides($settings['local']['admin']));
	}

	return $settings;
}

function get_content($block) {
	// Remove the lqx/ prefix from the block name
	$block_name = substr($block['name'], 4);

	return get_field($block_name . '_block_content');
}
