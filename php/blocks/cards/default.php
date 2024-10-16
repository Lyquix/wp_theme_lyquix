<?php

/**
 * default.php - Render function for Lyquix cards block
 *
 * @version     3.0.0
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/cards/slider/{preset}.php

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
		'swiper_options_override' => \lqx\util\schema_str_req_emp,
		'heading_style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'h3',
			'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']
		],
		'subheading_style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'p',
			'allowed' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
		],
		'heading_clickable' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'image_clickable' => \lqx\util\schema_str_req_y,
		'responsive_rules' => [
			'type' => 'array',
			'required' => true,
			'default' => [],
			'elems' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'screens' => [
						'type' => 'array',
						'required' => true,
						'default' => [],
						'elems' => [
							'type' => 'string',
							'required' => true,
							'allowed' => ['xs', 'sm', 'md', 'lg', 'xl']
						]
					],
					'columns' => [
						'type' => 'integer',
						'required' => true,
						'default' => 3,
						'range' => [1, 12]
					],
					'image_position' => [
						'type' => 'string',
						'required' => true,
						'allowed' => ['', 'left', 'right', 'top']
					],
					'icon_image_position' => [
						'type' => 'string',
						'required' => true,
						'allowed' => ['', 'left', 'right', 'center']
					]
				]
			]
		]
	]
]);

// If valid settings, use them, otherwise throw exception
if ($s['isValid']) $s = $s['data'];
else throw new \Exception('Invalid block settings: ' . var_export($s, true));

// Generate CSS classes for responsive rules
$css_classes = [];
foreach ($s['responsive_rules'] as $rule) {
	foreach ($rule['screens'] as $screen) {
		foreach ($rule as $prop => $value) {
			if ($prop === 'screens') continue;
			if ($value === '') continue;
			$css_classes[] = $screen . ':' . str_replace('_', '-', $prop) . '-' . $value;
		}
	}
}

// Get content and filter out invalid content
$c = array_filter(array_map(function($item) {
	$v = \lqx\util\validate_data($item, \lqx\cards\schema);
	return $v['isValid'] ? $v['data'] : null;
}, $content));

if (!empty($c)) require \lqx\blocks\get_template('cards', $s['preset']);
