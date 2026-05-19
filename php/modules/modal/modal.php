<?php

/**
 * modal.php - Lyquix modal module
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

namespace lqx\modules\modal;

/**
 * Get the modal from site options
 *
 * @return array - Array of modals
 *
*/
function rest_route() {
	$content = get_field('modal_module_content', 'option');
	$settings = get_field('modal_module_settings', 'option');

	if (!$content) return [];

	$content = array_map(function ($modal) use ($settings) {
		// Generate a stable hash id excluding the new custom fields for backwards compatibility
		$hash_data = $modal;
		unset($hash_data['item_id'], $hash_data['css_classes']);
		$modal['id'] = 'modal-' . md5(json_encode($hash_data));

		// Override with the custom item_id when provided
		if (!empty($modal['item_id'])) {
			$clean = preg_replace('/\s+/', '-', trim($modal['item_id']));
			$clean = preg_replace('/[^a-zA-Z0-9_-]/', '', $clean);
			if ($clean !== '') $modal['id'] = $clean;
		}
		unset($modal['item_id']);

		// Merge style preset and additional css_classes into one string
		$parts = array_filter([
			!empty($modal['style']) ? $modal['style'] : '',
			!empty($modal['css_classes']) ? $modal['css_classes'] : '',
		]);
		$modal['css_classes'] = implode(' ', $parts);

		$wp_tz = wp_timezone();

		// Parse start_date in WP timezone (ACF stores dates in the site's local timezone)
		$modalStartDate = $modal['start_date'] ? (new \DateTimeImmutable($modal['start_date'], $wp_tz))->getTimestamp() : false;

		// Convert start_date to UTC
		if ($modalStartDate !== false) {
			$modal['start_date'] = wp_date('c', $modalStartDate);
		}

		// Parse expiration in WP timezone
		$modalExpiration = $modal['expiration'] ? (new \DateTimeImmutable($modal['expiration'], $wp_tz))->getTimestamp() : false;

		// Convert expiration to UTC
		if ($modalExpiration !== false) {
			$modal['expiration'] = wp_date('c', $modalExpiration);
		}

		// Convert zero hide delay and dismiss duration to blank
		if ($modal['hide_delay'] == 0) $modal['hide_delay'] = '';
		if ($modal['dismiss_duration'] == 0) $modal['dismiss_duration'] = '';

		// Add settings to modal
		$modal['heading_style'] = $settings['heading_style'];

		return $modal;
	}, $content);

	// Filter out modal that are not enabled or have expired
	$content = array_filter($content, function ($modal) {
		// Skip items that aren't enabled
		if ($modal['enabled'] != 'y') return false;
		// Skip items that haven't started yet
		if ($modal['start_date'] != '' && time() < strtotime($modal['start_date'])) return false;
		// Skip items that have expired
		if ($modal['expiration'] != '' && time() > strtotime($modal['expiration'])) return false;
		// Skip items that don't match user's region
		if (!\lqx\regions\is_region_match($modal['related_regions'])) return false;
		return true;
	});

	return $content;
}

// Register a REST API endpoint to get the modal from site options
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/modal', [
		'methods' => 'GET',
		'callback' => '\lqx\modules\modal\rest_route',
		'permission_callback' => '__return_true',
	]);
});
