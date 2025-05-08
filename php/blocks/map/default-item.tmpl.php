<?php
/**
 * default-item.tmpl.php - Default Item template for Lyquix map block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default-item.tmpl.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/map/{preset}.php

if ($item['image'] && $s['items_display_settings']['show_image'] == 'y'): ?>
	<div class="image">
	<?= ($s['items_display_settings']['image_clickable'] == 'y' && $item['link'] !== '' ? '<a href="' . $item['link']['url'] . '">' : '')?>
		<img
			src="<?= esc_attr($item['image']['url']) ?>"
			alt="<?= esc_attr($item['image']['alt']) ?>"
		/>
		<?= ($s['items_display_settings']['image_clickable'] == 'y' && $item['link'] !=='' ? '</a>' : '')?>
	</div>
<?php endif;?>
<div class="text">
	<?php if ($s['items_display_settings']['show_heading'] == 'y'):?>
		<<?= $s['items_display_settings']['heading_style'] ?>>
			<?= ($s['items_display_settings']['heading_clickable'] == 'y' && $item['link'] !== '' ? '<a href="' . $item['link']['url'] . '">' : '')?><?= $item['title'] ?><?= ($s['items_display_settings']['heading_clickable'] == 'y' && $item['link'] !== '' ? '</a>' : '')?>
		</<?= $s['items_display_settings']['heading_style'] ?>>
	<?php endif; ?>
	<?php if ($item['subtitle'] != '' && $s['items_display_settings']['show_subheading'] == 'y'): ?><<?= $s['items_display_settings']['subtitle_style'] == 'p' ? 'p class="subheading"><strong' : $s['items_display_settings']['subtitle_style'] ?>>
		<?= $item['subtitle'] ?>
	</<?= $s['items_display_settings']['subtitle_style'] == 'p' ? 'strong></p' : $s['items_display_settings']['subtitle_style'] ?>>
	<?php endif; ?>
	<div class="address"><?= ($item['display_address'] !== '' ? $item['display_address'] : $item['address']) ?></div>
	<?php if ($item['phone_numbers'] && $s['items_display_settings']['show_phone_numbers'] == 'y'): ?>
	<div class="phone-numbers">
		<?php foreach($item['phone_numbers'] as $phone): ?>
			<div><?= $phone['label']?>: <a href=tel:"<?= $phone['phone_number'] ?>"><?= $phone['phone_number'] ?></a></div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<?php if ($s['items_display_settings']['show_description'] == 'y'):?> <?= $item['description'] ?> <?php endif; ?>
	<?php if (is_array($item['business_hours']) && count($item['business_hours']) > 0 && $s['items_display_settings']['show_business_hours'] == 'y') require \lqx\blocks\get_template('map', $s['preset'], 'office-hours'); ?>
	<?php	if ($s['show_get_directions_link'] == 'y'): ?><a href="https://maps.google.com/?q=<?=URLEncode($item['address'])?>">Get Directions</a><?php endif;?>
</div>
