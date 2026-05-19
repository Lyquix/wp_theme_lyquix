<?php

/**
 * misc.php - Miscelanous theme functionality
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

namespace lqx\misc;

/**
 * Force Gravity Forms to record the real client IP (Cloudflare + common proxy headers).
 */
add_filter('gform_ip_address', function ($ip) {

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

	foreach ($candidates as $key) {
		if (empty($_SERVER[$key])) {
			continue;
		}

		$value = trim((string) $_SERVER[$key]);

		// RFC 7239 Forwarded header: for=...
		if ($key === 'HTTP_FORWARDED') {
			$parts = preg_split('/\s*,\s*/', $value) ?: [];
			foreach ($parts as $part) {
				if (stripos($part, 'for=') === false) {
					continue;
				}

				$ipCandidate = explode('for=', $part, 2)[1];
				$ipCandidate = explode(';', $ipCandidate, 2)[0];
				$ipCandidate = trim($ipCandidate, " \t\n\r\0\x0B\"'[]");

				if (filter_var($ipCandidate, FILTER_VALIDATE_IP)) {
					return $ipCandidate;
				}
			}

			continue;
		}

		// All other headers: take first IP (may be comma-separated)
		$ips = preg_split('/\s*,\s*/', $value) ?: [];
		$ipCandidate = trim((string) $ips[0]);

		if (filter_var($ipCandidate, FILTER_VALIDATE_IP)) {
			return $ipCandidate;
		}
	}

	return $ip;
});
