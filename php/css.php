<?php

/**
 * css.php - Enqueues CSS files and render custom CSS
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

namespace lqx\css;

// Convert relative URLs to absolute URLs
function abs_url($rel, $base) {
	if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;
	if ($rel[0] == '#' || $rel[0] == '?') return $base . $rel;
	extract(parse_url($base));
	$path = preg_replace('#/[^/]*$#', '', $path);
	if ($rel[0] == '/') $path = '';
	$abs = $host . $path . '/' . $rel;
	$re = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];
	for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
	}
	return $scheme . '://' . $abs;
}

function enqueue_styles() {
	global $wp_styles;

	// Get styles to remove
	$remove_css_libraries = explode("\n", trim(get_theme_mod('remove_css_libraries', '')));
	foreach ($remove_css_libraries as $i => $url) $remove_css_libraries[$i] = abs_url(trim($url), get_site_url());

	// Dequeue matching styles
	if (count($remove_css_libraries)) {
		foreach ($wp_styles->registered as $css_code => $x) {
			$css_url = abs_url($wp_styles->registered[$css_code]->src, get_site_url());
			if (in_array($css_url, $remove_css_libraries)) wp_dequeue_style($css_code);
		}
	}

	// Array to store all stylesheets to be loaded
	$stylesheets = [];

	// Use non minified version?
	$non_min_css = get_theme_mod('non_min_css', '0');

	// Swiper
	if (get_theme_mod('swiperjs', 1)) {
		$stylesheets[] = [
			'handle' => 'swiper',
			'url' => 'https://cdn.jsdelivr.net/npm/swiper@11.0.5/swiper-bundle.min.css',
			'version' => '11.0.5'
		];
	}

	// Additional CSS Libraries
	$add_css_libraries = explode("\n", trim(get_theme_mod('add_css_libraries', '')));
	foreach ($add_css_libraries as $css_url) {
		$css_url = trim($css_url);
		if ($css_url) {
			// Check if stylesheet is local or remote
			if (parse_url($css_url, PHP_URL_SCHEME)) {
				// Absolute URL
				$stylesheets[] = [
					'handle' => base_convert(crc32($css_url), 10, 36),
					'url' => $css_url
				];
			} elseif (parse_url($css_url, PHP_URL_PATH)) {
				// Relative URL
				// Add leading / if missing
				if (substr($css_url, 0, 1) != '/') $css_url = '/' . $css_url;
				// Check if file exist
				if (file_exists(ABSPATH . $css_url)) {
					$stylesheets[] = [
						'handle' => base_convert(crc32($css_url), 10, 36),
						'url' => abs_url($css_url, get_site_url()),
						'version' => date("YmdHis", filemtime(get_home_path() . $css_url))
					];
				}
			}
		}
	}

	// Custom Project Styles
	if (file_exists(get_template_directory() . '/css/styles' . ($non_min_css ? '' : '.min') . '.css')) {
		$stylesheets[] = [
			'handle' => 'styles',
			'url' => get_template_directory_uri() . '/css/styles' . ($non_min_css ? '' : '.min') . '.css',
			'version' => date("YmdHis", filemtime(get_template_directory() . '/css/styles' . ($non_min_css ? '' : '.min') . '.css'))
		];
	}

	// Queue styles
	foreach ($stylesheets as $css_url) {
		wp_enqueue_style($css_url['handle'], $css_url['url'], [], array_key_exists('version', $css_url) ? $css_url['version'] : null);
	}
}

add_action('wp_enqueue_scripts', '\lqx\css\enqueue_styles', 100);

function render_page_custom_css() {
	// Render page custom CSS
	if (function_exists('get_field')) {
		$custom_css = get_field('custom_css');
		if ($custom_css) echo "<style>\n" . $custom_css . "\n</style>";
	}
}
