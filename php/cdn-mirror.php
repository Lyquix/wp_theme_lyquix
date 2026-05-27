<?php

/**
 * cdn-mirror.php - Local mirror for external CDN libraries
 *
 * Transparently swaps a CDN URL for a locally mirrored copy when present in
 * wp-content/uploads/lqx-cdn-mirror/. If no local copy exists, the original
 * CDN URL is returned. The mirror is populated/refreshed by the WP-CLI
 * command `wp lqx mirror-libs` (intended to be run from system cron).
 *
 * @version     3.2.0
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

namespace lqx\cdn_mirror;

const MIRROR_SUBDIR = 'lqx-cdn-mirror';

function get_mirror_dir() {
	$dir = wp_upload_dir()['basedir'] . '/' . MIRROR_SUBDIR;
	if (!is_dir($dir)) {
		wp_mkdir_p($dir);
		// Drop an index.php to prevent directory listing
		@file_put_contents($dir . '/index.php', "<?php // Silence is golden.\n");
	}
	return $dir;
}

function get_mirror_url_base() {
	return wp_upload_dir()['baseurl'] . '/' . MIRROR_SUBDIR;
}

function local_filename($cdn_url) {
	$ext = strtolower(pathinfo(parse_url($cdn_url, PHP_URL_PATH), PATHINFO_EXTENSION));
	if (!in_array($ext, ['js', 'css'], true)) $ext = 'bin';
	return substr(md5($cdn_url), 0, 16) . '.' . $ext;
}

function local_path($cdn_url) {
	return get_mirror_dir() . '/' . local_filename($cdn_url);
}

/**
 * If a local mirror exists for $cdn_url, return its public URL (with
 * cache-busting ?v=<mtime>). Otherwise return $cdn_url unchanged.
 * Non-absolute URLs are passed through.
 */
function get_url($cdn_url) {
	if (!is_string($cdn_url) || $cdn_url === '') return $cdn_url;
	if (!parse_url($cdn_url, PHP_URL_SCHEME)) return $cdn_url;

	$path = local_path($cdn_url);
	if (!file_exists($path)) return $cdn_url;

	return get_mirror_url_base() . '/' . local_filename($cdn_url) . '?v=' . filemtime($path);
}

/**
 * Build the canonical list of CDN URLs that should be mirrored. Mirrors the
 * conditional logic in js.php/css.php so the cron can pre-download everything
 * the theme might enqueue (both prod and dev variants where applicable).
 * Also includes user-added remote URLs from add_js_libraries/add_css_libraries.
 *
 * Filter `lqx_cdn_mirror_urls` allows projects to extend the list.
 */
function get_managed_urls() {
	$urls = [
		// Mobile-Detect — always enqueued
		'https://cdn.jsdelivr.net/npm/mobile-detect@1/mobile-detect.min.js',
	];

	if (get_theme_mod('dayjs', 1)) {
		$urls[] = 'https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js';
		$urls[] = 'https://cdn.jsdelivr.net/npm/dayjs@1/locale/en.js';
	}

	if (get_theme_mod('swiperjs', 1)) {
		$urls[] = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
		$urls[] = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
	}

	// Vue — mirror both prod and dev builds since which one is enqueued depends
	// on the non_min_js theme mod / environment at request time
	if (
		file_exists(get_stylesheet_directory() . '/js/vue.min.js') ||
		file_exists(get_stylesheet_directory() . '/js/vue.js')
	) {
		$urls[] = 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js';
		$urls[] = 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js';
	}

	// User-added libraries (absolute URLs only)
	foreach (['add_js_libraries', 'add_css_libraries'] as $mod) {
		$lines = explode("\n", trim((string) get_theme_mod($mod, '')));
		foreach ($lines as $u) {
			$u = trim($u);
			if ($u !== '' && parse_url($u, PHP_URL_SCHEME)) $urls[] = $u;
		}
	}

	return apply_filters('lqx_cdn_mirror_urls', array_values(array_unique($urls)));
}

/**
 * Download a single URL to the mirror directory.
 *
 * @return array Result with keys: url, status (downloaded|skipped|error), [bytes], [error]
 */
function download($cdn_url, $force = false) {
	if (!parse_url($cdn_url, PHP_URL_SCHEME)) {
		return ['url' => $cdn_url, 'status' => 'error', 'error' => 'Not an absolute URL'];
	}

	$path = local_path($cdn_url);
	if (file_exists($path) && !$force) {
		return ['url' => $cdn_url, 'status' => 'skipped'];
	}

	$response = wp_remote_get($cdn_url, [
		'timeout' => 30,
		'redirection' => 5,
		'user-agent' => 'lqx-cdn-mirror/1.0 (+' . home_url() . ')',
	]);
	if (is_wp_error($response)) {
		return ['url' => $cdn_url, 'status' => 'error', 'error' => $response->get_error_message()];
	}

	$code = (int) wp_remote_retrieve_response_code($response);
	if ($code !== 200) {
		return ['url' => $cdn_url, 'status' => 'error', 'error' => "HTTP $code"];
	}

	$body = wp_remote_retrieve_body($response);
	if ($body === '' || $body === null) {
		return ['url' => $cdn_url, 'status' => 'error', 'error' => 'Empty response body'];
	}

	get_mirror_dir(); // Ensure dir + index.php exist
	$tmp = $path . '.tmp';
	if (file_put_contents($tmp, $body) === false) {
		return ['url' => $cdn_url, 'status' => 'error', 'error' => 'Write to ' . $tmp . ' failed'];
	}
	// Atomic move so partial writes never replace a good mirror
	if (!@rename($tmp, $path)) {
		@unlink($tmp);
		return ['url' => $cdn_url, 'status' => 'error', 'error' => 'Rename to ' . $path . ' failed'];
	}

	return ['url' => $cdn_url, 'status' => 'downloaded', 'bytes' => strlen($body)];
}

/**
 * Refresh every managed URL. Returns an array of per-URL result rows.
 */
function refresh_all($force = false) {
	$results = [];
	foreach (get_managed_urls() as $u) {
		$results[] = download($u, $force);
	}
	return $results;
}

/**
 * Admin-triggered weekly refresh.
 *
 * On admin page loads (once per week, gated by a transient), schedule a
 * background WP-Cron event to fill any missing mirror files. The actual
 * download never runs inline so admin requests are never blocked.
 *
 * force=false means already-mirrored files are skipped — only new or
 * never-downloaded URLs are fetched. To upgrade a library, bump its pinned
 * version in get_managed_urls() (the URL's MD5 changes → new filename →
 * fresh download). For guaranteed scheduling on low-traffic sites, also run
 * `wp lqx mirror-libs` from system cron.
 */
add_action('admin_init', function () {
	if (!current_user_can('manage_options')) return;
	if (wp_doing_ajax() || wp_doing_cron()) return;

	if (get_transient('lqx_cdn_mirror_last_check')) return;

	// Mark as checked immediately so concurrent admin loads don't double-schedule.
	set_transient('lqx_cdn_mirror_last_check', time(), WEEK_IN_SECONDS);

	if (!wp_next_scheduled('lqx_cdn_mirror_refresh')) {
		wp_schedule_single_event(time() + 10, 'lqx_cdn_mirror_refresh');
	}
});

add_action('lqx_cdn_mirror_refresh', function () {
	refresh_all(false);
});

// WP-CLI: `wp lqx regenerate-block-manifest`
// Rebuilds php/blocks/blocks-manifest.php from the block.json files in the
// parent theme. Run this after adding or editing any block.json file.
if (defined('WP_CLI') && WP_CLI) {
	\WP_CLI::add_command('lqx regenerate-block-manifest', function () {
		$blocks_dir = get_template_directory() . '/php/blocks';
		$manifest   = $blocks_dir . '/blocks-manifest.php';

		$entries = [];
		foreach (glob($blocks_dir . '/*/block.json') as $json_file) {
			$data = json_decode(file_get_contents($json_file), true);
			if (!$data || empty($data['name'])) {
				\WP_CLI::warning('Skipped (invalid JSON): ' . $json_file);
				continue;
			}
			$slug = str_replace('lqx/', '', $data['name']);
			$entries[$slug] = $data;
		}
		ksort($entries);

		$php  = "<?php\n";
		$php .= "// Auto-generated — re-run `wp lqx regenerate-block-manifest` after editing block.json files.\n";
		$php .= "// WP 6.7+ block metadata collection: opcache-friendly alternative to per-request glob+JSON decode.\n";
		$php .= 'return ' . var_export($entries, true) . ";\n";

		if (file_put_contents($manifest, $php) === false) {
			\WP_CLI::error('Could not write ' . $manifest);
		}

		\WP_CLI::success(sprintf('Manifest written: %s (%d blocks)', $manifest, count($entries)));
	}, [
		'shortdesc' => 'Regenerate the blocks-manifest.php metadata collection for WP 6.7+ block registration.',
	]);
}

// WP-CLI: `wp lqx mirror-libs [--force]`
if (defined('WP_CLI') && WP_CLI) {
	\WP_CLI::add_command('lqx mirror-libs', function ($args, $assoc) {
		$force = !empty($assoc['force']);
		$results = refresh_all($force);

		$d = $s = $e = 0;
		foreach ($results as $r) {
			switch ($r['status']) {
				case 'downloaded':
					$d++;
					\WP_CLI::log(sprintf('OK   %s  (%d bytes)', $r['url'], $r['bytes']));
					break;
				case 'skipped':
					$s++;
					\WP_CLI::log('SKIP ' . $r['url'] . '  (already mirrored — use --force to redownload)');
					break;
				case 'error':
					$e++;
					\WP_CLI::warning('FAIL ' . $r['url'] . '  ' . ($r['error'] ?? 'unknown'));
					break;
			}
		}
		\WP_CLI::log(sprintf('---  %d downloaded, %d skipped, %d failed', $d, $s, $e));
		if ($e > 0) \WP_CLI::halt(1);
	}, [
		'shortdesc' => 'Download/refresh local mirrors of CDN libraries enqueued by the Lyquix theme.',
		'synopsis' => [
			['type' => 'flag', 'name' => 'force', 'optional' => true,
				'description' => 'Redownload even if a mirror already exists.'],
		],
	]);
}