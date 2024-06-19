<?php

/**
 * default.tmpl.php - Default template for the Lyquix Accordion block
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
//  Instead, copy it to /php/custom/blocks/accordion/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/accordion/{preset}.tmpl.php

?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-accordion <?= esc_attr($s['class']) ?>">

	<div
		class="accordion"
		id="<?= esc_attr($s['hash']) ?>"
		data-open-on-load="<?= $s['open_on_load'] ?>"
		data-open-multiple="<?= $s['open_multiple'] ?>"
		data-browser-history="<?= $s['browser_history'] ?>"
		data-auto-scroll="<?= implode(',', $s['auto_scroll']) ?>">

		<?php foreach ($c as $idx => $item) : ?>
			<div
				class="accordion-item <?= $item['additional_classes'] ? esc_attr($item['additional_classes']) : '' ?>"
				id="<?= $item['item_id'] ? esc_attr($item['item_id']) : $s['hash'] . '-' . $idx ?>">
				<?php require \lqx\blocks\get_template('accordion', $s['preset'], 'header'); ?>
				<?php require \lqx\blocks\get_template('accordion', $s['preset'], 'panel'); ?>
			</div>
		<?php endforeach; ?>
	</div>

</section>
