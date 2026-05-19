<?php

/**
 * leaving-site-alert.php - Lyquix leaving site alert module
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

namespace lqx\modules\leaving_site_alert;

/**
 * Get leaving site alert rules from site options.
 * Returns only enabled rules.
 *
 * @return array
 */
function rest_route() {
	$content = get_field('leaving_site_alert_module_content', 'option');

	if (!$content) return [];

	$content = array_filter($content, function ($rule) {
		return isset($rule['enabled']) && $rule['enabled'] === 'y';
	});

	$content = array_map(function ($rule) {
		$rule['id'] = 'lsa-' . md5(json_encode($rule));
		return $rule;
	}, $content);

	return array_values($content);
}

// Register REST API endpoint
add_action('rest_api_init', function () {
	register_rest_route('lyquix/v3', '/leaving-site-alert', [
		'methods' => 'GET',
		'callback' => '\lqx\modules\leaving_site_alert\rest_route',
		'permission_callback' => '__return_true',
	]);
});

function render() {
	require \lqx\modules\get_renderer('leaving-site-alert');
}
