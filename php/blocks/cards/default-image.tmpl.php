<?php

/**
 * default-image.tmpl.php - Default template for the Lyquix Cards block, image sub-template
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
//  Instead, copy it to /php/custom/blocks/cards/default-image.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/cards/{preset}-image.tmpl.php

?>
<div class="image" <?= $video_attrs ?>>
	<?php if ($item['video']['type'] == 'upload' && $item['video']['upload']) : ?>
		<video
			autoplay loop muted playsinline
			poster="<?= $item['image']['sizes']['large'] ?>">
			<source
				src="<?= esc_attr($item['video']['upload']['url']) ?>"
				type="<?= esc_attr($item['video']['upload']['mime_type']) ?>">
		</video>
	<?php else: ?>
		<?= $s['image_clickable'] == 'y' && $first_link ? '<a href="' . esc_attr($first_link) . '">' : '' ?>
		<img
			src="<?= esc_attr($item['image']['sizes']['large']) ?>"
			alt="<?= esc_attr($item['image']['alt']) ?>">
		<?= $s['image_clickable'] == 'y' && $first_link ? '</a>' : '' ?>
	<?php endif; ?>
</div>
