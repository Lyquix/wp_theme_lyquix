<?php

/**
 * setup.php - Theme initial setup
 *
 * @version     3.1.0
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

namespace lqx\setup;

/**
 * Theme setup
 * 		- Add theme support
 * 		- Load theme styles into editor
 * 		- Remove unnecessary wptexturize filter
 * 		- Disable srcset on images
 * 		- Hide PHP upgrade alert from dashboard
 * 		- Hide Yoast SEO meta box
 * 		- Allow SVGs in WP Uploads
 * 		- Load Global WordPress Styles
 * 		- Remove WordPress generator meta tag
 * 		- Remove weak password confirmation checkbox
 * 		- Change the default image sizes
 * 		- Add alerts for required plugins
 * 		- Add user management capabilities to editor user role
 * 		- Remove additional ACF extended menu items
 * 		- Hide Activity Log menu item for non administrator users
 *
 * @return void
 */
function theme_setup() {
	// Theme Features Support
	add_theme_support('automatic-feed-links');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption']);
	add_theme_support('customize-selective-refresh-widgets');

	// Load theme styles into editor
	add_theme_support('editor-styles');
	add_editor_style('css/editor.css');

	// Remove unnecessary wptexturize filter
	add_filter('run_wptexturize', '__return_false');

	// Disable srcset on images
	if (get_theme_mod('feat_disable_srcset', '1') === '1') {
		add_filter('wp_calculate_image_srcset', function ($sources) {
			return false;
		});
	}

	// Hide PHP upgrade alert from dashboard
	if (get_theme_mod('feat_hide_php_version_alert', '1') === '1') {
		add_action('admin_head', function () {
			echo '<style>#dashboard_php_nag {display:none;}</style>';
		});
	}

	// Hide Yoast SEO meta box
	if (get_theme_mod('feat_hide_yoast_metabox', '1') === '1') {
		add_action('admin_head', function () {
			echo '<style>#wpseo_meta {display:none;}</style>';
		});
	}

	// Allow SVGs in WP Uploads
	if (get_theme_mod('feat_allow_svg_upload', '1') === '1') {
		add_filter('upload_mimes', function ($mimes) {
			$mimes['svg'] = 'image/svg+xml';
			return $mimes;
		});
	}

	// Load Global WordPress Styles
	add_action('wp_head', function () {
		wp_enqueue_style('global-styles');
	});

	// Remove WordPress generator meta tag
	if (get_theme_mod('feat_hide_wp_generator_tag', '1') === '1') {
		remove_action('wp_head', 'wp_generator');
	}

	// Remove weak password confirmation checkbox
	if (get_theme_mod('feat_hide_weak_password_confirmation', '1') === '1') {
		add_action('login_head', '\lqx\setup\no_weak_password');
		add_action('admin_head', '\lqx\setup\no_weak_password');
		function no_weak_password() {
			echo '<style>.pw-weak { display: none !important; }</style>';
			echo '<script>(() => {var e = document.getElementById(\'pw-checkbox\'); if(e) e.disabled = true;})();</script>';
		}
	}

	// Change the default image sizes
	if (get_theme_mod('feat_image_sizes', '1') === '1') {
		add_action('admin_init', function () {
			// Update WordPress default image sizes
			update_option('thumbnail_size_w', 150);
			update_option('thumbnail_size_h', 150);
			update_option('thumbnail_crop', 1);

			update_option('medium_size_w', 1280);
			update_option('medium_size_h', 1280);

			update_option('large_size_w', 3840);
			update_option('large_size_h', 3840);
		});

		add_action('after_setup_theme', function () {
			// Add custom image size
			add_image_size('small', 640, 640);

			// Remove unwanted image sizes
			remove_image_size('medium_large');
			remove_image_size('1536x1536');
			remove_image_size('2048x2048');
		});

		// Filter intermediate image sizes
		add_filter('intermediate_image_sizes_advanced', function ($sizes) {
			return [
				'thumbnail' => $sizes['thumbnail'],
				'small' => ['width' => 640, 'height' => 640, 'crop' => false],
				'medium' => $sizes['medium'],
				'large' => $sizes['large']
			];
		}, 10, 1);

		// Filter available image sizes
		add_filter('intermediate_image_sizes', function ($sizes) {
			return [
				'thumbnail',
				'small',
				'medium',
				'large'
			];
		}, 10, 1);
	}

	// Disable automatic updates of plugins and themes
	add_filter('auto_update_plugin', '__return_false');
	add_filter('auto_update_theme', '__return_false');

	// Add alerts for required plugins
	if (get_theme_mod('feat_required_plugins_alert', '1') === '1' && current_user_can('administrator')) {
		// Check plugins and display alert
		if (get_transient('dismissed_required_plugins_alert') === false) {
			add_action('admin_init', function () {
				$required_plugins = [
					'aryo-activity-log/aryo-activity-log.php' => 'Activity Log',
					'admin-menu-editor-pro/menu-editor.php' => 'Admin Menu Editor Pro',
					'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
					'acf-extended-pro/acf-extended.php' => 'Advanced Custom Fields: Extended PRO',
					'tinymce-advanced/tinymce-advanced.php' => 'Advanced Editor Tools',
					'better-search-replace/better-search-replace.php' => 'Better Search Replace',
					'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
					'gravityforms/gravityforms.php' => 'Gravity Forms',
					'html-editor-syntax-highlighter/html-editor-syntax-highlighter.php' => 'HTML Editor Syntax Highlighter',
					'post-smtp/postman-smtp.php' => 'Post SMTP',
					'redirection/redirection.php' => 'Redirection',
					'simple-custom-post-order/simple-custom-post-order.php' => 'Simple Custom Post Order',
					'wordpress-seo/wp-seo.php' => 'Yoast SEO',
					'duplicate-post/duplicate-post.php' => 'Yoast Duplicate Post',
					'wordfence/wordfence.php' => 'Wordfence',
					'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
					'zero-spam/wordpress-zero-spam.php' => 'Zero Spam',
				];

				$premium_plugins = [
					'advanced-custom-fields-pro/acf.php' => 'https://www.advancedcustomfields.com/pro/',
					'acf-extended-pro/acf-extended.php' => 'https://www.acf-extended.com/',
					'acf-extended-pro-libphonenumber/acf-extended-libphonenumber.php' => 'https://www.acf-extended.com/features/fields/phone-number#phone-number-addon',
					'admin-menu-editor-pro/menu-editor.php' => 'https://adminmenueditor.com/',
					'gravityforms/gravityforms.php' => 'https://www.gravityforms.com/',
					'gravityformsrecaptcha/recaptcha.php' => 'https://www.gravityforms.com/add-ons/recaptcha/',
				];

				// Retrieve all installed plugins' data
				$all_plugins = get_plugins();
				$not_installed = [];
				$not_active = [];

				foreach ($required_plugins as $plugin_path => $plugin_name) {
					// Check if the plugin is installed
					if (isset($all_plugins[$plugin_path])) {
						// Check if the plugin is active
						if (!is_plugin_active($plugin_path)) {
							$not_active[] = $plugin_path;
						}
					} else {
						// Plugin is not installed
						$not_installed[] = $plugin_path;
					}
				}

				if (count($not_installed) || count($not_active)) {
					add_action('admin_notices', function () use ($not_active, $not_installed, $required_plugins, $premium_plugins) {
						echo '<div class="notice notice-error is-dismissible required-plugins-alert"><p><strong style="font-size: 1.25em;">Required Plugins</strong><br>';
						if (count($not_installed)) {
							$html = [];
							foreach ($not_installed as $plugin_path) {
								if (array_key_exists($plugin_path, $premium_plugins)) {
									$install_url = $premium_plugins[$plugin_path];
									$target = '_blank';
								} else {
									$plugin_slug = explode('/', $plugin_path)[0];
									$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug), 'install-plugin_' . $plugin_slug);
									$target = '';
								}
								$html[] = sprintf('<a href="%s" target="%s">%s</a>', $install_url, $target, $required_plugins[$plugin_path]);
							}
							echo  '<strong>Install:</strong> ' . implode(' | ', $html) . '<br>';
						}
						if (count($not_active)) {
							$html = [];
							foreach ($not_active as $plugin_path) {
								$plugin_slug = basename($plugin_path);
								$activate_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . urlencode($plugin_path), 'activate-plugin_' . $plugin_path);
								$html[] = sprintf('<a href="%s">%s</a>', $activate_url, $required_plugins[$plugin_path]);
							}
							echo '<strong>Activate:</strong> ' . implode(' | ', $html);
						}
						echo '</p></div>';

						// Add script to handle the notice dismissal
						echo '<script>
							jQuery(document).on("click", ".notice-dismiss", function() {
								jQuery.ajax({ url: ajaxurl,
								data: { action: "dismiss_required_plugins_alert" } });
							});
						</script>';
					});
				}
			});

			// Handle the dismiss AJAX request
			add_action('wp_ajax_dismiss_required_plugins_alert', function () {
				// Set the transient to expire after 1 day
				set_transient('dismissed_required_plugins_alert', true, DAY_IN_SECONDS);
				wp_die();
			});
		}
	}

	// Add user management capabilities to editor user role
	if (get_theme_mod('feat_user_management_editors', '1') === '1') {
		add_action('admin_init', function () {
			$role = get_role('editor');
			$role->add_cap('create_users');
			$role->add_cap('edit_users');
			$role->add_cap('delete_users');
			$role->add_cap('promote_users');
			$role->add_cap('list_users');
			$role->add_cap('remove_users');
		});
	}

	// Remove additional ACF extended menu items
	if (get_theme_mod('feat_hide_acf_ext_menu_items', '1') === '1') {
		add_action('admin_head', function () {
			echo '<style>#adminmenu .wp-submenu a[href*="';
			echo implode('"], #adminmenu .wp-submenu a[href*="', [
				'edit.php?post_type=acfe-dop',
				'edit-tags.php?taxonomy=acf-field-group-category',
				'edit.php?post_type=acfe-dbt',
				'edit.php?post_type=acfe-form',
				'edit.php?post_type=acfe-template'
			]);
			echo '"] { display: none; }</style>';
		}, 999);
	}

	// Hide Activity Log menu from non administrator users
	if (get_theme_mod('feat_hide_activity_log', '1') === '1' && !current_user_can('administrator')) {
		add_action('admin_menu', function () {
			remove_menu_page('activity-log-page');
		}, 999);
	}

	// Hide Alerts module from non administrator users
	if (get_theme_mod('feat_hide_module_alerts', '0') === '1' && !current_user_can('administrator')) {
		add_action('admin_head', function () {
			echo '<style>#adminmenu .wp-submenu a[href*="admin.php?page=alerts-content"] { display: none; }</style>';
		}, 999);
	}

	// Hide CTAs module from non administrator users
	if (get_theme_mod('feat_hide_module_ctas', '0') === '1' && !current_user_can('administrator')) {
		add_action('admin_head', function () {
			echo '<style>#adminmenu .wp-submenu a[href*="admin.php?page=cta-content"] { display: none; }</style>';
		}, 999);
	}

	// Hide Modals module from non administrator users
	if (get_theme_mod('feat_hide_module_modals', '0') === '1' && !current_user_can('administrator')) {
		add_action('admin_head', function () {
			echo '<style>#adminmenu .wp-submenu a[href*="admin.php?page=modals-content"] { display: none; }</style>';
		}, 999);
	}

	// Hide Popups module from non administrator users
	if (get_theme_mod('feat_hide_module_popups', '0') === '1' && !current_user_can('administrator')) {
		add_action('admin_head', function () {
			echo '<style>#adminmenu .wp-submenu a[href*="admin.php?page=popups-content"] { display: none; }</style>';
		}, 999);
	}



	// Move Excerpt field
	if (get_theme_mod('feat_move_excerpt', '1') === '1') {
		add_action('add_meta_boxes', function() {
			$post_types = get_post_types(['public' => true], 'names'); // Get all public post types

			foreach ($post_types as $post_type) {
				if (post_type_supports($post_type, 'excerpt')) {
					remove_meta_box('postexcerpt', $post_type, 'normal'); // Remove the default excerpt box position
					add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', $post_type, 'normal', 'high'); // Re-add with higher priority
				}
			}
		});
	}
}

add_action('after_setup_theme', '\lqx\setup\theme_setup');
