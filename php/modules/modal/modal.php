<?php

/**
 * modal.php - Lyquix modal module
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

namespace lqx\modules\modal;

// Get the modal from site options
function rest_route() {
	$content = get_field('modal_module_content', 'option');
	$settings = get_field('modal_module_settings', 'option');

	if (!$content) return [];

	$content = array_map(function ($modal) use ($settings) {
		// Add hash to modal
		$modal['id'] = 'modal-' . md5(json_encode($modal));

		// Convert expiration to UTC
		if ($modal['expiration'] != '') {
			$modal['expiration'] = date('c', strtotime($modal['expiration'] . ' ' . get_option('timezone_string')));
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
		return $modal['enabled'] == 'y' && ($modal['expiration'] == '' || time() <= strtotime($modal['expiration']));
	});

	return $content;
}

// Register a REST API endpoint to get the modal from site options
add_action('rest_api_init', function () {
	register_rest_route('wp/v2/options', '/modal', [
		'methods' => 'GET',
		'callback' => 'lqx\modules\modal\rest_route',
	]);
});

// Set the Style Preset values for the Lyquix Modules
add_filter('acf/load_field', function ($field) {
	// Field keys
	// TODO: Change these to the correct field keys
	$user = 'field_65c11d4eadade'; // style field
	$choice = 'field_65c11d5f554f8'; // style_name field

	if ($field['key'] == $user) {
		$choice_field = get_field_object($choice);

		// Add an empty choice
		$field['choices'][''] = 'Select';

		while (have_rows($choice_field['parent'], 'option')) {
			the_row();
			$value = get_sub_field($choice, 'option');
			$field['choices'][$value] = $value;

		}
	}
	return $field;
});