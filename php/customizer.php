<?php

/**
 * customizer.php - Set fields for theme customizer
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
				'default' => json_encode([
					'xs' => [
						'width' => 320,
						'height' => 720
					],
					'sm' => [
						'width' => 480,
						'height' => 1080
					],
					'md' => [
						'width' => 720,
						'height' => 1080
					],
					'lg' => [
						'width' => 1080,
						'height' => 1080
					],
					'xl' => [
						'width' => 1620,
						'height' => 1080
					]
				])
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
				'default' => '[]'
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
				'default' => '[]'
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

			switch ($options['type']) {
				case 'checkbox-group':
					$wp_customize->add_control(new checkbox_group_custom_control($wp_customize, $name, $control_opts));
					break;

				case 'viewports':
					$wp_customize->add_control(new viewports_custom_control($wp_customize, $name, $control_opts));
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
			if (!is_array($values)) $values = [
				'xs' => [
					'width' => 320,
					'height' => 720
				],
				'sm' => [
					'width' => 480,
					'height' => 1080
				],
				'md' => [
					'width' => 720,
					'height' => 1080
				],
				'lg' => [
					'width' => 1080,
					'height' => 1080
				],
				'xl' => [
					'width' => 1620,
					'height' => 1080
				]
			];
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
