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
			poster="<?= array_key_exists('url', $c['image_override']) ? $c['image_override']['url'] : get_the_post_thumbnail_url() ?>">
			<source
				src="<?= esc_attr($c['video']['upload']['url']) ?>"
				type="<?= $c['video']['upload']['mime_type'] ?>">
		</video>
	<?php else: ?>
		<?php if (array_key_exists('url', $c['image_override'])) : ?>
			<img
				src="<?= esc_attr($c['image_override']['url']) ?>"
				alt="<?= esc_attr($c['image_override']['alt']) ?>"
				class="<?= array_key_exists('url', $c['image_mobile']) ? 'xs:hidden md:block' : '' ?>" />
		<?php else :
			the_post_thumbnail('post-thumbnail', ['class' => $c['image_mobile'] !== false ? 'xs:hidden md:block' : '']);
		endif; ?>
		<?php if (array_key_exists('url', $c['image_mobile'])) : ?>
			<img
				src="<?= esc_attr($c['image_mobile']['url']) ?>"
				alt="<?= esc_attr($c['image_mobile']['alt']) ?>"
				class="xs:block md:hidden" />
		<?php endif; ?>
	<?php endif; ?>
</div>
