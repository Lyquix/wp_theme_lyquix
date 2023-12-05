<?php

/**
 * widgets.php - Setups the theme widget areas
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
//  If you need to add custom widget positions, use /php/custom/widgets.php
//  to add entries to the $widgets array

namespace lqx\widgets;

function add_widget_positions() {
	$widget_positions = [
		'Head Scripts',
		'Header',
		'Utility',
		'Top',
		'Left',
		'Center',
		'Right',
		'Before',
		'After',
		'Aside',
		'Previous',
		'Next',
		'Footer',
		'Bottom',
		'Copyright',
		'Body Scripts',
	];

	// Add custom widget positions to $widgets array
	if (file_exists(get_template_directory() . '/php/custom/widgets.php')) {
		require get_template_directory() . '/php/custom/widgets.php';

		if(count($custom_widget_positions)) {
			$widget_positions = array_merge($widget_positions, $custom_widget_positions);
		}
	}

	foreach ($widget_positions as $widget) {
		register_sidebar([
			'name' => __($widget, 'lyquix'),
			'id' => preg_replace('/[^a-z0-9]+/', '-', strtolower($widget)),
			'description' => '',
			'class' => '',
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => ''
		]);
	}
}

add_action('widgets_init', '\lqx\widgets\add_widget_positions');
