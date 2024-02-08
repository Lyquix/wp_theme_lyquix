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
	// Get and validate processed settings
	$s = (\lqx\util\validate_data($settings['processed'],[
		'type' => 'object',
		'required' => true,
		'keys' => [
			'anchor' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'class' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'hash' => [
				'type' => 'string',
				'required' => true,
				'default' => 'id-' . md5(json_encode([$settings, $content, random_int(1000, 9999)]))
			],
			'heading_style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'h3',
				'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6' ]
			]
		]
	]))['data'];

	// Filter out any content missing heading or content
	$c = \lqx\util\validate_data($content, [
		'type' => 'object',
		'required' => true,
		'default' => [],
		'elems' => [
			'type' => 'object',
			'keys' => [
				'heading' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
				'image' => [
					'type' => 'object',
					'keys' => LQX_VALIDATE_DATA_SCHEMA_IMAGE
				],
				'image_mobile' => [
					'type' => 'object',
					'keys' => LQX_VALIDATE_DATA_SCHEMA_IMAGE
				],
				'video' => [
					'type' => 'object',
					'keys' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'default' => 'url'
						],
						'url' => [
							'type' => 'string',
							'default' => ''
						],
						'upload' => [
							'type' => 'object',
							'keys' => LQX_VALIDATE_DATA_SCHEMA_VIDEO_UPLOAD
						]
					]
				],
				'links' => [
					'type' =>	'array',
					'required' => false,
					'default' => [],
					'elems' => [
						'type' => 'object',
						'required' => true,
						'keys' => [
							'type' => [
								'type' => 'string',
								'required' => true,
								'default' => 'button'
							],
							'link' => [
								'type' => 'object',
								'keys' => LQX_VALIDATE_DATA_SCHEMA_LINK
							]
						]
					]
				],
				'intro_text' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING
			]
		]
	])['data'];

	// Video attributes
	$video_attrs = '';
	if ($c['video']['type'] == 'url' && $c['video']['url']) {
		$video = \lqx\util\get_video_urls($c['video']['url']);
		if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
			'name' => str_replace('id-', 'banner-video-', $s['hash']),
			'type' => 'video',
			'url' => $c['video']['url'],
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
				<?php if($c['heading']): ?>
				<<?= $s['heading_style'] ?> class="title"><?= $c['heading'] ?></<?= $s['heading_style'] ?>>
				<?php endif; ?>
				<div class="intro"><?= $c['intro_text'] ?></div>
				<?php if (count($c['links'])) : ?>
					<ul class="links">
						<?php foreach ($c['links'] as $link) : ?>
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

			<?php if (is_array($c['image'])) : ?>
				<div class="image" <?= $video_attrs ?>>
					<?php if ($c['video']['type'] == 'upload' && $c['video']['upload']) : ?>
						<video
							autoplay loop muted playsinline
							poster="<?= $c['image']['sizes']['large'] ?>">
							<source
								src="<?= $c['video']['upload']['url'] ?>"
								type="<?= $c['video']['upload']['mime_type'] ?>">
						</video>
					<?php else: ?>
						<img
							src="<?= $c['image']['url'] ?>"
							alt="<?= htmlspecialchars($c['image']['alt']) ?>"
							class="<?= is_array($c['image_mobile']) ? 'xs-hide sm-hide' : '' ?>" />
						<?php if (is_array($c['image_mobile'])) : ?>
							<img
								src="<?= $c['image_mobile']['url'] ?>"
								alt="<?= htmlspecialchars($c['image_mobile']['alt']) ?>"
								class="hide xs-show sm-show" />
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>

	</section>
<?php
}
