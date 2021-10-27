<?php
/**
 * customizer.php - Set fields for theme customizer
 *
 * @version     2.3.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

function lqx_customizer_add($wp_customize) {
	$add_settings = [
		'Responsiveness' => [
			'min_screen' => [
				'type' => 'select',
				'label' => 'Minimum Screen Size',
				'choices' => ['0' => 'XS', '1' => 'SM', '2' => 'MD', '3' => 'LG', '4' => 'XL'],
				'default' => '0'
			],
			'max_screen' => [
				'type' => 'select',
				'label' => 'Maximum Screen Size',
				'choices' => ['0' => 'XS', '1' => 'SM', '2' => 'MD', '3' => 'LG', '4' => 'XL'],
				'default' => '4'
			]
		],
		'CSS' => [
			'non_min_css' => [
				'type' => 'radio',
				'label' => 'Use Original CSS',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'animatecss' => [
				'type' => 'radio',
				'label' => 'Load Animate.css',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'add_css_libraries' => [
				'type' => 'textarea',
				'label' => 'Additional CSS Libraries'
			],
			'remove_css_libraries' => [
				'type' => 'textarea',
				'label' => 'Remove CSS Libraries'
			]
		],
		'JS' => [
			'enable_jquery' => [
				'type' => 'radio',
				'label' => 'Enable jQuery',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'enable_jquery_ui' => [
				'type' => 'radio',
				'label' => 'Enable jQuery UI',
				'choices' => ['0' => 'No', '1' => 'Core', '2' => 'Core + Sortable'],
				'default' => '0'
			],
			'lqx_debug' => [
				'type' => 'radio',
				'label' => 'Enable lqx debug',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'non_min_js' => [
				'type' => 'radio',
				'label' => 'Use Original JS',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'lqx_options' => [
				'type' => 'textarea',
				'label' => 'Lyquix Library Options',
			],
			'scripts_options' => [
				'type' => 'textarea',
				'label' => 'Scripts Options',
			],
			'polyfill' => [
				'type' => 'radio',
				'label' => 'Use polyfill.io',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'lodash' => [
				'type' => 'radio',
				'label' => 'LoDash library',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'smoothscroll' => [
				'type' => 'radio',
				'label' => 'SmoothScroll library',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'momentjs' => [
				'type' => 'radio',
				'label' => 'Moment.js library',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'dotdotdot' => [
				'type' => 'radio',
				'label' => 'dotdotdot library',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'add_js_libraries' => [
				'type' => 'textarea',
				'label' => 'Additional JS Libraries'
			],
			'remove_js_libraries' => [
				'type' => 'textarea',
				'label' => 'Remove JS Libraries'
			]
		],
		'Accounts' => [
			'ga_account' => [
				'label' => 'Google Analytics Account',
			],
			'google_site_verification' => [
				'label' => 'google-site-verification',
			],
			'msvalidate' => [
				'label' => 'msvalidate.01',
			],
			'p_domain_verify' => [
				'label' => 'p:domain_verify',
			]
		],
		'IE' => [
			'ie9_alert' => [
				'type' => 'radio',
				'label' => 'IE9 alert',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'ie10_alert' => [
				'type' => 'radio',
				'label' => 'IE10 alert',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'ie11_alert' => [
				'type' => 'radio',
				'label' => 'IE11 alert',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			]
		]
	];

	foreach($add_settings as $section => $setting) {
		$wp_customize -> add_section('lqx_' . strtolower($section), [
			'title' => __($section, 'lyquix'),
			'priority' => 30,
		]);
		foreach($setting as $name => $options) {
			$wp_customize -> add_setting($name , [
				'type' => 'theme_mod',
				'transport' => 'refresh',
				'default' => array_key_exists('default', $options) ? $options['default'] : null
			]);
			$wp_customize -> add_control($name, [
				'type' => array_key_exists('type', $options) ? $options['type'] : null,
				'label' => __($options['label'], 'lyquix'),
				'section' => 'lqx_' . strtolower($section),
				'settings' => $name,
				'choices' => array_key_exists('choices', $options) ? $options['choices'] : null
			]);
		}
	}
}

/* DO NOT MODIFY THIS FILE! If you need custom functions, add them to /php/custom/functions.php */
