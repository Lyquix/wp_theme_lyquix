<?php

/**
 * share.php - Lyquix social sharing module
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

namespace lqx\modules\share;

/**
 * Get social share link from platform
 * @param  string $platform - Platform to get link for
 * 		- facebook, linkedin, twitter, reddit, whatsapp, telegram, email, print
 * @param  string $url - URL to share
 * 		- Default: current post URL
 * @param  string $title - Title to share
 * 		- Default: current post title
 *
 * @return string - Share link
 * 		- null if no platform is provided
 * 		- Share link for platform
 */
function get_share_link($platform, $url = null, $title = null) {
	// Return if no platform is provided
	if (!$platform) return null;

	// Default values for $url and $title
	if (!$url) $url = get_permalink();
	if (!$title) $title = get_the_title();

	// Encode $url and $title
	$url = urlencode($url);
	$title = urlencode($title);

	$share_link = '';
	switch ($platform) {
		case 'facebook':
			$share_link = 'https://www.facebook.com/sharer/sharer.php?u=' . $url;
			break;
		case 'linkedin':
			$share_link = 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url;
			break;
		case 'x':
			$share_link = 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title;
			break;
		case 'reddit':
			$share_link = 'https://www.reddit.com/submit?url=' . $url . '&title=' . $title;
			break;
		case 'whatsapp':
			$share_link = 'https://wa.me/?text=' . $title . '%20' . $url;
			break;
		case 'telegram':
			$share_link = 'https://t.me/share/url?url=' . $url . '&text=' . $title;
			break;
		case 'email':
			$share_link = 'mailto:?subject=' . $title . '&body=' . $url;
			break;
		case 'print':
			$share_link = 'javascript:window.print()';
			break;
	}

	return $share_link;
}

/**
 * Get inline style for social icons
 *
 * @param  array $settings - social icons settings
 * 		- style: icon style
 * 		- icon_color: icon color
 * 		- hover_icon_color: icon color on hover
 * 		- background_color: background color
 * 		- hover_background_color: background color on hover
 *
 * @return string - Inline style for social icons
 */
function get_inline_style($settings) {
	$inline_style = '';

	foreach ([
		'style' => 'style',
		'icon-color' => 'icon_color',
		'hover-icon-color' => 'hover_icon_color',
		'bg-color' => 'background_color',
		'hover-bg-color' => 'hover_background_color'
	] as $var => $key) {
		$value = $settings[$key];
		if (empty($value)) continue;
		$inline_style .= "--$var: $value; ";
	}

	return $inline_style;
}

// Render the alerts module
function render($settings = null) {
	require \lqx\modules\get_renderer('share');
}
