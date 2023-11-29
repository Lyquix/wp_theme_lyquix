<?php

/**
 * browser-alert.php - Includes alerts for outdated browsers
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

// Set content type headers for js file
header('Content-Type: application/javascript');

// Set headers to prevent browser caching
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Browser data dictionary
$browser_data = [
	'chrome' => [
		'name' => 'Chrome',
		'long_name' => 'Google Chrome',
		'wikidataId' => 'Q777',
		'url' => 'https://www.google.cn/chrome',
		'info' => '&#8220;Get more done with the new Google Chrome. A more simple, secure, and faster web browser than ever, with Google’s smarts built-in.&#8221;'
	],
	'firefox' => [
		'name' => 'Firefox',
		'long_name' => 'Mozilla Firefox',
		'wikidataId' => 'Q698',
		'url' => 'https://www.mozilla.org/firefox/',
		'info' => '&#8220;Faster page loading, less memory usage and packed with features, the new Firefox is here.&#8221;'
	],
	'safari' => [
		'name' => 'Safari',
		'long_name' => 'Apple Safari',
		'wikidataId' => 'Q35773',
		'url' => 'https://www.apple.com/safari/',
		'info' => '&#8220;Safari is faster and more energy efficient than other browsers. You can shop safely and simply in Safari on your Mac.&#8221;'
	],
	'opera' => [
		'name' => 'Opera',
		'long_name' => 'Opera',
		'wikidataId' => 'Q41242',
		'url' => 'https://www.opera.com/',
		'info' => '&#8220;Opera is a secure, innovative browser used by millions around the world with a built-in ad blocker, free VPN, and much more - all for your best browsing experience.&#8221;'
	],
	'edge' => [
		'name' => 'Edge',
		'long_name' => 'Microsoft Edge',
		'wikidataId' => 'Q18698690',
		'url' => 'https://www.microsoft.com/edge',
		'info' => '&#8220;Microsoft Edge offers world-class performance with more privacy, more productivity, and more value while you browse.&#8221;'
	],
	'brave' => [
		'name' => 'Brave',
		'long_name' => 'Brave',
		'wikidataId' => 'Q22906900',
		'url' => 'https://brave.com/',
		'info' => '&#8220;The Brave browser is a fast, private and secure web browser for PC, Mac and mobile.&#8221;'
	],
];

// Detect type of browser from user agent
function browser_type() {
	$ua = $_SERVER['HTTP_USER_AGENT'];

	$browser = null;

	if (preg_match('/MSIE/i', $ua) && !preg_match('/Opera/i', $ua)) {
		$browser = 'ie';
	} elseif (preg_match('/Firefox/i', $ua)) {
		$browser = 'firefox';
	} elseif (preg_match('/Chrome/i', $ua)) {
		$browser = 'chrome';
	} elseif (preg_match('/Safari/i', $ua)) {
		$browser = 'safari';
	} elseif (preg_match('/Opera/i', $ua)) {
		$browser = 'opera';
	} elseif (preg_match('/Edge/i', $ua)) {
		$browser = 'edge';
	} elseif (preg_match('/Brave/i', $ua)) {
		$browser = 'brave';
	}

	return $browser;
}

// Detect the browser version from user agent
function browser_version() {
	$ua = $_SERVER['HTTP_USER_AGENT'];

	$version = null;

	$browser = browser_type();

	if ($browser == 'ie') {
		preg_match('/MSIE (.*?);/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	} elseif ($browser == 'firefox') {
		preg_match('/Firefox\/(.*?)[\s]/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	} elseif ($browser == 'chrome') {
		preg_match('/Chrome\/(.*?)[\s]/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	} elseif ($browser == 'safari') {
		preg_match('/Version\/(.*?)[\s]/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	} elseif ($browser == 'opera') {
		preg_match('/Opera\/(.*?)[\s]/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	} elseif ($browser == 'edge') {
		preg_match('/Edge\/(.*?)[\s]/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	} elseif ($browser == 'brave') {
		preg_match('/Brave\/(.*?)[\s]/', $ua, $matches);
		if (count($matches) > 1) {
			$version = $matches[1];
		}
	}

	return $version;
}

// Get browser name from user agent
function get_browser_version($browser) {
	global $browser_data;

	// Get wikidataId from data dictionary
	if (array_key_exists($browser, $browser_data)) {
		$wikidataId = $browser_data[$browser]['wikidataId'];
	} else return null;

	// Query wikidataId for latest version
	$query = str_replace(["\n", "\t"], '', "SELECT ?version WHERE {
			wd:{$wikidataId} p:P348 [
				ps:P348 ?version;
				pq:P548 wd:Q2804309;
				wikibase:rank wikibase:NormalRank
			].
		} ORDER BY DESC (?version)");

	// Initialize curl
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL => 'https://query.wikidata.org/bigdata/namespace/wdq/sparql?format=json&query=' . urlencode($query),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => [
			'User-Agent: ' . $_SERVER['HTTP_USER_AGENT']
		]
	]);

	// Get response
	$res = curl_exec($curl);

	// Close connection
	curl_close($curl);

	// Decode response
	$res = json_decode($res, true);

	// Store all the version numbers in an array
	$versions = [];
	foreach ($res['results']['bindings'] as $version) {
		// Convert version string to array of integers
		$ver = explode('.', $version['version']['value']);

		// Limit to 2 segments
		$ver = array_slice($ver, 0, 2);

		// For each segment, remove non numeric characters
		$ver = array_map('intval', $ver);

		// Convert back to string
		$ver = implode('.', $ver);

		// Add version to array if not already there
		if(!in_array($ver, $versions)) $versions[] = $ver;
	}

	// Sort the versions array using the version_compare function
	usort($versions, function($a,$b) {
		return -1 * version_compare ( $a , $b );
	});

	// Reverse order
	$versions = array_reverse($versions);

	// Remove duplicates
	//$versions = array_unique($versions);

	// Save versions to data dictionary
	$browser_data[$browser]['version'] = $versions;

	return $versions;
}

// Get all browser versions
function get_all_browsers_versions() {
	global $browser_data;

	$versions = [];

	foreach ($browser_data as $browser => $data) {
		$versions[$browser] = get_browser_version($browser);
	}

	file_put_contents('browsers.json', json_encode($versions, JSON_PRETTY_PRINT));
}

// Check if browser is outdated
function browser_outdated() {
	global $browser_data;

	$browser = browser_type();
	$version = browser_version();

	$res = [
		'browser' => $browser,
		'user_version' => $version,
	];

	if (array_key_exists($res['browser'], $browser_data)) {
		// A browser will be considered outdated if it is older than the last 3 versions
		if(array_key_exists('accepted', $_GET)) $accepted_versions = intval($_GET['accepted']);
		if(!$accepted_versions) $accepted_versions = 3;
		$res['accepted_version'] = $browser_data[$res['browser']]['version'][$accepted_versions - 1];

		// Known browser with outdated version
		if (version_compare($res['user_version'], $res['accepted_version']) == -1) $res['outdated'] = true;

		// Known browser up to date
		else $res['outdated'] =  false;
	}
	// IE is always outdated
	elseif ($res['browser'] == 'ie') $res['outdated'] =  true;
	// Unknown browser
	else $res['outdated'] =  null;

	if($res['outdated']) {
		$res['info'] = array_map(function($item) {
			return [
				'name' => $item['name'],
				'long_name' => $item['long_name'],
				'url' => $item['url'],
				'info' => $item['info'],
				'version' => $item['version'][0]
			];
		}, $browser_data);
	}

	return $res;
}

// Check if the file browsers.json exists
if (file_exists('browsers.json')) {
	// Check if the last modified date of the file is more than 1 day ago
	if (filemtime('browsers.json') < strtotime('-1 day') || array_key_exists('refresh', $_GET)) get_all_browsers_versions();
	else {
		// Load versions from file to data dictionary
		$versions = json_decode(file_get_contents('browsers.json'), true);
		foreach ($versions as $browser => $version) {
			$browser_data[$browser]['version'] = $version;
		}
	}
} else get_all_browsers_versions();

echo 'lqxBrowserAlert(' . json_encode(browser_outdated()) . ');';

exit;
