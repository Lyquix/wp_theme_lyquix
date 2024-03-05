<?php

/**
 * update.php - Theme update checker and downloader
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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

function theme_update_notice() {
	// Get the current theme version
	$theme = wp_get_theme();
	$theme_version = $theme->get('Version');

	// Check in Github the latest released version of the theme
	$github_url = 'https://api.github.com/repos/Lyquix/wp_theme_lyquix/releases/latest';
	$github_response = wp_remote_get($github_url);
	if (!is_wp_error($github_response)) {
		$github_body = wp_remote_retrieve_body($github_response);
		$github_data = json_decode($github_body);

		// If the latest version is newer than the current version, show the update notice
		if (version_compare($github_data->tag_name, $theme_version, '>')) {
			// Add update notice
			echo '<div class="notice notice-update is-dismissible">';
			echo '<p>Lyquix Theme has an update available! <a href="https://github.com/Lyquix/wp_theme_lyquix/releases/latest">Download the Latest Release</a></p>';
			echo '</div>';
		}
	}

}

// Check if the user is an admin viewing a dashboard page
if (get_theme_mod('feat_theme_update', '1') === '1' && is_admin() && current_user_can('manage_options') && !wp_doing_ajax() && (!defined('DOING_AJAX') || !DOING_AJAX)) {
	add_action('admin_notices', '\lqx\update\theme_update_notice');
}
