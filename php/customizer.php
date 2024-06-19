<?php

/**
 * customizer.php - Set fields for theme customizer
 *
 * @version     3.0.0
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

namespace lqx\customizer;

/**
 * Add customizer fields
 *
 * @param WP_Customize_Manager $wp_customize - The customizer object
 *
 * @return void
 */
function customizer_add($wp_customize) {
	$add_settings = [
		'CSS' => [
			'non_min_css' => [
				'type' => 'radio',
				'label' => 'Use Original CSS (non-minified)',
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
				'choices' => ['0' => 'None', '1' => 'Errors', '2' => 'Errors, Warnings', '3' => 'Errors, Warnings, Info'],
				'default' => '0'
			],
			'non_min_js' => [
				'type' => 'radio',
				'label' => 'Use Original JS (non-minified)',
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
				'default' => '1'
			],
			'swiperjs' => [
				'type' => 'radio',
				'label' => 'Swiper library',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
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
				'label' => 'Google Analytics 4 Account (Measurement ID)',
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
		'Meta Tags' => [
			'google_site_verification' => [
				'label' => 'google-site-verification',
			],
			'msvalidate' => [
				'label' => 'msvalidate.01',
			],
			'p_domain_verify' => [
				'label' => 'p:domain_verify',
			],
			'add_meta_tags' => [
				'type' => 'textarea',
				'label' => 'Additional Meta Tags'
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
		],
		'Feature Flags' => [],
		'Theme Features' => [
			'feat_disable_comments' => [
				'type' => 'radio',
				'label' => 'Disable Comments',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_content_blocks' => [
				'type' => 'radio',
				'label' => 'Enable Content Blocks',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_layout_blocks' => [
				'type' => 'radio',
				'label' => 'Enable Layout Blocks',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_modules' => [
				'type' => 'radio',
				'label' => 'Enable Modules',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_tailwind' => [
				'type' => 'radio',
				'label' => 'Enable Tailwind',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_theme_update' => [
				'type' => 'radio',
				'label' => 'Enable Theme Update',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_livereload' => [
				'type' => 'radio',
				'label' => 'Enable LiveReload',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_disable_srcset' => [
				'type' => 'radio',
				'label' => 'Disable Image srcset',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_hide_php_version_alert' => [
				'type' => 'radio',
				'label' => 'Hide PHP Version Alert',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_hide_yoast_metabox' => [
				'type' => 'radio',
				'label' => 'Hide Yoast Metabox',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_allow_svg_upload' => [
				'type' => 'radio',
				'label' => 'Allow SVG Upload',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_hide_wp_generator_tag' => [
				'type' => 'radio',
				'label' => 'Hide WP Generator Tag',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_hide_weak_password_confirmation' => [
				'type' => 'radio',
				'label' => 'Hide Weak Password Confirmation',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_image_sizes' => [
				'type' => 'radio',
				'label' => 'Enable Image Sizes',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_required_plugins_alert' => [
				'type' => 'radio',
				'label' => 'Enable Required Plugins Alert',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_user_management_editors' => [
				'type' => 'radio',
				'label' => 'Enable User Management for Editor Role',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_hide_acf_ext_menu_items' => [
				'type' => 'radio',
				'label' => 'Hide ACF Extension Menu Items',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			]
		]
	];

	// Add custom menu positions to $menus array
	if (file_exists(get_template_directory() . '/php/custom/features.php')) {
		require get_template_directory() . '/php/custom/features.php';

		if (count($feature_flags)) {
			foreach ($feature_flags as $code => $title) {
				$add_settings['Feature Flags']['feature-' . $code] = [
					'type' => 'radio',
					'label' => $title,
					'choices' => ['0' => 'No', '1' => 'Yes'],
					'default' => '0'
				];
			}
		} else unset($add_settings['Feature Flags']);
	}

	foreach ($add_settings as $section => $setting) {
		$wp_customize->add_section('lqx_' . strtolower($section), [
			'title' => __($section, 'lyquix'),
			'priority' => 30,
		]);
		foreach ($setting as $name => $options) {
			$wp_customize->add_setting($name, [
				'type' => 'theme_mod',
				'transport' => 'refresh',
				'default' => $options['default'] ?? null
			]);
			$wp_customize->add_control($name, [
				'type' => $options['type'] ?? null,
				'label' => __($options['label'], 'lyquix'),
				'section' => 'lqx_' . strtolower($section),
				'settings' => $name,
				'choices' => $options['choices'] ?? null
			]);
		}
	}
}

add_action('customize_register', '\lqx\customizer\customizer_add');
