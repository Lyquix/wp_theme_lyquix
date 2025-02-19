<?php
/**
 * default-map.tmpl.php - Default template for the Lyquix Map block, map sub-template
 *
 * @version     3.0.0
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
//  Instead, copy it to /php/custom/blocks/map/default-map.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/map/{preset}-map.tmpl.php

$google_maps_api_key = acf_get_setting( 'google_api_key' );
?>
<div class="map" id="lqx-map"></div>
<!--Note for reviews (Remove later): I believe we need the following two tags to parse information from block settings.-->
<script src="//maps.googleapis.com/maps/api/js?key=<?= $google_maps_api_key ?>&amp;libraries=places"></script>
