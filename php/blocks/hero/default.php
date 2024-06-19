<?php

/**
 * default.php - Render function for Lyquix hero block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/hero/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/hero/{preset}.php

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
		'show_image' => \lqx\util\schema_str_req_y,
		'breadcrumbs' => [
			'type' => 'object',
			'required' => true,
			'keys' => [
				'show_breadcrumbs' => \lqx\util\schema_str_req_n,
				'type' => [
					'type' => 'string',
					'required' => true,
					'default' => 'parent',
					'allowed' => ['parent', 'category', 'post-type', 'post-type-category']
				],
				'depth' => [
					'type' => 'integer',
					'required' => true,
					'default' => 3,
					'range' => [1, 5]
				],
				'show_current' => \lqx\util\schema_str_req_n
			]
		]
	]
]);

// If valid settings, use them, otherwise throw exception
if ($s['isValid']) $s = $s['data'];
else throw new \Exception('Invalid block settings: ' . var_export($s, true));

// Filter out any content missing heading or content
$c = \lqx\util\validate_data($content, [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'breadcrumbs_override' => \lqx\util\schema_str_req_emp,
		'heading_override' => \lqx\util\schema_str_req_emp,
		'image_override' => [
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
		'intro_text' => \lqx\util\schema_str_req_emp
	]
]);

// If valid content, use it, otherwise return
if ($c['isValid']) $c = $c['data'];
else return;

// Video attributes
$video_attrs = '';
if ($c['video']['type'] == 'url' && $c['video']['url']) {
	$video = \lqx\util\get_video_urls($c['video']['url']);
	if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
		'name' => str_replace('id-', 'hero-video-', $s['hash']),
		'type' => 'video',
		'url' => $c['video']['url'],
		'useHash' => false
	])));
}

// Breadcrumbs
$breadcrumbs = '';
if ($s['breadcrumbs']['show_breadcrumbs'] == 'y') {
	$breadcrumbs = '<div class="breadcrumbs">';
	if ($c['breadcrumbs_override'] !== '') {
		$breadcrumbs .= $c['breadcrumbs_override'];
	} else {
		$breadcrumbs .= implode(' &raquo; ', array_map(function ($b) {
			if ($b['url']) return sprintf('<a href="%s">%s</a>', esc_attr($b['url']), $b['title']);
			else return $b['title'];
		}, \lqx\util\get_breadcrumbs(get_the_ID(), $s['breadcrumbs']['type'], $s['breadcrumbs']['depth'], $s['breadcrumbs']['show_current'])));
	}
	$breadcrumbs .= '</div>';
}

require \lqx\blocks\get_template('hero', $s['preset']);
