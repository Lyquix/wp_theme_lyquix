<?php

/**
 * default.php - Lyquix Filters module render functions
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
//  If you need a custom renderer, copy this file to php/custom/blocks/filters/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/filters/{preset}.php

namespace lqx\blocks\filters;

/**
 * Render the controls
 * THIS FUNCTION IS REQUIRED TO HANDLE PHP RENDERING FOR API CALLS
 * @param  array $settings Block settings
 */
function render_controls($s) {
	if ($s['render_mode'] == 'cards') return \lqx\filters\render_controls($s);
	// Custom controls rendering here
}

/**
 * Render the posts
 * THIS FUNCTION IS REQUIRED TO HANDLE PHP RENDERING FOR API CALLS
 * @param  array $settings Block settings
 */
function render_posts($s) {
	if ($s['render_mode'] == 'cards') return \lqx\filters\render_posts($s);
	// Custom posts rendering here
}

/**
 * Render the pagination
 * THIS FUNCTION IS REQUIRED TO HANDLE PHP RENDERING FOR API CALLS
 * @param  array $settings Block settings
 */
function render_pagination($s) {
	if ($s['render_mode'] == 'cards') return \lqx\filters\render_pagination($s);
	// Custom pagination rendering here
}

/**
 * Render the filters block
 * @param  array $settings Block settings
 */
function render($settings) {
	// Return if no preset has been selected
	if ($settings['processed']['preset'] == '') return;

	// Get the processed settings and posts with data
	$s = \lqx\filters\get_settings_and_posts($settings);

	require \lqx\blocks\get_template('filters', $s['preset']);
}
