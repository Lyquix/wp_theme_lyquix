<?php

/**
 * default.tmpl.php - Default template for the Lyquix Accordion Plus block
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
//  Instead, copy it to /php/custom/blocks/accordion-plus/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/accordion-plus/{preset}.tmpl.php

$allowed_blocks = [ 'lqx/accordion-item' ];
?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-accordion <?= esc_attr($s['class']) ?>"
	<?php if ($s['preset']): ?>data-preset="<?= esc_attr($s['preset']) ?>"<?php endif; ?>>

	<div
		class="accordion"
		id="<?= esc_attr($s['hash']) ?>"
		data-open-on-load="<?= esc_attr($s['open_on_load']) ?>"
		data-open-multiple="<?= esc_attr($s['open_multiple']) ?>"
		data-browser-history="<?= esc_attr($s['browser_history']) ?>"
		data-auto-scroll="<?= esc_attr(implode(',', $s['auto_scroll'])) ?>">

		<InnerBlocks allowedBlocks="<?= esc_attr( wp_json_encode( $allowed_blocks ) ) ?>" />
	</div>

</section>
