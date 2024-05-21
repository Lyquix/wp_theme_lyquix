<?php

/**
 * default.php - Lyquix CTA module render functions
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
//  If you need a custom renderer, copy this file to php/custom/modules/cta/default.php and modify it there

if ($settings == null) $settings = get_field('cta_module_settings', 'option');
if ($content == null) $content = get_field('cta_module_content', 'option');

// Check if there are any CTAs configured
if (empty($content)) return;

// Validate the settings
$s = \lqx\util\validate_data($settings, [
	'type' => 'object',
	'required' => true,
	'keys' => [
		'cta_block_styles' => [
			'type' => 'array',
			'required' => true,
			'default' => []
		],
		'heading_style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'p',
			'allowed' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
		],
	]
]);

// If valid settings, use them, otherwise throw exception
if ($s['isValid']) $s = $s['data'];
else throw new \Exception('Invalid block settings: ' . var_export($s, true));

// Get content and filter our invalid content
$c = array_filter(array_map(function($item) use($s) {
	$v = \lqx\util\validate_data($item, [
		'type' => 'object',
		'required' => true,
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
			'content' => \lqx\util\schema_str_req_emp,
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
			'enabled' => \lqx\util\schema_str_req_y,
			'slim_cta' => \lqx\util\schema_str_req_n,
			'display_logic' => [
				'type' => 'string',
				'required' => true,
				'default' => 'show',
				'allowed' => ['show', 'hide']
			],
			'display_exceptions' => [
				'type' => 'array',
				'required' => true,
				'default' => [],
				'elems' => [
					'type' => 'object',
					'keys' => [
						'url_pattern' => \lqx\util\schema_str_req_notemp
					]
				]
			],
			'style' => \lqx\util\schema_str_req_emp
		]
	]);

	// If invalid, skip
	if (!$v['isValid']) return null;

	// If not enabled, skip
	if ($v['data']['enabled'] != 'y') return null;

	// If there's no content, skip
	if (!$v['data']['heading'] && !$v['data']['content'] && !$v['data']['image']) return null;

	// Skip if display logic and exceptions are not met
	$display = true;
	if ($v['data']['display_logic'] == 'hide') $display = false;

	if (count($v['data']['display_exceptions'])) {
		foreach($v['data']['display_exceptions'] as $e) {
			if (array_key_exists('url_pattern', $e)) {
				if ($e['url_pattern']) {
					// Escape the URL for use in regex
					$url_pattern = preg_quote($e['url_pattern'], '/');
					// Replace \* with .*
					$url_pattern = str_replace('\\*', '.*', $url_pattern);
					// Check if the URL matches the pattern
					if (preg_match('/^' . $url_pattern . '$/', $_SERVER['REQUEST_URI'])) $display = !$display;
				}
			}
		}
	}

	if (!$display) return null;

	// Check style value
	if (!in_array($v['data']['style'], $s['cta_block_styles'])) $v['data']['style'] = '';

	return $v['data'];
}, $content));

// The active CTA
if (count($c) > 0) $cta = $c[0];
else return;

\lqx\modules\get_template('cta');
