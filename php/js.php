<?php
/**
 * js.php - Includes JavaScript libraries
 *
 * @version     2.4.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Prevent adding js libraries in wp_head()
global $wp_scripts;
$remove_js_libraries = explode("\n", trim(get_theme_mod('remove_js_libraries', '')));
foreach($wp_scripts -> queue as $i => $js) {
	if(array_search(trim($js), $remove_js_libraries)) unset($wp_scripts -> queue[$i]);
}

// Enable jQuery
if(get_theme_mod('enable_jquery', '1')) {
	wp_enqueue_script('jquery');
}
else {
	wp_dequeue_script('jquery');
}

// Enable jQuery UI
if(get_theme_mod('enable_jquery_ui', '0')) {
	wp_enqueue_script('jquery-ui-core');
	if(get_theme_mod('enable_jquery_ui') == 2) wp_enqueue_script('jquery-ui-sortable');
}
else {
	wp_dequeue_script('jquery-ui-core');
	wp_dequeue_script('jquery-ui-sortable');
}

// Array to store all scripts to be loaded
$scripts = [];

// Use non minified version?
$non_min_js = get_theme_mod('non_min_js', '0');

// LoDash
if(get_theme_mod('lodash', '0')) {
	$scripts[] = ['url' => $cdnjs_url . 'lodash.js/4.17.4/lodash' . ($non_min_js ? '' : '.min') . '.js'];
}

// SmoothScroll
if(get_theme_mod('smoothscroll', '0')) {
	$scripts[] = ['url' => $cdnjs_url . 'smoothscroll/1.4.6/SmoothScroll' . ($non_min_js ? '' : '.min') . '.js'];
}

// MomentJS
if(get_theme_mod('momentjs', '0')) {
	$scripts[] = ['url' => $cdnjs_url . 'moment.js/2.18.1/moment' . ($non_min_js ? '' : '.min') . '.js'];
}

// DotDotDot
if(get_theme_mod('dotdotdot', '0')) {
	$scripts[] = ['url' => $cdnjs_url . 'jQuery.dotdotdot/1.7.4/jquery.dotdotdot' . ($non_min_js ? '' : '.min') . '.js'];
}

// MobileDetect
$scripts[] = ['url' => $cdnjs_url . 'mobile-detect/1.3.6/mobile-detect' . ($non_min_js ? '' : '.min') . '.js'];

// Additional JS Libraries
$add_js_libraries = explode("\n", trim(get_theme_mod('add_js_libraries', '')));
foreach($add_js_libraries as $jsurl) {
	$jsurl = trim($jsurl);
	if($jsurl) {
		// Check if script is local or remote
		if(parse_url($jsurl, PHP_URL_SCHEME)) {
			// Absolute URL
			$scripts[] = ['url' => $jsurl];
		}
		elseif (parse_url($jsurl, PHP_URL_PATH)) {
			// Relative URL
			// Add leading / if missing
			if(substr($jsurl,0,1) != '/') $jsurl = '/' . $jsurl;
			// Check if file exist
			if(file_exists(ABSPATH . $jsurl)) {
				$scripts[] = ['url' => $jsurl, 'version' => date("YmdHis", filemtime(ABSPATH . $jsurl))];
			}
		}
	}
}

// Lyquix
$scripts[] = [
	'url' => $tmpl_url . '/js/lyquix' . ($non_min_js ? '' : '.min') . '.js',
	'version' => date("YmdHis", filemtime($tmpl_path . '/js/lyquix' . ($non_min_js ? '' : '.min') . '.js'))
];

// Vue
if(file_exists($tmpl_path . '/js/vue.js')) {
	$scripts[] = [
		'url' => $tmpl_url . '/js/vue' . ($non_min_js ? '' : '.min') . '.js',
		'version' => date("YmdHis", filemtime($tmpl_path . '/js/vue' . ($non_min_js ? '' : '.min') . '.js'))
	];
}

// Scripts
if(file_exists($tmpl_path . '/js/scripts.js')) {
	$scripts[] = [
		'url' => $tmpl_url . '/js/scripts' . ($non_min_js ? '' : '.min') . '.js',
		'version' => date("YmdHis", filemtime($tmpl_path . '/js/scripts' . ($non_min_js ? '' : '.min') . '.js'))
	];
}

// Unique filename based on scripts, last update, and order
$scripts_filename = base_convert(md5(json_encode($scripts)), 16, 36) . '.js';

// Check if dist directory exists
if (!is_dir($tmpl_path . '/dist/')) {
	mkdir($tmpl_path . '/dist/');
}

// Check if file has already been created
if(!file_exists($tmpl_path . '/dist/' . $scripts_filename)) {
	// Prepare file
	$scripts_data = "/* " . $scripts_filename . " */\n";
	foreach($scripts as $idx => $script) {
		if(array_key_exists('data', $script)) {
			$scripts_data .= "/* Custom script declaration */\n";
			$scripts_data .= $script['data'] . "\n";
		}
		elseif (array_key_exists('version', $script)) {
			$scripts_data .= "/* Local script: " . $script['url'] . ", Version: " . $script['version'] . " */\n";
			$script['url'] = ABSPATH . $script['url'];
			$scripts_data .= file_get_contents($script['url']) . "\n";
		}
		else {
			$scripts_data .= "/* Remote script: " . $script['url'] . " */\n";
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $script['url']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$scripts_data .= curl_exec($curl) . "\n";
			curl_close($curl);
		}
	}
	// Save file
	file_put_contents($tmpl_path . '/dist/' . $scripts_filename, $scripts_data);
	unset($scripts_data);
}

// Set lqx options
$lqx_options = [
	'responsive' => [
		'minIndex' => get_theme_mod('min_screen', '0'),
		'maxIndex' => get_theme_mod('max_screen', '4')
	],
	'siteURL' => $site_abs_url,
	'tmplURL' => $tmpl_url
];

if(get_theme_mod('lqx_debug', '0')) {
	$lqx_options['debug'] = true;
}

if(get_theme_mod('ga_account', '') || get_theme_mod('ga4_account', '')) {
	$lqx_options['analytics'] = [
		'trackingId' => get_theme_mod('ga_account'),
		'measurementId' => get_theme_mod('ga4_account'),
		'sendPageview' => get_theme_mod('ga_pageview', '1') ? true : false,
		'useAnalyticsJS' => get_theme_mod('ga_use_analytics_js', '1') ? true : false,
		'usingGTM' => get_theme_mod('ga_via_gtm', '0') ? true : false
	];
}

// Merge with options from template settings
$theme_lqx_options = json_decode(get_theme_mod('lqx_options'), true);
if(!$theme_lqx_options) $theme_lqx_options = [];
$lqx_options = array_replace_recursive($lqx_options, $theme_lqx_options);
$theme_script_options = json_decode(get_theme_mod('scripts_options'), true);
if(!$theme_script_options) $theme_script_options = [];
$scripts_options = array_replace_recursive([], $theme_script_options);

function lqx_render_js() {
	global $tmpl_url, $scripts_filename, $lqx_options, $scripts_options;
	echo '<script defer src="' . $tmpl_url . '/dist/' . $scripts_filename . '" onload="lqx.ready(' . htmlentities(json_encode($lqx_options)) . '); $lqx.ready(' . htmlentities(json_encode($scripts_options)) . ');"></script>' . "\n";

	// Load GTM head code
	if(get_theme_mod('gtm_account', '')) {
		echo "<script>" .
			"(function(w, d, s, l, i){".
			"w[l] = w[l] || [];" .
			"w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});" .
			"var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';" .
			"j.async = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;" .
			"f.parentNode.insertBefore(j, f);" .
			"})(window, document, 'script', 'dataLayer', '" . get_theme_mod('gtm_account') . "');" .
			"</script>";
	}
}
