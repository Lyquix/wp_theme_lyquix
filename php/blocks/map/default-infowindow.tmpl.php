<?php

/**
 * default-infowindow.tmpl.php - Default Infowindow template for  Lyquix map block
 *
 * @version     3.4.0
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
//  If you need a custom renderer, copy this file to php/custom/blocks/map/default-infowindow.tmpl.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/map/{preset}.php

// note: unlike other things we've utilized in our theme, this file must return a string and not raw html
// otherwise it will not properly add the contents to the infowindow when opened
$html = '<div class="infowindow">';
if ($image !== null && $image !== false) :
$html = '<div class="image">'.
	($s['items_display_settings']['image_clickable'] == 'y' && $link !== '' ? '<a href="' . $link['url'] . '">' : '').
		'<img
			src="'.esc_attr($image['url']).'"
			'.\lqx\util\get_alt_attribs($image['alt'] ?? '').'
			'.($s['lazy_load'] != 'y' ? 'loading="eager" data-no-lazy="1"' : '').'
		/>'.
		($s['items_display_settings']['image_clickable'] == 'y' && $link !== '' ? '</a>' : '').
	'</div>';
endif;
$html .= '<div class="text">';
	$html .= '<'.$s['items_display_settings']['heading_style'].'>'.($s['items_display_settings']['heading_clickable'] == 'y' && $link !== null && $link !== '' ? '<a href="' . $link['url'] . '">' : ''). $heading . ($s['items_display_settings']['heading_clickable'] == 'y' && $link !== null && $link !== '' ? '</a>' : '') . '</'. $s['items_display_settings']['heading_style'] . '>';
	if ($subheading != ''): '<'. $s['items_display_settings']['subtitle_style'] == 'p' ? 'p class="subtitle"><strong' : $s['items_display_settings']['subtitle_style'] .'>'.
		$subheading .
	'</' . ($s['items_display_settings']['subtitle_style'] == 'p' ? 'strong></p' : $s['items_display_settings']['subtitle_style']) . '>';
	endif;
	$html .= '<div class="address">' . ($display_address_override !== '' ? $display_address_override : $address['address']) .'</div>';
	$html .= '<div class="phone-numbers">';
	if (is_array($phone_numbers)):
		foreach($phone_numbers as $phone):
			$html .=	'<div>' . esc_html($phone['label']) . ': <a href="tel:' . esc_attr($phone['phone_number']) . '">' . esc_html($phone['phone_number']) . '</a></div>';
		endforeach;
	endif;
	$html .= '</div>';
	$html .= $description;
	//if (count($item['business_hours']) > 0) require \lqx\blocks\get_template('map', $s['preset'], 'office-hours');
	if ($s['show_get_directions_link'] == 'y') $html .= '<a href="https://maps.google.com/?q='.URLEncode($address['address']).'">Get Directions</a>';
$html .= '</div>';
$html .= '</div>';
return $html;
