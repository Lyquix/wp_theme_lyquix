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
					'name'   => $_region['name']  ?? null,
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

	$test = ['lat' => $lat, 'lon' => $lon];

	/**
	 * Convert a GeoJSON coordinate pair [lon, lat] to ['lat'=>..., 'lon'=>...]
	 */
	$coord_to_point = static function($coord) {
		if (!is_array($coord) || count($coord) < 2) return null;
		$lon = (float)$coord[0];
		$lat = (float)$coord[1];
		if (!is_finite($lat) || !is_finite($lon)) return null;
		return ['lat' => $lat, 'lon' => $lon];
	};

	/**
	 * Check a point in a GeoJSON Polygon coordinates array:
	 * Polygon coords: [ [ [lon,lat], ... ] , [hole...], ... ]
	 * We only test the outer ring (index 0) for now (common and usually intended).
	 */
	$in_polygon_coords = static function(array $polygonCoords) use ($test, $coord_to_point) {
		if (!isset($polygonCoords[0]) || !is_array($polygonCoords[0])) return false;

		$ring = $polygonCoords[0];
		$poly = [];
		foreach ($ring as $c) {
			$p = $coord_to_point($c);
			if ($p) $poly[] = $p;
		}
		if (count($poly) < 3) return false;

		return is_in_polygon($test, $poly);
	};

	/**
	 * Evaluate a GeoJSON geometry (or a Feature/FeatureCollection) against the test point.
	 * Supports:
	 * - Polygon, MultiPolygon
	 * - Optional circle / rectangle via properties (non-standard patterns)
	 */
	$point_in_geojson = static function(array $geojson) use ($test, $in_polygon_coords) {
		$type = $geojson['type'] ?? null;

		// FeatureCollection
		if ($type === 'FeatureCollection' && isset($geojson['features']) && is_array($geojson['features'])) {
			foreach ($geojson['features'] as $feature) {
				if (is_array($feature) && $point_in_geojson = null) {} // placeholder to avoid PHP notice in some linters
			}
			// We'll handle recursion below by calling ourselves properly (without tricks):
			foreach ($geojson['features'] as $feature) {
				if (!is_array($feature)) continue;
				// Recurse per feature
				if (($feature['type'] ?? null) === 'Feature' || isset($feature['geometry'])) {
					if ((static function(array $f) use (&$geojson, $test, $in_polygon_coords) {
						$properties = (isset($f['properties']) && is_array($f['properties'])) ? $f['properties'] : [];
						$geometry   = (isset($f['geometry']) && is_array($f['geometry'])) ? $f['geometry'] : null;

						// Optional non-standard circle support via properties
						// Example:
						// properties: { shape: "circle", center: [lon,lat] OR {lat,lon}, radius_km: 25 }
						if (isset($properties['shape']) && $properties['shape'] === 'circle') {
							$radius = $properties['radius_km'] ?? $properties['radius'] ?? null;
							$center = $properties['center'] ?? null;

							if (is_array($center) && isset($center['lat'], $center['lon'])) {
								$centerPt = ['lat' => (float)$center['lat'], 'lon' => (float)$center['lon']];
								if ($radius !== null && inCircle($test, $centerPt, $radius)) return true;
							} elseif (is_array($center) && count($center) >= 2) {
								$centerPt = ['lon' => (float)$center[0], 'lat' => (float)$center[1]];
								if ($radius !== null && inCircle($test, $centerPt, $radius)) return true;
							}
						}

						// Optional non-standard rectangle support via properties
						// Example:
						// properties: { shape: "square", corner1: {lat,lon}, corner2: {lat,lon} }
						if (isset($properties['shape']) && ($properties['shape'] === 'square' || $properties['shape'] === 'rectangle')) {
							$c1 = $properties['corner1'] ?? null;
							$c2 = $properties['corner2'] ?? null;
							if (is_array($c1) && is_array($c2) && isset($c1['lat'],$c1['lon'],$c2['lat'],$c2['lon'])) {
								if (inSquare($test, ['lat'=>(float)$c1['lat'],'lon'=>(float)$c1['lon']], ['lat'=>(float)$c2['lat'],'lon'=>(float)$c2['lon']])) {
									return true;
								}
							}
						}

						if (!$geometry) return false;

						$gType = $geometry['type'] ?? null;

						// Polygon
						if ($gType === 'Polygon' && isset($geometry['coordinates']) && is_array($geometry['coordinates'])) {
							return $in_polygon_coords($geometry['coordinates']);
						}

						// MultiPolygon
						if ($gType === 'MultiPolygon' && isset($geometry['coordinates']) && is_array($geometry['coordinates'])) {
							foreach ($geometry['coordinates'] as $polyCoords) {
								if (is_array($polyCoords) && $in_polygon_coords($polyCoords)) return true;
							}
							return false;
						}

						// If someone stored raw "Polygon coordinates" (no wrapper object)
						if (isset($f['coordinates']) && is_array($f['coordinates']) && $gType === null) {
							// Try interpret as Polygon coords
							return $in_polygon_coords($f['coordinates']);
						}

						return false;
					})($feature)) {
						return true;
					}
				}
			}
			return false;
		}

		// Feature
		if ($type === 'Feature') {
			$geometry = (isset($geojson['geometry']) && is_array($geojson['geometry'])) ? $geojson['geometry'] : null;
			$properties = (isset($geojson['properties']) && is_array($geojson['properties'])) ? $geojson['properties'] : [];

			// Non-standard shapes via properties (same logic as above)
			if (isset($properties['shape']) && $properties['shape'] === 'circle') {
				$radius = $properties['radius_km'] ?? $properties['radius'] ?? null;
				$center = $properties['center'] ?? null;

				if (is_array($center) && isset($center['lat'], $center['lon'])) {
					$centerPt = ['lat' => (float)$center['lat'], 'lon' => (float)$center['lon']];
					if ($radius !== null && inCircle($test, $centerPt, $radius)) return true;
				} elseif (is_array($center) && count($center) >= 2) {
					$centerPt = ['lon' => (float)$center[0], 'lat' => (float)$center[1]];
					if ($radius !== null && inCircle($test, $centerPt, $radius)) return true;
				}
			}

			if (isset($properties['shape']) && ($properties['shape'] === 'square' || $properties['shape'] === 'rectangle')) {
				$c1 = $properties['corner1'] ?? null;
				$c2 = $properties['corner2'] ?? null;
				if (is_array($c1) && is_array($c2) && isset($c1['lat'],$c1['lon'],$c2['lat'],$c2['lon'])) {
					if (inSquare($test, ['lat'=>(float)$c1['lat'],'lon'=>(float)$c1['lon']], ['lat'=>(float)$c2['lat'],'lon'=>(float)$c2['lon']])) {
						return true;
					}
				}
			}

			if (!$geometry) return false;

			$gType = $geometry['type'] ?? null;

			if ($gType === 'Polygon' && isset($geometry['coordinates']) && is_array($geometry['coordinates'])) {
				return $in_polygon_coords($geometry['coordinates']);
			}

			if ($gType === 'MultiPolygon' && isset($geometry['coordinates']) && is_array($geometry['coordinates'])) {
				foreach ($geometry['coordinates'] as $polyCoords) {
					if (is_array($polyCoords) && $in_polygon_coords($polyCoords)) return true;
				}
				return false;
			}

			return false;
		}

		// Bare Geometry
		if ($type === 'Polygon' && isset($geojson['coordinates']) && is_array($geojson['coordinates'])) {
			return $in_polygon_coords($geojson['coordinates']);
		}
		if ($type === 'MultiPolygon' && isset($geojson['coordinates']) && is_array($geojson['coordinates'])) {
			foreach ($geojson['coordinates'] as $polyCoords) {
				if (is_array($polyCoords) && $in_polygon_coords($polyCoords)) return true;
			}
			return false;
		}

		return false;
	};

	// Check each region
	foreach ($geo_data as $region) {
		$alias = $region['alias'] ?? null;
		$g     = $region['geojson'] ?? null;
		if (!$alias || !is_array($g)) continue;

		if ($point_in_geojson($g)) {
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
	$forced_region_post_types = get_field('forced_region_post_types', 'option'); // TODO we need to create this option field
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
		$forced_region = get_field('forced_region', $post_id); // TODO do we have this field? For example in Landing pages?
		if (is_array($related_region) && $related_region[0] !== '') {
			return $related_region[0];
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
 * Check if a point is inside a polygon (ray casting).
 * poly is an array of vertices: [['lat'=>..., 'lon'=>...], ...]
 * Known limitation: doesn't handle polygons crossing poles or the international date line.
 *
 * @param array $test ['lat'=>..., 'lon'=>...]
 * @param array $poly array of vertices (each vertex array has 'lat' and 'lon')
 */
function is_in_polygon(array $test, array $poly) {
	$testLat = (float)($test['lat'] ?? NAN);
	$testLon = (float)($test['lon'] ?? NAN);

	if (!is_finite($testLat) || !is_finite($testLon) || count($poly) < 3) {
		return false;
	}

	$oddNodes = false;
	$j = count($poly) - 1;

	for ($i = 0; $i < count($poly); $i++) {
		$iLat = (float)($poly[$i]['lat'] ?? NAN);
		$iLon = (float)($poly[$i]['lon'] ?? NAN);
		$jLat = (float)($poly[$j]['lat'] ?? NAN);
		$jLon = (float)($poly[$j]['lon'] ?? NAN);

		// If any vertex is invalid, mirror TS behavior? TS would likely produce NaN math => false-ish.
		// Here we fail fast.
		if (!is_finite($iLat) || !is_finite($iLon) || !is_finite($jLat) || !is_finite($jLon)) {
			return false;
		}

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
