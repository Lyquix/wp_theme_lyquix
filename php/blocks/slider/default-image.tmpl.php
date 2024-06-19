<?php

/**
 * default-image.tmpl.php - Default template for the Lyquix Slider block, image sub-template
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
//  Instead, copy it to /php/custom/blocks/slider/default-image.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/slider/{preset}-image.tmpl.php


// Video attributes
$video_attrs = '';
if ($item['video']['type'] == 'url' && $item['video']['url']) {
	$video = \lqx\util\get_video_urls($item['video']['url']);
	if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
		'name' => str_replace('id-', 'slider-video-', $s['hash']) . '-' . $idx,
		'type' => 'video',
		'url' => $video['url'],
		'useHash' => false
	])));
}

?>
<div class="image" <?= $video_attrs ?>>

	<?php if ($item['image_link'] && $item['image_link']['url'] && $item['video']['type'] != 'url') : ?>
		<a
			href="<?= esc_attr($item['image_link']['url']) ?>"
			title="<?= esc_attr($item['image_link']['title']) ?>"
			target="<?= $item['image_link']['target'] ?>">
	<?php endif; ?>

		<?php if ($item['video']['type'] == 'upload' && $item['video']['upload']) : ?>
			<video
				autoplay loop muted playsinline
				poster="<?= $item['image']['sizes']['large'] ?>">
				<source
					src="<?= esc_attr($item['video']['upload']['url']) ?>"
					type="<?= esc_attr($item['video']['upload']['mime_type']) ?>">
			</video>
		<?php else: ?>
			<?php if (array_key_exists('url', $item['image'])) : ?>
				<img
					src="<?= esc_attr($item['image']['url']) ?>"
					alt="<?= esc_attr($item['image']['alt']) ?>"
					class="<?= array_key_exists('url', $item['image_mobile']) ? 'xs:hidden md:block' : '' ?>" />
			<?php endif;
			if (array_key_exists('url', $item['image_mobile'])) : ?>
				<img
					src="<?= esc_attr($item['image_mobile']['url']) ?>"
					alt="<?= esc_attr($item['image_mobile']['alt']) ?>"
					class="xs:block md:hidden" />
			<?php endif; ?>
		<?php endif; ?>

	<?php if ($item['image_link'] && $item['image_link']['url'] && $item['video']['type'] != 'url') : ?>
		</a>
	<?php endif; ?>

</div>
