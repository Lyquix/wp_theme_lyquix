<?php

/**
 * css.php - Enqueues CSS files and render custom CSS
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

namespace lqx\css;

/**
 * Convert relative URLs to absolute URLs
 *
 * @param string $rel - The relative URL
 * @param string $base - The base URL
 *
 * @return string - The absolute URL
 */
function abs_url($rel, $base) {
	if (empty($base)) throw new \InvalidArgumentException("Base URL cannot be empty.");

	// Parse base URL and ensure it has a valid scheme and host
	$parts = parse_url($base);
	if (!$parts || !isset($parts['scheme']) || !isset($parts['host'])) {
		throw new \InvalidArgumentException("Invalid base URL.");
	}

	// Return the base URL if the relative URL is empty
	if (!$rel || !is_string($rel)) return $base;

	// Check if the relative URL is already an absolute URL
	if (filter_var($rel, FILTER_VALIDATE_URL)) return $rel;

	// Relative is a hash
	if ($rel[0] == '#') return explode('#', $base)[0] . $rel;

	// Relative is a query string
	if ($rel[0] == '?') return explode('?', $base)[0] . $rel;

	// Relative is a path relative to the root
	if ($rel[0] == '/') return (array_key_exists('path', $parts) ? explode($parts['path'], $base)[0] : $base) . $rel;

	// Relative is a path relative to the current directory
	// Base has no path
	if (!array_key_exists('path', $parts)) return $base . '/' . $rel;

	// Base has a path
	$path = preg_replace('#/[^/]*$#', '', $parts['path']);
	return explode($parts['path'], $base)[0] . $path . '/' . $rel;
}


/**
 * Enqueues or dequeues CSS libraries for the WordPress theme.
 *
 * @param array $wp_styles - Global variable containing all registered styles.
 * @param string $remove_css_libraries - The CSS libraries to remove, specified in the theme settings.
 *
 * The function first retrieves the CSS libraries to remove from the theme settings.
 * Then, it dequeues any matching styles from the registered styles.
 *
 * @return void
 */

function get_stylesheets() {
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

	// Force non-minified version on local environments
	if (getenv('WPCONFIG_ENVNAME') == 'local' || substr($_SERVER['HTTP_HOST'], -5) == '.test') $non_min_css = '1';

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

	return $stylesheets;
}

function get_critical_css() {
	$critical_css = null;

	if (is_singular()) {
		$post_type = get_post_type();
		$slug = get_post_field('post_name');
		$critical_css = get_template_directory() . "/css/critical/{$post_type}" . ($post_type === 'page' ? "-{$slug}" : '') . '.css';

		if ($critical_css && file_exists($critical_css)) {
			$critical_css = file_get_contents($critical_css);
		}
	}

	return $critical_css;
}

add_action('wp_enqueue_scripts', function () use ($critical_css) {
	$critical_css = get_critical_css();
	$stylesheets = get_stylesheets();

	if ($critical_css && !isset($_GET['no-critical-path-css'])) {
		wp_register_style('critical-path-css', false);
		wp_enqueue_style('critical-path-css');
		wp_add_inline_style('critical-path-css', $critical_css);

		foreach ($stylesheets as $css_url) {
			wp_enqueue_style($css_url['handle'], $css_url['url'], [], $css_url['version'] ?? null, 'print');
		}

		add_action('wp_footer', function () use ($stylesheets) {
			echo '<script>document.querySelectorAll(\'';
			echo implode(', ', array_map(function ($s) use ($stylesheets) {
				return '#' . $s['handle'] . '-css';
			}, $stylesheets));
			echo '\').forEach(s => { if (s.sheet) s.media = \'all\'; else s.onload = () => s.media = \'all\'; });</script>';
		});
	} else {
		foreach ($stylesheets as $css_url) {
			wp_enqueue_style($css_url['handle'], $css_url['url'], [], $css_url['version'] ?? null);
		}
	}
}, 0);

// Renders custom CSS for the page
function render_page_custom_css() {
	if (function_exists('get_field')) {
		$custom_css = get_field('custom_css');
		if ($custom_css) echo "<style>\n" . $custom_css . "\n</style>";
	}
}
