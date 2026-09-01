<?php

/**
 * setup.php - Theme initial setup
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

namespace lqx\setup;

/**
 * Theme setup
 * 		- Add theme support
 * 		- Load theme styles into editor
 * 		- Remove unnecessary wptexturize filter
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
		// Set full image scaling to 5120x5120
		add_filter('big_image_size_threshold', function ($threshold) { return 5120; });

		add_action('admin_init', function () {
			// Update WordPress default image sizes
			update_option('thumbnail_size_w', 150);
			update_option('thumbnail_size_h', 150);
			update_option('thumbnail_crop', 1);

			update_option('medium_size_w', 1280);
			update_option('medium_size_h', 1280);

			update_option('large_size_w', 2560);
			update_option('large_size_h', 2560);
		});

		add_action('after_setup_theme', function () {
			// Add custom image size
			add_image_size('xsmall', 320, 320);
			add_image_size('small', 640, 640);
			add_image_size('xlarge', 3840, 3840);

			// Remove unwanted image sizes
			remove_image_size('medium_large');
			remove_image_size('1536x1536');
			remove_image_size('2048x2048');
		});

		// Filter intermediate image sizes
		add_filter('intermediate_image_sizes_advanced', function ($sizes) {
			return [
				'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
				'xsmall' => ['width' => 320, 'height' => 320, 'crop' => false],
				'small' => ['width' => 640, 'height' => 640, 'crop' => false],
				'medium' => ['width' => 1280, 'height' => 1280, 'crop' => false],
				'large' => ['width' => 2560, 'height' => 2560, 'crop' => false],
				'xlarge' => ['width' => 3840, 'height' => 3840, 'crop' => false],
			];
		}, 10, 1);

		// Filter available image sizes
		add_filter('intermediate_image_sizes', function ($sizes) {
			return [
				'thumbnail',
				'xsmall',
				'small',
				'medium',
				'large',
				'xlarge'
			];
		}, 10, 1);


		add_action('admin_enqueue_scripts', function ($hook) {
			if ($hook !== 'options-media.php') {
				return;
			}

			$js = <<<JS
			document.addEventListener('DOMContentLoaded', function () {
				const fields = [
					'thumbnail_size_w', 'thumbnail_size_h', 'thumbnail_crop',
					'medium_size_w', 'medium_size_h', 'large_size_w', 'large_size_h'
				];

				fields.forEach(function (id) {
					const el = document.getElementById(id);
					if (el) el.disabled = true;
				});

				const table = document.querySelector('.wrap table');
				if (table) {
					const note = document.createElement('p');
					note.innerHTML = '<strong>Note:</strong> Image sizes are enforced by the theme and cannot be edited here.';
					table.insertAdjacentElement('afterend', note);
				}
			});
			JS;

			// Ensure a script handle exists to attach inline JS to
			wp_enqueue_script('jquery-core');
			wp_add_inline_script('jquery-core', $js);
		});


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
					'advanced-custom-fields-pro/acf.php' => 'Advanced Custom Fields PRO',
					'acf-extended-pro/acf-extended.php' => 'Advanced Custom Fields: Extended PRO',
					'tinymce-advanced/tinymce-advanced.php' => 'Advanced Editor Tools',
					'better-search-replace/better-search-replace.php' => 'Better Search Replace',
					'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
					'gravityforms/gravityforms.php' => 'Gravity Forms',
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

	// Remove the body padding WP core's default theme.json injects since WP 5.9,
	// which interferes with the site's own layout styles
	if (get_theme_mod('feat_remove_body_padding', '1') === '1') {
		add_filter('wp_theme_json_data_default', function ($theme_json) {
			return $theme_json->update_with([
				'version' => 2,
				'styles' => [
					'spacing' => [
						'padding' => [
							'top'    => null,
							'right'  => null,
							'bottom' => null,
							'left'   => null,
						],
					],
				],
			]);
		});
	}

	// Add or remove user management capabilities on the editor user role
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
	} else {
		// Remove user management capabilities if the option is disabled
		add_action('admin_init', function () {
			$role = get_role('editor');
			$role->remove_cap('create_users');
			$role->remove_cap('edit_users');
			$role->remove_cap('delete_users');
			$role->remove_cap('promote_users');
			$role->remove_cap('list_users');
			$role->remove_cap('remove_users');
		});
	}

	// Add or remove Manager role (Editor + User Management)
	if (get_theme_mod('feat_manager_role', '0') === '1') {
		add_action('admin_init', function () {
			$editor = get_role('editor');
			$manager = get_role('manager');

			if (!$manager) {
				// Create Manager role cloning Editor capabilities
				add_role('manager', 'Manager', $editor->capabilities);
				$manager = get_role('manager');
			}

			// Add user management capabilities
			$manager->add_cap('create_users');
			$manager->add_cap('edit_users');
			$manager->add_cap('delete_users');
			$manager->add_cap('promote_users');
			$manager->add_cap('list_users');
			$manager->add_cap('remove_users');

            // Gravity Forms Capabilities
            $editor->add_cap('gravityforms_view_entries');
            $editor->add_cap('gravityforms_edit_entries');
            $editor->add_cap('gravityforms_delete_entries');
            $editor->add_cap('gravityforms_export_entries');
            $editor->add_cap('gravityforms_view_entry_notes');
            $editor->add_cap('gravityforms_edit_entry_notes');
		});
	} else {
		// Remove Manager role if the option is disabled
		add_action('admin_init', function () {
			if (get_role('manager')) {
				remove_role('manager');
			}
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

    // Hide Tools menu from non administrator users
    if (!current_user_can('administrator')) {
        add_action('admin_menu', function () {
            remove_menu_page('tools.php');
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

	// Featured Image Column in Post/CPT Lists
	if (get_theme_mod('feat_featured_image_column', '1') === '1') {
		add_action('init', function() {
			$post_types = get_post_types(['public' => true], 'names');
			foreach ($post_types as $post_type) {
				add_filter('manage_' . $post_type . '_posts_columns', function($columns) {
					$new = [];
					foreach ($columns as $key => $label) {
						$new[$key] = $label;
						if ($key === 'featured') {
							$new['lqx_featured_image'] = __('Featured Image', 'lyquix');
						}
					}
					if (!isset($new['lqx_featured_image'])) {
						$new['lqx_featured_image'] = __('Featured Image', 'lyquix');
					}
					return $new;
				}, 20);

				add_action('manage_' . $post_type . '_posts_custom_column', function($column, $post_id) {
					if ($column !== 'lqx_featured_image') return;
					if (has_post_thumbnail($post_id)) {
						echo get_the_post_thumbnail($post_id, [60, 60], [
							'style'   => 'width:60px;height:auto;border-radius:3px;display:block;',
							'loading' => 'lazy',
						]);
					} else {
						echo '<span aria-hidden="true">&mdash;</span>';
					}
				}, 10, 2);
			}
		});

		add_filter('default_hidden_columns', function($hidden, $screen) {
			if ($screen && isset($screen->base) && $screen->base === 'edit') {
				$hidden[] = 'lqx_featured_image';
			}
			return $hidden;
		}, 10, 2);
	}

	// Post ID Column in Post/CPT Lists
	if (get_theme_mod('feat_post_id_column', '1') === '1') {
		add_action('init', function() {
			$post_types = get_post_types(['public' => true], 'names');
			foreach ($post_types as $post_type) {
				add_filter('manage_' . $post_type . '_posts_columns', function($columns) {
					$new = [];
					foreach ($columns as $key => $label) {
						$new[$key] = $label;
						if ($key === 'title') {
							$new['lqx_post_id'] = __('ID', 'lyquix');
						}
					}
					if (!isset($new['lqx_post_id'])) {
						$new['lqx_post_id'] = __('ID', 'lyquix');
					}
					return $new;
				}, 20);

				add_action('manage_' . $post_type . '_posts_custom_column', function($column, $post_id) {
					if ($column !== 'lqx_post_id') return;
					echo esc_html($post_id);
				}, 10, 2);
			}
		});

		add_filter('default_hidden_columns', function($hidden, $screen) {
			if ($screen && isset($screen->base) && $screen->base === 'edit') {
				$hidden[] = 'lqx_post_id';
			}
			return $hidden;
		}, 10, 2);
	}

	// Suppress warnings and notices from PHP
	if (get_theme_mod('suppress_php_warnings', '0') === '1') {
		add_action('wp', function() {
			error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_CORE_WARNING & ~E_COMPILE_WARNING & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_USER_DEPRECATED);
		});

	}

	//Hide excerpt config from editor sidepanel
	add_action('admin_head', function () {
		echo '<style>.editor-sidebar__panel{.editor-post-featured-image+.components-flex:has(.editor-post-excerpt__dropdown){display: none;}}</style>';
	});

	// Replace WP login logo if a custom logo is set in the Customizer.
	add_action('login_enqueue_scripts', function () {
		// Get custom login image attachment ID from Customizer
		$custom_login_image_id = (int) get_theme_mod('login_logo_image_id', 0);
		if (!$custom_login_image_id) return;

		// Get image URL from attachment ID
		$src = wp_get_attachment_image_src($custom_login_image_id, 'full');

		// Safety check
		if (empty($src[0])) return;
		$url = $src[0];
		?>
		<style>
			/* Custom login logo */
			.login h1 a {
				background-image: url('<?php echo esc_url($url); ?>') !important;
				background-size: 100% !important;
				width: 80% !important;
				margin-top: 24px;
			}
		</style>
		<?php
	});

	// Change login logo URL and title text to site home URL and name
	add_filter('login_headerurl', function () { return home_url('/'); });
	add_filter('login_headertext', function () { return get_bloginfo('name'); });

	// Hide admin bar on frontend for selected roles.
	add_filter('show_admin_bar', function ($show) {
		if (is_admin()) return $show; // only frontend

		$hidden_roles_raw = get_theme_mod('admin_bar_hide_roles', '[]');
		$hidden_roles = is_string($hidden_roles_raw) ? json_decode($hidden_roles_raw, true) : $hidden_roles_raw;
		if (!is_array($hidden_roles)) $hidden_roles = [];

		if (!$hidden_roles || !is_user_logged_in()) return $show;

		$user_roles = wp_get_current_user()->roles ?? [];
		if (!$user_roles) return $show;

		// If user has ANY role in the hidden list, hide the bar
		if (array_intersect($user_roles, $hidden_roles)) return false;

		return $show;
	}, 20);

	// Admin bar collapse/expand behavior with a "notch" (frontend only).
	add_action('wp_enqueue_scripts', function () {
		if (is_admin() || !is_user_logged_in()) return;
		if (!is_admin_bar_showing()) return;

		$collapse_enabled = (string) get_theme_mod('admin_bar_collapse', '0') === '1';
		if (!$collapse_enabled) return;

		// Disable WP's default "bump" that adds margin-top, since we're sliding the bar
		remove_action('wp_head', '_admin_bar_bump_cb');

		$pos = (string) get_theme_mod('admin_bar_notch_position', 'center');
		if (!in_array($pos, ['center', 'left', 'right'], true)) $pos = 'center';

		// CSS: slide admin bar out leaving a notch visible; toggle with body class
		$css = '
			/* Ensure no WP admin-bar bump */
			html { margin-top: 0 !important; }

			:root {
				--lqx-ab-h: 32px;
			}
			@media screen and (max-width: 782px) {
				:root { --lqx-ab-h: 46px; }
			}

			/* Slide bar out, leaving only notch height visible */
			#wpadminbar {
				position: fixed !important;
				top: 0; left: 0; right: 0;
				transform: translateY(calc(-1 * var(--lqx-ab-h)));
				transition: transform 200ms ease;
				will-change: transform;
			}
			body.lqx-adminbar-open #wpadminbar {
				transform: translateY(0);
			}

			/* On smaller screens WP re-lays-out the admin bar and the
			   top-secondary ("Howdy, user") menu can stay visible even while
			   the bar is collapsed. Hide it until the bar is opened. */
			@media screen and (max-width: 782px) {
				#wpadminbar #wp-admin-bar-top-secondary {
					visibility: hidden;
					opacity: 0;
					pointer-events: none;
					transition: opacity 200ms ease;
				}
				body.lqx-adminbar-open #wpadminbar #wp-admin-bar-top-secondary {
					visibility: visible;
					opacity: 1;
					pointer-events: auto;
				}
			}

			/* Notch button */
			#lqx-adminbar-notch {
				position: fixed;
				top: 0;
				height: 16px;
				width: var(--lqx-notch-w);
				z-index: 999999; /* above admin bar */
				border: 0;
				padding: 0;
				cursor: pointer;
				background: rgba(128,128,128,0.65);
				transition: top 200ms ease;
				max-width: 100%;
			}

			/* Position variants */
			body.lqx-notch-center #lqx-adminbar-notch {
				width: 32px;
				left: 50%;
				border-radius: 0 0 32px 32px;
				transform: translateX(-50%);
			}
			body.lqx-notch-left #lqx-adminbar-notch {
				width: 16px;
				left: 0;
				border-radius: 0 0 32px 0;
			}
			body.lqx-notch-right #lqx-adminbar-notch {
				width: 16px;
				right: 0;
				border-radius: 0 0 0 32px;
			}

			/* When open, keep notch visible and aligned */
			body.lqx-adminbar-open #lqx-adminbar-notch {
				top: var(--lqx-ab-h);
			}
		';

		wp_register_style('lqx-adminbar-notch', false);
		wp_enqueue_style('lqx-adminbar-notch');
		wp_add_inline_style('lqx-adminbar-notch', $css);

		// JS: toggle
		$js = '
			(function () {
				var body = document.body;
				if (!body) return;

				// Apply notch position class
				body.classList.add("lqx-notch-' . esc_js($pos) . '");

				// Create notch button
				var btn = document.createElement("button");
				btn.id = "lqx-adminbar-notch";
				btn.type = "button";
				btn.setAttribute("aria-label", "Toggle admin bar");
				btn.setAttribute("aria-expanded", open ? "true" : "false");

				btn.addEventListener("click", function () {
					var isOpen = body.classList.toggle("lqx-adminbar-open");
					btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
				});

				body.appendChild(btn);
			})();
		';

		wp_register_script('lqx-adminbar-notch', '', [], false, true);
		wp_enqueue_script('lqx-adminbar-notch');
		wp_add_inline_script('lqx-adminbar-notch', $js);
	}, 20);

	// Switch system fonts to Inter
	function enqueue_inter($is_frontend = null) {
		wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
		wp_add_inline_style('inter-font', ($is_frontend === true ? '' : 'body, #login, #loginform, .login,') . '
			#wpadminbar, #wpadminbar * {
					font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
		');
	};
	if (get_theme_mod('feat_switch_dashboard_fonts', '1') === '1') {
		add_action('admin_enqueue_scripts', '\lqx\setup\enqueue_inter');
		add_action('login_enqueue_scripts', '\lqx\setup\enqueue_inter');
		add_action('wp_enqueue_scripts', function() {
			if (is_user_logged_in()) \lqx\setup\enqueue_inter(true);
		});
	}
}

add_action('after_setup_theme', '\lqx\setup\theme_setup');

// Block Directory — remove the "Discover more blocks" remote API call from the
// editor. That call is a synchronous fetch that blocks editor bootstrap and is
// irrelevant on managed/production sites.
remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');

// Heartbeat — reduce the editor autosave polling interval from the default 15s
// to 60s. Cuts PHP-worker load during long editing sessions while keeping
// autosave functional. Applies only in admin context (not frontend heartbeat).
add_filter('heartbeat_settings', function ($settings) {
	if (is_admin()) $settings['interval'] = 60;
	return $settings;
});
