<?php
/**
 * default.tmpl.php - Default template for Lyquix map block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default.tmpl.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/map/{preset}.php

//while this might be redundant for the main map block this is important for filters
$map_settings = \lqx\blocks\get_settings('map', null, $s['preset'], $s['style']);
// do we need to map the posts' data to match the expected structure of the map?
$map_settings = $map_settings['processed'];
$s['google_maps_display_settings'] = $map_settings['google_maps_display_settings'];
$s['items_display_settings'] = $map_settings['items_display_settings'];
$s['show_get_directions_link'] = $map_settings['show_get_directions_link'];
$processed_items = [];
// to prepare items for rendering and addition to the map, prepare the information.
foreach($c as $item) {
	if (isset($item['id'])) {
		$location_fields = get_field('location', $item['id']);
		$id = $item['id'];
	} else {
		$location_fields = $item['location'];
		$id = null;
	}
	$address = $location_fields['address'];
	$lat = ($location_fields['latitude_override'] !== '' ? $location_fields['latitude_override'] : $address['lat']);
	$lon = ($location_fields['longitude_override'] !== '' ? $location_fields['longitude_override'] : $address['lng']);
	$description = ($location_fields['description'] !== '' ? $location_fields['description'] : get_the_excerpt($id));
	$image = $location_fields['image'];
	$heading = ($location_fields['heading'] !== '' ? $location_fields['heading'] : get_the_title($id));
	$subheading = ($location_fields['subheading'] !== '' ? $location_fields['subheading'] : '');
	$link = ($location_fields['link'] !== null ? $location_fields['link'] : get_permalink($id));
	$phone_numbers = $location_fields['phone_numbers'];
	$business_hours = $location_fields['business_hours'];
	$display_address_override =$location_fields['display_address_override'];
	$custom_class = $location_fields['additional_classes'];
	$custom_id = $location_fields['item_id'];
	$processed_item = [
		'item_id' => $id,
		'title' => $heading,
		'subtitle' => $subheading,
		'link' => $link,
		'description' => $description,
		'image' => $image,
		'business_hours' => $business_hours,
		'phone_numbers' => $phone_numbers,
		'lat' => $lat,
		'lon' => $lon,
		'address' => (is_array($address) ? $address['address'] : ''),
		'state' => (is_array($address) ?  $address['state_short'] : ''),
		'city'=> (is_array($address) ? $address['city'] : ''),
		'zip'=> (is_array($address) ? $address['post_code'] : ''),
		'street'=> (is_array($address) ? $address['name'] : ''),
		'display_address' => $display_address_override,
		'icon' => ($s['google_maps_display_settings']['pin_override'] !== null ? $s['google_maps_display_settings']['pin_override']['url'] : 'http://maps.google.com/mapfiles/ms/icons/' . $item['pin_color'] . '-dot.png'),
		'infoWindow' => $s['show_infowindows'] == 'y' ? 'true' : 'false',
		'html' => ($s['show_infowindows'] == 'y' ? require \lqx\blocks\get_template('map', $s['preset'], 'infowindow'): ''),
		'additional_classes' => $custom_class,
		'custom_id' => $custom_id
	];
	array_push($processed_items, $processed_item);
}
if ($s['show_map'] == 'y'):
?>
<section
	id="<?= esc_attr($s['hash']) ?>"
	class="lqx-block-map <?= esc_attr($s['class']) ?>" data-settings="<?= esc_attr(json_encode($map_settings)) ?>" data-items="<?= esc_attr(json_encode($processed_items)) ?>">
<div class="map-wrapper">
	<?php require \lqx\blocks\get_template('map', $s['preset'], 'map'); ?>
</div>
<?php endif;
if ($map_settings['show_items'] == 'y') require \lqx\blocks\get_template('map', $s['preset'], 'items');
?>
</section>
