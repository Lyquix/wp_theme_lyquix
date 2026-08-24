<?php

/**
 * customizer.php - Set fields for theme customizer
 *
 * @version     3.4.0
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
function customizer_add($wp_customize)
{

	$add_settings = [
		'Branding' => [
			'login_logo_image_id' => [
				'type' => 'media-image',
				'label' => 'Login Logo (wp-login.php)',
				'default' => 0,
				'sanitize_callback' => 'absint'
			],
		],
		'Admin Bar' => [
			'admin_bar_hide_roles' => [
				'type' => 'checkbox-group',
				'label' => 'Hide Admin Bar on Frontend for Roles',
				'choices' => (function () {
					$roles = [];
					$wp_roles = wp_roles();
					foreach ($wp_roles->roles as $role_key => $role) {
						$roles[$role_key] = $role['name'];
					}
					ksort($roles);
					return $roles;
				})(),
				'default' => '[]'
			],
			'admin_bar_collapse' => [
				'type' => 'radio',
				'label' => 'Enable Admin Bar Expand/Collapse (Notch)',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'admin_bar_notch_position' => [
				'type' => 'radio',
				'label' => 'Notch Position',
				'choices' => [
					'left'   => 'Left corner',
					'center' => 'Center',
					'right'  => 'Right corner',
				],
				'default' => 'center',
				'active_callback' => function () {
					return get_theme_mod('admin_bar_collapse', '0') == '1';
				}
			],
		],
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
			],
			'load_critical_path_css' => [
				'type' => 'radio',
				'label' => 'Load Critical Path CSS when available',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'viewports_critical_path_css' => [
				'type' => 'viewports',
				'label' => 'Viewports for Critical Path CSS',
				'default' => \lqx\util\get_breakpoints(),
				'active_callback' => function () {
					return get_theme_mod('load_critical_path_css', '1') == '1';
				}
			],
			'exclude_types_critical_path_css' => [
				'type' => 'checkbox-group',
				'label' => 'Exclude Post Types from Critical Path CSS',
				'choices' => (function () {
					$post_types = [
						'page' => 'Page',
						'post' => 'Post'
					];
					foreach (get_post_types(['_builtin' => false, 'public' => true], 'objects') as $post_type) {
						$post_types[$post_type->name] = $post_type->label;
					}
					ksort($post_types);
					return $post_types;
				})(),
				'default' => '[]',
				'active_callback' => function () {
					return get_theme_mod('load_critical_path_css', '1') == '1';
				}
			],
			'exclude_pages_critical_path_css' => [
				'type' => 'checkbox-group',
				'label' => 'Exclude Pages from Critical Path CSS',
				'choices' => (function () {
					$pages = [];
					foreach (get_pages(['post_status' => 'publish']) as $page) {
						$slug_path = \lqx\critical\slug_path($page);
						$pages[$slug_path] = $slug_path;
					}
					ksort($pages);
					return $pages;
				})(),
				'default' => '[]',
				'active_callback' => function () {
					return get_theme_mod('load_critical_path_css', '1') == '1';
				}
			]
		],
		'JS' => [
			'enable_jquery' => [
				'type' => 'radio',
				'label' => 'Enable jQuery',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'enable_jquery_migrate' => [
				'type' => 'radio',
				'label' => 'Enable jQuery Migrate',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
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
		'PHP' => [
			'suppress_php_warnings' => [
				'type' => 'radio',
				'label' => 'Suppress PHP Warnings and Notices',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
		],
		'IP Geolocation' => [
			'ip2geo_maxmind_license_key' => [
				'type' => 'text',
				'label' => 'MaxMind GeoLite2 License Key',
				'default' => ''
			],
			'ip2geo_max_db_age' => [
				'type' => 'text',
				'label' => 'Maximum Database Age (days)',
				'default' => '90'
			],
			'ip2geo_ip_address_header' => [
				'type' => 'radio',
				'label' => 'IP Address HTTP Header',
				'choices' => [
					'REMOTE_ADDR' => 'REMOTE_ADDR',
					'HTTP_CF_CONNECTING_IP' => 'HTTP_CF_CONNECTING_IP',
					'HTTP_CLIENT_IP' => 'HTTP_CLIENT_IP',
					'HTTP_FASTLY_CLIENT_IP' => 'HTTP_FASTLY_CLIENT_IP',
					'HTTP_FORWARDED' => 'HTTP_FORWARDED',
					'HTTP_FORWARDED_FOR' => 'HTTP_FORWARDED_FOR',
					'HTTP_TRUE_CLIENT_IP' => 'HTTP_TRUE_CLIENT_IP',
					'HTTP_VIA' => 'HTTP_VIA',
					'HTTP_X_CLUSTER_CLIENT_IP' => 'HTTP_X_CLUSTER_CLIENT_IP',
					'HTTP_X_FORWARDED' => 'HTTP_X_FORWARDED',
					'HTTP_X_FORWARDED_FOR' => 'HTTP_X_FORWARDED_FOR',
					'HTTP_X_REAL_IP' => 'HTTP_X_REAL_IP'
				],
				'default' => 'REMOTE_ADDR'
			],
			'ip2geo_test_ip_address' => [
				'type' => 'text',
				'label' => 'Test IP Address',
				'default' => ''
			],
		],
		'Analytics' => [
			'ga4_account' => [
				'type' => 'text',
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
				'type' => 'text',
				'label' => 'Google Tag Manager Account',
			],
			'clarity_account' => [
				'type' => 'text',
				'label' => 'Microsoft Clarity Project ID',
			],
		],
		'Meta Tags' => [
			'google_site_verification' => [
				'type' => 'text',
				'label' => 'google-site-verification',
			],
			'msvalidate' => [
				'type' => 'text',
				'label' => 'msvalidate.01',
			],
			'p_domain_verify' => [
				'type' => 'text',
				'label' => 'p:domain_verify',
			],
			'rel_preconnect' => [
				'type' => 'textarea',
				'label' => 'rel=preconnect URLs',
				'default' => implode("\n", [
					'https://cdn.jsdelivr.net',
					'https://www.google.com',
					'https://www.google-analytics.com',
					'https://www.googletagmanager.com',
					'https://www.gstatic.com',
					'https://fonts.gstatic.com',
					'https://fonts.googleapis.com'
				])
			],
			'add_meta_tags' => [
				'type' => 'textarea',
				'label' => 'Additional Meta Tags',
				'sanitize_callback' => null
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
				'default' => '3',
				'active_callback' => function () {
					return get_theme_mod('browser_alert', '1') == '1';
				}
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
			'feat_hide_module_alerts' => [
				'type' => 'radio',
				'label' => 'Hide Alerts Module for Non Administrators',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'feat_hide_module_ctas' => [
				'type' => 'radio',
				'label' => 'Hide CTAs Module for Non Administrators',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'feat_hide_module_modals' => [
				'type' => 'radio',
				'label' => 'Hide Modals Module for Non Administrators',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'feat_hide_module_popups' => [
				'type' => 'radio',
				'label' => 'Hide Popups Module for Non Administrators',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
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
			'feat_featured_image_column' => [
				'type' => 'radio',
				'label' => 'Enable Featured Image Column in Post/CPT Lists',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_post_id_column' => [
				'type' => 'radio',
				'label' => 'Enable Post ID Column in Post/CPT Lists',
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
			'feat_manager_role' => [
				'type' => 'radio',
				'label' => 'Enable Manager Role (Editor + User Management)',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'feat_hide_acf_ext_menu_items' => [
				'type' => 'radio',
				'label' => 'Hide ACF Extension Menu Items',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_hide_activity_log' => [
				'type' => 'radio',
				'label' => 'Hide Activity Log for Non Administrator Users',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_move_excerpt' => [
				'type' => 'radio',
				'label' => 'Move Excerpt to after Content form',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_qr_generator' => [
				'type' => 'radio',
				'label' => 'Enable QR Code Generator',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_redirects' => [
				'type' => 'radio',
				'label' => 'Enable Redirects Manager (requires Redirection plugin)',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			],
			'feat_switch_dashboard_fonts' => [
				'type' => 'radio',
				'label' => 'Switch Dashboard Fonts to Inter',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '1'
			]
		],
		'URL Shortener' => [
			'feat_url_shortener' => [
				'type' => 'radio',
				'label' => 'Enable URL Shortener',
				'choices' => ['0' => 'No', '1' => 'Yes'],
				'default' => '0'
			],
			'url_shortener_provider' => [
				'type' => 'radio',
				'label' => 'URL Shortener Provider',
				'choices' => ['bitly' => 'Bitly', 'tinyurl' => 'TinyURL', 'yourls' => 'YOURLS'],
				'default' => 'tinyurl',
				'active_callback' => function () {
					return get_theme_mod('feat_url_shortener', '0') == '1';
				}
			],
			'url_shortener_bitly_api_key' => [
				'type' => 'text',
				'label' => 'Bitly Access Token',
				'default' => '',
				'active_callback' => function () {
					return get_theme_mod('feat_url_shortener', '0') == '1' && get_theme_mod('url_shortener_provider', 'tinyurl') == 'bitly';
				}
			],
			'url_shortener_tinyurl_api_key' => [
				'type' => 'text',
				'label' => 'TinyURL API Token',
				'default' => '',
				'active_callback' => function () {
					return get_theme_mod('feat_url_shortener', '0') == '1' && get_theme_mod('url_shortener_provider', 'yourls') == 'tinyurl';
				}
			],
			'url_shortener_yourls_url' => [
				'type' => 'text',
				'label' => 'YOURLS Base URL',
				'default' => '',
				'active_callback' => function () {
					return get_theme_mod('feat_url_shortener', '0') == '1' && get_theme_mod('url_shortener_provider', 'yourls') == 'yourls';
				}
			],
			'url_shortener_yourls_api_key' => [
				'type' => 'text',
				'label' => 'YOURLS Signature Token',
				'default' => '',
				'active_callback' => function () {
					return get_theme_mod('feat_url_shortener', '0') == '1' && get_theme_mod('url_shortener_provider', 'yourls') == 'yourls';
				}
			]
		]
	];

	// Add custom menu positions to $menus array
	if (file_exists(get_stylesheet_directory() . '/php/custom/features.php')) {
		require get_stylesheet_directory() . '/php/custom/features.php';

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
			$settings_opts = [
				'type' => 'theme_mod',
				'transport' => 'refresh',
				'default' => $options['default'] ?? null
			];

			if (array_key_exists('sanitize_callback', $options)) $settings_opts['sanitize_callback'] = $options['sanitize_callback'];
			else {
				switch ($options['type']) {
					case 'text':
						$settings_opts['sanitize_callback'] = 'sanitize_text_field';
						break;

					case 'textarea':
						$settings_opts['sanitize_callback'] = 'sanitize_textarea_field';
						break;
				}
			}

			$wp_customize->add_setting($name, $settings_opts);

			$control_opts = [
				'label' => __($options['label'], 'lyquix'),
				'section' => 'lqx_' . strtolower($section),
				'settings' => $name,
			];

			if (array_key_exists('choices', $options)) $control_opts['choices'] = $options['choices'];
			if (array_key_exists('active_callback', $options)) $control_opts['active_callback'] = $options['active_callback'];

			switch ($options['type']) {
				case 'checkbox-group':
					$wp_customize->add_control(new checkbox_group_custom_control($wp_customize, $name, $control_opts));
					break;

				case 'viewports':
					$wp_customize->add_control(new viewports_custom_control($wp_customize, $name, $control_opts));
					break;

				case 'media-image':
					// Uses the Media Library uploader/selector (returns attachment ID)
					if (class_exists('\WP_Customize_Media_Control')) {
						$control_opts['mime_type'] = 'image';
						$wp_customize->add_control(new \WP_Customize_Media_Control($wp_customize, $name, $control_opts));
					} else {
						// Fallback to a plain input if media control is unavailable
						$control_opts['type'] = 'number';
						$wp_customize->add_control($name, $control_opts);
					}
					break;

				default:
					if (array_key_exists('type', $options)) $control_opts['type'] = $options['type'];
					$wp_customize->add_control($name, $control_opts);
					break;
			}
		}
	}
}

add_action('customize_register', '\lqx\customizer\customizer_add');

// Conditionally show/hide customizer controls based on related settings
add_action('customize_controls_print_footer_scripts', function () {
	?>
	<script>
	(function($) {
		/**
		 * Simple toggle: show control when a setting equals a value
		 */
		function bindToggle(settingId, controlId, value) {
			wp.customize.control(controlId, function(control) {
				function toggle() {
					control.active.set(wp.customize(settingId).get() === value);
				}
				wp.customize(settingId).bind(toggle);
				toggle();
			});
		}

		// Admin Bar: notch position depends on collapse enabled
		bindToggle('admin_bar_collapse', 'admin_bar_notch_position', '1');

		// Critical Path CSS: sub-settings depend on load enabled
		bindToggle('load_critical_path_css', 'viewports_critical_path_css', '1');
		bindToggle('load_critical_path_css', 'exclude_types_critical_path_css', '1');
		bindToggle('load_critical_path_css', 'exclude_pages_critical_path_css', '1');

		// Browser Alert: versions depends on alert enabled
		bindToggle('browser_alert', 'accepted_browser_versions', '1');

		// URL Shortener: provider radio depends on feature enabled
		bindToggle('feat_url_shortener', 'url_shortener_provider', '1');

		// URL Shortener: provider-specific fields depend on feature + provider
		var providerControls = {
			bitly: ['url_shortener_bitly_api_key'],
			yourls: ['url_shortener_yourls_url', 'url_shortener_yourls_api_key'],
			tinyurl: ['url_shortener_tinyurl_api_key']
		};

		$.each(providerControls, function(provider, controlIds) {
			$.each(controlIds, function(i, controlId) {
				wp.customize.control(controlId, function(control) {
					function toggle() {
						var enabled = wp.customize('feat_url_shortener').get() === '1';
						var selected = wp.customize('url_shortener_provider').get();
						control.active.set(enabled && selected === provider);
					}
					wp.customize('feat_url_shortener').bind(toggle);
					wp.customize('url_shortener_provider').bind(toggle);
					toggle();
				});
			});
		});
	})(jQuery);
	</script>
	<?php
});

if (class_exists('\WP_Customize_Control')) {
	class checkbox_group_custom_control extends \WP_Customize_Control
	{
		public $type = 'checkbox-group';

		public function enqueue()
		{
			// Check if already enqueued
			if (wp_script_is('lqx-customizer-checkbox-group-custom-control', 'enqueued')) return;

			wp_register_script('lqx-customizer-checkbox-group-custom-control', false);
			wp_enqueue_script('lqx-customizer-checkbox-group-custom-control');
			wp_add_inline_script(
				'lqx-customizer-checkbox-group-custom-control',
				'
				jQuery(document).ready(function() {
					jQuery(\'#customize-theme-controls\').on(\'change\', \'.customize-control-checkbox-group input[type="checkbox"]\', function() {
						let values = jQuery(this).parents(\'.customize-control-checkbox-group\').find(\'input[type="checkbox"]:checked\').map(
							function() {
								return this.value;
							}
						).get();
						jQuery(this).parents(\'.customize-control-checkbox-group\').find(\'input[type="hidden"]\').val(JSON.stringify(values)).trigger(\'change\');
					});
				});'
			);
		}

		public function render_content()
		{
			if (empty($this->choices)) return;
			if (!empty($this->label)) echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
			if (!empty($this->description)) echo '<span class="customize-control-description description">' . esc_html($this->description) . '</span>';

			$values = $this->value();
			if (is_string($values)) $values = json_decode($this->value(), true);
			if (!is_array($values)) $values = [];
?>
			<div class="customize-control-checkbox-group">
				<?php foreach ($this->choices as $value => $label) : ?>
					<label>
						<input type="checkbox" value="<?= esc_attr($value); ?>" <?php checked(in_array($value, $values)); ?> />
						<?= esc_html($label); ?>
					</label>
					<br>
				<?php endforeach; ?>
			</div>
			<input type="hidden" <?php $this->link(); ?> value="<?= esc_attr(json_encode($values)); ?>" />
		<?php
		}
	}

	class viewports_custom_control extends \WP_Customize_Control
	{
		public $type = 'viewports';

		public function enqueue()
		{
			// Check if already enqueued
			if (wp_script_is('lqx-customizer-viewports-custom-control', 'enqueued')) return;

			wp_register_script('lqx-customizer-viewports-custom-control', false);
			wp_enqueue_script('lqx-customizer-viewports-custom-control');
			wp_add_inline_script(
				'lqx-customizer-viewports-custom-control',
				'
				jQuery(document).ready(function() {
					jQuery(\'#customize-theme-controls\').on(\'input change\', \'.customize-control-viewports input[type="number"]\', function() {
							var values = {};
							jQuery(this).parents(\'.customize-control-viewports\').find(\'input[type="number"]\').each(function() {
									var parts = jQuery(this).data(\'viewport\').split(\'-\');
									var screen = parts[0]; // xs, sm, etc.
									var dimension = parts[1]; // width or height

									if (!(screen in values)) values[screen] = {};
									values[screen][dimension] = parseInt(jQuery(this).val());
							});
							jQuery(this).parents(\'.customize-control-viewports\').find(\'input[type="hidden"]\').val(JSON.stringify(values)).trigger(\'change\');
					});
				});'
			);
			wp_register_style('lqx-customizer-viewports-custom-control', false);
			wp_enqueue_style('lqx-customizer-viewports-custom-control');
			wp_add_inline_style('lqx-customizer-viewports-custom-control', '
				.customize-control-viewports label {
					display: flex;
					gap: 0.5em;
					align-items: baseline;
				}
			');
		}

		public function render_content()
		{
			if (!empty($this->label)) echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
			if (!empty($this->description)) echo '<span class="customize-control-description description">' . esc_html($this->description) . '</span>';

			$values = $this->value();
			if (is_string($values)) $values = json_decode($this->value(), true);
			if (!is_array($values)) $values = \lqx\util\get_breakpoints();
		?>
			<div class="customize-control-viewports">
				<?php foreach ($values as $label => $viewport) : ?>
					<label>
						<?= esc_html($label); ?>
						<input type="number" value="<?= esc_attr($viewport['width']); ?>" data-viewport="<?= $label; ?>-width" />
						<input type="number" value="<?= esc_attr($viewport['height']); ?>" data-viewport="<?= $label; ?>-height" />
					</label>
					<br>
				<?php endforeach; ?>
			</div>
			<input type="hidden" <?php $this->link(); ?> value="<?= esc_attr(json_encode($values)); ?>" />
<?php
		}
	}
}
