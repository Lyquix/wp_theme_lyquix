<?php

/**
 * default.tmpl.php - Default template for the Lyquix Testimonial block
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
//  Instead, copy it to /php/custom/blocks/testimonial/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/testimonial/{preset}.tmpl.php
?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-testimonial <?= esc_attr($s['class']) ?>"
	<?php if ($s['preset']): ?>data-preset="<?= esc_attr($s['preset']) ?>"<?php endif; ?>>
	<div
		class="testimonial <?= $s['slider'] == 'y' ? 'slider' : '' ?>"
		id="<?= esc_attr($s['hash']) ?>"
		data-slider="<?= $s['slider'] ?>"
		data-swiper-options-override="<?= esc_attr($s['swiper_options_override'] ?? '') ?>">

		<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>
		<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'testimonial-wrapper' ?>">

			<?php

			if ($s['random_display'] == 'y') {
				if (!empty($c['testimonials'])) {
					$random_key = array_rand($c['testimonials']);
					$c['testimonials'] = [$c['testimonials'][$random_key]]; // Keep only one random item
				}
			}

			foreach ($c['testimonials'] as $idx => $item) {
				require \lqx\blocks\get_template('testimonial', $s['preset'], 'item');
			}
			?>

		</ul>

		<?php if ($s['slider'] == 'y') : ?>
			<div class="controls">
				<div class="swiper-button-prev"></div>
				<div class="swiper-button-next"></div>
			</div>
		<?php endif; ?>

		<?= $s['slider'] == 'y' ? '</div>' : '' ?>

	</div>
</section>
