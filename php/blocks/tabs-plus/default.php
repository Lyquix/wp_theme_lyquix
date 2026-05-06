<?php

/**
 * default.php - Render function for Lyquix tabs block
 *
 * @version     3.1.0
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
//  If you need a custom renderer, copy this file to php/custom/blocks/tabs/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/tabs/{preset}.php

// Get the processed settings
$s = \lqx\util\validate_data($settings['processed'], [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'anchor' => \lqx\util\schema_str_req_emp,
		'class' => \lqx\util\schema_str_req_emp,
		'hash' => [
			'type' => 'string',
			'required' => true,
			'default' => 'id-' . substr(md5(json_encode([$settings, $content, random_int(1000, 9999)])), 24)
		],
		'browser_history' => \lqx\util\schema_str_req_n,
		'close_on_click' => \lqx\util\schema_str_req_n,
		'lazy_load' => \lqx\util\schema_str_req_y,
		'convert_to_accordion' => [
			'type' => 'array',
			'required' => true,
			'default' => [],
			'elems' => [
				'type' => 'string',
				'allowed' => ['xs', 'sm', 'md', 'lg', 'xl']
			]
		],
		'auto_scroll' => [
			'type' => 'array',
			'required' => true,
			'default' => [],
			'elems' => [
				'type' => 'string',
				'allowed' => ['xs', 'sm', 'md', 'lg', 'xl']
			]
		]
	]
]);

// If valid settings, use them, otherwise throw exception
$s = $s['isValid'] ? $s['data'] : null;
if (empty($s)) {
	if (\lqx\util\is_local_environment()) throw new \Exception('Invalid block settings: ' . var_export($settings['processed'], true));
	return;
}

require \lqx\blocks\get_template('tabs-plus', $s['preset']);
