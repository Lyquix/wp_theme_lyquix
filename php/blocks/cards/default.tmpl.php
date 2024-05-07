<?php

/**
 * default.tmpl.php - Default template for the Lyquix Cards block
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
//  Instead, copy it to /php/custom/blocks/cards/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/cards/{preset}.tmpl.php

?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-cards <?= esc_attr($s['class']) ?>">

	<div
		class="cards <?= implode(' ', $css_classes) ?>"
		id="<?= esc_attr($s['hash']) ?>"
		data-slider="<?= $s['slider'] ?>"
		data-swiper-options-override="<?= esc_attr($s['swiper_options_override']) ?>"
		data-heading-style="<?= $s['heading_style'] ?>"
		data-subheading-style="<?= $s['subheading_style'] ?>"
		data-heading-clickable="<?= $s['heading_clickable'] ?>"
		data-image-clickable="<?= $s['image_clickable'] ?>"
		data-responsive-rules="<?= esc_attr(json_encode($s['responsive_rules'])) ?>">

		<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>

			<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'cards-wrapper' ?>">

				<?php foreach ($c as $idx => $item) :
					// Video attributes
					$video_attrs = '';
					if ($item['video']['type'] == 'url' && $item['video']['url']) {
						$video = \lqx\util\get_video_urls($item['video']['url']);
						if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
							'name' => str_replace('id-', 'card-video-', $s['hash']) . '-' . $idx,
							'type' => 'video',
							'url' => $video['url'],
							'useHash' => false
						])));
					}
				?>

				<?php require \lqx\blocks\get_template('cards', $preset, 'item'); ?>

				<?php endforeach; ?>

			</ul>

			<?php if ($s['slider'] == 'y') require \lqx\blocks\get_template('cards', $preset, 'controls'); ?>

		<?= $s['slider'] == 'y' ? '</div>' : '' ?>

	</div>

</section>
