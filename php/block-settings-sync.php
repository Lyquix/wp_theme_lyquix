<?php

/**
 * block-settings-sync.php - Export/import Block Global Settings, Styles, and Presets
 *
 * ACF Local JSON (acf-json/) only versions field group *structure*. The actual
 * values admins save into block Global Settings, Styles, and Presets live in
 * wp_options and are never written to git. This file exports those values to
 * a JSON file in the child theme automatically on every save (acf/save_post),
 * and falls back to reading that file (acf/load_value) whenever an
 * environment's database doesn't have a value yet — so the file is kept in
 * sync and re-applied automatically, with no manual export/import step. A
 * WP-CLI command pair and an admin UI are also provided to force a one-off
 * re-sync.
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

namespace lqx\block_settings;

/**
 * Discover all registered Lyquix block names (content blocks + layout blocks,
 * parent and child theme) by scanning their block.json files.
 *
 * @return array Sorted, deduplicated list of bare block names (no 'lqx/' prefix)
 */
function get_block_names() {
	$search_dirs = [
		get_template_directory()   . '/php/blocks/',
		get_template_directory()   . '/php/layouts/',
		get_stylesheet_directory() . '/php/custom/blocks/',
	];

	$block_names = [];
	foreach ($search_dirs as $dir) {
		if (!is_dir($dir)) continue;
		foreach (glob($dir . '*/block.json') ?: [] as $json_file) {
			$json = json_decode(file_get_contents($json_file), true);
			if (!empty($json['name'])) {
				$block_names[] = str_replace('lqx/', '', $json['name']);
			}
		}
	}

	$block_names = array_unique($block_names);
	sort($block_names);
	return $block_names;
}

/**
 * Collect the Global Settings, Styles, and Presets option values for every block.
 *
 * @return array Block name => ['global' => ..., 'styles' => ..., 'presets' => ...]
 */
function export_data() {
	$data = [];
	foreach (get_block_names() as $block_name) {
		$data[$block_name] = [
			'global'  => get_field($block_name . '_block_global', 'option'),
			'styles'  => get_field($block_name . '_block_styles', 'option'),
			'presets' => get_field($block_name . '_block_presets', 'option'),
		];
	}
	return $data;
}

/**
 * Default path for the exported JSON file. Lives in the child theme's
 * acf-json/ directory (not the parent theme) so it sits next to the field
 * group definitions it provides values for, and is versioned with the
 * site-specific project repo rather than the shared framework, which gets
 * replaced wholesale on theme updates.
 *
 * The file has no top-level "key" property, so ACF's own Local JSON scanner
 * (which requires one) silently ignores it — see
 * includes/local-json.php:410 in ACF Pro.
 *
 * @return string
 */
function default_file_path() {
	return get_stylesheet_directory() . '/acf-json/block-settings.json';
}

/**
 * Write the current block Global Settings, Styles, and Presets values to a JSON file.
 *
 * @param string|null $path Defaults to default_file_path()
 * @return string The path written to
 */
function export_to_file($path = null) {
	$path = $path ?: default_file_path();
	file_put_contents($path, json_encode(export_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	return $path;
}

/**
 * Read a previously exported JSON file and write its values back into the
 * ACF options for each block. Keys absent from the file, or set to null, are
 * left untouched (a block whose settings were never saved has nothing to import).
 *
 * @param string|null $path Defaults to default_file_path()
 * @return array List of block names actually updated
 */
function import_from_file($path = null) {
	$path = $path ?: default_file_path();
	if (!file_exists($path)) return [];

	$data = json_decode(file_get_contents($path), true);
	if (!is_array($data)) return [];

	$updated = [];
	foreach ($data as $block_name => $settings) {
		if (!is_array($settings)) continue;
		foreach (['global', 'styles', 'presets'] as $key) {
			if (!array_key_exists($key, $settings) || $settings[$key] === null) continue;
			update_field($block_name . '_block_' . $key, $settings[$key], 'option');
		}
		$updated[] = $block_name;
	}

	return $updated;
}

/**
 * Given a field name like "accordion_block_global", returns ['accordion', 'global'],
 * or null if the name doesn't match one of the three tracked suffixes.
 *
 * @param string $name
 * @return array|null [$block_name, $setting_key]
 */
function match_field_name($name) {
	foreach (['global', 'styles', 'presets'] as $key) {
		$suffix = '_block_' . $key;
		if (substr($name, -strlen($suffix)) === $suffix) {
			return [substr($name, 0, -strlen($suffix)), $key];
		}
	}
	return null;
}

/**
 * Lazily reads and caches the exported JSON file for the duration of the request.
 *
 * @return array|null
 */
function get_file_data() {
	static $data = null;
	static $loaded = false;
	if (!$loaded) {
		$path = default_file_path();
		$data = file_exists($path) ? json_decode(file_get_contents($path), true) : null;
		$loaded = true;
	}
	return is_array($data) ? $data : null;
}

/**
 * Falls back to the exported file's value whenever a block's Global Settings,
 * Styles, or Presets option has no value saved in this environment's database
 * yet (e.g. a fresh install, or a new dev pulling the repo for the first time).
 * A value that's already saved in the database always takes priority, so this
 * never clobbers changes made locally but not yet re-exported.
 *
 * This makes the exported file act as the "default" for these fields, mirroring
 * how ACF Local JSON already works for field group structure.
 */
add_filter('acf/load_value', function ($value, $post_id, $field) {
	if ($post_id !== 'options') return $value;
	if ($value !== null && $value !== false && $value !== '') return $value;

	$match = match_field_name($field['name'] ?? '');
	if (!$match) return $value;

	[$block_name, $setting_key] = $match;
	$file_data = get_file_data();

	return $file_data[$block_name][$setting_key] ?? $value;
}, 10, 3);

/**
 * Auto re-export the file every time an admin saves any block's Global Settings,
 * Styles, or Presets, so the git-tracked file always mirrors the database and
 * devs never have to remember to click "Export".
 *
 * Runs after the existing options.php save hooks (cache rebuild at 40, cache
 * flush at 50), since export_data() should read the freshly-saved values.
 */
add_action('acf/save_post', function ($post_id) {
	if ($post_id !== 'options') return;
	export_to_file();
}, 60);

// WP-CLI commands: `wp lqx export-block-settings [--file=<path>]`, `wp lqx import-block-settings [--file=<path>]`
if (defined('WP_CLI') && WP_CLI) {
	\WP_CLI::add_command('lqx export-block-settings', function ($args, $assoc_args) {
		$path = \lqx\block_settings\export_to_file($assoc_args['file'] ?? null);
		\WP_CLI::success("Exported block settings to $path");
	});

	\WP_CLI::add_command('lqx import-block-settings', function ($args, $assoc_args) {
		$updated = \lqx\block_settings\import_from_file($assoc_args['file'] ?? null);
		if (!count($updated)) {
			\WP_CLI::warning('No block settings were imported (file missing or empty)');
			return;
		}
		\WP_CLI::success('Imported settings for: ' . implode(', ', $updated));
	});
}

/**
 * Renders the Sync Block Settings admin page, with Export/Import buttons that
 * call the same export_to_file()/import_from_file() functions as the WP-CLI commands.
 *
 * @return void
 */
function render_admin_page() {
	$path = default_file_path();
	?>
<div class="wrap">
	<h1><?= __('Sync Block Settings'); ?></h1>
	<hr class="wp-header-end">
	<p><?= sprintf(__('This file is kept in sync automatically: every time a block\'s Global Settings, Styles, or Presets are saved, <code>%s</code> is re-written, and any environment whose database doesn\'t have a value yet (a fresh install, or a new dev pulling the repo) automatically falls back to reading it. The buttons below are only needed to force a one-off re-sync.'), esc_html($path)); ?></p>
	<p>
		<button class="button button-primary" id="lqxExportBtn"><?= __('Re-export now'); ?></button>
		<button class="button" id="lqxImportBtn"><?= __('Force import from file'); ?></button>
	</p>
	<p id="lqxSyncResult"></p>
</div>
<script>
	jQuery(document).ready(function($) {
		function run(action) {
			$('#lqxSyncResult').text('Working...');
			$.post(ajaxurl, {
				action: action,
				_ajax_nonce: '<?php echo wp_create_nonce('lqx-sync-block-settings'); ?>'
			}, function(response) {
				$('#lqxSyncResult').text(response);
			});
		}
		$('#lqxExportBtn').on('click', function() { run('lqx_export_block_settings'); });
		$('#lqxImportBtn').on('click', function() {
			if (confirm('This will overwrite this site\'s saved block Global Settings, Styles, and Presets with the contents of the file. Continue?')) {
				run('lqx_import_block_settings');
			}
		});
	});
</script>
	<?php
}

add_action('admin_menu', function () {
	add_submenu_page(
		'site-settings',
		'Sync Block Settings',
		'Sync Block Settings',
		'manage_options',
		'sync-block-settings',
		'\lqx\block_settings\render_admin_page'
	);
}, 9999);

add_action('wp_ajax_lqx_export_block_settings', function () {
	if (!check_ajax_referer('lqx-sync-block-settings')) wp_die('Nonce validation failed');
	if (!current_user_can('manage_options')) wp_die('Not allowed');

	$path = \lqx\block_settings\export_to_file();
	echo 'Exported to ' . esc_html($path);
	wp_die();
});

add_action('wp_ajax_lqx_import_block_settings', function () {
	if (!check_ajax_referer('lqx-sync-block-settings')) wp_die('Nonce validation failed');
	if (!current_user_can('manage_options')) wp_die('Not allowed');

	$updated = \lqx\block_settings\import_from_file();
	echo count($updated)
		? 'Imported settings for: ' . esc_html(implode(', ', $updated))
		: 'No block settings file found to import, or it was empty.';
	wp_die();
});
