<?php

/**
 * social.php - Lyquix Social icons and sharing module
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

namespace lqx\modules\social;

/**
 * Get platform name from URL
 * @param  string $url - URL to check
 */
function get_platform($url) {
	// Check if $url is a valid URL
	if (!filter_var($url, FILTER_VALIDATE_URL)) return null;

	// Get the domain name from $url
	$domain = parse_url($url, PHP_URL_HOST);

	$platform = null;
	switch (true) {
		case strpos($domain, 'facebook.com') !== false:
			$platform = 'Facebook';
			break;
		case strpos($domain, 'linkedin.com') !== false:
			$platform = 'LinkedIn';
			break;
		case strpos($domain, 'youtube.com') !== false:
			$platform = 'YouTube';
			break;
		case strpos($domain, 'twitter.com') !== false:
		case strpos($domain, 'x.com') !== false:
			$platform = 'X';
			break;
		case strpos($domain, 'instagram.com') !== false:
			$platform = 'Instagram';
			break;
		case strpos($domain, 'github.com') !== false:
			$platform = 'GitHub';
			break;
		case strpos($domain, 'threads.net') !== false:
			$platform = 'Threads';
			break;
		case strpos($domain, 'tiktok.com') !== false:
			$platform = 'TikTok';
			break;
		default:
			$platform = 'Unknown';
	}

	return [
		'name' => $platform,
		'code' => strtolower($platform)
	];
}

/**
 * Get social share link from platform
 * @param  string $platform - Platform to get link for
 * @param  string $url - URL to share
 * @param  string $title - Title to share
 */
function get_share_link($platform, $url = null, $title = null) {
	// Return if no platform is provided
	if(!$platform) return null;

	// Default values for $url and $title
	if(!$url) $url = get_permalink();
	if(!$title) $title = get_the_title();

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
 * @param  array $settings - social icons settings
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

if (file_exists(get_stylesheet_directory() . '/php/custom/modules/social/render.php')) {
	require_once get_stylesheet_directory() . '/php/custom/modules/social/render.php';
} else {
	require_once get_stylesheet_directory() . '/php/modules/social/render.php';
}
