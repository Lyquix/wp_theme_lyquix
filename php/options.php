<?php

/**
 * options.php - WordPress hooks for options
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

/**
 * Rebuild regions-cache.json whenever the ACF options page is saved.
 * The cache is consumed by:
 *   - run_early_ip_region_detect() (called from wp-config.php before WordPress loads)
 *   - get_regions_config() (frontend region detection and block display rules)
 *   - render_lyquix_options() in js.php (region data passed to JavaScript)
 *   - region-selector.php (region list UI)
 */
add_action('acf/save_post', function($post_id) {
	if ($post_id !== 'options') return;

	$regions = get_field('regions', 'option');
	if (!is_array($regions) || !count($regions)) return;

	$export = [];
	$full_regions = [];
	foreach ($regions as $region) {
		$alias   = $region['alias'] ?? null;
		$geojson = $region['geojson'] ?? '';
		$decoded_geojson = null;
		if (is_string($geojson) && trim($geojson) !== '') {
			$decoded = json_decode($geojson, true);
			if (is_array($decoded)) $decoded_geojson = $decoded;
		}
		// Full region data for frontend use (js.php, region-selector.php)
		$full_regions[] = [
			'name'         => $region['name'] ?? null,
			'mobile_label' => $region['mobile_label'] ?? null,
			'alias'        => $alias,
			'phone_number' => $region['phone_number'] ?? null,
			'address'      => $region['address'] ?? null,
			'description'  => $region['description'] ?? null,
			'geojson'      => $decoded_geojson,
		];
		// Geo-only data for point-in-polygon checks
		if ($alias && $decoded_geojson) {
			$export[] = ['alias' => $alias, 'geojson' => $decoded_geojson];
		}
	}

	// Export all settings needed by frontend code
	$config = [
		'regions'                   => $export,
		'full_regions'              => $full_regions,
		'forced_region_post_types'  => get_field('forced_region_post_types', 'option') ?: [],
		'no_user_region_meaning'    => get_field('no_user_region_meaning', 'option') ?? 'outside-region',
		'no_content_region_meaning' => get_field('no_content_region_meaning', 'option') ?? 'everywhere',
		'ip_header'                 => get_theme_mod('ip2geo_ip_address_header', 'REMOTE_ADDR'),
		'test_ip'                   => get_theme_mod('ip2geo_test_ip_address', ''),
		'mmdb_path'                 => wp_get_upload_dir()['basedir'] . '/GeoLite2-City.mmdb',
		'reader_path'               => get_template_directory() . '/php/ip2geo/',
	];

	file_put_contents(
		WP_CONTENT_DIR . '/regions-cache.json',
		json_encode($config, JSON_PRETTY_PRINT)
	);
}, 20);

/**
 * After saving ACF options, mark all option-page rows as autoloaded.
 *
 * ACF stores every option-page field (values + field-key references) as a
 * separate wp_options row with autoload='no'. That causes one SELECT per row
 * on every request. Setting autoload='yes' makes WordPress include all of them
 * in the single wp_load_alloptions() query that runs at boot — before init,
 * before ACF initialises, before any hook fires — eliminating every individual
 * get_option() hit for ACF option-page data at zero extra query cost.
 *
 * Excludes exploded repeater-row fields (e.g. ..._0_start_date) — marking those
 * autoload='yes' changes which cache-invalidation branch WordPress's
 * delete_option() takes (it stops clearing the per-option cache, only the bulk
 * alloptions cache). A paginated repeater that deletes a row and rewrites
 * another row's data into that same slot within one request then reads back a
 * stale cached value, so update_option() wrongly takes the UPDATE path against
 * a row that's already gone — 0 rows affected, save silently lost. Repeater
 * rows are also the case autoloading benefits least: exploding them into the
 * alloptions blob bloats it on every request for data rarely needed there.
 *
 * Triggered at priority 30 (after the cache file is written at priority 20).
 * The admin only needs to save the ACF options page once for this to take effect.
 */
add_action('acf/save_post', function($post_id) {
	if ($post_id !== 'options') return;

	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->options} SET autoload = 'yes'
			 WHERE (option_name LIKE %s OR option_name LIKE %s)
			 AND option_name NOT REGEXP %s
			 AND autoload != 'yes'",
			$wpdb->esc_like('options_') . '%',
			$wpdb->esc_like('_options_') . '%',
			'_[0-9]+_'
		)
	);
}, 30);

/**
 * Build a PHP file cache of all block global/styles/presets settings.
 *
 * On every admin save of the options page, this hook:
 *   1. Discovers all registered block types by scanning block.json files in
 *      both the parent theme (/php/blocks/) and child theme (/php/custom/blocks/).
 *   2. Calls get_field() for each block type's global/styles/presets,
 *      which resolves ACF field definitions and returns the full data
 *      (including defaults for any new sub-fields added to field groups).
 *   3. Writes the result to wp-content/block-settings-cache.php as a PHP
 *      file loaded via opcache — zero DB queries on frontend page loads.
 *
 * This eliminates SELECT queries for both existing and non-existent option
 * rows (the latter arise from sub-fields added after the last admin save).
 * All blocks are covered regardless of whether they have ever been saved.
 *
 * Triggered at priority 40 (after the autoload update at priority 30).
 */
add_action('acf/save_post', function($post_id) {
	if ($post_id !== 'options') return;

	// Scan both parent and child theme block directories for block.json files
	$search_dirs = [
		get_template_directory()   . '/php/blocks/',
		get_stylesheet_directory() . '/php/custom/blocks/',
	];

	$block_names = [];
	foreach ($search_dirs as $dir) {
		if (!is_dir($dir)) continue;
		foreach (glob($dir . '*/block.json') ?: [] as $json_file) {
			$json = json_decode(file_get_contents($json_file), true);
			if (!empty($json['name'])) {
				// Strip the 'lqx/' namespace prefix to get the bare block name
				$block_names[] = str_replace('lqx/', '', $json['name']);
			}
		}
	}

	$cache = [];
	foreach (array_unique($block_names) as $block_name) {
		$cache[$block_name] = [
			'global'  => get_field($block_name . '_block_global', 'option'),
			'styles'  => get_field($block_name . '_block_styles', 'option'),
			'presets' => get_field($block_name . '_block_presets', 'option'),
		];
	}

	$cache_file = WP_CONTENT_DIR . '/block-settings-cache.php';
	file_put_contents($cache_file, '<?php return unserialize(' . var_export(serialize($cache), true) . ');');

	if (function_exists('opcache_invalidate')) {
		opcache_invalidate($cache_file, true);
	}
}, 40);

/**
 * Flush W3 Total Cache when ACF options pages are saved.
 * Triggered at priority 50 (after all other options page hooks).
 */
add_action('acf/save_post', function($post_id) {
	// Only run for specific ACF options pages
	$acf_option_pages = array(
		'site-settings',
		'alerts-content',
		'cta-content',
		'leaving-site-alert-content',
		'modals-content',
		'popups-content',
		'social'
	);

	// Check if this is one of our ACF options pages
	if (!in_array($post_id, $acf_option_pages)) {
		return;
	}

	// Flush W3 Total Cache completely
	if (function_exists('w3tc_flush_posts')) {
        w3tc_flush_posts();
	}
}, 50);
