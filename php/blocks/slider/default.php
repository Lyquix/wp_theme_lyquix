<?php

/**
 * default.php - Render function for Lyquix hero block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/slider/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/slider/{preset}.php

namespace lqx\blocks\slider;

/**
 * Render function for Lyquix Slider block
 *
 * @param array $content - block content
 */
function render($settings, $content) {
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
				'default' => 'id-' . md5(json_encode([$settings, $content, random_int(1000, 9999)]))
			],
			'heading_style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'h3',
				'allowed' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
			],
			'autoplay' => \lqx\util\schema_str_req_y,
			'autoplay_delay' => [
				'type' => 'integer',
				'required' => true,
				'default' => 15,
				'range' => [0, 60]
			],
			'swiper_options_override' => \lqx\util\schema_str_req_emp,
			'loop' => \lqx\util\schema_str_req_y,
			'pagination' => \lqx\util\schema_str_req_y,
			'navigation' => \lqx\util\schema_str_req_y
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid block settings: ' . var_export($s, true));

	// Get content and filter our invalid content
	$c = array_filter(array_map(function($item) {
		$v = \lqx\util\validate_data($item, [
			'type' => 'object',
			'required' => true,
			'keys' => [
				'image' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_image
				],
				'image_mobile' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_image
				],
				'video' => [
					'type' => 'object',
					'keys' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'default' => 'url',
							'allowed' => ['url', 'upload']
						],
						'url' => \lqx\util\schema_str_req_emp,
						'upload' => [
							'type' => 'object',
							'default' => [],
							'keys' => \lqx\util\schema_data_video
						]
					]
				],
				'image_link' => \lqx\util\schema_str_req_emp,
				'links' => [
					'type' =>	'array',
					'default' => [],
					'elems' => [
						'type' => 'object',
						'keys' => [
							'type' => [
								'type' => 'string',
								'required' => true,
								'default' => 'button',
								'allowed' => ['button', 'link']
							],
							'link' => [
								'type' => 'object',
								'required' => true,
								'keys' => \lqx\util\schema_data_link
							]
						]
					]
				],
				'heading' => \lqx\util\schema_str_req_emp,
				'body' => \lqx\util\schema_str_req_emp,
				'thumbnail' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_image
				],
				'teaser_text' => \lqx\util\schema_str_req_emp
			]
		]);
		return $v['isValid'] ? $v['data'] : null;
	}, $content));

	if (!empty($c)) require \lqx\blocks\get_template('slider', $s['preset']);
}
