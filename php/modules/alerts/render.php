<?php

/**
 * render.php - Lyquix Socials module render functions
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
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
//  If you need a custom renderer, copy this file to php/custom/modules/alerts/render.php and modify it there

namespace lqx\modules\alerts;

/**
 * Render alerts
 * @param  array $settings - alerts settings
 * 	autoplay - boolean, whether to autoplay alerts
 * 	autoplay_delay - integer, delay in seconds between alerts
 *  swiper_options_override - string, a JSON object to override Swiper options
 * 	heading_style - string, style of heading: p, h1, h2, h3, h4, h5, h6
 */
function render() {
	// Get settings
	$s = get_field('alerts_module_settings', 'option');

	?>
	<section id="lqx-module-alerts">
		<div
			class="alerts hidden"
			data-autoplay="<?= $s['autoplay'] ?>"
			data-autoplay-delay="<?= $s['autoplay_delay'] * 1000 ?>"
			data-swiper-options-override="<?= htmlspecialchars($s['swiper_options_override']) ?>"
			data-heading-style="<?= $s['heading_style'] ?>">
			<button class="close">Close</button>
			<div class="swiper">
				<div class="swiper-wrapper"></div>
			</div>
			<div class="controls">
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>
		</div>
	</section>
	<?php
}
