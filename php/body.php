<?php
/**
 * body.php - Prepares body classes
 *
 * @version     2.2.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

// Prepare array of classes for body tag
if(@!is_array($body_classes)) {
	$body_classes = [];
}
if($home) {
	$body_classes[] = 'home';
}
foreach(array('404', 'search', 'home', 'category',
	'post_type_archive', 'tax', 'tag', 'author', 'date',
	'single', 'page', 'attachment') as $type) {
	$f = 'is_' . $type;
	if($f()) {
		switch($type) {
			case 'home':
				$body_classes[] = 'blog';
				break;
			case 'post_type_archive':
				$body_classes[] = 'archive-' . get_post_type();
				break;
			case 'category':
				$body_classes[] = $type;
				$body_classes[] = $type . '-' . $wp_query -> query['category_name'];
				break;
			case 'tax':
				$body_classes[] = 'taxonomy';
				$body_classes[] = 'taxonomy-' . $wp_query -> query_vars['taxonomy'];
				$body_classes[] = 'taxonomy-' . $wp_query -> query_vars['taxonomy'] . '-' . $wp_query -> query_vars['term'];
				break;
			case 'single':
				$body_classes[] = $type;
				$body_classes[] = $type . '-' . get_post_type();
				$body_classes[] = $type . '-' . get_post_type() . '-' . $wp_query -> query['name'];
				break;
			case 'page':
				$body_classes[] = $type;
				$body_classes[] = $type . '-' . $wp_query -> query['pagename'];
				break;
			case 'attachment':
				$body_classes[] = $type;
				$mime_type = explode('/', get_post_mime_type());
				$body_classes[] = $type . '-' . $mime_type[0];
				$body_classes[] = $type . '-' . $mime_type[0] . '-' . $wp_query -> query['attachment'];
				break;
			default:
				$body_classes[] = $type;
		}
	}
}
