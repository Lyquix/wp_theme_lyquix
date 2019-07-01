<?php
/**
 * css.php - Includes CSS files
 *
 * @version     2.1.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Prevent adding css libraries in wp_head()
global $wp_styles;
$remove_css_libraries = explode("\n", trim(get_theme_mod('remove_css_libraries')));
foreach($wp_styles -> queue as $i => $css) {
	if(array_search(trim($css), $remove_css_libraries)) unset($wp_styles -> queue[$i]);
}

// Array to store all stylesheets to be loaded
$stylesheets = [];

/*
// Process dependencies
$stylesheet_handles = [];

function lqx_get_style_dependencies($h) {
	global $wp_styles;
	$deps = [];
	if(count($wp_styles -> registered[$h] -> deps)) {
		foreach($wp_styles -> registered[$h] -> deps as $d) {
			$deps = array_merge($deps, lqx_get_style_dependencies($d));
			$deps[] = $d;
		}
	}
	return $deps;
}

foreach($wp_styles -> queue  as $stylesheet_handle) {
	$stylesheet_handles = array_merge($stylesheet_handles, lqx_get_style_dependencies($stylesheet_handle));
	$stylesheet_handles[] = $stylesheet_handle;
}

// Parse enqueued styles
foreach($stylesheet_handles as $stylesheet_handle) {
	$stylesheet = $wp_styles -> registered[$stylesheet_handle];
	// Check if stylesheet is local or remote
	if(parse_url($stylesheet -> src, PHP_URL_SCHEME)) {
		// Absolute URL
		if(get_theme_mod('merge_css_remote')) {
			$stylesheets[] = ['url' => $stylesheet -> src . ($stylesheet -> ver ? '?ver=' . $stylesheet -> ver : '')];
			wp_dequeue_style($stylesheet_handle);
		}
	}
	elseif (parse_url($stylesheet -> src, PHP_URL_PATH)) {
		// Relative URL
		if(get_theme_mod('merge_css_local')) {
			$url = $stylesheet -> src;
			// Add leading / if missing
			if(substr($url,0,1) != '/') $url = '/' . $url;
			// Check if file exist
			if(file_exists(ABSPATH . $url)) {
				$stylesheets[] = [
					'url' => $url,
					'version' => date("YmdHis", filemtime(ABSPATH . $url))
				];
				wp_dequeue_style($stylesheet_handle);
			}
		}
	}
}
*/

// Use non minified version?
$non_min_css = get_theme_mod('non_min_css');

// Animte.css
if(get_theme_mod('animatecss')) {
	$stylesheets[] = ['url' => $cdnjs_url . 'animate.css/3.7.0/animate' . ($non_min_css ? '' : '.min') . '.css'];
}

// Additional CSS Libraries
$add_css_libraries = explode("\n", trim(get_theme_mod('add_css_libraries', '')));
foreach($add_css_libraries as $cssurl) {
	$cssurl = trim($cssurl);
	if($cssurl) {
		// Check if stylesheet is local or remote
		if(parse_url($cssurl, PHP_URL_SCHEME)) {
			// Absolute URL
			$stylesheets[] = ['url' => $cssurl];
		}
		elseif (parse_url($cssurl, PHP_URL_PATH)) {
			// Relative URL
			// Add leading / if missing
			if(substr($cssurl,0,1) != '/') $cssurl = '/' . $cssurl;
			// Check if file exist
			if(file_exists(ABSPATH . $cssurl)) {
				$stylesheets[] = ['url' => $cssurl, 'version' => date("YmdHis", filemtime(get_home_path() . $cssurl))];
			}
		}
	}
}

// Custom Project Styles
if(file_exists($tmpl_path . '/css/styles' . ($non_min_css ? '' : '.min') . '.css')) {
	$stylesheets[] = [
		'url' => $tmpl_url . '/css/styles' . ($non_min_css ? '' : '.min') . '.css',
		'version' => date("YmdHis", filemtime($tmpl_path . '/css/styles' . ($non_min_css ? '' : '.min') . '.css'))
	];
}

// Unique filename based on stylesheets, last update, and order
$stylesheet_filename = base_convert(md5(json_encode($stylesheets)), 16, 36) . '.css';

// Function to convert relative URLs into absolute provided a base URL
function lqx_rel_to_abs_url($rel, $base) {
	// Parse base URL  and convert to local variables: $scheme, $host,  $path
	$base_parts = parse_url($base);

	// Return protocol-neutral URLs
	if(strpos($rel, "//") === 0) {
		return ($base_parts['scheme'] ? $base_parts['scheme'] . ':' . $rel : $rel);
	}

	// Return if already absolute URL
	if(parse_url($rel, PHP_URL_SCHEME) != '') {
		return $rel;
	}

	// queries and anchors
	if($rel[0] == '#' || $rel[0] == '?') {
		return $base . $rel;
	}

	// remove non-directory element from path
	$base_parts['path'] = preg_replace( '#/[^/]*$#', '', $base_parts['path'] );

	// destroy path if relative url points to root
	if($rel[0] ==  '/') {
		$base_parts['path'] = '';
	}

	// dirty absolute URL
	$abs = $base_parts['host'] . $base_parts['path'] . "/" . $rel;

	// replace '//' or  '/./' or '/foo/../' with '/'
	$count = true;
	while($count) $abs = preg_replace("/(\/\.?\/)/", "/", $abs, $limit = -1, $count);
	$count = true;
	while($count) $abs = preg_replace("/\/(?!\.\.)[^\/]+\/\.\.\//", "/", $abs, $limit = -1, $count);

	// absolute URL is ready!
	return ($base_parts['scheme'] ? $base_parts['scheme'] . '://' : '') . $abs;
}
// Check if dist directory exists
if (!is_dir($tmpl_path . '/dist/')) {
	mkdir($tmpl_path . '/dist/');
}

// Check if file has already been created
if(!file_exists($tmpl_path . '/dist/' . $stylesheet_filename)) {
	// Regular expression to find url in files
	$urlRegex = '/url\(\s*[\"\\\']?([^\"\\\'\)]+)[\"\\\']?\s*\)/';
	// Regular expression to find @import rules in files
	$importRegex = '/(@import\s+[^;]*;)/';
	// Prepare file
	$stylesheet_data = "/* " . $stylesheet_filename . " */\n";
	$stylesheet_imports = "/* @import rules moved to the top of the document */\n";
	foreach($stylesheets as $idx => $stylesheet) {
		if(array_key_exists('data', $stylesheet)) {
			$stylesheet_data .= "/* Custom stylesheet declaration */\n";
			$tmp = $stylesheet['data'];
			// Move @import rules
			preg_match_all($importRegex, $tmp, $matches);
			foreach($matches[1] as $imp) {
				$stylesheet_imports .= $imp . "\n";
				$tmp = str_replace($imp, "\n/*@import rule moved to the top of the file*/\n", $tmp);
			}
			$stylesheet_data .= $tmp . "\n";
		}
		elseif (array_key_exists('version', $stylesheet)) {
			$stylesheet_data .= "/* Local stylesheet: " . $stylesheet['url'] . ", Version: " . $stylesheet['version'] . " */\n";
			$tmp = file_get_contents(get_home_path() . $stylesheet['url']) . "\n";
			// Update URLs
			preg_match_all($urlRegex, $tmp, $matches);
			foreach($matches[1] as $rel) {
				$abs = lqx_rel_to_abs_url($rel, $stylesheet['url']);
				$tmp = str_replace($rel, $abs, $tmp);
			}
			// Move @import rules
			preg_match_all($importRegex, $tmp, $matches);
			foreach($matches[1] as $imp) {
				$stylesheet_imports .= $imp . "\n";
				$tmp = str_replace($imp, "\n/*@import rule moved to the top of the file*/\n", $tmp);
			}
			$stylesheet_data .= $tmp . "\n";
		}
		else {
			$stylesheet_data .= "/* Remote stylesheet: " . $stylesheet['url'] . " */\n";
			$tmp = file_get_contents($stylesheet['url']) . "\n";
			// Update URLs
			preg_match_all($urlRegex, $tmp, $matches);
			foreach($matches[1] as $rel) {
				$abs = lqx_rel_to_abs_url($rel, $stylesheet['url']);
				$tmp = str_replace($rel, $abs, $tmp);
			}
			// Move @import rules
			preg_match_all($importRegex, $tmp, $matches);
			foreach($matches[1] as $imp) {
				$stylesheet_imports .= $imp . "\n";
				$tmp = str_replace($imp, "\n/*@import rule moved to the top of the file*/\n", $tmp);
			}
			$stylesheet_data .= $tmp . "\n";
		}
	}
	// Save file
	file_put_contents($tmpl_path . '/dist/' . $stylesheet_filename, $stylesheet_imports . "\n" . $stylesheet_data);
	unset($stylesheet_imports, $stylesheet_data);
}

function lqx_render_css() {
	global $tmpl_url, $stylesheet_filename;
	echo '<style>/* FOUC workaround */body {opacity:0;}</style>' . "\n";
	echo '<link href="' . $tmpl_url . '/dist/' . $stylesheet_filename . '" rel="preload" as="style" onload="this.rel=\'stylesheet\'" />' . "\n";
}
