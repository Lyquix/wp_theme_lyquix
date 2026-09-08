<?php

/**
 * css.php - Enqueues CSS files and render custom CSS
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
	if (\lqx\util\is_local_environment()) $non_min_css = '1';

	// Swiper — same enable/scope decision as the script, see \lqx\js\swiper_enabled()
	if (\lqx\js\swiper_enabled()) {
		$stylesheets[] = [
			'handle' => 'swiper',
			'url' => \lqx\cdn_mirror\get_url('https://cdn.jsdelivr.net/npm/swiper@14/swiper-bundle.min.css'),
			'version' => '14'
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
					'handle' => base_convert(crc32($css_url), 16, 36),
					'url' => \lqx\cdn_mirror\get_url($css_url)
				];
			} elseif (parse_url($css_url, PHP_URL_PATH)) {
				// Relative URL
				// Add leading / if missing
				if (substr($css_url, 0, 1) != '/') $css_url = '/' . $css_url;
				// Check if file exist
				if (file_exists(ABSPATH . $css_url)) {
					$stylesheets[] = [
						'handle' => base_convert(crc32($css_url), 16, 36),
						'url' => abs_url($css_url, get_site_url()),
						'version' => date("YmdHis", filemtime(get_home_path() . $css_url))
					];
				}
			}
		}
	}

	// Custom Project Styles
	if (file_exists(get_stylesheet_directory() . '/css/styles' . ($non_min_css ? '' : '.min') . '.css')) {
		$stylesheets[] = [
			'handle' => 'styles',
			'url' => get_stylesheet_directory_uri() . '/css/styles' . ($non_min_css ? '' : '.min') . '.css',
			'version' => date("YmdHis", filemtime(get_stylesheet_directory() . '/css/styles' . ($non_min_css ? '' : '.min') . '.css'))
		];
	}

	return $stylesheets;
}

function get_critical_css() {
	// Skip if not a single page
	if (!is_singular()) return null;

	$slug = \lqx\critical\slug_path(get_post());

	// Skip pages in exclude_pages_critical_path_css
	$exclude_pages = get_theme_mod('exclude_pages_critical_path_css', '[]');
	if (is_string($exclude_pages)) $exclude_pages = json_decode($exclude_pages, true);
	if (!is_array($exclude_pages)) $exclude_pages = [];
	if (in_array($slug, $exclude_pages)) return null;

	$post_type = get_post_type();

	// Skip post types in exclude_types_critical_path_css
	$exclude_types = get_theme_mod('exclude_types_critical_path_css', '[]');
	if (is_string($exclude_types)) $exclude_types = json_decode($exclude_types, true);
	if (!is_array($exclude_types)) $exclude_types = [];
	if (in_array($post_type, $exclude_types)) return null;

	$slug = str_replace('/', '---', $slug);
	$filename = get_stylesheet_directory() . "/css/critical/{$post_type}" . ($post_type === 'page' ? "-{$slug}" : '') . '.css';

	// Skip if file doesn't exist
	if (!file_exists($filename)) return null;

	return file_get_contents($filename);
}

add_action('wp_enqueue_scripts', function () {
	$critical_css = get_critical_css();
	$stylesheets = get_stylesheets();

	if (get_theme_mod('load_critical_path_css', 1) && $critical_css && !isset($_GET['no-critical-path-css'])) {
		wp_register_style('critical-path-css', false);
		wp_enqueue_style('critical-path-css');
		wp_add_inline_style('critical-path-css', $critical_css);

		foreach ($stylesheets as $css_url) {
			wp_enqueue_style($css_url['handle'], $css_url['url'], [], $css_url['version'] ?? null, 'print');
		}

		add_action('wp_footer', function () use ($stylesheets) {
			$selectors = implode(', ', array_map(function ($s) {
				return '#' . $s['handle'] . '-css';
			}, $stylesheets));
			wp_print_inline_script_tag(
				'document.querySelectorAll(\'' . $selectors . '\').forEach(s => { if (s.sheet) s.media = \'all\'; else s.onload = () => s.media = \'all\'; });',
				['id' => 'lqx-css-media-switch']
			);
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
		if ($custom_css) echo "<style>\n" . str_ireplace('</style', '<\/style', $custom_css) . "\n</style>";
	}
}
