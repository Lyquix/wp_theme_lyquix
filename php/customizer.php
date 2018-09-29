<?php
/**
 * customizer.php - Set fields for theme customizer
 *
 * @version     2.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

function lqx_customizer_add($wp_customize) {
	$add_settings = array(
		'Responsiveness' => array(
			'min_screen' => array(
				'type' => 'select',
				'label' => 'Minimum Screen Size',
				'choices' => array('0' => 'XS', '1' => 'SM', '2' => 'MD', '3' => 'LG', '4' => 'XL'),
				'default' => '0'
			),
			'max_screen' => array(
				'type' => 'select',
				'label' => 'Maximum Screen Size',
				'choices' => array('0' => 'XS', '1' => 'SM', '2' => 'MD', '3' => 'LG', '4' => 'XL'),
				'default' => '4'
			)
		),
		'CSS' => array(
			/*
			'merge_css_local' => array(
				'type' => 'checkbox',
				'label' => 'Merge CSS: Local CSS Files'
			),
			'merge_css_remote' => array(
				'type' => 'checkbox',
				'label' => 'Merge CSS: Remote CSS Files'
			),
			'merge_css_inline' => array(
				'type' => 'checkbox',
				'label' => 'Merge CSS: Inline CSS Declarations'
			),
			*/
			'non_min_css' => array(
				'type' => 'radio',
				'label' => 'Use Original CSS',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'animatecss' => array(
				'type' => 'radio',
				'label' => 'Load Animate.css',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'add_css_libraries' => array(
				'type' => 'textarea',
				'label' => 'Additional CSS Libraries'
			),
			'remove_css_libraries' => array(
				'type' => 'textarea',
				'label' => 'Remove CSS Libraries'
			)
		),
		'JS' => array(
			'enable_jquery' => array(
				'type' => 'radio',
				'label' => 'Enable jQuery',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '1'
			),
			'enable_jquery_ui' => array(
				'type' => 'radio',
				'label' => 'Enable jQuery UI',
				'choices' => array('0' => 'No', '1' => 'Core', '2' => 'Core + Sortable'),
				'default' => '0'
			),
			/*
			'merge_js_local' => array(
				'type' => 'checkbox',
				'label' => 'Merge JS: Local JS Files'
			),
			'merge_js_remote' => array(
				'type' => 'checkbox',
				'label' => 'Merge JS: Remote JS Files'
			),
			'merge_js_inline' => array(
				'type' => 'checkbox',
				'label' => 'Merge JS: Inline JS Declarations'
			),
			*/
			'lqx_debug' => array(
				'type' => 'radio',
				'label' => 'Enable lqx debug',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'non_min_js' => array(
				'type' => 'radio',
				'label' => 'Use Original JS',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'lqx_options' => array(
				'label' => 'Lyquix Library Options',
			),
			'polyfill' => array(
				'type' => 'radio',
				'label' => 'Use polyfill.io',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '1'
			),
			'lodash' => array(
				'type' => 'radio',
				'label' => 'LoDash library',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'smoothscroll' => array(
				'type' => 'radio',
				'label' => 'SmoothScroll library',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'momentjs' => array(
				'type' => 'radio',
				'label' => 'Moment.js library',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'dotdotdot' => array(
				'type' => 'radio',
				'label' => 'dotdotdot library',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			),
			'add_js_libraries' => array(
				'type' => 'textarea',
				'label' => 'Additional JS Libraries'
			),
			'remove_js_libraries' => array(
				'type' => 'textarea',
				'label' => 'Remove JS Libraries'
			)
		),
		'Accounts' => array(
			'ga_account' => array(
				'label' => 'Google Analytics Account',
			),
			'google_site_verification' => array(
				'label' => 'google-site-verification',
			),
			'msvalidate' => array(
				'label' => 'msvalidate.01',
			),
			'p_domain_verify' => array(
				'label' => 'p:domain_verify',
			)
		),
		'IE' => array(
			'ie9_alert' => array(
				'type' => 'radio',
				'label' => 'IE9 alert',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '1'
			),
			'ie10_alert' => array(
				'type' => 'radio',
				'label' => 'IE10 alert',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '1'
			),
			'ie11_alert' => array(
				'type' => 'radio',
				'label' => 'IE11 alert',
				'choices' => array('0' => 'No', '1' => 'Yes'),
				'default' => '0'
			)
		)
	);

	foreach($add_settings as $section => $setting) {
		$wp_customize -> add_section('lqx_' . strtolower($section), array(
			'title' => __($section, 'lyquix'),
			'priority' => 30,
		));
		foreach($setting as $name => $options) {
			$wp_customize -> add_setting($name , array(
				'type' => 'theme_mod',
				'transport' => 'refresh',
				'default' => array_key_exists('default', $options) ? $options['default'] : null
			));
			$wp_customize -> add_control($name, array(
				'type' => array_key_exists('type', $options) ? $options['type'] : null,
				'label' => __($options['label'], 'lyquix'),
				'section' => 'lqx_' . strtolower($section),
				'settings' => $name,
				'choices' => array_key_exists('choices', $options) ? $options['choices'] : null
			));
		}
	}
}

/* DO NOT MODIFY THIS FILE! If you need custom functions, add them to /php/custom/functions.php */
