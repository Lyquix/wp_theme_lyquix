<?php

/**
 * blocks.php - Lyquix Gutenberg blocks
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
//  Instead add directories under /php/custom/blocks and use block.json files

namespace lqx\blocks;

/**
 * Process overrides for a block
 *
 * @param array $settings The settings for the block
 * 		- The settings array must have the following structure:
 * 			- Each key is a setting name
 * 			- Each value is an array with the following keys:
 * 				- The setting value
 * 				- The override value
 * @return array The processed settings
 */
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

/**
 * Recursively removes any keys from $settings that are null or ''
 * Only associative arrays (not list/sequential) are traversed
 *
 * @param array $settings The settings to process
 * 		- The settings array must have the following structure:
 * 			- Each key is a setting name
 * 			- Each value is an array with the following keys:
 * 				- The setting value
 * 				- The override value
 * @return array The processed settings
*/
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

/**
 * Recursively merge seetings and overrides
 * Only associative arrays (not list/sequential) are traversed
 *
 * @param array $settings The settings to merge
 *  		- The settings array must have the following structure:
 * 			- Each key is a setting name
 * 			- Each value is an array with the following keys:
 * 				- The setting value
 * 				- The override value
 * @param array $override The overrides to apply
 * 		- The overrides array must have the following structure:
 * 			- Each key is a setting name
 * 			- Each value is an array with the following keys:
 * 				- The setting value
 * 				- The override value
 * @return array The merged settings
*/
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

/**
 * Get the settings for a block
 *
 * @param array|string $block The block to get settings for
 * 		- If $block is a string, it will be converted to an array
 * 		- The block array must have the following
 * 			- name: The block name
 * 			- anchor: The block anchor
 * 			- className: The block class name
 * @param int $post_id The post ID to get settings for
 * 		- If the post ID is not provided, it will default to the current post
 * 		- If the post ID is the same as the current post, it will default to null
 * @param string $forced_preset The preset to force
 * 		- If nothing then null
 * 		- If a preset is forced, the user settings will be ignored
 * @param string $forced_style The style to force, if nothing then null
 * 		- If a style is forced, the user settings will be ignored
 * 		- If a preset is forced, the style will be set to the preset style
 *
 * @return array The settings for the block
 */
function get_settings($block, $post_id = null, $forced_preset = null, $forced_style = null) {
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
			'block' => $block_name,
			'anchor' => isset($block['anchor']) ? esc_attr($block['anchor']) : '',
			'class' => isset($block['className']) ? $block['className'] : '',
			'hash' => 'id-' . substr(md5(json_encode([get_the_ID(), $block, random_int(1000, 9999)])), 24), // Generate a unique hash for the block
			'post_id' => $post_id ?? get_the_ID(),
			'preset' => '',
			'style' => ''
		]
	];

	// Initialize the processed settings with the global settings
	if ($settings['global'] !== null) $settings['processed'] = array_merge($settings['processed'], $settings['global']);

	// Check for forced preset and style
	if ($forced_preset !== null || $forced_style !== null) {
		$settings['local']['user'] = [
			'style' => $forced_style,
			'preset' => $forced_preset
		];
	}

	// Check for user settings
	if ($settings['local']['user'] !== null) {
		// Style and preset
		if ($settings['local']['user']['style']) {
			$settings['processed']['style'] = $settings['local']['user']['style'];
			$settings['processed']['class'] .= ' ' . $settings['local']['user']['style'];
		}
		if ($settings['local']['user']['preset']) $settings['processed']['preset'] = $settings['local']['user']['preset'];

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

/**
 * Get the content for a block
 * If the block is a string, it will be converted to an array
 * If the post ID is not provided, it will default to the current post
 * If the post ID is the same as the current post, it will default to null
 * The block name will have the lqx/ prefix removed
 * The content will be retrieved from the ACF field with the block name + '_block_content'
 *
 * @param array|string $block The block to get content for
 * 		- If $block is a string, it will be converted to an array
 *  		- The block array must have the following
 * 			- name: The block name
 * 			- anchor: The block anchor
 * 			- className: The block class name
 * 		- The block name must have the lqx/ prefix
 * 		- The block name must match the ACF field name
 * 		- The block name must match the block directory name
 * 		- The block name must match the block template name
 * @param int $post_id The post ID to get content for
 * 		- If the post ID is not provided, it will default to the current post
 * 		- If the post ID is the same as the current post, it will default to null
 *
 * @return string The content for the block
*/
function get_content($block, $post_id = null) {
	// If $block is a string, convert it to an array
	if (is_string($block)) $block = ['name' => $block];

	// Remove the lqx/ prefix from the block name
	$block_name = substr($block['name'], 4);

	// For some reason get_field doesn't work when passing the post ID for the current post
	if ($post_id === get_the_ID()) $post_id = null;

	return get_field($block_name . '_block_content', $post_id);
}

/**
 * Get the block template
 * If the block is a string, it will be converted to an array
 * If the post ID is not provided, it will default to the current post
 * If the post ID is the same as the current post, it will default to null
 * The block name will have the lqx/ prefix removed
 * The template will be retrieved from the ACF field with the block name + '_block_template'
 *
 * @param array|string $array The block to get the template for
 * 		- If $block is a string, it will be converted to an array
 * 		- The block array must have the following
 * 			- name: The block name
 * 			- anchor: The block anchor
 * 			- className: The block class name
 * 		- The block name must have the lqx/ prefix
 * 		- The block name must match the ACF field name
 * 		- The block name must match the block directory name
 * 		- The block name must match the block template name
 * @param int $keytoFind The post ID to get the template for
 * 		- If the post ID is not provided, it will default to the current post
 * 		- If the post ID is the same as the current post, it will default to null
 *
 * @return string The template for the block
*/
function find_value_by_key($array, $keyToFind) {
	$result = null;
	array_walk_recursive($array, function ($value, $key) use ($keyToFind, &$result) {
		if ($key === $keyToFind) {
			$result = $value;
			return;
		}
	});
	return $result;
}

/**
 * Retrieves all global field groups from JSON files in the acf-json directory of the current theme.
 * A field group is considered global if its name contains '_block_global' or '_module_settings'.
 *
 * @return array An array of global field groups. Each field group is an associative array with details about the field.
 */
function get_global_field_groups() {
	$field_groups = [];

	$json_files = glob(get_stylesheet_directory() . '/acf-json/*.json');
	foreach ($json_files as $json_file) {
		$field_group = json_decode(file_get_contents($json_file), true);
		if (!empty($field_group['fields'])) {
			foreach ($field_group['fields'] as $field) {
				if (strpos($field['name'], '_block_global') !== false || strpos($field['name'], '_module_settings') !== false) {
					$field_groups[] = $field;
				}
			}
		}
	}

	return $field_groups;
}

/**
 * Resets the value of a given field to its default value.
 * If the field is a group, it recursively resets all its sub-fields.
 *
 * @param array $field The field to reset. It's an associative array that includes the field's key, default value, type, and possibly sub-fields.
 *         		- The field's key is used to update the field's value in the database.
 * 			 		  - The default value is used to reset the field's value.
 * 			 		  - The type is used to determine if the field is a group.
 * 			 		  - The sub-fields are used to reset all the field's sub-fields in the case of a group.
 * @param array $ancestors An array of keys representing the field's ancestors in the case of nested groups.
 * 			 		- The ancestors are used to update the field's value in the database.
 * 			 		  - The ancestors are used to reset the field's value.
 * 			 		  - The ancestors are used to reset all the field's sub-fields in the case of a group.
 *
 * @return void
 */
function reset_field($field, $ancestors) {
	$default_value = $field['default_value'] ?? '';
	if (is_array($default_value) && !count($default_value)) $default_value = '';
	$value = [$field['key'] => $default_value];
	if (count($ancestors) > 1) {
		for ($i = count($ancestors) - 1; $i > 0; $i--) {
			$value = [$ancestors[$i] => $value];
		}
	}
	update_field($ancestors[0], $value, 'option');

	if ($field['type'] == 'group' && isset($field['sub_fields'])) {
		foreach ($field['sub_fields'] as $sub_field) {
			\lqx\blocks\reset_field($sub_field, array_merge($ancestors, [$field['key']]));
		}
	}
}

/**
 * Resets the global settings of all Lyquix blocks and modules to their default values.
 * The field groups to reset are provided in the $_POST['field_groups'] array.
 * Each field group is reset by calling the reset_field function.
 *
 * @return void
*/
function reset_global_settings_ajax() {
	// Check nonce for security
	if (!check_ajax_referer('reset-global-settings')) wp_die('Nonce validation failed');

	// Check if field key is set
	if (!isset($_POST['field_groups'])) wp_send_json_error('Field groups not provided');

	$field_groups = \lqx\blocks\get_global_field_groups();

	foreach ($field_groups as $field_group) {
		if (in_array($field_group['name'], $_POST['field_groups']) && isset($field_group['sub_fields'])) {
			foreach ($field_group['sub_fields'] as $sub_field) {
				\lqx\blocks\reset_field($sub_field, [$field_group['name']]);
			}
		}
	}
	echo 'Done! The global settings of the selected Lyquix blocks and modules have been reset to their default values.';
	wp_die();
}

/**
 * Renders the Reset Global Settings page.
 * The page displays a list of all Lyquix blocks and modules with checkboxes to select which ones to reset.
 * The user can select all blocks and modules at once by checking the "Toggle All" checkbox.
 * The user can reset the selected blocks and modules by clicking the "Reset Global Settings" button.
 * The user is prompted to confirm the action before the settings are reset.
 *
 * @return void
*/
function reset_global_settings_page() {
	?>
<div class="wrap">
	<h1><?= __('Reset Global Settings'); ?></h1>
	<hr class="wp-header-end">
	<div class="wp-ui-notification" style="box-sizing: border-box; width: 100%; padding: 1em; border-radius: 0.5em; margin: 1em auto;">
		<?= __('<strong>Warning!</strong><br>This will reset the global settings of all Lyquix blocks and modules.<br>This action cannot be undone. We recommend that you first take a database backup.'); ?>
	</div>
	<div>
		<p>
			<input type="checkbox" id="toggleAll" checked> <label for="toggleAll">Toggle All</label><br>
			<?php
			$field_groups = array_map(function ($value) {
				return $value['name'];
			}, \lqx\blocks\get_global_field_groups());

			// Separate the values into two arrays based on "block" or "module"
			$block_values = array_filter($field_groups, function ($value) {
				return strpos($value, '_block_global') !== false;
			});
			$module_values = array_filter($field_groups, function ($value) {
				return strpos($value, '_module_settings') !== false;
			});

			// Sort the arrays alphabetically
			sort($block_values);
			sort($module_values);

			// Combine the sorted arrays
			$field_groups = array_merge($block_values, $module_values);

			// Render the checkboxes
			foreach ($field_groups as $field_group) {
				echo str_replace(
					'%s', $field_group,
					'<input type="checkbox" name="field_groups[]" id="field_group_%s" value="%s" checked> <label for="field_group_%s">%s</label><br>'
				);
			}
			?>
		</p>
		<button class="custom-reset-button button button-primary wp-ui-notification" id="resetFieldsBtn">
			Reset Global Settings
		</button>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		// Toggle All checkbox functionality
		jQuery('#toggleAll').click(function() {
			jQuery('input[name="field_groups[]"]').prop('checked', this.checked);
		});

		// Check if all checkboxes are checked
		jQuery('input[name="field_groups[]"]').click(function() {
			let allChecked = jQuery('input[name="field_groups[]"]').length === jQuery('input[name="field_groups[]"]:checked').length;
			jQuery('#toggleAll').prop('checked', allChecked);
		});

		// Handle reset button click
		jQuery('#resetFieldsBtn').click(function() {
			let checkedCheckboxes = jQuery('input[name="field_groups[]"]:checked');
			if (checkedCheckboxes.length === 0) {
					alert('Please select at least one block or module.');
					return;
			}

			if (confirm("Are you sure you want to reset the global settings of all Lyquix blocks and modules to their default values?")) {
				let data = {
					action: "reset_global_settings",
					_ajax_nonce: "<?php echo wp_create_nonce('reset-global-settings'); ?>",
					field_groups: checkedCheckboxes.map(function() {
						return this.value;
					}).get()
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

/**
 * Renders the block based on the selected preset and available overrides.
 *
 * @param array $settings The settings for the block.
 * 		- The settings array must have the following structure:
 * 			- global: The global settings for the block.
 * 			- styles: The styles for the block.
 * 			- presets: The presets for the block.
 * 			- local: The local settings for the block.
 * 			- processed: The processed settings for the block.
 * 				- block: The block name.
 * 				- anchor: The block anchor.
 * 				- class: The block class name.
 * 				- hash: A unique hash for the block.
 * 				- post_id: The post ID.
 * 				- preset: The selected preset.
 * 				- style: The selected style.
 * @param string $content The content for the block.
 * 		- The content must be a string.
 * 		- The content must be sanitized.
 *
 * @return void
*/
function render_block($settings, $content) {
	// Get the renderer based on the selected preset and available overrides
	require get_renderer($settings['processed']['block'], $settings['processed']['preset']);
}

/**
 * Load the block renderer based on the selected preset and available overrides
 * or use the default renderer if no preset is selected.
 *
 * @param string $block_name The name of the block.
 * 		- The block name must have the lqx/ prefix.
 * @param string $preset The selected preset.
 * 		- If nothing then null.
 *
 * @return string The path to the renderer file.
*/
function get_renderer($block_name, $preset = null) {
	$dir = get_stylesheet_directory() . '/php/custom/blocks/' . $block_name . '/';

	if ($preset && file_exists($dir . $preset . '.php')) {
		return $dir . $preset . '.php';
	} elseif (file_exists($dir . 'default.php')) {
		return $dir . 'default.php';
	} else {
		return get_stylesheet_directory() . '/php/blocks/' . $block_name . '/default.php';
	}
}

/**
 * Load the block template based on the selected preset and available overrides
 * or use the default template if no preset is selected.
 *
 * @param string $block_name The name of the block.
 * 		- The block name must have the lqx/ prefix.
 * @param string $preset The selected preset.
 * 		- If nothing then null.
 * @param string $sub_template The selected sub-template.
 * 		- If nothing then null.
 *
 * @return string The path to the template file.
*/
function get_template($block_name, $preset = null, $sub_template = null) {
	$dir = get_stylesheet_directory() . '/php/custom/blocks/' . $block_name . '/';
	$default_file = 'default';
	$preset_file = $preset;
	if ($sub_template) {
		$preset_file .= '-' . $sub_template;
		$default_file .= '-' . $sub_template;
	}
	$preset_file .= '.tmpl.php';
	$default_file .= '.tmpl.php';

	if ($preset && file_exists($dir . $preset_file)) {
		return $dir . $preset_file;
	} elseif (file_exists($dir . $default_file)) {
		return $dir . $default_file;
	} else {
		return get_stylesheet_directory() . '/php/blocks/' . $block_name . '/' . $default_file;
	}
}

if (get_theme_mod('feat_content_blocks', '1') === '1') {
	// Register the Lyquix Modules block category
	add_filter('block_categories_all', function ($categories) {
		array_unshift($categories, [
			'slug'  => 'lqx-content-blocks',
			'title' => 'Lyquix Content Blocks'
		]);

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

	// Set the Style Preset values for the Lyquix blocks
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

			// Cards
			[ // style and style_name fields
				'user' => 'field_658db3cbe1430',
				'choice' => 'field_658db3c35c5ac'
			],
			[ // preset and preset_name fields
				'user' => 'field_658db3cbe1486',
				'choice' => 'field_658db3c5e9695'
			],

			// Filters
			[ // style and style_name fields
				'user' => 'field_65f1dd3000026',
				'choice' => 'field_65f1ddf02e615'
			],
			[ // preset and preset_name fields
				'user' => 'field_65f1e8893c299',
				'choice' => 'field_65f20fbeb1be3'
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

			// Hero
			[ // style and style_name fields
				'user' => 'field_657217f48ca53',
				'choice' => 'field_657761228bf9d'
			],
			[ // preset and preset_name fields
				'user' => 'field_657218058ca54',
				'choice' => 'field_657766304a9cc'
			],

			// Logos
			[ // style and style_name fields
				'user' => 'field_65a05775a0471',
				'choice' => 'field_65a0592361823'
			],

			// Slider
			[ //  style and style_name fields
				'user' => 'field_659d51caf3d2a',
				'choice' => 'field_659d2fcdd2f8e'
			],
			[ // preset and preset_name fields
				'user' => 'field_66799b371d5e9',
				'choice' => 'field_659fd4dcc3449'
			],

			// Tabs
			[ //  style and style_name fields
				'user' => 'field_656f866617343',
				'choice' => 'field_656f879ccf606'
			],
			[ // preset and preset_name fields
				'user' => 'field_656f866617344',
				'choice' => 'field_656f87fcef854'
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

	// Load field display logic
	add_action('acf/init', function () {
		if (is_admin()) {
			wp_enqueue_script('custom-acf-js', get_template_directory_uri() . '/php/blocks/field-display.js', ['wp-data', 'acf-input', 'jquery']);
			// Passing to js the url+nonce required for ajax call and the json containing the fields dependencies
			$globalSettings = [];
			$rules = [
				[
					"settings" => [
						"block_name" => "lqx/cards",
						"content_field" => "field_658db3b2c1203",
						"global_field" => "field_658db3c317b3c",
						"presets_field" => "field_658db3c317b95",
						"user_field" => "field_658db3cb6f46c",
						"admin_field" => "field_658db3baec514"
					],
					"rules" => [
						[
							"field" => "subheading",
							"controller" => "show_subheading",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "image",
							"controller" => "show_image",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "icon_image",
							"controller" => "show_icon_image",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "video",
							"controller" => "show_video",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "labels",
							"controller" => "show_labels",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "links",
							"controller" => "show_links",
							"operator" => "==",
							"value" => "y"
						]
					]
				],
				[
					"settings" => [
						"block_name" => "lqx/hero",
						"content_field" => "field_6541322b28452",
						"global_field" => "field_65413f3a9e679",
						"presets_field" => "field_6577644a0a231",
						"user_field" => "field_657217d28ca52",
						"admin_field" => "field_657218d86ffd3"
					],
					"rules" => [
						[
							"field" => "image_override",
							"controller" => "show_image",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "image_mobile",
							"controller" => "show_image",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "video",
							"controller" => "show_image",
							"operator" => "==",
							"value" => "y"
						],
						[
							"field" => "breadcrumbs_override",
							"controller" => "show_breadcrumbs",
							"operator" => "==",
							"value" => "y"
						]
					]
				]
			];
			foreach ($rules as $rule) {
				$globalSettings[] = [
					'key' => $rule['settings']['global_field'],
					'value' => get_field($rule['settings']['global_field'], 'options')
				];
				$globalSettings[] = [
					'key' => $rule['settings']['presets_field'],
					'value' => get_field($rule['settings']['presets_field'], 'options')
				];
			}
			wp_localize_script('custom-acf-js', 'acfObj', array(
				'json' => $rules,
				'globalSettings' => $globalSettings
			));
		}
	});

	// Endpoint for getting ACF fields through AJAX
	add_action('wp_ajax_get_acf_field', function () {
		// Check for nonce for security
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'acf_ajax_nonce')) {
			wp_die('Nonce validation failed');
		}

		// Check if field key is set
		if (isset($_POST['field_key']) && $_POST['field_key'] != '') {
			$field_key = sanitize_text_field($_POST['field_key']);
			$value = get_field($field_key, 'options');
			wp_send_json_success($value);
		} elseif (isset($_POST['field_group'])) {
			$field_group = sanitize_text_field($_POST['field_group']);
			$fieldArray = get_field($field_group, 'options');
			wp_send_json_success(find_value_by_key($fieldArray, $_POST['controller']));
		} else {
			wp_send_json_error('Field key not provided');
		}
	});

	// Add admin page for resetting global settings
	add_action('admin_menu', function () {
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
