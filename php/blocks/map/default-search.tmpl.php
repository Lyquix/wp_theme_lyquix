<?php
/**
 * default-search.tmpl.php - Default search template for Lyquix map block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/default-search.tmpl.php and modify it there
//  You may also create custom renderer for specific presets, by copying this file to /php/custom/blocks/map/{preset}.php
?>
<div class="lqx-map-search">
	<div class="input-wrapper">
		<input class="search_query" id="lqx-map-search=-ield" name="search_query" type="text" placeholder="Enter an address or zip code" autocomplete="off">
		<button type="submit" name="submit" class="submit-button use-my-location" value=""></button>
	</div>
	<?php if ($s['show_distance_limit_drop_down'] == 'y'): ?>
		<select class="miles-from" id="miles-from">
			<option value="all">All locations</option>
			<option value="5">5 miles</option>
			<option value="10">10 miles</option>
			<option value="25">25 miles</option>
			<option value="50">50 miles</option>
			<option value="100">100 miles</option>
		</select>
	<?php endif;?>
	<div class="manual-container">
		<div class="manual-link">Use my current location</div>
	</div>
</div>
