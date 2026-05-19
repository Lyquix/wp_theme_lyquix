<?php

/**
 * alerts.php - Lyquix alerts module
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

namespace lqx\modules\alerts;

/**
 * Get alerts from site options
 *
 * @return array - Array of alerts
*/
function rest_route() {
	$content = get_field('alerts_module_content', 'option');

	if (!$content) return [];

	$content = array_map(function ($alert) {
		// Add a unuque id to alert
		$alert['id'] = 'alert-' . md5(json_encode($alert));

		$wp_tz = wp_timezone();

		// Parse start_date in WP timezone (ACF stores dates in the site's local timezone)
		$alertStartDate = $alert['start_date'] ? (new \DateTimeImmutable($alert['start_date'], $wp_tz))->getTimestamp() : false;

		// Convert start_date to UTC
		if ($alertStartDate !== false) {
			$alert['start_date'] = wp_date('c', $alertStartDate);
		}

		// Parse expiration in WP timezone
		$alertExpiration = $alert['expiration'] ? (new \DateTimeImmutable($alert['expiration'], $wp_tz))->getTimestamp() : false;

		// Convert expiration to UTC
		if ($alertExpiration !== false) {
			$alert['expiration'] = wp_date('c', $alertExpiration);
		}

		return $alert;
	}, $content);

	// Filter out alerts that are not enabled or have expired
	$content = array_values(array_filter($content, function ($alert) {
		// Skip items that aren't enabled
		if ($alert['enabled'] != 'y') return false;
		// Skip items with no content
		if (!$alert['heading'] && !$alert['body']) return false;
		// Skip items that haven't started yet
		if ($alert['start_date'] != '' && time() < strtotime($alert['start_date'])) return false;
		// Skip items that have expired
		if ($alert['expiration'] != '' && time() > strtotime($alert['expiration'])) return false;
		// Skip items that don't match user's region
		if (!\lqx\regions\is_region_match($alert['related_regions'])) return false;
		return true;
	}));
	return $content;
}

// Register a REST API endpoint to get the alerts from site options
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/alerts', [
		'methods' => 'GET',
		'callback' => '\lqx\modules\alerts\rest_route',
		'permission_callback' => '__return_true',
	]);
});

// Render the alerts module
function render($settings = null) {
	require \lqx\modules\get_renderer('alerts');
}
