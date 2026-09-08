<?php

/**
 * default.tmpl.php - Default template for the Lyquix Tabs plus block
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
//  Instead, copy it to /php/custom/blocks/tabs-plus/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/tabs-plus/{preset}.tmpl.php
$allowed_blocks = [ 'lqx/tab-item' ];
$innerBlocks = $settings['innerBlocks'];
?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-tabs  <?= esc_attr($s['class']) ?>"
	<?php if ($s['preset']): ?>data-preset="<?= esc_attr($s['preset']) ?>"<?php endif; ?>>

	<div
		class="tabs"
		id="<?= esc_attr($settings['hash']) ?>"
		data-browser-history="<?= esc_attr($s['browser_history']) ?>"
		data-close-on-click="<?= esc_attr($s['close_on_click']) ?>"
		data-convert-to-accordion="<?= esc_attr(implode(',', $s['convert_to_accordion'])) ?>"
		data-auto-scroll="<?= esc_attr(implode(',', $s['auto_scroll'])) ?>">

		<ul
			class="tabs-list"
			role="tablist"
			aria-hidden="false">
			<?php
			foreach ($innerBlocks as $idx => $item) {
				$block = [
					'name' => $item['blockName'],
					'anchor' => $item['attrs']['anchor'] ?? '',
					'className' => $item['attrs']['className'] ?? '',
				];
				$itemSettings = \lqx\blocks\get_settings($block);
				require \lqx\blocks\get_template('tabs-plus', $itemSettings['processed']['preset'], 'tab');
			}
			?>
		</ul>

		<InnerBlocks allowedBlocks="<?= esc_attr( wp_json_encode( $allowed_blocks ) ) ?>" />

	</div>

</section>
