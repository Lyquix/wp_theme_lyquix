<?php
/**
 * default-office-hours.tmpl.php - Default office hours template for Lyquix map block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default-office-hours.tmpl.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/map/{preset}.php
?>
<div class="hours">
<?php foreach($item['business_hours'] as $hours):
	switch($hours['day_group_type']) {
		case 'single':
			echo $hours['single'] . ': ' . $hours['hours']['open'] . ' - ' . $hours['hours']['close'];
			break;
		case 'multiple':
			echo implode(', ', $hours['multiple']) . ': ' . $hours['hours']['open'] . ' - ' . $hours['hours']['close'];
			break;
		case 'range':
			echo $hours['range']['start'] . ' - ' . $hours['range']['end'] . ': ' . $hours['hours']['open'] . ' - ' . $hours['hours']['close'];
			break;
	}
endforeach;
?>
</div>
