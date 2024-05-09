<?php

/**
 * default.tmpl.php - Default template for the Lyquix Slider block
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
//  Instead, copy it to /php/custom/blocks/slider/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/slider/{preset}.tmpl.php

?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-slider <?= esc_attr($s['class']) ?>">

	<div
		class="slider"
		id="<?= esc_attr($s['hash']) ?>"
		data-autoplay="<?= $s['autoplay'] ?>"
		data-autoplay-delay="<?= $s['autoplay_delay'] ?>"
		data-swiper-options-override="<?= esc_attr($s['swiper_options_override']) ?>"
		data-loop="<?= $s['loop'] ?>"
		data-navigation="<?= $s['navigation'] ?>"
		data-pagination="<?= $s['pagination'] ?>">

		<div class="swiper">

			<ul class="swiper-wrapper">

				<?php foreach ($c as $idx => $item) : ?>

					<div class="swiper-slide" data-slide-teaser="<?= $item['teaser_text']?>" <?= !empty($item['thumbnail']['url']) ? 'data-slide-thumbnail="'.$item['thumbnail']['url'].'"' : '' ?>>

						<?php require \lqx\blocks\get_template('slider', $preset, 'image'); ?>

						<?php require \lqx\blocks\get_template('slider', $preset, 'text'); ?>

						<?php if (count($item['links'])) require \lqx\blocks\get_template('slider', $preset, 'links'); ?>

					</div>

				<?php endforeach; ?>

			</ul>

			<?php if ($s['navigation'] == 'y') : ?>
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			<?php endif; ?>

			<?php if ($s['pagination'] == 'y') : ?>
				<div class="swiper-pagination"></div>
			<?php endif; ?>

		</div>
	</div>
</section>
