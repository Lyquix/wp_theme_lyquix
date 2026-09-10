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
$display = $s['items_display_settings'] ?? [];
// The link is an ACF link array, or a permalink string when the location has no link
$link_url = is_array($link ?? null) ? ($link['url'] ?? '') : (string) ($link ?? '');
$address_text = is_array($address ?? null) ? ($address['address'] ?? '') : '';
$heading_tag = tag_escape($display['heading_style'] ?? 'h3');
$subtitle_tag = tag_escape($display['subtitle_style'] ?? 'p');

$html = '<div class="infowindow">';
if (!empty($image['url'])) :
	$img = '<img src="' . esc_url($image['url']) . '" ' . \lqx\util\get_alt_attribs($image['alt'] ?? '') . (($s['lazy_load'] ?? 'y') != 'y' ? ' loading="eager" data-no-lazy="1"' : '') . ' />';
	$html .= '<div class="image">' . (($display['image_clickable'] ?? 'y') == 'y' && $link_url !== '' ? '<a href="' . esc_url($link_url) . '">' . $img . '</a>' : $img) . '</div>';
endif;
$html .= '<div class="text">';
	$html .= '<' . $heading_tag . '>' . (($display['heading_clickable'] ?? 'y') == 'y' && $link_url !== '' ? '<a href="' . esc_url($link_url) . '">' . $heading . '</a>' : $heading) . '</' . $heading_tag . '>';
	if (($subheading ?? '') != '') :
		$html .= $subtitle_tag == 'p' ? '<p class="subtitle"><strong>' . $subheading . '</strong></p>' : '<' . $subtitle_tag . '>' . $subheading . '</' . $subtitle_tag . '>';
	endif;
	$html .= '<div class="address">' . (($display_address_override ?? '') !== '' ? $display_address_override : esc_html($address_text)) . '</div>';
	$html .= '<div class="phone-numbers">';
	if (is_array($phone_numbers ?? null)):
		foreach($phone_numbers as $phone):
			$html .=	'<div>' . esc_html($phone['label'] ?? '') . ': <a href="tel:' . esc_attr($phone['phone_number'] ?? '') . '">' . esc_html($phone['phone_number'] ?? '') . '</a></div>';
		endforeach;
	endif;
	$html .= '</div>';
	$html .= $description ?? '';
	//if (count($item['business_hours']) > 0) require \lqx\blocks\get_template('map', $s['preset'], 'office-hours');
	if (($s['show_get_directions_link'] ?? 'n') == 'y' && $address_text !== '') $html .= '<a href="' . esc_url('https://maps.google.com/?q=' . urlencode($address_text)) . '">Get Directions</a>';
$html .= '</div>';
$html .= '</div>';
return $html;
