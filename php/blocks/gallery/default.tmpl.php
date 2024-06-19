<?php

/**
 * default.tmpl.php - Default template for the Lyquix Gallery block
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
//  Instead, copy it to /php/custom/blocks/gallery/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/gallery/{preset}.tmpl.php

?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-gallery <?= esc_attr($s['class']) ?>">

	<div
		class="gallery <?= $s['slider'] == 'y' ? 'slider' : '' ?>"
		id="<?= esc_attr($s['hash']) ?>"
		data-slider="<?= $s['slider'] ?>"
		data-swiper-options-override="<?= esc_attr($s['swiper_options_override']) ?>"
		data-heading-style="<?= $s['heading_style'] ?>"
		data-browser-history="<?= $s['browser_history'] ?>">

		<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>
			<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'gallery-wrapper' ?>">

				<?php
				foreach ($c['slides'] as $idx => $item) {
					$video = null;
					if ($item['video']) $video = \lqx\util\get_video_urls($item['video']);
					require \lqx\blocks\get_template('gallery', $s['preset'], 'item');
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
