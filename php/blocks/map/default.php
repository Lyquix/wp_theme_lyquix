<?php

/**
 * default.php - Render function for Lyquix map block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/map/{preset}.php

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
		'show_items' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'show_map' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'show_infowindows' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'show_get_directions_link' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'show_get_directions_box' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'show_search' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'show_distance_limit_drop_down' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y',
			'allowed' => ['y', 'n']
		],
		'map_vendor' => [
			'type' => 'string',
			'required' => true,
			'default' => 'google',
			'allowed' => ['google', 'esri']
		],
		'google_maps_display_settings' => [
			'type' => 'object',
			'default' => [],
			'elems' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'enable_zoom' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'enable_pan' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'default_zoom_level'=> [
						'type' => 'string',
						'required' => false,
						'default' => '15'
					],
					'type_of_map' => [
						'type' => 'string',
						'required' => true,
						'default' => 'road',
						'allowed' => ['road', 'satellite', 'terrain']
					],
					'snazzy_maps_styles' => [
						'type' => 'string',
						'required' => false,
						'default' => ''
					],
					'pin_override' => [
						'type' => 'array'
					]
				]
			]
		],
		'items_display_settings' => [
			'type' => 'object',
			'default' => [],
			'elems' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'show_heading' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'heading_style' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']
					],
					'heading_clickable' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'show_subheading' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'subtitle_style' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
					],
					'show_image' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'image_clickable' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'show_labels' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'show_phone_numbers' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'show_description' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
					'show_business_hours' => [
						'type' => 'string',
						'required' => true,
						'default' => 'y',
						'allowed' => ['y', 'n']
					],
				]
			]
		]
	]
]);
// If valid settings, use them, otherwise throw exception
if ($s['isValid']) $s = $s['data'];
else throw new \Exception('Invalid block settings: ' . var_export($s, true));
// Get content and filter out invalid content
$c = array_filter(array_map(function($item) {
	$v = \lqx\util\validate_data($item, [
		'type' => 'object',
		'keys' => [
			'location' => [
				'type' => 'object',
				'default' => [],
				'elems' => [
					'type' => 'object',
					'required' => true,
					'keys' => [
						'heading' => \lqx\util\schema_str_req_emp,
						'subheading' => \lqx\util\schema_str_req_emp,
						'address' => [
							'type' => 'object',
							'required' => true,
						],
						'display_address_override' => \lqx\util\schema_str_req_n,
						'lattitude_override' => \lqx\util\schema_str_req_n,
						'longitude_override' => \lqx\util\schema_str_req_n,
						'image' => [
							'type' => 'object',
							'default' => [],
							'keys' => \lqx\util\schema_data_image
						],
						'phone_numbers' => [
							'type' => 'object',
							'default' => [],
						],
						'description' => \lqx\util\schema_str_req_emp,
						'link' => [
							'type' => 'object',
							'default' => [],
							'keys' => \lqx\util\schema_data_link
						],
						'labels'=> [
							'type' => 'object',
							'default' => [],
						],
						'business_hours' => [
							'type' => 'object',
							'default' => [],
						],
						'pin_color' => \lqx\util\schema_str_req_n,
					],
					'additional_classes' => \lqx\util\schema_str_req_emp,
					'item_id' => \lqx\util\schema_str_req_emp,
				],
			],
		],
	]);
	return $v['isValid'] ? $v['data'] : null;
}, $content));

if (!empty($c)) require \lqx\blocks\get_template('map', $s['preset']);
