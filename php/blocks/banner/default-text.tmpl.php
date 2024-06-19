<?php

/**
 * default-text.tmpl.php - Default template for the Lyquix Banner block, text sub-template
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
//  Instead, copy it to /php/custom/blocks/banner/default-text.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/banner/{preset}-text.tmpl.php

?>
<div class="text">
	<?php if ($c['heading']): ?>
	<<?= $s['heading_style'] ?> class="title"><?= $c['heading'] ?></<?= $s['heading_style'] ?>>
	<?php endif; ?>
	<div class="intro"><?= $c['intro_text'] ?></div>
	<?php if (count($c['links'])) : ?>
		<ul class="links">
			<?php foreach ($c['links'] as $link) : ?>
				<li>
					<a
						class="<?= $link['type'] == 'button' ? 'button' : 'readmore' ?>"
						href="<?= esc_attr($link['link']['url']) ?>"
						target="<?= $link['link']['target'] ?>">
						<?= $link['link']['title'] ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
