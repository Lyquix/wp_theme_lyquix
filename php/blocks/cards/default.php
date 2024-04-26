<?php

/**
 * default.php - Render function for Lyquix cards block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/cards/slider/{preset}.php

namespace lqx\blocks\cards;

/**
 * Render function for Lyquix cards block
 *
 * @param array $settings - block settings
 *              processed: Processed settings
 *                 anchor: A custom id for the cards block
 *                 class: Additional classes to add to the cards block
 *                 hash: A unique hash of the cards block
 *                 slider: Whether to use a slider
 *                 swiper_options_override: Swiper options override
 *                 heading_style: Style of the heading
 *                 subheading_style: Style of the subheading
 *                 heading_clickable: Whether the heading is clickable
 *                 image_clickable: Whether the image is clickable
 *                 responsive_rules: Responsive rules (array of objects)
 *                     screens: Array of screens to apply the rule to (xs, sm, md, lg, xl)
 *                     columns: Number of columns
 *                     image_position: Image position (left, right, top)
 *                     icon_image_position: Icon image position (left, right, center)
 *
 * @param array $content - array of cards
 *              heading: Heading
 *              subheading: Subheading
 *              image: Image
 *              icon_image: Icon image
 *              video: Video
 *              body: Body
 *              links: Links
 *              labels: Labels
 *
 */
function render($settings, $content) {
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

	// Get content and filter our invalid content
	$c = array_filter(array_map(function($item) {
		$v = \lqx\util\validate_data($item, [
			'type' => 'object',
			'keys' => [
				'heading' => \lqx\util\schema_str_req_emp,
				'subheading' => \lqx\util\schema_str_req_emp,
				'image' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_image
				],
				'icon_image' => [
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
							'default' => 'url'
						],
						'url' => \lqx\util\schema_str_req_emp,
						'upload' => [
							'type' => 'object',
							'default' => [],
							'keys' => \lqx\util\schema_data_video
						]
					]
				],
				'body' => \lqx\util\schema_str_req_emp,
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
				'labels' => [
					'type' =>	'array',
					'default' => [],
					'elems' => [
						'type' => 'object',
						'required' => true,
						'keys' => [
							'label' => \lqx\util\schema_str_req_emp,
							'value' => \lqx\util\schema_str_req_emp
						]
					]
				]
			]
		]);
		return $v['isValid'] ? $v['data'] : null;
	}, $content));

	$preset = $settings['local']['user']['preset'];

	if (!empty($c)) require \lqx\blocks\get_template('cards', $preset);
}
