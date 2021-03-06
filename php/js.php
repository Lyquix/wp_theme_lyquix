<?php
/**
 * js.php - Includes JavaScript libraries
 *
 * @version     2.3.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Prevent adding js libraries in wp_head()
global $wp_scripts;
$remove_js_libraries = explode("\n", trim(get_theme_mod('remove_js_libraries')));
foreach($wp_scripts -> queue as $i => $js) {
	if(array_search(trim($js), $remove_js_libraries)) unset($wp_scripts -> queue[$i]);
}

// Enable jQuery
if(get_theme_mod('enable_jquery')) {
	wp_enqueue_script('jquery');
}
else {
	wp_dequeue_script('jquery');
}

// Enable jQuery UI
if(get_theme_mod('enable_jquery_ui')) {
	wp_enqueue_script('jquery-ui-core');
	if(get_theme_mod('enable_jquery_ui') == 2) wp_enqueue_script('jquery-ui-sortable');
}
else {
	wp_dequeue_script('jquery-ui-core');
	wp_dequeue_script('jquery-ui-sortable');
}

// Array to store all scripts to be loaded
$scripts = [];

/*
// Process dependencies
$script_handles = [];

function lqx_get_script_dependencies($h) {
	global $wp_scripts;
	$deps = [];
	if(count($wp_scripts -> registered[$h] -> deps)) {
		foreach($wp_scripts -> registered[$h] -> deps as $d) {
			$deps = array_merge($deps, lqx_get_script_dependencies($d));
			$deps[] = $d;
		}
	}
	return $deps;
}

foreach($wp_scripts -> queue  as $script_handle) {
	$script_handles = array_merge($script_handles, lqx_get_script_dependencies($script_handle));
	$script_handles[] = $script_handle;
}

// Parse enqueued scripts
foreach($script_handles as $script_handle) {
	$script = $wp_scripts -> registered[$script_handle];
	// Check if script is local or remote
	if(parse_url($script -> src, PHP_URL_SCHEME)) {
		// Absolute URL
		if(get_theme_mod('merge_css_remote')) {
			$scripts[] = ['url' => $script -> src . ($script -> ver ? '?ver=' . $script -> ver : '')];
			wp_dequeue_script($script_handle);
		}
	}
	elseif (parse_url($script -> src, PHP_URL_PATH)) {
		// Relative URL
		if(get_theme_mod('merge_css_local')) {
			$url = $script -> src;
			// Add leading / if missing
			if(substr($url,0,1) != '/') $url = '/' . $url;
			// Check if file exist
			if(file_exists(ABSPATH . $url)) {
				$scripts[] = [
					'url' => $url,
					'version' => date("YmdHis", filemtime(ABSPATH . $url))
				];
				wp_dequeue_script($script_handle);
			}
		}
	}
}
*/

// Use non minified version?
$non_min_js = get_theme_mod('non_min_js');

// LoDash
if(get_theme_mod('lodash')) {
	$scripts[] = ['url' => $cdnjs_url . 'lodash.js/4.17.4/lodash' . ($non_min_js ? '' : '.min') . '.js'];
}

// SmoothScroll
if(get_theme_mod('smoothscroll')) {
	$scripts[] = ['url' => $cdnjs_url . 'smoothscroll/1.4.6/SmoothScroll' . ($non_min_js ? '' : '.min') . '.js'];
}

// MomentJS
if(get_theme_mod('momentjs')) {
	$scripts[] = ['url' => $cdnjs_url . 'moment.js/2.18.1/moment' . ($non_min_js ? '' : '.min') . '.js'];
}

// DotDotDot
if(get_theme_mod('dotdotdot')) {
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
				$scripts[] = ['url' => $jsurl, 'version' => date("YmdHis", filemtime(get_home_path() . $jsurl))];
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
			$scripts_data .= file_get_contents($script['url']) . "\n";
		}
	}
	// Save file
	file_put_contents($tmpl_path . '/dist/' . $scripts_filename, $scripts_data);
	unset($scripts_data);
}

// Set lqx options
$lqx_options = [
	'responsive' => [
		'minIndex' => get_theme_mod('min_screen'),
		'maxIndex' => get_theme_mod('max_screen')
	],
	'siteURL' => $site_abs_url,
	'tmplURL' => $tmpl_url
];

if(get_theme_mod('lqx_debug')) {
	$lqx_options['debug'] = true;
}

if(get_theme_mod('ga_account')) {
	$lqx_options['analytics'] = [
		'createParams' => [
			'default' => [
				'trackingId' => get_theme_mod('ga_account'),
				'cookieDomain' => 'auto'
			]
		]
	];
}

// Merge with options from template settings
$lqx_options = array_replace_recursive($lqx_options, json_decode(get_theme_mod('lqx_options') ? get_theme_mod('lqx_options') : '{}', true));

function lqx_render_js() {
	global $tmpl_url, $scripts_filename, $lqx_options;
	echo '<script defer src="' . $tmpl_url . '/dist/' . $scripts_filename . '" onload="lqx.ready(' . htmlentities(json_encode($lqx_options)) . ');"></script>' . "\n";
}
