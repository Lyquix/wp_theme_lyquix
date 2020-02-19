<?php
/**
 * widgets.php - Setups the theme widget areas
 *
 * @version     2.2.2
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

function lqx_widgets() {
	$widgets = [
		[
			'name' => 'Head Scripts',
			'naked' => true
		],
		['name' => 'Header',],
		['name' => 'Utility'],
		['name' => 'Top'],
		['name' => 'Left'],
		['name' => 'Center'],
		['name' => 'Right'],
		['name' => 'Before'],
		['name' => 'After'],
		['name' => 'Aside'],
		['name' => 'Previous'],
		['name' => 'Next'],
		['name' => 'Footer'],
		['name' => 'Bottom'],
		['name' => 'Copyright'],
		[
			'name' => 'Body Scripts',
			'naked' => true
		]
	];

	foreach($widgets as $widget) {
		register_sidebar([
			'name' => __($widget['name'], 'lyquix'),
			'id' => array_key_exists('id', $widget) ? $widget['id'] : str_replace(' ', '-', strtolower($widget['name'])),
			'description' => '',
			'class' => '',
			'before_widget' => array_key_exists('naked', $widget) ? '' : '<section class="widget %2$s">',
			'after_widget' => array_key_exists('naked', $widget) ? '' : '</section>',
			'before_title' => array_key_exists('naked', $widget) ? '' : '<h2 class="widget-title">',
			'after_title' => array_key_exists('naked', $widget) ? '' : '</h2>'
		]);
	}
}

/* DO NOT MODIFY THIS FILE! If you need custom functions, add them to /php/custom/functions.php */
