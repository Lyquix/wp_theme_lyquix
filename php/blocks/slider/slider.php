<?php

/**
 * slider.php - Lyquix Slider Block
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

$settings = \lqx\blocks\get_settings($block);
$content = \lqx\blocks\get_content($block);

if (file_exists(get_stylesheet_directory() . '/php/custom/blocks/slider/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/blocks/slider/render.php';
} else {
	require_once get_stylesheet_directory() . '/php/blocks/slider/render.php';
}

\lqx\blocks\slider\render($settings, $content);