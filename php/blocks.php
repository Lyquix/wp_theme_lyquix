<?php

/**
 * blocks.php - Lyquix Gutenberg blocks
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
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
//  Instead add directories under /php/custom/blocks and use block.json files

namespace lqx\blocks;

// Process overrides for settings presets
function process_overrides($settings) {
	$processed = [];
	foreach ($settings as $key => $value) {
		// Check if the key ends in _override_group
		if (substr($key, -15) == '_override_group') {
			// Extract the key name by removing _override_group
			$original_key = substr($key, 0, -15);

			// Check if the override is set to true
			if ($value[$original_key . '_override'] === true) {
				// Get the override value
				if ($value[$original_key]) {
					$processed[$original_key] = $value[$original_key];
				}
			}
		} elseif ($value) {
			// Check if $value is an array
			if (is_array($value)) {
				// Recursively process the overrides
				$processed[$key] = process_overrides($value);
			} else {
				$processed[$key] = $value;
			}
		}
	}

	return $processed;
}

// Recursively removes any keys from $settings that are null or ''
// Only associative arrays (not list/sequential) are traversed
function remove_empty_settings($settings) {
	# Assumes settings are associative arrays
	foreach ($settings as $key => $value) {
		if (is_array($value)) {
			if (\lqx\util\array_is_list($value)) {
				// If value is a list array, remove it if empty (do not traverse it)
				if (!count($value)) unset($settings[$key]);
			} else {
				// Traverse associative arrays
				$value = remove_empty_settings($value);
				if (!count($value)) {
					// Remove the key if the array is empty
					unset($settings[$key]);
				} else {
					$settings[$key] = $value;
				}
			}
		} elseif ($value === null || $value === '') {
			// Remove any primitive values that are null or ''
			unset($settings[$key]);
		}
	}

	return $settings;
}

// Recursively merge seetings and overrides
// Only associative arrays (not list/sequential) are traversed
function merge_settings($settings, $override) {
	$settings = is_array($settings) ? $settings : [];
	$override = is_array($override) ? $override : [];

	# Assumes settings are associative arrays
	foreach ($override as $key => $value) {
		if (is_array($value)) {
			if (\lqx\util\array_is_list($value)) {
				// If value is a list array, replace the value, do not traverse it
				$settings[$key] = $value;
			} else {
				if (isset($settings[$key])) {
					// If the setting is being overrident, traverse associative arrays
					$settings[$key] = merge_settings($settings[$key], $value);
				} else {
					// Otherwise, just set the value
					$settings[$key] = $value;
				}
			}
		} else {
			// Set the value for primitive values
			$settings[$key] = $value;
		}
	}

	return $settings;
}

// Get the settings for a block
function get_settings($block, $post_id = null) {
	// If $block is a string, convert it to an array
	if (is_string($block)) {
		$block = [
			'name' => $block,
			'anchor' => '',
			'className' => ''
		];
	}

	// For some reason get_field doesn't work when passing the post ID for the current post
	if ($post_id === get_the_ID()) $post_id = null;

	// Get the block name by removing the lqx/ prefix
	$block_name = str_replace('lqx/', '', $block['name']);

	// Initialize the settings array
	$settings = [
		'global' => get_field($block_name . '_block_global', 'option'),
		'styles' => get_field($block_name . '_block_styles', 'option'),
		'presets' => get_field($block_name . '_block_presets', 'option'),
		'local' => [
			'user' => get_field($block_name . '_block_user', $post_id),
			'admin' => get_field($block_name . '_block_admin', $post_id)
		],
		'processed' => [
			'anchor' => isset($block['anchor']) ? esc_attr($block['anchor']) : '',
			'class' => isset($block['className']) ? $block['className'] : '',
			'hash' => 'id-' . md5(json_encode([get_the_ID(), $block, random_int(1000, 9999)])) // Generate a unique hash for the block
		]
	];

	if ($settings['global'] != null) $settings['processed'] = array_merge($settings['processed'], $settings['global']);

	// Check for user settings
	if ($settings['local']['user'] !== null) {
		// Style presets
		if ($settings['local']['user']['style']) $settings['processed']['class'] .= ' ' . $settings['local']['user']['style'];

		// Check for settings presets
		if (isset($settings['local']['user']['preset']) && $settings['local']['user']['preset'] !== '') {
			foreach ($settings['presets'] as $preset) {
				if ($preset['preset_name'] == $settings['local']['user']['preset']) {
					// Process the overrides
					$settings['processed'] = merge_settings($settings['processed'], remove_empty_settings(process_overrides($preset[$block_name . '_block_admin'])));
					break;
				}
			}
		}
	}

	// Check for admin settings
	if (!empty($settings['local']['admin'])) {
		// Process the overrides
		$settings['processed'] = merge_settings($settings['processed'], remove_empty_settings(process_overrides($settings['local']['admin'])));
	}

	return $settings;
}

function get_content($block, $post_id = null) {
	// If $block is a string, convert it to an array
	if (is_string($block)) $block = ['name' => $block];

	// Remove the lqx/ prefix from the block name
	$block_name = substr($block['name'], 4);

	// For some reason get_field doesn't work when passing the post ID for the current post
	if ($post_id === get_the_ID()) $post_id = null;

	return get_field($block_name . '_block_content', $post_id);
}

function reset_global_settings_ajax() {
	// Check nonce for security
	check_ajax_referer('reset-global-settings');
	$json_dir_path = get_stylesheet_directory() . '/acf-json';
	$json_files    = glob($json_dir_path . '/*.json');
	foreach ($json_files as $json_file) {
		$json_content = file_get_contents($json_file);
		$field_group  = json_decode($json_content, true);
		if (!empty($field_group['fields'])) {
			foreach ($field_group['fields'] as $field) {
				if (strpos($field['name'], '_block_global') !== false || strpos($field['name'], '_module_settings') !== false) {
					// Reset or Clear data
					$value_to_set = isset($field['default_value']) ? $field['default_value'] : '';
					update_field($field['key'], $value_to_set, 'option');
				}
			}
		}
	}
	echo 'Done! The global settings of all Lyquix blocks and modules have been reset to their default values.';
	wp_die();
}

function reset_global_settings_page() {
	?>
<div class="wrap">
	<h1><?= __('Reset Global Settings'); ?></h1>
	<hr class="wp-header-end">
	<div class="wp-ui-notification" style="box-sizing: border-box; width: 100%; padding: 1em; border-radius: 0.5em; margin: 1em auto;">
		<?= __('<strong>Warning!</strong><br>This will reset the global settings of all Lyquix blocks and modules.<br>This action cannot be undone. We recommend that you first take a database backup.'); ?>
	</div>
	<div>
		<button class="custom-reset-button button button-primary wp-ui-notification" id="resetFieldsBtn">
			Reset Global Settings
		</button>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery('#resetFieldsBtn').click(function() {
			if (confirm("Are you sure you want to reset the global settings of all Lyquix blocks and modules to their default values?")) {
				let data = {
					action: "reset_global_settings",
					_ajax_nonce: "<?php echo wp_create_nonce('reset-global-settings'); ?>",
				};
				jQuery.post(ajaxurl, data, function(response) {
					alert(response);
				});
			}
		});
	});
</script>
<?php
}

if (get_theme_mod('feat_gutenberg_blocks', '1') === '1') {
	// Register the Lyquix Modules block category
	add_filter('block_categories_all', function ($categories) {
		$categories[] = [
			'slug'  => 'lqx-module-blocks',
			'title' => 'Lyquix Modules'
		];

		return $categories;
	});

	// Register ACF blocks
	add_action('init', function () {
		// Use glob to find 'block.json' files in the 'blocks' directory
		$matches = array_merge(glob(__DIR__ . '/blocks/*/block.json'), glob(__DIR__ . 'custom/blocks/*/block.json'));

		// Check if any matches were found
		if (!empty($matches)) {
			foreach ($matches as $match) {
				// Get the directory name for each match
				register_block_type(dirname($match));
			}
		}
	});

	// Set the Style Preset values for the Lyquix Modules
	add_filter('acf/load_field', function ($field) {
		$field_keys = [
			// Accordion
			[ // style and style_name fields
				'user' => 'field_656c9b99e9e1f',
				'choice' => 'field_656e7cb6b285f'
			],
			[ // preset and preset_name fields
				'user' => 'field_656c9bb1e9e20',
				'choice' => 'field_656d01578aa30'
			],

			// Banner
			[ // style and style_name fields
				'user' => 'field_657727e6739ff',
				'choice' => 'field_65806108a3e5d'
			],
			[ // preset and preset_name fields
				'user' => 'field_657727e673a6d',
				'choice' => 'field_656d01578aa30'
			],

			// Hero
			[ // style and style_name fields
				'user' => 'field_657217f48ca53',
				'choice' => 'field_657761228bf9d'
			],
			[ // preset and preset_name fields
				'user' => 'field_657218058ca54',
				'choice' => 'field_657766304a9cc'
			],

			// Gallery
			[ // style and style_name fields
				'user' => 'field_6577582aed940',
				'choice' => 'field_65806199e7ed0'
			],
			[ // preset and preset_name fields
				'user' => 'field_6577582aed9a8',
				'choice' => 'field_658061d6e7ed2'
			],

			// Tabs
			[ //  style and style_name fields
				'user' => 'field_656f866617343',
				'choice' => 'field_656f879ccf606'
			],
			[ // preset and preset_name fields
				'user' => 'field_656f866617344',
				'choice' => 'field_656f87fcef854'
			],

			// Slider
			[ //  style and style_name fields
				'user' => 'field_659d51caf3d2a',
				'choice' => 'field_659d2fcdd2f8e'
			],
			[ // preset and preset_name fields
				'user' => 'field_659d5299a2521',
				'choice' => 'field_659d3012b3e36'
			]
		];

		foreach ($field_keys as $k) {
			if ($field['key'] == $k['user']) {
				$choice_field = get_field_object($k['choice']);

				// Add an empty choice
				$field['choices'] = ['' => 'Select'];

				while (have_rows($choice_field['parent'], 'option')) {
					the_row();
					$value = get_sub_field($k['choice'], 'option');
					$field['choices'][$value] = $value;
				}
			}
		}
		return $field;
	});

	// Add admin page for resetting global settings
	add_action('admin_menu', function() {
		add_submenu_page(
			'site-settings',
			'Reset Global Settings',
			'Reset Global Settings',
			'manage_options',
			'reset-global-settings',
			'\lqx\blocks\reset_global_settings_page'
		);
	}, 9999);

	// Endpoint for resetting global settings AJAX
	add_action('wp_ajax_reset_global_settings', '\lqx\blocks\reset_global_settings_ajax');
}
