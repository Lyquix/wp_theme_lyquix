<?php

/**
 * default.php - Render function for Lyquix testimonial block
 *
 * @version     3.2.0
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
//  If you need a custom renderer, copy this file to php/custom/blocks/testimonial/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/testimonial/{preset}.php

// Return if there is no content
if ($content === false) return;

// Get and validate processed settings
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
		'slider' => \lqx\util\schema_str_req_n,
		'random_display' => \lqx\util\schema_str_req_n
	]
]);

// If valid settings, use them, otherwise throw exception
$s = $s['isValid'] ? $s['data'] : null;
if (empty($s)) {
	if (\lqx\util\is_local_environment()) throw new \Exception('Invalid block settings: ' . var_export($settings['processed'], true));
	return;
}

// Get the top level items first
$c = \lqx\util\validate_data($content, [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'testimonials' => [
			'type' => 'array',
			'required' => true,
			'default' => []
		]
	]
])['data'];

// Get content and filter our invalid content
$c['testimonials'] = array_filter(array_map(function ($item) {
	$v = \lqx\util\validate_data($item, [
		'type' => 'object',
		'keys' => [
			'config' => [
				'type' => 'array',
				'required' => true,
				'default' => []
			],
			'content' => \lqx\util\schema_str
		]
	]);

	$v['data']['config'] = \lqx\util\validate_data($item['config'], [
		'type' => 'object',
		'keys' => [
			'background_color' => \lqx\util\schema_str,
			'foreground_color' => \lqx\util\schema_str,
			'image' => [
				'type' => 'object',
				'default' => [],
				'keys' => \lqx\util\schema_data_image
			],
			'name' => \lqx\util\schema_str,
			'job_title' => \lqx\util\schema_str,
			'additional_classes' => \lqx\util\schema_str_req_emp,
			'item_id' => \lqx\util\schema_str_req_emp
		]
	])['data'];

	// Skip if data is not valid
	if (!$v['isValid']) return null;

	$v['data'] = array_merge($v['data'], $v['data']['config']);
	unset($v['data']['config']);

	return $v['data'];
}, $c['testimonials']));

if (!empty($c)) require \lqx\blocks\get_template('testimonial', $s['preset']);
