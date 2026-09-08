<?php

/**
 * ip2geo.php - Lyquix IP geolocation
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

namespace lqx\ip2geo;

// Cron hook that refreshes the GeoLite2 database
const UPDATE_HOOK = 'lqx_ip2geo_update_db';

// Guards against two overlapping downloads of a ~60MB archive
const LOCK_TRANSIENT = 'lqx_ip2geo_updating';

/**
 * Absolute path of the GeoLite2 database.
 *
 * @return string
 */
function database_path() {
	return wp_get_upload_dir()['basedir'] . '/GeoLite2-City.mmdb';
}

/**
 * Is the database missing, or older than the configured maximum age?
 *
 * @return bool
 */
function database_is_stale() {
	$db = database_path();
	if (!file_exists($db)) return true;

	return time() - filemtime($db) > (int) get_theme_mod('ip2geo_max_db_age', '90') * 86400;
}

/**
 * Download and install the GeoLite2 database.
 *
 * Runs on cron rather than inside a request: the archive is tens of megabytes and
 * has to be decompressed and untarred, which is far too slow to do while a visitor
 * waits, and doing it inline let concurrent requests all start the same download.
 *
 * @return true|\WP_Error
 */
function update_database() {
	$license_key = get_theme_mod('ip2geo_maxmind_license_key', '');
	if (!$license_key) return new \WP_Error('lqx_ip2geo_no_key', 'No MaxMind license key configured');

	// Another run is already working on it
	if (get_transient(LOCK_TRANSIENT)) return new \WP_Error('lqx_ip2geo_locked', 'An update is already running');
	set_transient(LOCK_TRANSIENT, 1, 15 * MINUTE_IN_SECONDS);

	$basedir = wp_get_upload_dir()['basedir'];
	$db_filename = 'GeoLite2-City';
	$db_basepath = $basedir . '/' . $db_filename;
	$db_tar_gz = $db_basepath . '.tar.gz';
	$db_tar = $db_basepath . '.tar';
	$db = database_path();

	try {
		$download_url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . $license_key . '&suffix=tar.gz';

		$response = wp_remote_get($download_url, ['timeout' => 300, 'stream' => true, 'filename' => $db_tar_gz]);

		if (is_wp_error($response)) {
			// The message can carry the URL, and the URL carries the license key
			return new \WP_Error('lqx_ip2geo_download_failed', 'Error downloading database');
		}

		if (wp_remote_retrieve_response_code($response) !== 200) {
			if (file_exists($db_tar_gz)) unlink($db_tar_gz);
			return new \WP_Error('lqx_ip2geo_download_failed', 'Error downloading database', [
				'http_code' => wp_remote_retrieve_response_code($response)
			]);
		}

		// Decompress gz
		$p = new \PharData($db_tar_gz);
		$p->decompress();
		unlink($db_tar_gz);

		// Unarchive tar
		$p = new \PharData($db_tar);
		$p->extractTo($basedir, null, true);
		unlink($db_tar);

		// Move file, and delete directory
		$extract_dirs = glob($basedir . '/' . $db_filename . '_*', GLOB_ONLYDIR);
		if (empty($extract_dirs)) return new \WP_Error('lqx_ip2geo_extract_failed', 'Could not find extracted database');

		$extract_dir = $extract_dirs[0];
		rename($extract_dir . '/' . $db_filename . '.mmdb', $db);
		foreach (glob($extract_dir . '/*') as $file) unlink($file);
		rmdir($extract_dir);

		return true;
	} finally {
		delete_transient(LOCK_TRANSIENT);
	}
}
add_action(UPDATE_HOOK, '\lqx\ip2geo\update_database');

// Keep the database fresh in the background
add_action('init', function () {
	if (!wp_next_scheduled(UPDATE_HOOK)) wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', UPDATE_HOOK);
});

// Don't leave the schedule behind when the theme is swapped out
add_action('switch_theme', function () {
	wp_clear_scheduled_hook(UPDATE_HOOK);
});

function rest_route()
{
	$db = database_path();

	// The database is refreshed on cron. If it isn't there yet, ask for one now and
	// answer without it rather than making this visitor wait for the download.
	if (!file_exists($db)) {
		if (!get_transient(LOCK_TRANSIENT) && !wp_next_scheduled(UPDATE_HOOK, [])) {
			wp_schedule_single_event(time(), UPDATE_HOOK);
		}
		return ['error' => 'Geolocation database unavailable'];
	}

	if (database_is_stale() && !get_transient(LOCK_TRANSIENT)) wp_schedule_single_event(time(), UPDATE_HOOK);

	// Get IP address from HTTP request, honouring the site's configured header
	$ip = get_theme_mod('ip2geo_test_ip_address', '')
		?: \lqx\util\get_client_ip(get_theme_mod('ip2geo_ip_address_header', 'REMOTE_ADDR'));

	// Sanitize IP address
	$ip = filter_var($ip, FILTER_VALIDATE_IP);

	// Catch if IP address is invalid
	if (!$ip) return ['error' => 'Invalid IP address'];

	// Check if IP address is private
	if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) return ['error' => 'Private IP address'];

	// Include required classes
	require_once 'ip2geo/Reader.php';
	require_once 'ip2geo/Decoder.php';
	require_once 'ip2geo/InvalidDatabaseException.php';
	require_once 'ip2geo/Metadata.php';
	require_once 'ip2geo/Util.php';

	// Get location data
	$reader = new Reader($db);
	$geo = $reader->get($ip);
	$reader->close();

	// Response scope: ip and accuracy radius are intentionally omitted from the
	// public REST response to minimize PII exposure. Server-side callers that
	// need them can read $_SERVER directly or inline this logic.
	return [
		'city' => $geo['city']['names']['en'] ?? null,
		'subdivision' => $geo['subdivisions'][0]['names']['en'] ?? null,
		'country' => $geo['country']['iso_code'] ?? null,
		'continent' => $geo['continent']['code'] ?? null,
		'time_zone' => $geo['location']['time_zone'] ?? null,
		'lat' => $geo['location']['latitude'] ?? null,
		'lon' => $geo['location']['longitude'] ?? null,
	];
}

// Register a REST API endpoint to get the alerts from site options
add_action('rest_api_init', function () {
	\lqx\rest\register_public_route('ip2geo', [
		'methods' => 'GET',
		'callback' => '\lqx\ip2geo\rest_route',
	]);
});
