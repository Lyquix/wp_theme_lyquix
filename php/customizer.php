<?php

/**
 * customizer.php - Set fields for theme customizer
 *
 * @version     2.3.3
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

namespace lqx;

function customizer_add($wp_customize) {
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
				'choices' => ['0' => 'None', '1' => 'Errors', '2' => 'Warnings', '3' => 'Info'],
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
			'dayjs' => [
				'type' => 'radio',
				'label' => 'Day.js library',
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
		'Analytics' => [
			'ga4_account' => [
				'label' => 'Google Analytics 4 Measurement ID',
			],
			'ga_pageview' => [
				'type' => 'radio',
				'label' => 'Send Google Analytics Pageview',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'ga_via_gtm' => [
				'type' => 'radio',
				'label' => 'Google Analytics loaded via GTM',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'gtm_account' => [
				'label' => 'Google Tag Manager Account',
			],
			'clarity_account' => [
				'label' => 'Microsoft Clarity Project ID',
			],
		],
		'Accounts' => [
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
		'Browser Alert' => [
			'browser_alert' => [
				'type' => 'radio',
				'label' => 'Enable Browser Alert',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'accepted_browser_versions' => [
				'type' => 'radio',
				'label' => 'Acceptable Browser Versions',
				'choices' => ['1' => 'Only Lastest', '2' => 'Last 2', '3' => 'Last 3', '4' => 'Last 4', '5' => 'Last 5'],
				'default' => '3'
			]
		]
	];

	foreach ($add_settings as $section => $setting) {
		$wp_customize->add_section('lqx_' . strtolower($section), [
			'title' => __($section, 'lyquix'),
			'priority' => 30,
		]);
		foreach ($setting as $name => $options) {
			$wp_customize->add_setting($name, [
				'type' => 'theme_mod',
				'transport' => 'refresh',
				'default' => array_key_exists('default', $options) ? $options['default'] : null
			]);
			$wp_customize->add_control($name, [
				'type' => array_key_exists('type', $options) ? $options['type'] : null,
				'label' => __($options['label'], 'lyquix'),
				'section' => 'lqx_' . strtolower($section),
				'settings' => $name,
				'choices' => array_key_exists('choices', $options) ? $options['choices'] : null
			]);
		}
	}
}

add_action('customize_register', 'lqx\customizer_add');
