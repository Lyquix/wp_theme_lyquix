<?php

/**
 * index.php - Checks for outdated browsers
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
		'url' => 'https://www.google.com/chrome',
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

/**
 * Retrieves the first match from a regular expression pattern in a user agent string.
 *
 * @param string $regex The regular expression pattern.
 * @param string $ua The user agent string.
 * @return string The first match found, or an empty string if no match is found.
 */
function get_first_match($regex, $ua) {
	preg_match($regex, $ua, $match);
	return isset($match[1]) ? $match[1] : '';
}

/**
 * Retrieves the second match from a regular expression pattern in a user agent string.
 *
 * @param string $regex The regular expression pattern.
 * @param string $ua The user agent string.
 * @return string The second match found, or an empty string if no match is found.
 */
function get_second_match($regex, $ua) {
	preg_match($regex, $ua, $match);
	return isset($match[2]) ? $match[2] : '';
}

/**
 * Detects the browser based on the user agent string.
 *
 * @return array An associative array containing the browser type and version.
 *               - type (string): The type of the browser. Possible values are 'opera', 'msie', 'msedge', 'chrome', 'firefox', 'safari', or the browser name in lowercase if unknown.
 *               - version (string): The version of the browser.
 */
function detect_browser() {
	$ua = $_SERVER['HTTP_USER_AGENT'];
	$browser = [];

	if (preg_match('/opera|opr/i', $ua)) {
		$browser = [
			'type' => 'opera',
			'version' => get_first_match('/version\/(\d+(\.\d+)?)/i', $ua) ?: get_first_match('/(?:opera|opr)[\s/](\d+(\.\d+)?)/i', $ua)
		];
	} else if (preg_match('/msie|trident/i', $ua)) {
		$browser = [
			'type' => 'msie',
			'version' => get_first_match('/(?:msie |rv:)(\d+(\.\d+)?)/i', $ua)
		];
	} else if (preg_match('/chrome.+? edge/i', $ua)) {
		$browser = [
			'type' => 'msedge',
			'version' => get_first_match('/edge\/(\d+(\.\d+)?)/i', $ua)
		];
	} else if (preg_match('/chrome|crios|crmo/i', $ua)) {
		$browser = [
			'type' => 'chrome',
			'version' => get_first_match('/(?:chrome|crios|crmo)\/(\d+(\.\d+)?)/i', $ua)
		];
	} else if (preg_match('/firefox/i', $ua)) {
		$browser = [
			'type' => 'firefox',
			'version' => get_first_match('/(?:firefox)[ /](\d+(\.\d+)?)/i', $ua)
		];
	} else if (preg_match('/safari/i', $ua)) {
		$browser = [
			'type' => 'safari',
			'version' => get_first_match('/safari\/(\d+(\.\d+)?)/i', $ua)
		];
	} else {
		$browser = [
			'version' => get_second_match('/^(.*)\/(.*) /', $ua)
		];
		$browser['type'] = strtolower(str_replace(' ', '', $browser['name']));
	}

	return $browser;
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
		if (!in_array($ver, $versions)) $versions[] = $ver;
	}

	// Sort the versions array using the version_compare function
	usort($versions, function ($a, $b) {
		return -1 * version_compare($a, $b);
	});

	// Remove duplicates
	$versions = array_unique($versions);

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

	$res = detect_browser();

	if (array_key_exists($res['type'], $browser_data)) {
		// A browser will be considered outdated if it is older than the last 3 versions
		if (array_key_exists('accepted', $_GET)) $accepted_versions = intval($_GET['accepted']);
		if (!$accepted_versions) $accepted_versions = 3;
		$res['accepted_version'] = $browser_data[$res['type']]['version'][$accepted_versions - 1];

		// Known browser with outdated version
		if (version_compare($res['version'], $res['accepted_version']) == -1) $res['outdated'] = true;

		// Known browser up to date
		else $res['outdated'] =  false;
	}
	// IE is always outdated
	elseif ($res['type'] == 'msie') $res['outdated'] =  true;
	// Unknown browser
	else $res['outdated'] =  null;

	if ($res['outdated']) {
		$res['info'] = array_map(function ($item) {
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

// Check if browser is outdated
$browser_outdated = browser_outdated();

if ($browser_outdated['outdated'] === true) {
	// Get the request URI and remove: index.php, any query string, trailing slash, and hash
	$browsers_uri = preg_replace('/index\.php|(\?.*)|\/$|#.*/', '', $_SERVER['REQUEST_URI']);

	// Get the contents of browsers.js and minify it
	$browsers_js = file_get_contents('browsers.min.js');
	$browsers_js = str_replace('BROWSERS_URI', $browsers_uri, $browsers_js);
	$browsers_js = str_replace('BROWSERS_DATA', base64_encode(json_encode($browser_outdated)), $browsers_js);
	echo $browsers_js;
}
else echo sprintf('/* %s */;',json_encode($browser_outdated));

exit;
