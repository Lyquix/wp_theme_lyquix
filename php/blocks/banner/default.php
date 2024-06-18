<?php

/**
 * default.php - Render function for Lyquix hero block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/banner/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/banner/{preset}.php

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

// Filter out any content missing heading or content
$c = \lqx\util\validate_data($content, [
	'type' => 'object',
	'keys' => [
		'heading' => \lqx\util\schema_str_req_emp,
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


// If no content, return
if (!$c['heading'] && !$c['image'] && !$c['links'] && !$c['intro_text']) return;

// Video attributes
$video_attrs = '';
if ($c['video']['type'] == 'url' && $c['video']['url']) {
	$video = \lqx\util\get_video_urls($c['video']['url']);
	if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
		'name' => str_replace('id-', 'banner-video-', $s['hash']),
		'type' => 'video',
		'url' => $c['video']['url'],
		'useHash' => false
	])));
}

require \lqx\blocks\get_template('banner', $s['preset']);
