<?php

/**
 * default-heading.tmpl.php - Default template for the Lyquix Filters block, heading sub-template
 *
 * @version     3.2.0
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
//  Instead, copy it to /php/custom/blocks/filters/default-heading.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-heading.tmpl.php
?>
<?php if ($s['show_heading'] == 'y' && count($s['posts'])):
	//  before rendering the heading, check it to see if an override was set on the block
	$heading = $s['default_heading'] ?? '';

	if (isset($c['heading_override']) && $c['heading_override']) {
		$heading = $c['heading_override'];
	}
	//  furthermore, replace wildcard strings in the text if present
	//  %POST_TYPE% - current post type
	//  %POST_TITLE% - current post title
	//  for post type the function normally returns the slug so we want the label that shows up on the backend, we want to grab the singular name
	//  only run the check if needed
	if (strpos($heading, '%POST_TYPE%') !== false) {
		$post_type_labels = get_post_type_labels(get_post_type_object($s['post_type']));
		$heading = str_replace('%POST_TYPE%', $post_type_labels->singular_name, $heading);
	}
	if (strpos($heading, '%POST_TITLE%') !== false) {
		$heading = str_replace('%POST_TITLE%', get_the_title(), $heading);
	}?>
	<<?= $s['heading_style'] ?> class="filters-heading"><?= $heading ?></<?= $s['heading_style'] ?>>
<?php endif; ?>
