<?php

/**
 * default-item.tmpl.php - Default template for the Lyquix Cards block, item sub-template
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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
//  Instead, copy it to /php/custom/blocks/cards/default-item.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/cards/{preset}-item.tmpl.php

?>
<li
	class="<?= $s['slider'] == 'y' ? 'swiper-slide' : 'card' ?>"
	id="<?= $s['hash'] . '-' . $idx ?>">

	<?php if (!empty($item['labels'])) require \lqx\blocks\get_template('cards', $preset, 'labels'); ?>

	<?php if ($item['image']) require \lqx\blocks\get_template('cards', $preset, 'image'); ?>

	<?php if ($item['icon_image']) require \lqx\blocks\get_template('cards', $preset, 'icon-image'); ?>

	<?php require \lqx\blocks\get_template('cards', $preset, 'text'); ?>

</li>
