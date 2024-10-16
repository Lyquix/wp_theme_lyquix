<?php

/**
 * update.php - Theme update checker and downloader
 *
 * @version     3.0.0
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

namespace lqx\update;

/**
 * Check if the theme has an update available and show a notice in the admin dashboard if it does
 *
 * @return bool
 * 		True if the theme has an update available, false otherwise
 */
function theme_update_notice() {
	// Get the current theme version
	$theme = wp_get_theme();
	$theme_version = $theme->get('Version');

	// Check if we have a cached version of the latest release
	$tag_name = '';
	if (file_exists(__DIR__ . '/update.json')) {
		if (filemtime(__DIR__ . '/update.json') > strtotime('-1 day')) {
			$github_data = json_decode(file_get_contents(__DIR__ . '/update.json'), true);
			$tag_name = $github_data['tag_name'];
		}
	}

	if (!$tag_name) {
		// Check in Github the latest released version of the theme
		$github_url = 'https://api.github.com/repos/Lyquix/wp_theme_lyquix/releases/latest';
		$github_response = wp_remote_get($github_url);
		if (!is_wp_error($github_response)) {
			$github_body = wp_remote_retrieve_body($github_response);
			$github_data = json_decode($github_body, true);
			if (array_key_exists('tag_name', $github_data)) {
				$tag_name = $github_data['tag_name'];
				file_put_contents(__DIR__ . '/update.json', json_encode($github_data, JSON_PRETTY_PRINT));
			}
		}
	}

	// If the latest version is newer than the current version, show the update notice
	if ($tag_name && version_compare($tag_name, $theme_version, '>')) {
		// Add update notice
		echo '<div class="notice notice-update is-dismissible">';
		echo '<p>Lyquix Theme has an update available! <a href="https://github.com/Lyquix/wp_theme_lyquix/releases/latest">Download the Latest Release</a></p>';
		echo '</div>';
	}
}

// Check if the user is an admin viewing a dashboard page
if (get_theme_mod('feat_theme_update', '1') === '1' && is_admin() && current_user_can('manage_options') && !wp_doing_ajax() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	add_action('admin_notices', '\lqx\update\theme_update_notice');
}
