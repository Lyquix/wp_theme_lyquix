<?php

/**
 * social.php - Lyquix social icons module
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

// Render the alerts module
function render($settings = null) {
	require \lqx\modules\get_renderer('social');
}
