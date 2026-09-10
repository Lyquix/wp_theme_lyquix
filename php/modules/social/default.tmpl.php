<?php

/**
 * default.tmpl.php - Lyquix social icons module render functions
 *
 * @version     3.4.0
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
//  If you need a custom renderer, copy this file to php/custom/modules/social/default.tmpl.php and modify it there

?>
<div class="lqx-module-social">
	<ul class="icons-list <?= esc_attr($s['style']) ?>" style="<?= esc_attr(\lqx\modules\social\get_inline_style($s)) ?>">
		<?php foreach ($s['links'] as $l) :
			$platform = \lqx\modules\social\get_platform($l['url']);
			if ($platform['code'] !== 'unknown'): ?>
			<li>
				<a class="link-<?= esc_attr($platform['code']) ?>"
					href="<?= esc_attr($l['url']) ?>"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="Follow us on <?= esc_attr($platform['name']) ?>">
					<svg aria-hidden="true" class="icon" width="48" height="48">
						<use href="<?= esc_url(get_template_directory_uri()) ?>/images/social/sprites.svg#<?= esc_attr($platform['code']) ?>">
					</svg>
				</a>
			</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
