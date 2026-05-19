<?php

/**
 * fields.php - Utility functions for fields
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

namespace lqx\fields;

add_filter('acf/fields/relationship/result', function ($title, $post, $field, $post_id) {
	// Define emojis for post statuses
	$status_emojis = array(
		'publish' => '🟢',    // Published
		'draft' => '🟡',      // Draft
		'pending' => '📝',    // Pending
		'trash' => '❌',      // Trash
		'future' => '🕰️',    // Scheduled
		'private' => '🚧',    // Private
	);

	// Get the post status
	$status = get_post_status($post->ID);
	$status_emoji = isset($status_emojis[$status]) ? $status_emojis[$status] : '❓';

	// Get the date format from WordPress settings
	$date_format = get_option('date_format');
	$post_date = get_the_date($date_format, $post->ID);

	// Construct the new title
	return sprintf('%s %s [%d] %s', $status_emoji, $title, $post->ID, $post_date);
}, 10, 4);
