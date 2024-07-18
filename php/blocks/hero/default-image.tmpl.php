<?php

/**
 * default.tmpl.php - Default template for the Lyquix Hero block
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
//  Instead, copy it to /php/custom/blocks/hero/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/hero/{preset}.tmpl.php

?>
<div class="image" <?= $video_attrs ?>>
	<?php if ($c['video']['type'] == 'upload' && $c['video']['upload']) : ?>
		<video
			autoplay loop muted playsinline
			poster="<?= array_key_exists('url', $c['image_override']) ?
				($s['image_size'] == 'full' ? $c['image_override']['url'] : $c['image_override']['sizes'][$s['image_size']]) :
				get_the_post_thumbnail_url(null, $s['image_size']) ?>">
			<source
				src="<?= esc_attr($c['video']['upload']['sizes']['small']) ?>"
				type="<?= $c['video']['upload']['mime_type'] ?>">
		</video>
	<?php else: ?>
		<?php if (array_key_exists('url', $c['image_override'])) : ?>
			<img
				src="<?= esc_attr($s['image_size'] == 'full' ? $c['image_override']['url'] : $c['image_override']['sizes'][$s['image_size']]) ?>"
				alt="<?= esc_attr($c['image_override']['alt']) ?>"
				class="<?= array_key_exists('url', $c['image_mobile']) ? 'xs:hidden md:block' : '' ?>"
				<?= $s['disable_lazy_loading'] == 'y' ? 'loading="eager" data-skip-lazy' : '' ?> />
		<?php else :
			$the_post_thumbnail_opts = [];
			if (array_key_exists('url', $c['image_mobile'])) {
				$the_post_thumbnail_opts['class'] =  'xs:hidden md:block';
			}
			if ($s['disable_lazy_loading'] == 'y' ) {
				$the_post_thumbnail_opts['loading'] = 'eager';
				$the_post_thumbnail_opts['data-skip-lazy'] = '';
			}
			the_post_thumbnail($s['image_size'], $the_post_thumbnail_opts);
		endif; ?>
		<?php if (array_key_exists('url', $c['image_mobile'])) : ?>
			<img
				src="<?= esc_attr($s['image_mobile_size'] == 'full' ? $c['image_mobile']['url'] : $c['image_mobile']['sizes'][$s['image_mobile_size']]) ?>"
				alt="<?= esc_attr($c['image_mobile']['alt']) ?>"
				class="xs:block md:hidden"
				<?= $s['disable_lazy_loading'] == 'y' ? 'loading="eager" data-skip-lazy' : '' ?> />
		<?php endif; ?>
	<?php endif; ?>
</div>
