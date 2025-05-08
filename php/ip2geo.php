<?php

/**
 * ip2geo.php - Lyquix IP geolocation
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

namespace lqx\ip2geo;

function rest_route()
{

	$db_filename = 'GeoLite2-City';
	$db_basepath = wp_get_upload_dir()['basedir'] . '/' . $db_filename;
	$db_tar_gz = $db_basepath . '.tar.gz';
	$db_tar = $db_basepath . '.tar';
	$db = $db_basepath . '.mmdb';
	$db_exists = file_exists($db);

	// If database doesn't exist, attempt to download and extract it
	if (!$db_exists || ($db_exists && (time() - filemtime($db) > get_theme_mod('ip2geo_max_db_age', '90') * 86400))) {
		$download_url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key=' . get_theme_mod('ip2geo_maxmind_license_key', '') . '&suffix=tar.gz';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $download_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$download_content = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
		$curl_err = curl_errno($ch);

		// Check if the response code is 200
		if ($curl_info['http_code'] !== 200 || $curl_err !== 0) return ['error' => 'Error downloading database', 'curl_info' => $curl_info, 'curl_err' => curl_error($ch)];
		curl_close($ch);

		$fh = fopen($db_tar_gz, 'w');
		fwrite($fh, $download_content);
		fclose($fh);

		// Decompress gz
		$p = new \PharData($db_tar_gz);
		$p->decompress();
		unlink($db_tar_gz);

		// Unarchive tar
		$p = new \PharData($db_tar);
		$p->extractTo(wp_get_upload_dir()['basedir']);
		unlink($db_tar);

		// Move file, and delete directory
		$extract_dir = glob(wp_get_upload_dir()['basedir'] . '/' . $db_filename . '*', GLOB_ONLYDIR)[0];
		rename($extract_dir . '/' . $db_filename . '.mmdb', $db);
		foreach (glob($extract_dir . '/*') as $file) unlink($file);
		rmdir($extract_dir);
	}

	// Get IP address from HTTP request
	$ip = get_theme_mod('ip2geo_test_ip_address', '') ?: $_SERVER[get_theme_mod('ip2geo_ip_address_header', 'REMOTE_ADDR')];

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

	return [
		'city' => $geo['city']['names']['en'],
		'subdivision' => $geo['subdivisions'][0]['names']['en'],
		'country' => $geo['country']['iso_code'],
		'continent' => $geo['continent']['code'],
		'time_zone' => $geo['location']['time_zone'],
		'lat' => $geo['location']['latitude'],
		'lon' => $geo['location']['longitude'],
		'radius' => $geo['location']['accuracy_radius'], // in km
		'ip' => $ip
	];
}

// Register a REST API endpoint to get the alerts from site options
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/ip2geo', [
		'methods' => 'GET',
		'callback' => '\lqx\ip2geo\rest_route',
		'permission_callback' => '__return_true',
	]);
});
