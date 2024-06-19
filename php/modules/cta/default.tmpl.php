<?php

/**
 * default.tmpl,php - Lyquix CTA module render functions
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
//  If you need a custom renderer, copy this file to php/custom/modules/cta/default.tmpl.php and modify it there
?>
<section class="lqx-module-cta">
	<div class="cta <?= $cta['slim_cta'] == 'y' ? 'slim' : '' ?> <?= $cta['style'] ?>">
		<div class="image">
			<?php if (array_key_exists('url', $cta['image'])) : ?>
				<img
					src="<?= esc_attr($cta['image']['url']) ?>"
					alt="<?= esc_attr($cta['image']['alt']) ?>"
					class="<?= array_key_exists('url', $cta['image_mobile']) ? 'xs-hide sm-hide' : '' ?>" />
			<?php endif;
			if (array_key_exists('url',$cta['image_mobile'])) : ?>
				<img
					src="<?= esc_attr($cta['image_mobile']['url']) ?>"
					alt="<?= esc_attr($cta['image_mobile']['alt']) ?>"
					class="hide xs-show sm-show" />
			<?php endif; ?>
		</div>
		<div class="content">
			<<?= $s['heading_style'] == 'p' ? 'p class="title"><strong' : $s['heading_style'] ?>>
				<?= $cta['heading'] ?>
			</<?= $s['heading_style'] == 'p' ? 'strong></p' : $s['heading_style'] ?>>
			<?= $cta['content'] ?>
			<?php if (count($cta['links'])): ?>
				<ul class="links">
					<?php foreach($cta['links'] as $link):?>
						<li>
							<a
								class="<?= $link['type'] == 'button' ? 'button': 'readmore' ?>"
								href="<?= esc_attr($link['link']['url']) ?>"
								target="<?= $link['link']['target'] ?>">
								<?= $link['link']['title'] ?>
							</a>
						</li>
					<?php endforeach;?>
				</ul>
			<?php endif;?>
		</div>
	</div>
</section>
