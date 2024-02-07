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
//  If you need a custom renderer, copy this file to php/custom/blocks/gallery/render.php and modify it there

namespace lqx\blocks\gallery;

/**
 * Render gallery
 * @param  array $settings - gallery settings
 * 	slider - boolean, whether to use a slider
 *  swiper_options_override - string, a JSON object to override Swiper options
 * 	browser_history - boolean, whether to use browser history
 */
function render($settings, $content) {
	// Get settings
	$s = $settings['processed'];

	// Heading style
	$heading_tag_open  = $s['heading_style'] == 'p' ? '<p class="title"><strong>' : '<' . $s['heading_style'] . '>';
	$heading_tag_close  = $s['heading_style'] == 'p' ? '</strong></p>' : '</' . $s['heading_style'] . '>';

	?>
	<section
		id="<?= $s['anchor'] ?>"
		class="lqx-block-gallery <?= $s['class'] ?>">

		<div
			class="gallery <?= $s['slider'] == 'y' ? 'slider' : '' ?>"
			id="<?= $s['hash'] ?>"
			data-slider="<?= $s['slider'] ?>"
			data-swiper-options-override="<?= htmlspecialchars($s['swiper_options_override']) ?>"
			data-heading-style="<?= $s['heading_style'] ?>"
			data-browser-history="<?= $s['browser_history'] ?>">

			<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>
				<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'gallery-wrapper' ?>">
					<?php foreach ($content['slides'] as $idx => $item) : ?>
						<?php if ($item['video']) $video = \lqx\util\get_video_urls($item['video']); ?>
						<li
							class="<?= $s['slider'] == 'y' ? 'swiper-slide' : 'gallery-slide' ?>"
							id="<?= $item['slug'] ? $item['slug'] : $s['hash'] . '-' . $idx ?>"
							data-lyqbox="<?= htmlentities(json_encode([
								'name' => $content['lightbox_slug'],
								'type' => $video['url'] ? 'video' : 'image',
								'url' => $video['url'] ? $video['url'] : $item['image']['sizes']['large'],
								'title' => $item['title'],
								'caption' => $item['caption']
							])) ?>">
							<img
								src="<?= $item['thumbnail']['sizes']['large'] ?>"
								alt="<?= htmlspecialchars($item['image']['alt']) ?>">
							<?= $heading_tag_open . $item['title'] . $heading_tag_close ?>
							<?= '<p>' . $item['teaser'] . '</p>' ?>
						</li>
					<?php endforeach; ?>
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
	<?php
}
