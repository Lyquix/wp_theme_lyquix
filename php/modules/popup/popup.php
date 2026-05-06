<?php

/**
 * popup.php - Lyquix popup module
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

namespace lqx\modules\popup;

/**
 * Get the popup from site options
 * 		- Filter out popups that are not enabled or have expired
 * 		- Convert expiration to UTC
 * 		- Convert zero hide delay and dismiss duration to blank
 * 		- Add settings to modal
 * 		- Add a unique id to popup
 * 		- Return array of popups
 *
 * @return array - Array of popups
 */
function rest_route() {
	$content = get_field('popup_module_content', 'option');
	$settings = get_field('popup_module_settings', 'option');

	if (!$content) return [];

	$content = array_map(function ($popup) use ($settings) {
		// Generate a stable hash id excluding the new custom fields for backwards compatibility
		$hash_data = $popup;
		unset($hash_data['item_id'], $hash_data['css_classes']);
		$popup['id'] = 'popup-' . md5(json_encode($hash_data));

		// Override with the custom item_id when provided
		if (!empty($popup['item_id'])) {
			$clean = preg_replace('/\s+/', '-', trim($popup['item_id']));
			$clean = preg_replace('/[^a-zA-Z0-9_-]/', '', $clean);
			if ($clean !== '') $popup['id'] = $clean;
		}
		unset($popup['item_id']);

		// Merge style preset and additional css_classes into one string
		$parts = array_filter([
			!empty($popup['style']) ? $popup['style'] : '',
			!empty($popup['css_classes']) ? $popup['css_classes'] : '',
		]);
		$popup['css_classes'] = implode(' ', $parts);

		$popupExpiration = strtotime($popup['expiration']);

		// Convert expiration to UTC
		if ($popupExpiration !== false) {
			$popup['expiration'] = wp_date('c', $popupExpiration);
		}

		// Convert zero hide delay and dismiss duration to blank
		if ($popup['hide_delay'] == 0) $popup['hide_delay'] = '';
		if ($popup['dismiss_duration'] == 0) $popup['dismiss_duration'] = '';

		// Add settings to popup
		$popup['heading_style'] = $settings['heading_style'] ?? '';

		return $popup;
	}, $content);

	// Filter out popup that are not enabled or have expired
	$content = array_filter($content, function ($popup) {
		// Skip items that aren't enabled
		if ($popup['enabled'] !== 'y') return false;
		// Skip items that have expired
		if ($popup['expiration'] !== '' && time() > strtotime($popup['expiration'])) return false;
		// Skip items that don't match user's region
		if (!\lqx\regions\is_region_match($popup['related_regions'])) return false;
		return true;
	});

	return $content;
}

// Register a REST API endpoint to get the popup from site options
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/popup', [
		'methods' => 'GET',
		'callback' => '\lqx\modules\popup\rest_route',
		'permission_callback' => '__return_true',
	]);
});

// Render the alerts module
function render() {
	require \lqx\modules\get_renderer('popup');
}
