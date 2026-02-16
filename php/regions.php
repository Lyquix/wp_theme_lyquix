<?php

/**
 * regions.php - Regionalization functionality for WP Theme Lyquix
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

namespace lqx\regions;

/**
 * Get the user's region from cookie if set
 *
 * @return string|null - The user's region from cookie or null if not set
 */
function get_region_from_cookie() {
	return $_COOKIE['selectedRegion'] ?? null;
}

/**
 * Get the user's region from IP geolocation
 *
 * @return string|null - The user's region from IP geolocation or null if not found
 */
function get_region_from_ip() {
	$regions = get_field('regions', 'option');
	if (!is_array($regions) || !count($regions)) return null;

	// Build region geo data
	$geo_data = [];
	foreach ($regions as $_region) {
		$region_geojson = $_region['geojson'] ?? '';
		if (is_string($region_geojson) && trim($region_geojson) !== '') {
			$decoded = json_decode($region_geojson, true);
			if (is_array($decoded)) {
				$geo_data[] = [
					'alias'  => $_region['alias'] ?? null,
					'geojson'=> $decoded,
				];
			}
		}
	}

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
	$forced_region_post_types = get_field('forced_region_post_types', 'option');
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
	// Check if post type forces the region
	$region_from_post_type = get_region_from_post_type();
	if ($region_from_post_type !== null) return $region_from_post_type;

	// Check if the post forces the region
	$region_from_post = get_region_from_post();
	if ($region_from_post !== null) return $region_from_post;

	// Then check cookie since it reflects user selection
	$region_from_cookie = get_region_from_cookie();
	if ($region_from_cookie !== null) return $region_from_cookie;

	// Finally check IP geolocation as fallback
	$region_from_ip = get_region_from_ip();
	if ($region_from_ip !== null) return $region_from_ip;

	// If no region found, return default based on setting
	if ($no_user_region_meaning === null || !in_array($no_user_region_meaning, ['outside-region', 'everywhere'])) {
		$no_user_region_meaning = get_field('no_user_region_meaning', 'option') ?? 'outside-region'; // TODO we need to create this option field
	}
	return $no_user_region_meaning;
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
	if ($no_content_region_meaning === null || !in_array($no_content_region_meaning, ['everywhere', 'none'])) {
		$no_content_region_meaning = get_field('no_content_region_meaning', 'option') ?? 'everywhere'; // TODO we need to create this option field
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
