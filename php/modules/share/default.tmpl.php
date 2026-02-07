<?php

/**
 * default.tmpl.php - Lyquix social sharing module
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
//  If you need a custom renderer, copy this file to php/custom/modules/social/default.tmpl.php and modify it there

?>
<div class="lqx-module-share">
	<ul class="share-list <?= $s['style'] ?>" style="<?= \lqx\modules\share\get_inline_style($s) ?>">
		<?php foreach ($s['platforms'] as $p) :
			$share_link = \lqx\modules\share\get_share_link($p['platform_name']['value']);
			if ($share_link) : ?>
			<li>
				<a class="link-<?= $p['platform_name']['value'] ?>" href="#"
					data-platform="<?= $p['platform_name']['value'] ?>"
					data-link="<?= $share_link ?>"
					rel="noopener noreferrer"
					aria-label="Share on <?= $p['platform_name']['label'] ?>">
					<svg aria-hidden="true" class="icon" width="48" height="48">
						<use href="<?= get_template_directory_uri(); ?>/images/social/sprites.svg#<?= $p['platform_name']['value'] ?>">
					</svg>
				</a>
			</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
	<script>
		(function () {
			let shareButtons = document.querySelectorAll('.lqx-module-share a');
			shareButtons.forEach(function(button) {
				button.addEventListener('click', function(e) {
					e.preventDefault();
					let platform = button.getAttribute('data-platform');
					let shareURL = button.getAttribute('data-link');
					if (platform === 'print') window.print();
					else window.open(shareURL, '_blank', 'noopener, noreferrer, width=600, height=400');
				});
			});
		})();
	</script>
</div>
