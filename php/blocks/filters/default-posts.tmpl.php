<?php

/**
 * default-posts.tmpl.php - Default template for the Lyquix Filters block, posts sub-template
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
//  Instead, copy it to /php/custom/blocks/filters/default-posts.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-posts.tmpl.php

if (count($s['posts'])) {
	// For settings we need to get the preset settings from cards.
	$cards_settings = \lqx\blocks\get_settings('cards', null, $s['render_php']['preset'], $s['render_php']['style']);

	// Change the hash to use the same as the filters
	$cards_settings['processed']['hash'] = $s['hash'] . '-posts';

	// Add class 'posts' to the classes array
	$cards_settings['processed']['class'] = 'posts ' . ($s['render_php']['style'] ?: '');

	// Pass filter preset to cards if cards preset is empty
	$cards_settings['processed']['preset'] = $cards_settings['processed']['preset'] ?: $s['preset'];

	// Pass group_by settings through to card items
	$cards_settings['processed']['group_by'] = $s['group_by'] ?? 'n';
	if (($s['group_by'] ?? 'n') === 'y') {
		foreach (['group_by_source', 'group_by_acf_field', 'group_by_post_property', 'group_by_field_name',
		          'group_by_taxonomy', 'group_by_value_type', 'group_by_date_key_format',
		          'group_by_date_label_format', 'group_by_heading_tag', 'group_by_heading_template'] as $_gb_key) {
			$cards_settings['processed'][$_gb_key] = $s[$_gb_key] ?? '';
		}
	}

	// Render the cards
	\lqx\blocks\render_block($cards_settings, $s['posts']);
}
else if($s['show_no_results_message'] == 'y') {
	// No posts found
	?>
	<div class="no-results">
		<?= $s['no_results_message'] ?>
	</div>
	<?php
}
