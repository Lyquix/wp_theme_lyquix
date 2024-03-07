<?php

/**
 * body.php - Prepares body classes
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

namespace lqx\body;

function classes() {
	global $wp_query;

	$classes = [];

	if (is_front_page()) {
		$classes[] = 'home';
	}

	foreach ([
		'404', 'search', 'home', 'category',
		'post_type_archive', 'tax', 'tag', 'author', 'date',
		'single', 'page', 'attachment'
	] as $type) {
		$f = 'is_' . $type;
		if ($f()) {
			switch ($type) {
				case 'home':
					$classes[] = 'blog';
					break;
				case 'post_type_archive':
					$classes[] = 'archive-' . get_post_type();
					break;
				case 'category':
					$classes[] = $type;
					$classes[] = $type . '-' . $wp_query->query['category_name'];
					break;
				case 'tax':
					$classes[] = 'taxonomy';
					$classes[] = 'taxonomy-' . $wp_query->query_vars['taxonomy'];
					$classes[] = 'taxonomy-' . $wp_query->query_vars['taxonomy'] . '-' . $wp_query->query_vars['term'];
					break;
				case 'single':
					$classes[] = $type;
					$classes[] = $type . '-' . get_post_type();
					$classes[] = $type . '-' . get_post_type() . '-' . $wp_query->query['name'];
					break;
				case 'page':
					$classes[] = $type;
					if (array_key_exists('pagename', $wp_query->query)) $classes[] = $type . '-' . $wp_query->query['pagename'];
					break;
				case 'attachment':
					$classes[] = $type;
					$mime_type = explode('/', get_post_mime_type());
					$classes[] = $type . '-' . $mime_type[0];
					$classes[] = $type . '-' . $mime_type[0] . '-' . $wp_query->query['attachment'];
					break;
				default:
					$classes[] = $type;
			}
		}
	}

	// Set feature flags
	if (file_exists(get_template_directory() . '/php/custom/features.php')) {
		require get_template_directory() . '/php/custom/features.php';

		if (count($feature_flags)) {
			foreach ($feature_flags as $code => $title) {
				if (get_theme_mod('feature-' . $code, '0') == '1') {
					$classes[] = 'feature-' . $code;
				}
			}
		}
	}

	return implode(' ', $classes);
}
