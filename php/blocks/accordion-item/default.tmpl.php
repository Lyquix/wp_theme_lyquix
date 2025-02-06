<?php

/**
 * default.tmpl.php - Default template for the Lyquix Accordion Item block
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
//  Instead, copy it to /php/custom/blocks/accordion-item/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/accordion-item/{preset}.tmpl.php
$s['uid'] = uniqid();
?>
<div
	class="accordion-item <?= $c['additional_classes'] ? esc_attr($c['additional_classes']) : '' ?>"
	id="<?= $c['item_id'] ? esc_attr($c['item_id']) : $s['hash'] ?>">
	<?php require \lqx\blocks\get_template('accordion-item', $s['preset'], 'header'); ?>
	<?php require \lqx\blocks\get_template('accordion-item', $s['preset'], 'panel'); ?>
</div>
