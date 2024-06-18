<?php

/**
 * default.php - Lyquix social icons module render functions
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
//  If you need a custom renderer, copy this file to php/custom/modules/social/default.php and modify it there

// Get settings
if ($settings == null) $settings = get_field('social_icons_module_settings', 'option');

// Validate settings
$s = \lqx\util\validate_data($settings, [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'links' => [
			'type' => 'array',
			'default' => [],
			'elems' => [
				'type' => 'object',
				'keys' => [
					'url' => \lqx\util\schema_str_req_notemp
				]
			]
		],
		'style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'square',
			'allowed' => ['square', 'circle', 'rounded']
		],
		'background_color' => \lqx\util\schema_hex_color,
		'icon_color' => \lqx\util\schema_hex_color,
		'hover_icon_color' => \lqx\util\schema_hex_color,
		'hover_background_color' => \lqx\util\schema_hex_color
	]
]);

// If valid settings, use them, otherwise throw exception
if ($s['isValid']) $s = $s['data'];
else throw new \Exception('Invalid module settings: ' . var_export($s, true));

// Check if there are any social links configured
if (!count($s['links'])) return;

require \lqx\modules\get_template('social');
