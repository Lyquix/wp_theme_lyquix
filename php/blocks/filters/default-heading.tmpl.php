<?php

/**
 * default-heading.tmpl.php - Default template for the Lyquix Filters block, heading sub-template
 *
 * @version     3.4.0
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
<?php
// Resolve view all URL
$view_all_url = '';
if ($s['show_view_all'] == 'dynamic') {
	$view_all_url = get_post_type_archive_link($s['post_type']) ?: '';
} elseif ($s['show_view_all'] == 'url' && !empty($s['view_all_url'])) {
	$view_all_url = $s['view_all_url'];
}

$show_heading_el = $s['show_heading'] == 'y' && count($s['posts']);
$show_view_all_link = !empty($view_all_url);

if ($show_heading_el || $show_view_all_link): ?>
<div class="filters-header">
	<?php if ($show_heading_el):
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
	<?php if ($show_view_all_link): ?>
		<a href="<?= esc_url($view_all_url) ?>" class="filters-view-all">View All</a>
	<?php endif; ?>
</div>
<?php endif; ?>
