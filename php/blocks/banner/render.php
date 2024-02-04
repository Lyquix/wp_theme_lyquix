<?php

/**
 * render.php - Render function for Lyquix hero block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/banner/render.php and modify it there

namespace lqx\blocks\banner;

/**
 * Render function for Lyquix banner block
 *
 * @param array $content - block content
 */
function render($settings, $content) {
	// Processed settings
	$s = $settings['processed'];

	// Video attributes
	$video_attrs = '';
	if ($content['video']['type'] == 'url' && $content['video']['url']) {
		$video = \lqx\util\get_video_urls($content['video']['url']);
		if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
			'name' => str_replace('id-', 'banner-video-', $s['hash']),
			'type' => 'video',
			'url' => $content['video']['url'],
			'title' => $content['heading'] ? $content['heading'] : 'Banner Video',
			'useHash' => false
		])));
	}

	?>
	<section
		id="<?= $s['anchor'] ?>"
		class="lqx-block-banner <?= $s['class'] ?>">

		<div
			class="banner"
			id="<?= $s['hash'] ?>"
			data-heading-style="<?= $s['heading_style'] ?>">

			<div class="text">
				<?php if($content['heading']): ?>
				<<?= $s['heading_style'] ?> class="title"><?= $content['heading'] ?></<?= $s['heading_style'] ?>>
				<?php endif; ?>
				<div class="intro"><?= $content['intro_text'] ?></div>
				<?php if (count($content['links'])) : ?>
					<ul class="links">
						<?php foreach ($content['links'] as $link) : ?>
							<li>
								<a
									class="<?= $link['type'] == 'button' ? 'button' : 'readmore' ?>"
									href="<?= $link['link']['url'] ?>"
									target="<?= $link['link']['target'] ?>">
									<?= $link['link']['title'] ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<?php if (is_array($content['image'])) : ?>
				<div class="image" <?= $video_attrs ?>>
					<?php if ($content['video']['type'] == 'upload' && $content['video']['upload']) : ?>
						<video
							autoplay loop muted playsinline
							poster="<?= $content['image']['sizes']['large'] ?>">
							<source
								src="<?= $content['video']['upload']['url'] ?>"
								type="<?= $content['video']['upload']['mime_type'] ?>">
						</video>
					<?php else: ?>
						<img
							src="<?= $content['image']['url'] ?>"
							alt="<?= htmlspecialchars($content['image']['alt']) ?>"
							class="<?= is_array($content['image_mobile']) ? 'xs-hide sm-hide' : '' ?>" />
						<?php if (is_array($content['image_mobile'])) : ?>
							<img
								src="<?= $content['image_mobile']['url'] ?>"
								alt="<?= htmlspecialchars($content['image_mobile']['alt']) ?>"
								class="hide xs-show sm-show" />
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>

	</section>
<?php
}
