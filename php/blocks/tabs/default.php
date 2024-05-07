<?php

/**
 * default.php - Render function for Lyquix tabs block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/tabs/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/tabs/{preset}.php

namespace lqx\blocks\tabs;

/**
 * Render function for Lyquix tabs block
 *
 * @param array $settings - block settings
 * @param array $content - block content
 *
 * anchor: The anchor of the tabs
 * class: Additional classes to add to the tabs
 * hash: A unique hash of the tabs
 * heading_style: Sets the heading level for the tabs headings
 * browser_history: Controls if interacting with a tab will add a history entry to the browser
 * convert_to_accordion: Controls if tabs will be converted to an accordion in mobile
 * auto_scroll: Sets in what screen sizes the tabs will auto scroll to the top of the open tab
 */
function render($settings, $content) {
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
				'default' => 'id-' . md5(json_encode([$settings, $content, random_int(1000, 9999)]))
			],
			'heading_style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'h3',
				'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']
			],
			'browser_history' => \lqx\util\schema_str_req_n,
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
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid block settings: ' . var_export($s, true));

	// Get content and filter our invalid content
	$c = array_filter(array_map(function($item) {
		$v = \lqx\util\validate_data($item, [
			'type' => 'object',
			'required' => true,
			'keys' => [
				'label' => \lqx\util\schema_str_req_notemp,
				'heading' => \lqx\util\schema_str_req_emp,
				'content' => \lqx\util\schema_str_req_notemp,
			]
		]);
		return $v['isValid'] ? $v['data'] : null;
	}, $content));

	$preset = $settings['local']['user']['preset'] ?? '';

	if (!empty($c)) require \lqx\blocks\get_template('tabs', $preset);
}
