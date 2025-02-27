<?php

/**
 * default-item.tmpl.php - Default template for the Lyquix Testimonial block, item sub-template
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
//  Instead, copy it to /php/custom/blocks/testimonial/default-item.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/testimonial/{preset}-item.tmpl.php

?>
<li
	class="<?= $s['slider'] == 'y' ? 'swiper-slide' : 'testimonial-slide' ?> <?= $item['additional_classes'] ? esc_attr($item['additional_classes']) : '' ?>"
	id="<?= $item['item_id'] ?: $s['hash'] . '-' . $idx ?>"
	<?php if($item['background_color']): ?> style="background: <?= $item['background_color'] ?>" <?php endif; ?>
	>
	<blockquote <?php if($item['foreground_color']): ?> style="--text-color: <?= $item['foreground_color'] ?>" <?php endif; ?>><?= $item['content'] ?></blockquote>
	<?php if ($item['name'] !== ''): ?>
	<div class="author-wrapper">
		<div class="testimonial-author" <?php if($item['foreground_color']): ?> style="--text-color: <?= $item['foreground_color'] ?>" <?php endif; ?>>
			<span class="name !m-0"><?= strip_tags($item['name']) ?></span>
			<span class="job-title ml-[1ch] lg:m-0"><?= strip_tags($item['job_title']) ?></span>
		</div>
	</div>
	<?php endif; ?>
</li>
