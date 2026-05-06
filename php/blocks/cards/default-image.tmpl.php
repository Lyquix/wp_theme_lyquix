<?php

/**
 * default-image.tmpl.php - Default template for the Lyquix Cards block, image sub-template
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
//  Instead, copy it to /php/custom/blocks/cards/default-image.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/cards/{preset}-image.tmpl.php

?>
<div class="image" <?= $video_attrs ?>>
	<?php if ($item['video']['type'] == 'upload' && isset($item['video']['upload'])  && $s['show_video'] == 'y') :
		$video_classes = [];
		if ($s['lazy_load'] == 'y') {
			$video_classes[] = 'lazyload-video';
		}
		if ($s['hover_play'] == 'y') {
			$video_classes[] = 'video-hover-play';
		}
		if ($s['viewport_play'] == 'y') {
			$video_classes[] = 'video-viewport-play';
		}
		?>
		<video class="<?= implode(' ', $video_classes)?>" preload="auto"
			loop muted playsinline autoplay
			poster="<?= $item['image']['sizes']['medium'] ?>"
			data-src="<?= esc_attr($hover_content['video']['url'] ?? '') ?>"
			type="<?= esc_attr($hover_content['video']['mime_type'] ?? '') ?>">
			<source
				src="<?= esc_attr($item['video']['upload']['url']) ?>"
				type="<?= esc_attr($item['video']['upload']['mime_type']) ?>"/>
		</video>
	<?php else: ?>
		<?= $s['image_clickable'] == 'y' && isset($item['link']) && $item['link']['url'] ? sprintf('<a href="%s" target ="%s">', esc_attr($item['link']['url']), $item['link']['target']) : '' ?>
	<?php if($item['image_html']):
		// TODO what is image_html?
		echo $item['image_html'];
	else:
		?>
		<img
			<?= \lqx\util\get_src_srcset_sizes_attribs($item['image']) ?>
			<?= \lqx\util\get_alt_attribs($item['image']['alt'] ?? '') ?>
			<?= $s['lazy_load'] != 'y' ? 'loading="eager" data-no-lazy="1"' : '' ?>>
	<?php endif; ?>
		<?= $s['image_clickable'] == 'y' && isset($item['link']) && $item['link']['url'] ? '</a>' : '' ?>
	<?php endif; ?>
</div>
