<?php

/**
 * router.php - Routes request to the appropriate template
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

namespace lqx\router;

/**
 * Check if a template file exists
 *
 * @param string $tmpl_name
 * 		Name of the template file
 *
 * @return bool
 */
function tmpl_file_exists($tmpl_name) {
	return file_exists(get_template_directory() . '/php/custom/templates/' . $tmpl_name . '.php');
}

/**
 * Render template based on the current request
 * 		- This function is called from /php/router.php
 * 		- It checks the current request and loads the appropriate template
 * 		- If no suitable template is found, it throws a WordPress error
 *
 * @return void
 * 		Loads the appropriate template
 */
function render() {
	global $wp_query;
	$tmpl_name = '';

	// Home page
	if (is_front_page() && tmpl_file_exists('front-page')) $tmpl_name = 'front-page';

	// Blog archive
	elseif (is_home() && tmpl_file_exists('home')) $tmpl_name = 'home';

	// 404 page
	elseif (is_404() && tmpl_file_exists('404')) $tmpl_name = '404';

	// Search results
	elseif (is_search() && tmpl_file_exists('search')) $tmpl_name = 'search';

	// Archive
	elseif (is_archive()) {
		// Custom post type archive
		if (is_post_type_archive()) {
			if (tmpl_file_exists('archive-' . get_post_type())) $tmpl_name = 'archive-' . get_post_type();
			elseif (tmpl_file_exists('archive')) $tmpl_name = 'archive';
		}

		// Category archive
		elseif (is_category()) {
			if (tmpl_file_exists('category-' . $wp_query->query['category_name'])) $tmpl_name = 'category-' . $wp_query->query['category_name'];
			elseif (tmpl_file_exists('category')) $tmpl_name = 'category';
		}

		// Taxonomy archive
		elseif (is_tax()) {
			if (tmpl_file_exists('taxonomy-' . $wp_query->query_vars['taxonomy'] . '-' . $wp_query->query_vars['term'])) $tmpl_name = 'taxonomy-' . $wp_query->query_vars['taxonomy'] . '-' . $wp_query->query_vars['term'];
			elseif (tmpl_file_exists('taxonomy-' . $wp_query->query_vars['taxonomy'])) $tmpl_name = 'taxonomy-' . $wp_query->query_vars['taxonomy'];
			elseif (tmpl_file_exists('taxonomy')) $tmpl_name = 'taxonomy';
		}

		// Tag archive
		elseif (is_tag()) {
			if (tmpl_file_exists('tag-' . $wp_query->query['tag'])) $tmpl_name = 'tag-' . $wp_query->query['tag'];
			elseif (tmpl_file_exists('tag')) $tmpl_name = 'tag';
		}

		// Author archive
		elseif (is_author()) {
			if (tmpl_file_exists('author-' . $wp_query->query['author_name'])) $tmpl_name = 'author-' . $wp_query->query['author_name'];
			elseif (tmpl_file_exists('author')) $tmpl_name = 'author';
		}

		// Date archive
		elseif (is_date()) {
			if (is_day()) {
				if (tmpl_file_exists('day-' . $wp_query->query['year'] . '-' . $wp_query->query['monthnum'] . '-' . $wp_query->query['day'])) $tmpl_name = 'day-' . $wp_query->query['year'] . '-' . $wp_query->query['monthnum'] . '-' . $wp_query->query['day'];
				elseif (tmpl_file_exists('day')) $tmpl_name = 'day';
			} elseif (is_month()) {
				if (tmpl_file_exists('month-' . $wp_query->query['year'] . '-' . $wp_query->query['monthnum'])) $tmpl_name = 'month-' . $wp_query->query['year'] . '-' . $wp_query->query['monthnum'];
				elseif (tmpl_file_exists('month')) $tmpl_name = 'month';
			} elseif (is_year()) {
				if (tmpl_file_exists('year-' . $wp_query->query['year'])) $tmpl_name = 'year-' . $wp_query->query['year'];
				elseif (tmpl_file_exists('year')) $tmpl_name = 'year';
			}
			if (!$tmpl_name && tmpl_file_exists('date')) $tmpl_name = 'date';
		}

		// Custom router logic
		elseif (file_exists(get_template_directory() . '/php/custom/router.php')) {
			require get_template_directory() . '/php/custom/router.php';
		}
	}

	// Singular post
	elseif (is_singular()) {
		// Blog post or custom post type
		if (is_single()) {
			if (tmpl_file_exists(get_post_type() . '-' . $wp_query->query['name'])) $tmpl_name = get_post_type() . '-' . $wp_query->query['name'];
			elseif (tmpl_file_exists(get_post_type())) $tmpl_name = get_post_type();
		}

		// Page
		elseif (is_page()) {
			if (array_key_exists('pagename', $wp_query->query) && tmpl_file_exists('page-' . $wp_query->query['pagename'])) $tmpl_name = 'page-' . $wp_query->query['pagename'];
			elseif (get_page_template_slug() && tmpl_file_exists(str_replace('page-templates/', '', str_replace('.php', '', get_page_template_slug())))) $tmpl_name = str_replace('page-templates/', '', str_replace('.php', '', get_page_template_slug()));
			elseif (tmpl_file_exists('page')) $tmpl_name = 'page';
		}

		// Attachment
		elseif (is_attachment()) {
			$mime_type = explode('/', get_post_mime_type());
			if (tmpl_file_exists($mime_type[0] . '-' . $wp_query->query['attachment'])) $tmpl_name = $mime_type[0] . '-' . $wp_query->query['attachment'];
			elseif (tmpl_file_exists($mime_type[0] . '-' . $mime_type[1])) $tmpl_name = $mime_type[0] . '-' . $mime_type[1];
			elseif (tmpl_file_exists($mime_type[0])) $tmpl_name = $mime_type[0];
			elseif (tmpl_file_exists('attachment')) $tmpl_name = 'attachment';
		}

		// Custom router logic
		elseif (file_exists(get_template_directory() . '/php/custom/router.php')) {
			require get_template_directory() . '/php/custom/router.php';
		}
	}

	// Load template file if found
	if ($tmpl_name) {
		require get_template_directory() . '/php/custom/templates/' . $tmpl_name . '.php';
	}

	// Fallback to default templates
	else {
		if (is_home() || is_archive()) {
			if (tmpl_file_exists('archive')) require get_template_directory() . '/php/custom/templates/archive.php';
			else require get_template_directory() . '/php/archive.php';
		} elseif (is_singular()) {
			if (tmpl_file_exists('singular')) require get_template_directory() . '/php/custom/templates/singular.php';
			else require get_template_directory() . '/php/singular.php';
		}

		// There was an unexpected template request, or the theme default templates are missing
		else {
			$msg = [
				"<h1>Error: no suitable template found</h1>",
				"<pre>",
				'$wp_query: ' . print_r($wp_query, true),
				'$tmpl_name: ' . $tmpl_name,
				'get_page_template_slug(): ' . get_page_template_slug(),
			];

			foreach ([
				'is_front_page', 'is_home', 'is_404', 'is_search',
				'is_archive', 'is_post_type_archive', 'get_post_type',
				'is_category', 'get_the_category', 'is_tax', 'is_tag',
				'is_author', 'is_date', 'is_year', 'is_month', 'is_day',
				'is_singular', 'is_single', 'is_page',
				'is_attachment', 'get_post_mime_type'
			] as $f) {
				$msg[] = $f . '(): ' . print_r($f(), true);
			}

			$msg[] = '</pre>';

			// Throw a WordPress error
			wp_die(implode("\n", $msg), 'No suitable template found');
		}
	}
}
