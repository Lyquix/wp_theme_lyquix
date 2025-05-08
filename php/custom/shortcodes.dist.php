<?php

/**
 * shortcodes.dist.php - Add custom shortcodes
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
//  Instead copy this file to /php/custom/shortcodes.php
//  and add the shortcodes in there

add_shortcode('lqx-shortcode-tag', function ($atts, $content = null) {
	// $defaults is an associative array that specifies the recognized attribute names and their default values.
	$defaults = [];
	$atts = shortcode_atts($defaults, $atts);

	// $content is the text between the opening and closing shortcode tags.
	$html = do_shortcode($content); // Processed any shortcodes in $content

	// Add your shortcode markup and logic here

	return $html;
});
