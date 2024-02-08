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
//  If you need a custom renderer, copy this file to php/custom/blocks/hero/render.php and modify it there

namespace lqx\blocks\hero;

/**
 * Render function for Lyquix hero block
 *
 * @param array $settings - block settings
 * @param array $content - block content
 *
 * anchor: The anchor of the tabs
 * class: Additional classes to add to the tabs
 * hash: A unique hash of the tabs
 * show_image: Controls if the image will be shown
 * show_breadcrumbs: Controls if breadcrumbs will be shown
 * type: Sets the type of breadcrumbs to show
 * depth: Sets the depth of the breadcrumbs to show
 * show_current: Controls if the current page will be shown in the breadcrumbs
 */
function render($settings, $content) {
	// Processed settings
	$s = $settings['processed'];

	// Video attributes
	$video_attrs = '';
	if ($content['video']['type'] == 'url' && $content['video']['url']) {
		$video = \lqx\util\get_video_urls($content['video']['url']);
		if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
			'name' => str_replace('id-', 'hero-video-', $s['hash']),
			'type' => 'video',
			'url' => $content['video']['url'],
			'useHash' => false
		])));
	}

	// Breadcrumbs
	$breadcrumbs = '';
	if ($s['breadcrumbs']['show_breadcrumbs'] == 'y') {
		$breadcrumbs = '<div class="breadcrumbs">';
		if ($content['breadcrumbs_override'] !== '') {
			$breadcrumbs .= $content['breadcrumbs_override'];
		} else {
			$breadcrumbs .= implode(' &raquo; ', array_map(function ($b) {
				if ($b['url']) return sprintf('<a href="%s">%s</a>', $b['url'], $b['title']);
				else return $b['title'];
			}, \lqx\util\get_breadcrumbs(get_the_ID(), $s['breadcrumbs']['type'], $s['breadcrumbs']['depth'], $s['breadcrumbs']['show_current'])));
		}
		$breadcrumbs .= '</div>';
	}
?>
	<section
		id="<?= $s['anchor'] ?>"
		class="lqx-block-hero <?= $s['class'] ?>">

		<div
			class="hero"
			id="<?= $s['hash'] ?>"
			data-show-image="<?= $s['show_image'] ?>"
			data-breadcrumbs-show-breadcrumbs="<?= $s['breadcrumbs']['show_breadcrumbs'] ?>"
			data-breadcrumbs-type="<?= $s['breadcrumbs']['type'] ?>"
			data-breadcrumbs-depth="<?= $s['breadcrumbs']['depth'] ?>"
			data-breadcrumbs-show-current="<?= $s['breadcrumbs']['show_current'] ?>"
			>

			<div class="text">
				<?= $breadcrumbs ?>
				<h1 class="title"><?= $content['heading_override'] ? $content['heading_override'] : get_the_title() ?></h1>
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

			<?php if ($s['show_image'] == 'y') : ?>
				<div class="image" <?= $video_attrs ?>>
					<?php if ($content['video']['type'] == 'upload' && $content['video']['upload']) : ?>
						<video
							autoplay loop muted playsinline
							poster="<?= is_array($content['image_override']) ? $content['image_override']['url'] : get_the_post_thumbnail_url() ?>">
							<source
								src="<?= $content['video']['upload']['url'] ?>"
								type="<?= $content['video']['upload']['mime_type'] ?>">
						</video>
					<?php else: ?>
						<?php if (is_array($content['image_override'])) : ?>
							<img
								src="<?= $content['image_override']['url'] ?>"
								alt="<?= htmlspecialchars($content['image_override']['alt']) ?>"
								class="<?= is_array($content['image_mobile']) ? 'xs-hide sm-hide' : '' ?>" />
						<?php else :
							the_post_thumbnail('post-thumbnail', ['class' => $content['image_mobile'] !== false ? 'xs-hide sm-hide' : '']);
						endif; ?>
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
