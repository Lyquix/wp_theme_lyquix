<?php

/**
 * default.tmpl.php - Default template for the Lyquix Tab item block
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
//  Instead, copy it to /php/custom/blocks/tab-item/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/tab-item/{preset}.tmpl.php
$s['uid'] = uniqid();
?>
<section
	class="tab-panel  <?= $c['additional_classes'] ? esc_attr($c['additional_classes']) : '' ?>"
	id="<?= $c['item_id'] ? esc_attr($c['item_id']) . '-panel' : $s['hash'] . '-panel-' . $s['uid'] ?>"
	aria-labelledby="<?= $s['hash'] . '-tab-' . $s['uid'] ?>"
	aria-hidden="<?= '' // $idx == 0 ? 'false' : 'true' ?>"
	role="tabpanel"
	tabindex="0">

	<?php if ($s['convert_to_accordion']) : ?>
		<button
			class="accordion-header"
			id="<?= $s['hash'] . '-header-' . $s['uid'] ?>"
			aria-expanded="<?= '' // $idx == 0 ? 'true' : 'false' ?>"
			aria-controls="<?= $s['hash'] . '-panel-' . $s['uid'] ?>"
			aria-hidden="true">
			<?= $c['label'] ?>
		</button>
	<?php endif; ?>

	<div
		class="tab-content"
		id="<?= $s['hash'] . '-content-' . $s['uid'] ?>">
	<?= $c['heading'] ? sprintf('<%s>%s</%s>', $s['heading_style'], $c['heading'], $s['heading_style']) : '' ?>
	<InnerBlocks />
	</div>

</section>
