<?php
/**
 * widgets.php - Setups the theme widget areas
 *
 * @version     2.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

function lqx_widgets() {
	$widgets = array(
		array(
			'name' => 'Head Scripts',
			'naked' => true
		),
		array('name' => 'Header',),
		array('name' => 'Utility'),
		array('name' => 'Top'),
		array('name' => 'Left'),
		array('name' => 'Center'),
		array('name' => 'Right'),
		array('name' => 'Before'),
		array('name' => 'After'),
		array('name' => 'Aside'),
		array('name' => 'Previous'),
		array('name' => 'Next'),
		array('name' => 'Footer'),
		array('name' => 'Bottom'),
		array('name' => 'Copyright'),
		array(
			'name' => 'Body Scripts',
			'naked' => true
		)
	);

	foreach($widgets as $widget) {
		register_sidebar(array(
			'name' => __($widget['name'], 'lyquix'),
			'id' => array_key_exists('id', $widget) ? $widget['id'] : str_replace(' ', '-', strtolower($widget['name'])),
			'description' => '',
			'class' => '',
			'before_widget' => array_key_exists('naked', $widget) ? '' : '<section class="widget %2$s">',
			'after_widget' => array_key_exists('naked', $widget) ? '' : '</section>',
			'before_title' => array_key_exists('naked', $widget) ? '' : '<h2 class="widget-title">',
			'after_title' => array_key_exists('naked', $widget) ? '' : '</h2>'
		));
	}
}

/* DO NOT MODIFY THIS FILE! If you need custom functions, add them to /php/custom/functions.php */
