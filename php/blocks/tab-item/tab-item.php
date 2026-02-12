<?php

/**
 * tab.php - Lyquix tab item block
 *
 * @version     3.1.0
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

// Get block settings and content
$settings = \lqx\blocks\get_settings($block);
$content = \lqx\blocks\get_content($block);

// TODO - parse and merge parent settings
$preset = $context['acf/fields']['tabs-plus_block_user_preset'] ?? null;
if($preset) {
	$presets = get_field('tabs-plus_block_presets', 'option');
	foreach ($presets as $item) {
		if($item['preset_name'] == $preset) {
			$settings['processed']['convert_to_accordion'] = $item['tabs-plus_block_admin']['convert_to_accordion_override_group']['convert_to_accordion_override'];
		}
	}
}

$settings['processed']['convert_to_accordion'] = $settings['processed']['convert_to_accordion'] ?? $context['acf/fields']['tabs-plus_block_admin_convert_to_accordion_override_group_convert_to_accordion_override'] ?? null;
$settings['processed']['hash'] = $block['parentHash'];
$settings['processed']['idx'] = $block['itemIndex'];

// Render the block
\lqx\blocks\render_block($settings, $content);
