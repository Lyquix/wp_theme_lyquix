<?php

/**
 * client-ip.php - Client IP resolution shared by the theme and the early region detection
 *
 * @version     3.5.0
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

// No WordPress functions in here: regions.php loads this file from wp-config.php, before WordPress,
// so the early region detection resolves the visitor's IP exactly like the ip2geo REST endpoint does.

namespace lqx\util;

/**
 * Resolve the client's IP address.
 *
 * Walks the usual proxy headers in order of trustworthiness and returns the first
 * value that parses as an IP, falling back to REMOTE_ADDR. Note that every header
 * before REMOTE_ADDR is client-supplied and can be forged: this is good enough for
 * geolocation and rate limiting, and must not be used for authorization.
 *
 * @param string|null $preferred - header to consult first, e.g. the site's configured
 *                                 ip2geo_ip_address_header. Ignored when empty.
 *
 * @return string - an IP address, or '' when none could be determined
 */
function get_client_ip($preferred = null) {
	$candidates = [
		'HTTP_CF_CONNECTING_IP',
		'HTTP_CF_CONNECTING_IPV6',
		'HTTP_TRUE_CLIENT_IP',
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	];

	if (!empty($preferred)) array_unshift($candidates, $preferred);

	foreach ($candidates as $key) {
		if (empty($_SERVER[$key])) continue;

		$value = trim((string) $_SERVER[$key]);

		// RFC 7239 Forwarded header: for=...
		if ($key === 'HTTP_FORWARDED') {
			foreach (preg_split('/\s*,\s*/', $value) ?: [] as $part) {
				if (stripos($part, 'for=') === false) continue;

				$ip = explode(';', explode('for=', $part, 2)[1], 2)[0];
				$ip = trim($ip, " \t\n\r\0\x0B\"'[]");

				if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
			}

			continue;
		}

		// All other headers: take the first IP (may be comma-separated)
		$ips = preg_split('/\s*,\s*/', $value) ?: [];
		$ip = trim((string) ($ips[0] ?? ''));

		if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
	}

	return '';
}

/**
 * The geolocation test IP only applies when the request is explicitly flagged for testing:
 * a ?lqx-ip2geo-test (or lqx-ip2geo-test cookie) triggers it, and an IP in the flag value
 * overrides the configured one. Without the flag the configured test IP is ignored, so a
 * leftover value in the Customizer ("Test IP Address") can't pin every anonymous visitor's
 * region to it — dev kept a Miami IP set, and every visitor got Florida preselected.
 */
function geolocation_test_ip(string $configured): string {
	$flag = $_GET['lqx-ip2geo-test'] ?? $_COOKIE['lqx-ip2geo-test'] ?? null;
	if ($flag === null) return '';
	if ($flag !== '' && $flag !== '1' && filter_var($flag, FILTER_VALIDATE_IP)) return $flag;
	return $configured;
}
