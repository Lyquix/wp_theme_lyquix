<?php
// note: unlike other things we've utilized in our theme, this file must return a string and not raw html
// otherwise it will not properly add the contents to the infowindow when opened
$html = '<div class="infowindow">';
if ($image !== null && $image !== false) :
$html = '<div class="image">'.
	($s['items_display_settings']['image_clickable'] == 'y' && $link !== '' ? '<a href="' . $link['url'] . '">' : '').
		'<img
			src="'.esc_attr($image['url']).'"
			alt="'.esc_attr($image['alt']).'"
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
			$html .=	'<div>' . $phone['label'] . ': <a href=tel:"' . $phone['phone_number'] .'">' . $phone['phone_number'] . '</a></div>';
		endforeach;
	endif;
	$html .= '</div>';
	$html .= $description;
	//if (count($item['business_hours']) > 0) require \lqx\blocks\get_template('map', $s['preset'], 'office-hours');
	if ($s['show_get_directions_link'] == 'y') $html .= '<a href="https://maps.google.com/?q='.URLEncode($address['address']).'">Get Directions</a>';
$html .= '</div>';
$html .= '</div>';
return $html;
?>
