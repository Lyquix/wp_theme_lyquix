<?php

/**
 * alerts.php - Lyquix alerts module
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

namespace lqx\modules\alerts;

// Get the alerts from site options
function rest_route() {
	$alerts = get_field('alerts_module_content', 'option');

	if (!$alerts) return [];

	$alerts = array_map(function ($alert) {
		// Add hash to alert
		$alert['hash'] = md5(json_encode($alert));

		// Convert expiration to UTC
		if ($alert['expiration'] != '') {
			$alert['expiration'] = date('c', strtotime($alert['expiration'] . ' ' . get_option('timezone_string')));
		}

		return $alert;
	}, $alerts);

	// Filter out alerts that are not enabled or have expired
	$alerts = array_filter($alerts, function ($alert) {
		return $alert['enabled'] == 'y' && ($alert['expiration'] == '' || time() <= strtotime($alert['expiration']));
	});

	return $alerts;
}

// Register a REST API endpoint to get the alerts from site options
add_action('rest_api_init', function () {
	register_rest_route('wp/v2/options', '/alerts', [
		'methods' => 'GET',
		'callback' => 'lqx\modules\alerts\rest_route',
	]);
});

if (file_exists(get_stylesheet_directory() . '/php/custom/modules/alerts/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/modules/alerts/render.php';
} else {
	require_once get_stylesheet_directory() . '/php/modules/alerts/render.php';
}
