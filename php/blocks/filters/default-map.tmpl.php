<?php
/**
 * default-map.tmpl.php - Default template for the Lyquix Filters block, map sub-template
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
//  Instead, copy it to /php/custom/blocks/filters/default-map.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-map.tmpl.php

if ($s['render_map_php']['show_map'] == 'y') {
	// For settings we need to get the preset settings from cards.
	$map_settings = \lqx\blocks\get_settings('map', null, $s['render_map_php']['map_preset'], $s['render_map_php']['map_style']);

	// Change the hash to use the same as the filters
	$map_settings['processed']['hash'] = $s['hash'] . '-map';

	// Add class 'map' to the classes array
	$map_settings['processed']['class'] = 'map';

	// Render the map
	\lqx\blocks\render_block($map_settings, $s['posts']);
}
?>
