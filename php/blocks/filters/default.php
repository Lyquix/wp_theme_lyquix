<?php

/**
 * default.php - Lyquix Filters module render functions
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
//  If you need a custom renderer, copy this file to php/custom/blocks/filters/default.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/filters/{preset}.php

if ($settings['processed']['preset']) { // only proceed if a preset has been selected
	// Get the processed settings and posts with data
	// TODO we should add some validation to $s
	$s = \lqx\filters\get_settings_and_posts($settings);

	$c =\lqx\util\validate_data($content, [
		'type' => 'object',
		'keys' => [
			'heading_override' => \lqx\util\schema_str,
		]
	]);
	if ($c['isValid']) $c = $c['data'];

	// TODO if settings or content aren't valid, we should return here
	require \lqx\blocks\get_template('filters', $s['preset']);
}

// TODO: this whole thing needs some refactoring
