<?php

/**
 * regions.php - Regionalization functionality for WP Theme Lyquix
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

namespace lqx\regions;

/**
 * Load regions configuration from the cache file (generated on admin save)
 * or fall back to ACF options if the cache file doesn't exist.
 *
 * @return array The regions configuration
 */
function get_regions_config() {
	static $config = null;
	if ($config !== null) return $config;

	// Support both early context (no WP_CONTENT_DIR) and normal WordPress context
	$cache_file = defined('WP_CONTENT_DIR')
		? WP_CONTENT_DIR . '/regions-cache.json'
		: dirname(__DIR__, 3) . '/regions-cache.json';

	if (file_exists($cache_file)) {
		$json = file_get_contents($cache_file);
		$config = json_decode($json, true);
		if (is_array($config)) return $config;
	}

	// Fallback: load from ACF (expensive — only runs when cache file is missing)
	$regions = function_exists('get_field') ? get_field('regions', 'option') : [];
	$config = [
		'regions' => [],
		'full_regions' => [],
		'forced_region_post_types' => function_exists('get_field') ? (get_field('forced_region_post_types', 'option') ?: []) : [],
		'no_user_region_meaning' => function_exists('get_field') ? (get_field('no_user_region_meaning', 'option') ?? 'outside-region') : 'outside-region',
		'no_content_region_meaning' => function_exists('get_field') ? (get_field('no_content_region_meaning', 'option') ?? 'everywhere') : 'everywhere',
	];
	if (is_array($regions)) {
		foreach ($regions as $region) {
			$alias = $region['alias'] ?? null;
			$geojson_str = $region['geojson'] ?? '';
			$decoded_geojson = null;
			if (is_string($geojson_str) && trim($geojson_str) !== '') {
				$decoded = json_decode($geojson_str, true);
				if (is_array($decoded)) $decoded_geojson = $decoded;
			}
			$config['full_regions'][] = [
				'name' => $region['name'] ?? null,
				'mobile_label' => $region['mobile_label'] ?? null,
				'alias' => $alias,
				'phone_number' => $region['phone_number'] ?? null,
				'address' => $region['address'] ?? null,
				'description' => $region['description'] ?? null,
				'geojson' => $decoded_geojson,
			];
			if ($alias && $decoded_geojson) {
				$config['regions'][] = ['alias' => $alias, 'geojson' => $decoded_geojson];
			}
		}
	}
	return $config;
}

/**
 * Check whether any regions are configured.
 * When no regions exist, all regionalization logic should be skipped.
 *
 * @return bool
 */
function has_regions(): bool {
	$config = get_regions_config();
	return !empty($config['full_regions']);
}

/**
 * Get the user's region from cookie if set
 *
 * @return string|null - The user's region from cookie or null if not set
 */
function get_region_from_cookie() {
    return $_COOKIE['selectedRegion'] ?? $_COOKIE['ipDetectedRegion'] ?? null;
}

/**
 * Get the user's region from IP geolocation
 *
 * @return string|null - The user's region from IP geolocation or null if not found
 */
function get_region_from_ip() {
	$config = get_regions_config();
	$geo_data = $config['regions'] ?? [];

	if (!count($geo_data)) return null;

	$geo = \lqx\ip2geo\rest_route();
	$lat = isset($geo['lat']) ? (float)$geo['lat'] : null;
	$lon = isset($geo['lon']) ? (float)$geo['lon'] : null;

	if ($lat === null || $lon === null || !is_finite($lat) || !is_finite($lon)) return null;

	// Check each region
	foreach ($geo_data as $region) {
		$alias = $region['alias'] ?? null;
		$g     = $region['geojson'] ?? null;
		if (!$alias || !is_array($g)) continue;

		if (point_in_geojson($lon, $lat, $g)) {
			return $alias;
		}
	}

	return null;
}

/**
 * Get the user's region from post type if on a relevant post type
 *
 * @return string|null - The user's region from post type or null if not on relevant post type or not set
 */
function get_region_from_post_type() {
	$post_type = get_post_type();
	$config = get_regions_config();
	$forced_region_post_types = $config['forced_region_post_types'] ?? [];
	if (is_array($forced_region_post_types) && in_array($post_type, $forced_region_post_types)) {
		$related_region = get_field('related_regions');
		if (is_array($related_region) && $related_region[0] !== '') {
			return $related_region[0];
		}
	}
	return null;
}

/**
 * Get the user's region from a custom field on the post if set
 *
 * @return string|null - The user's region from custom field or null if not set
 */
function get_region_from_post() {
	$post_id = get_the_ID();
	if ($post_id) {
		$forced_region = get_field('forced_region', $post_id);
		if (is_array($forced_region) && $forced_region[0] !== '') {
			return $forced_region[0];
		}
	}
	return null;
}

/**
 * Get the user's region, checking in order: post type, post custom field, cookie, IP geolocation
 * If no region is found, return a default value (e.g. 'outside-region' or 'everywhere' based on a setting)
 * @param string|null $no_user_region_meaning Optional parameter to specify what it means when no user region is found. If null, it will be determined by a setting (e.g. 'outside-region' or 'everywhere').
 * @return string - The user's region or default value if no region is found
 */
function get_region($no_user_region_meaning = null) {
	// Cache the resolved region for the entire request to avoid
	// re-computing on every is_region_match() call
	static $cached_region = null;
	if ($cached_region !== null && $no_user_region_meaning === null) return $cached_region;

	// Check if post type forces the region
	$region_from_post_type = get_region_from_post_type();
	if ($region_from_post_type !== null) { $cached_region = $region_from_post_type; return $cached_region; }

	// Check if the post forces the region
	$region_from_post = get_region_from_post();
	if ($region_from_post !== null) { $cached_region = $region_from_post; return $cached_region; }

	// Then check cookie since it reflects user selection
	$region_from_cookie = get_region_from_cookie();
	if ($region_from_cookie !== null) { $cached_region = $region_from_cookie; return $cached_region; }

	// Finally check IP geolocation as fallback
	$region_from_ip = get_region_from_ip();
	if ($region_from_ip !== null) { $cached_region = $region_from_ip; return $cached_region; }

	// If no region found, return default based on setting
	if ($no_user_region_meaning === null || !in_array($no_user_region_meaning, ['outside-region', 'everywhere'])) {
		$config = get_regions_config();
		$no_user_region_meaning = $config['no_user_region_meaning'] ?? 'outside-region';
	}
	$cached_region = $no_user_region_meaning;
	return $cached_region;
}

/**
 * Check if the user's region matches any of the content regions, considering the case where content has no regions assigned
 * If content has no regions assigned, the behavior is determined by the $no_content_region_meaning parameter or a setting (e.g. 'everywhere' or 'outside-region')
 * @param array|null $content_regions Array of regions assigned to the content or null if no regions assigned
 * @param string|null $user_region The user's region. If null, it will be determined
 * @param string|null $no_content_region_meaning Optional parameter to specify what it means when content has no regions assigned. If null, it will be determined by a setting (e.g. 'everywhere' or 'none').
 * @return bool True if user's region matches content regions or if content has no regions and $
 */
function is_region_match($content_regions, $user_region = null, $no_content_region_meaning = null) {
	if (!has_regions()) return true;
    $content_regions = is_array($content_regions) ? array_filter($content_regions) : $content_regions;
	if ($no_content_region_meaning === null || !in_array($no_content_region_meaning, ['everywhere', 'none'])) {
		$config = get_regions_config();
		$no_content_region_meaning = $config['no_content_region_meaning'] ?? 'everywhere';
	}

	// No regions assigned to the content
	if (!is_array($content_regions) || !count($content_regions) || !$content_regions) {
		if ($no_content_region_meaning == 'none') return false;
		else return true; // 'everywhere' means content with no regions should be shown to everyone regardless of their region
	}

	if ($user_region === null) $user_region = get_region();

	return in_array($user_region, $content_regions);
}

/**
 * Check if a point is inside a polygon ring using ray casting.
 * Ring is a GeoJSON coordinate array: [[lon, lat], [lon, lat], ...]
 * Known limitation: doesn't handle polygons crossing poles or the international date line.
 *
 * @param float $testLon Longitude of the test point
 * @param float $testLat Latitude of the test point
 * @param array $ring Array of GeoJSON coordinate pairs [lon, lat]
 * @return bool
 */
function is_in_polygon(float $testLon, float $testLat, array $ring): bool {
	if (count($ring) < 3) return false;

	$oddNodes = false;
	$j = count($ring) - 1;

	for ($i = 0; $i < count($ring); $i++) {
		$iLon = (float)$ring[$i][0];
		$iLat = (float)$ring[$i][1];
		$jLon = (float)$ring[$j][0];
		$jLat = (float)$ring[$j][1];

		if (($iLat < $testLat && $jLat >= $testLat) || ($jLat < $testLat && $iLat >= $testLat)) {
			$xIntersect = $iLon + ($testLat - $iLat) / ($jLat - $iLat) * ($jLon - $iLon);
			if ($xIntersect < $testLon) {
				$oddNodes = !$oddNodes;
			}
		}

		$j = $i;
	}

	return $oddNodes;
}

/**
 * Check if a point is inside a GeoJSON Polygon coordinates array (with hole support).
 * Coordinates format: [ outerRing, hole1, hole2, ... ] where each ring is [[lon, lat], ...]
 * Point must be inside the outer ring and NOT inside any hole.
 *
 * @param float $testLon Longitude of the test point
 * @param float $testLat Latitude of the test point
 * @param array $coords GeoJSON Polygon coordinates array
 * @return bool
 */
function is_in_polygon_coords(float $testLon, float $testLat, array $coords): bool {
	if (!isset($coords[0]) || !is_array($coords[0]) || count($coords[0]) < 3) return false;

	// Must be inside outer ring
	if (!is_in_polygon($testLon, $testLat, $coords[0])) return false;

	// Must NOT be inside any hole
	for ($i = 1; $i < count($coords); $i++) {
		if (is_array($coords[$i]) && is_in_polygon($testLon, $testLat, $coords[$i])) return false;
	}

	return true;
}

/**
 * Check if a point is inside a GeoJSON object.
 * Supports FeatureCollection, Feature, Polygon, and MultiPolygon.
 *
 * @param float $testLon Longitude of the test point
 * @param float $testLat Latitude of the test point
 * @param array $geojson Parsed GeoJSON object
 * @return bool
 */
function point_in_geojson(float $testLon, float $testLat, array $geojson): bool {
	$type = $geojson['type'] ?? null;

	// FeatureCollection: any feature matches
	if ($type === 'FeatureCollection' && isset($geojson['features']) && is_array($geojson['features'])) {
		foreach ($geojson['features'] as $feature) {
			if (is_array($feature) && point_in_geojson($testLon, $testLat, $feature)) return true;
		}
		return false;
	}

	// Feature: check geometry
	if ($type === 'Feature') {
		$geometry = (isset($geojson['geometry']) && is_array($geojson['geometry'])) ? $geojson['geometry'] : null;
		if (!$geometry) return false;
		return point_in_geojson($testLon, $testLat, $geometry);
	}

	// Polygon
	if ($type === 'Polygon' && isset($geojson['coordinates']) && is_array($geojson['coordinates'])) {
		return is_in_polygon_coords($testLon, $testLat, $geojson['coordinates']);
	}

	// MultiPolygon: any polygon matches
	if ($type === 'MultiPolygon' && isset($geojson['coordinates']) && is_array($geojson['coordinates'])) {
		foreach ($geojson['coordinates'] as $polyCoords) {
			if (is_array($polyCoords) && is_in_polygon_coords($testLon, $testLat, $polyCoords)) return true;
		}
		return false;
	}

	return false;
}

/**
 * Early IP-based region detection for W3TC cookie group cache key injection.
 *
 * Reads regions-cache.json and the MaxMind GeoLite2 database to detect the
 * visitor's region from their IP address, then sets $_COOKIE['ipDetectedRegion']
 * so W3TC can incorporate it into the Redis cache key on the current request.
 *
 * Designed to be called from wp-config.php before WordPress (and advanced-cache.php)
 * loads. Uses only the polygon functions in this namespace — no WordPress functions
 * required.
 *
 * @return void
 */
function run_early_ip_region_detect() {
	// Skip for admin, cron, and CLI
	if (
		(defined('DOING_CRON') && DOING_CRON) ||
		(defined('DOING_AJAX') && DOING_AJAX) ||
		php_sapi_name() === 'cli' ||
		str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/wp-admin') ||
		str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/wp-login.php') ||
		str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/wp-json')
	) {
		return;
	}

	// Skip if user has an explicit manual selection — their choice takes full priority
	if (!empty($_COOKIE['selectedRegion'])) return;

	// Resolve cache file path (WP_CONTENT_DIR may not be defined in early context)
	$cache_file = defined('WP_CONTENT_DIR')
		? WP_CONTENT_DIR . '/regions-cache.json'
		: dirname(__DIR__, 3) . '/regions-cache.json';

	if (!file_exists($cache_file)) return;

	$config = json_decode(file_get_contents($cache_file), true);
	if (!is_array($config)) return;

	$regions     = $config['regions']                ?? [];
	$mmdb_path   = $config['mmdb_path']              ?? '';
	$reader_path = $config['reader_path']            ?? '';
	$ip_header   = $config['ip_header']              ?? 'REMOTE_ADDR';
	$test_ip     = $config['test_ip']                ?? '';
	$default     = $config['no_user_region_meaning'] ?? 'outside-region';

	if (!$regions || !file_exists($mmdb_path) || !file_exists($reader_path . 'Reader.php')) return;

	$ip = $test_ip ?: ($_SERVER[$ip_header] ?? '');
	$ip = filter_var($ip, FILTER_VALIDATE_IP);
	if (!$ip) return;
	if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) return;

	require_once $reader_path . 'Reader.php';
	require_once $reader_path . 'Decoder.php';
	require_once $reader_path . 'InvalidDatabaseException.php';
	require_once $reader_path . 'Metadata.php';
	require_once $reader_path . 'Util.php';

	try {
		$reader = new \lqx\ip2geo\Reader($mmdb_path);
		$geo    = $reader->get($ip);
		$reader->close();
	} catch (\Exception $e) {
		return;
	}

	$lat = isset($geo['location']['latitude'])  ? (float)$geo['location']['latitude']  : null;
	$lon = isset($geo['location']['longitude']) ? (float)$geo['location']['longitude'] : null;

	if ($lat === null || $lon === null || !is_finite($lat) || !is_finite($lon)) {
		$region = $default;
	} else {
		$region = $default;
		foreach ($regions as $r) {
			if (!empty($r['alias']) && !empty($r['geojson']) && point_in_geojson($lon, $lat, $r['geojson'])) {
				$region = $r['alias'];
				break;
			}
		}
	}

	// Inject into $_COOKIE so W3TC reads it when building the Redis cache key.
	// Only ipDetectedRegion is touched — selectedRegion belongs to the user.
	$_COOKIE['ipDetectedRegion'] = $region;
}

// When this file is loaded before WordPress (e.g. required from wp-config.php for
// early IP region detection), WP functions don't exist yet — skip hook registration
if (!function_exists('add_action')) return;

add_action('acf/save_post', function($post_id) {
    if ($post_id !== 'options') return;

    $regions = get_field('regions', 'option');

    $export = [];
    $full_regions = [];
    if (is_array($regions)) {
        foreach ($regions as $region) {
            $alias       = $region['alias'] ?? null;
            $geojson_str = $region['geojson'] ?? '';
            $decoded_geojson = null;
            if (is_string($geojson_str) && trim($geojson_str) !== '') {
                $decoded = json_decode($geojson_str, true);
                if (is_array($decoded)) $decoded_geojson = $decoded;
            }
            $full_regions[] = [
                'name'         => $region['name'] ?? null,
                'mobile_label' => $region['mobile_label'] ?? null,
                'alias'        => $alias,
                'phone_number' => $region['phone_number'] ?? null,
                'address'      => $region['address'] ?? null,
                'description'  => $region['description'] ?? null,
                'geojson'      => $decoded_geojson,
            ];
            if ($alias && $decoded_geojson) {
                $export[] = ['alias' => $alias, 'geojson' => $decoded_geojson];
            }
        }
    }

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