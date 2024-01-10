<?php

/**
 * setup.php - Theme initial setup
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

namespace lqx\setup;

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
	add_filter('max_srcset_image_width', (function () {
		return 1;
	})());

	// Hide PHP upgrade alert from dashboard
	// Hide Yoast SEO meta box
	add_action('admin_head', function () {
		echo '<style>#dashboard_php_nag, #wpseo_meta {display:none;}</style>';
	});

	// Allow SVGs in WP Uploads
	add_filter('upload_mimes', function ($mimes) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	});

	// Load Global WordPress Styles
	add_action('wp_head', function () {
		wp_enqueue_style('global-styles');
	});

	// Remove WordPress generator meta tag
	remove_action('wp_head', 'wp_generator');

	// Remove weak password confirmation checkbox
	add_action('login_init', '\lqx\setup\no_weak_password');
	add_action('admin_head', '\lqx\setup\no_weak_password');
	function no_weak_password() {
		echo '<style>.pw-weak { display: none !important; }</style>';
		echo '<script>(() => {var e = document.getElementById(\'pw-checkbox\'); if(e) e.disabled = true;})();</script>';
	}

	// Change the default image sizes
	add_image_size('small', 640, 640);
	add_action('init', 	function () {
		remove_image_size('medium_large');
		remove_image_size('1536x1536');
		remove_image_size('2048x2048');
	});
	add_filter('intermediate_image_sizes_advanced', function ($sizes) {
		file_put_contents(__DIR__ . '/setup1.log', json_encode($sizes, JSON_PRETTY_PRINT));
		return [
			'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
			'small' => ['width' => 640, 'height' => 640, 'crop' => false],
			'medium' => ['width' => 1280, 'height' => 1280, 'crop' => false],
			'large' => ['width' => 3840, 'height' => 3840, 'crop' => false]
		];
	}, 10, 1);
	add_filter('intermediate_image_sizes', function ($sizes) {
		file_put_contents(__DIR__ . '/setup2.log', json_encode($sizes, JSON_PRETTY_PRINT));
		return [
			'thumbnail',
			'small',
			'medium',
			'large'
		];
	}, 10, 1);

	// Add alerts for required plugins
	add_action('admin_init', function () {
		$required_plugins = [
			'aryo-activity-log/aryo-activity-log.php' => 'Activity Log',
			'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
			'acf-extended-pro/acf-extended.php' => 'Advanced Custom Fields: Extended PRO',
			'admin-menu-editor-pro/menu-editor.php' => 'Admin Menu Editor Pro',
			'gravityforms/gravityforms.php' => 'Gravity Forms',
			'post-smtp/postman-smtp.php' => 'Post SMTP',
			'redirection/redirection.php' => 'Redirection',
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'duplicate-post/duplicate-post.php' => 'Yoast Duplicate Post',
			'simple-custom-post-order/simple-custom-post-order.php' => 'Simple Custom Post Order',
			'tinymce-advanced/tinymce-advanced.php' => 'Advanced Editor Tools',
			'html-editor-syntax-highlighter/html-editor-syntax-highlighter.php' => 'HTML Editor Syntax Highlighter',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wordfence/wordfence.php' => 'Wordfence',
		];

		$premium_plugins = [
			'advanced-custom-fields-pro/acf.php' => 'https://www.advancedcustomfields.com/pro/',
			'acf-extended-pro/acf-extended.php' => 'https://www.acf-extended.com/',
			'admin-menu-editor-pro/menu-editor.php' => 'https://adminmenueditor.com/',
			'gravityforms/gravityforms.php' => 'https://www.gravityforms.com/'
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
				echo '<div class="notice notice-error is-dismissible"><p><strong style="font-size: 1.25em;">Required Plugins</strong><br>';
				if (count($not_installed)) {
					$html = [];
					foreach($not_installed as $plugin_path) {
						if(array_key_exists($plugin_path, $premium_plugins)) {
							$install_url = $premium_plugins[$plugin_path];
							$target = '_blank';
						}
						else {
							$plugin_slug = explode('/', $plugin_path)[0];
							$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug);
							$target = '';
						}
						$html[] = sprintf('<a href="%s" target="%s">%s</a>', $install_url, $target, $required_plugins[$plugin_path]);
					}
					echo  '<strong>Install:</strong> ' . implode(' | ', $html) . '<br>';
				}
				if (count($not_active)) {
					$html = [];
					foreach($not_active as $plugin_path) {
						$plugin_slug = basename($plugin_path);
						$activate_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . urlencode( $plugin_path ), 'activate-plugin_' . $plugin_path);
						$html[] = sprintf('<a href="%s">%s</a>', $activate_url, $required_plugins[$plugin_path]);
					}
					echo '<strong>Activate:</strong> ' . implode(' | ', $html);
				}
				echo '</p></div>';
			});
		}
	});

	// Add user management capabilities to editor user role
	add_action('admin_init', function () {
		$role = get_role('editor');
		$role->add_cap('create_users');
		$role->add_cap('edit_users');
		$role->add_cap('delete_users');
		$role->add_cap('promote_users');
		$role->add_cap('list_users');
		$role->add_cap('remove_users');
	});

	// Remove additional ACF extended menu items
	add_action('admin_menu', function () {
		global $submenu, $admin_submenu_backup;
		$remove_menus = [
			'edit.php?post_type=acf-field-group' => [
				'edit.php?post_type=acf-post-type',
				'edit.php?post_type=acfe-dop',
				'edit-tags.php?taxonomy=acf-field-group-category',
				'edit.php?post_type=acfe-dbt',
				'edit.php?post_type=acfe-form',
				'acfe-settings',
				'edit.php?post_type=acfe-template'
			],
			'options-general.php' => ['acfe-options'],
			'tools.php' => [
				'edit.php?post_type=acfe-dpt',
				'edit.php?post_type=acfe-dt',
				'acfe-rewrite-rules',
				'acfe-scripts'
			]
		];
		foreach ($remove_menus as $parent_slug => $submenus) {
			foreach ($submenus as $submenu_slug) {
				foreach ($submenu[$parent_slug] as $k => $sub) {
					if (in_array($submenu_slug, $submenu[$parent_slug][$k])) {
						$admin_submenu_backup[$parent_slug][$k] = $submenu[$parent_slug][$k];
						unset($submenu[$parent_slug][$k]);
					}
				}
			}
		}
	}, 999);

	// Add ACF extended pages for ACF screens
	add_action('current_screen', function ($screen) {
		global $submenu, $admin_submenu_backup;
		if (str_contains($screen->id, 'acf') && count($admin_submenu_backup)) {
			foreach ($admin_submenu_backup as $parent_slug => $submenus_array) {
				foreach ($submenus_array as $submenu_array) {
					$submenu[$parent_slug][] = $submenu_array;
				}
			}
		}
	});
}

add_action('after_setup_theme', '\lqx\setup\theme_setup');
