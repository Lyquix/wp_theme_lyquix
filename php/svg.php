<?php

/**
 * svg.php - Sanitize SVG uploads
 *
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2026 Lyquix
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

namespace lqx\svg;

/**
 * An SVG is an executable document: it can carry <script>, on* event handlers,
 * javascript: URLs and external references, and WordPress serves it from the site's
 * own origin. Anyone who can upload media could otherwise plant a file that runs
 * JavaScript as whoever opens it.
 *
 * This sanitizer removes those vectors while leaving legitimate artwork untouched.
 * It is deliberately a deny-list of dangerous constructs rather than an allow-list of
 * permitted elements: allow-lists routinely strip valid markup (gradients, filters,
 * masks, fonts, custom namespaces) and break real uploads.
 */

// Elements that can execute script or embed foreign content
const DENIED_ELEMENTS = [
	'script',
	'foreignobject',
	'handler',    // SVG Tiny 1.2 event handler
	'listener',   // SMIL event listener
	'iframe',
	'embed',
	'object',
	'audio',
	'video',
	'animation',  // SVG Tiny 1.2, can reference external documents
];

// Attributes that are never safe
const DENIED_ATTRIBUTES = [
	'formaction',
	'xlink:arcrole',
	'xlink:role',
	'ping',
];

// SMIL animation elements: harmless in themselves, but they can write any attribute
// (including event handlers and hrefs) onto their target once the animation runs
const ANIMATION_ELEMENTS = [
	'set',
	'animate',
	'animatetransform',
	'animatemotion',
];

// Attributes whose value is a URL and must therefore be scheme-checked
const URL_ATTRIBUTES = [
	'href',
	'xlink:href',
	'src',
	'data',
	'from',
	'to',
	'values',
	'by',
	'begin',
	'end',
	'attributename',
	'filter',
	'mask',
	'clip-path',
	'fill',
	'stroke',
	'marker-start',
	'marker-mid',
	'marker-end',
	'style',
];

/**
 * Is this URL value safe to keep?
 * 		- Same-document fragments (#id) and relative paths are kept
 * 		- data: is allowed only for images
 * 		- http(s) is kept (an external image reference cannot execute script)
 * 		- everything else (javascript:, data:text/html, vbscript:, ...) is dropped
 *
 * @param string $value - the attribute value
 *
 * @return bool - true when the value may be kept
 */
function is_safe_url($value) {
	$value = trim($value);
	if ($value === '') return true;

	// Strip HTML entities and whitespace used to obfuscate the scheme (java\0script:, java&#9;script:)
	$normalized = strtolower(preg_replace('/[\s\x00-\x20]+/', '', html_entity_decode($value, ENT_QUOTES, 'UTF-8')));

	if (str_starts_with($normalized, '#')) return true;
	if (str_starts_with($normalized, 'data:image/') && !str_contains($normalized, 'svg+xml')) return true;
	if (preg_match('/^(javascript|vbscript|livescript|mocha|data|blob|file|about):/', $normalized)) return false;

	// Anything still carrying a scheme must be http(s)
	if (preg_match('/^[a-z][a-z0-9+.-]*:/', $normalized)) {
		return (bool) preg_match('/^https?:/', $normalized);
	}

	return true;
}

/**
 * Remove script vectors from an SVG document string
 *
 * @param string $svg - raw SVG file contents
 *
 * @return string|false - sanitized SVG, or false when the file cannot be parsed as SVG
 */
function sanitize($svg) {
	// Fail closed: without DOM we cannot verify the file, so it must not be accepted
	if (!class_exists('\DOMDocument')) return false;
	if (trim($svg) === '') return false;

	$had_declaration = (bool) preg_match('/^\s*<\?xml/i', $svg);

	// Strip the XML declaration's encoding quirks and any DOCTYPE (entity expansion / XXE)
	$svg = preg_replace('/<!DOCTYPE[^>]*(\[[^\]]*\])?>/is', '', $svg);
	$svg = preg_replace('/<!ENTITY[^>]*>/is', '', $svg);

	$doc = new \DOMDocument();
	$doc->preserveWhiteSpace = true;
	$doc->formatOutput = false;

	// Never resolve external entities or network references while parsing
	$options = LIBXML_NONET | LIBXML_NOENT;
	if (defined('LIBXML_COMPACT')) $options |= LIBXML_COMPACT;

	$previous = libxml_use_internal_errors(true);
	// Force UTF-8 handling without adding a wrapper element
	$loaded = $doc->loadXML('<?xml version="1.0" encoding="UTF-8"?>' . preg_replace('/^<\?xml[^>]*\?>/i', '', $svg), $options);
	libxml_clear_errors();
	libxml_use_internal_errors($previous);

	if (!$loaded || !$doc->documentElement) return false;
	if (strtolower($doc->documentElement->localName) !== 'svg') return false;

	$xpath = new \DOMXPath($doc);

	// Remove denied elements regardless of namespace
	foreach ($xpath->query('//*') as $node) {
		if (in_array(strtolower($node->localName), DENIED_ELEMENTS, true)) {
			$node->parentNode?->removeChild($node);
		}
	}

	// Animation elements that target an event handler or a URL attribute
	foreach ($xpath->query('//*') as $node) {
		if (!in_array(strtolower($node->localName), ANIMATION_ELEMENTS, true)) continue;

		$target = strtolower((string) $node->getAttribute('attributeName'));
		if ($target === '' || str_starts_with($target, 'on') || in_array($target, URL_ATTRIBUTES, true)) {
			$node->parentNode?->removeChild($node);
		}
	}

	// Remove comments and processing instructions (can hide markup for old parsers)
	foreach ($xpath->query('//comment() | //processing-instruction()') as $node) {
		$node->parentNode?->removeChild($node);
	}

	// Clean attributes
	foreach ($xpath->query('//*') as $node) {
		if (!$node->hasAttributes()) continue;

		// Iterate over a copy: removing while iterating skips entries
		foreach (iterator_to_array($node->attributes) as $attr) {
			$name = strtolower($attr->nodeName);
			$local = strtolower($attr->localName);
			$value = $attr->nodeValue;

			// Event handlers: onclick, onload, onmouseover, ...
			if (str_starts_with($local, 'on')) {
				$node->removeAttributeNode($attr);
				continue;
			}

			if (in_array($name, DENIED_ATTRIBUTES, true) || in_array($local, DENIED_ATTRIBUTES, true)) {
				$node->removeAttributeNode($attr);
				continue;
			}

			if (in_array($name, URL_ATTRIBUTES, true) || in_array($local, URL_ATTRIBUTES, true)) {
				// style and paint attributes may carry url(...) references
				if (preg_match_all('/url\(\s*[\'"]?([^\'")]+)/i', (string) $value, $m)) {
					foreach ($m[1] as $url) {
						if (!is_safe_url($url)) {
							$node->removeAttributeNode($attr);
							continue 2;
						}
					}
				}
				if (!is_safe_url((string) $value)) {
					$node->removeAttributeNode($attr);
					continue;
				}
			}

			// Inline CSS cannot import remote stylesheets or call expressions
			if ($local === 'style' && preg_match('/@import|expression\s*\(|behavior\s*:/i', (string) $value)) {
				$node->removeAttributeNode($attr);
			}
		}
	}

	// <style> blocks: drop @import and legacy IE expressions
	foreach ($xpath->query('//*[local-name()="style"]') as $style) {
		$css = $style->textContent;
		$clean = preg_replace('/@import[^;]+;?/i', '', $css);
		$clean = preg_replace('/expression\s*\([^)]*\)/i', '', $clean);
		$clean = preg_replace('/url\(\s*[\'"]?\s*(javascript|vbscript|data:text)[^)]*\)/i', 'none', $clean);
		if ($clean !== $css) $style->textContent = $clean;
	}

	$out = $doc->saveXML($doc->documentElement);
	if ($out === false) return false;

	// Keep the file shaped like the original: only re-emit the XML declaration if it had one
	return $had_declaration ? '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $out : $out;
}

if (get_theme_mod('feat_allow_svg_upload', '1') === '1' && apply_filters('lqx_sanitize_svg_uploads', true)) {
	/**
	 * Sanitize SVG files as they are uploaded.
	 *
	 * Runs on the temporary file before WordPress moves it into uploads. A file that
	 * cannot be parsed as SVG is rejected with a readable message rather than being
	 * silently emptied, so editors know what happened.
	 */
	add_filter('wp_handle_upload_prefilter', function ($file) {
		$is_svg = ($file['type'] ?? '') === 'image/svg+xml'
			|| strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION)) === 'svg';

		if (!$is_svg || !empty($file['error']) || empty($file['tmp_name']) || !is_readable($file['tmp_name'])) return $file;

		$original = file_get_contents($file['tmp_name']);
		if ($original === false) return $file;

		$clean = sanitize($original);

		if ($clean === false) {
			$file['error'] = class_exists('\DOMDocument')
				? __('This file could not be read as a valid SVG image and was not uploaded.', 'lyquix')
				: __('SVG uploads are disabled because the PHP DOM extension is missing on this server.', 'lyquix');
			return $file;
		}

		// Only rewrite when something actually changed
		if ($clean !== $original) file_put_contents($file['tmp_name'], $clean);

		return $file;
	});
}
