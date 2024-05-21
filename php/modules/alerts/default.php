<?php

/**
 * default.php - Lyquix alerts module render function
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
//  If you need a custom renderer, copy this file to php/custom/modules/alerts/default.php and modify it there

// Get settings
if ($settings == null) $settings = get_field('alerts_module_settings', 'option');

// Validate the settings
$s = \lqx\util\validate_data($settings, [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'autoplay' => \lqx\util\schema_str_req_y,
		'autoplay_delay' => [
			'type' => 'integer',
			'required' => true,
			'default' => 15,
			'range' => [0, 60]
		],
		'swiper_options_override' => \lqx\util\schema_str_req_emp,
		'heading_style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'h3',
			'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']
		]
	]
]);

// If valid settings, use them, otherwise throw exception
if ($s['isValid']) $s = $s['data'];
else throw new \Exception('Invalid block settings: ' . var_export($s, true));

require \lqx\modules\get_template('alerts');
