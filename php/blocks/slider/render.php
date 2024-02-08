<?php

/**
 * render.php - Render function for Lyquix hero block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/slider/render.php and modify it there

namespace lqx\blocks\slider;

/**
 * Render function for Lyquix Slider block
 *
 * @param array $content - block content
 */
function render($settings, $content) {
	// Get and validate processed settings
	$s = (\lqx\util\validate_data($settings['processed'], [
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
				'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p']
			],
			'autoplay' => [
				'type' => 'string',
				'required' => true,
				'default' => 'y',
				'allowed' => ['y', 'n']
			],
			'autoplay_delay' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'swiper_options_override' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
			'loop' => [
				'type' => 'string',
				'required' => true,
				'default' => 'y',
				'allowed' => ['y', 'n']
			],
			'pagination' => [
				'type' => 'string',
				'required' => true,
				'default' => 'y',
				'allowed' => ['y', 'n']
			],
			'navigation' => [
				'type' => 'string',
				'required' => true,
				'default' => 'y',
				'allowed' => ['y', 'n']
			]
		]
	]))['data'];

	// Filter out any content missing heading or content
	$c = \lqx\util\validate_data($content, [
		'type' => 'array',
		'required' => true,
		'default' => [],
		'elems' => [
			'type' => 'object',
			'keys' => [
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
				'image_link' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
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
				'heading' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
				'body' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
				'thumbnail' => [
					'type' => 'object',
					'keys' => LQX_VALIDATE_DATA_SCHEMA_IMAGE
				],
				'teaser_text' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING
			]
		]
	])['data'];
?>
	<section
		id="<?= $s['anchor'] ?>"
		class="lqx-block-slider <?= $s['class'] ?>">

		<div
			class="slider"
			id="<?= $s['hash'] ?>"
			data-autoplay="<?= $s['autoplay'] ?>"
			data-autoplay-delay="<?= $s['autoplay_delay'] ?>"
			data-swiper-options-override="<?= htmlspecialchars($s['swiper_options_override']) ?>"
			data-loop="<?= $s['loop'] ?>"
			data-navigation="<?= $s['navigation'] ?>"
			data-pagination="<?= $s['pagination'] ?>">

			<div class="swiper">

				<ul class="swiper-wrapper">

					<?php foreach ($c as $idx => $item) : ?>
						<div class="swiper-slide">
							<?php
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
										href="<?= $item['image_link']['url'] ?>"
										title="<?= htmlspecialchars($item['image_link']['title']) ?>"
										target="<?= $item['image_link']['target'] ?>">
								<?php endif; ?>

									<?php if ($item['video']['type'] == 'upload' && $item['video']['upload']) : ?>
										<video
											autoplay loop muted playsinline
											poster="<?= $item['image']['sizes']['large'] ?>">
											<source
												src="<?= $item['video']['upload']['url'] ?>"
												type="<?= $item['video']['upload']['mime_type'] ?>">
										</video>
									<?php else: ?>
										<?php if (is_array($item['image'])) : ?>
											<img
												src="<?= $item['image']['url'] ?>"
												alt="<?= htmlspecialchars($item['image']['alt']) ?>"
												class="<?= is_array($item['image_mobile']) ? 'xs-hide sm-hide' : '' ?>" />
										<?php endif;
										if (is_array($item['image_mobile'])) : ?>
											<img
												src="<?= $item['image_mobile']['url'] ?>"
												alt="<?= htmlspecialchars($item['image_mobile']['alt']) ?>"
												class="hide xs-show sm-show" />
										<?php endif; ?>
									<?php endif; ?>

								<?php if ($item['image_link'] && $item['image_link']['url'] && $item['video']['type'] != 'url') : ?>
									</a>
								<?php endif; ?>

							</div>

							<div class="text">
								<?php if ($item['heading']) : ?>
									<<?= $s['heading_style'] ?>>
										<?= $item['heading'] ?>
									</<?= $s['heading_style'] ?>>
								<?php endif; ?>
								<?= $item['body'] ?>
							</div>

							<?php if (!empty($item['links'])) : ?>
								<ul class="links">
									<?php foreach ($item['links'] as $link) : ?>
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

					<?php endforeach; ?>

				</ul>

				<?php if ($s['navigation'] == 'y') : ?>
					<div class="swiper-button-prev"></div>
					<div class="swiper-button-next"></div>
				<?php endif; ?>

				<?php if ($s['pagination'] == 'y') : ?>
					<div class="swiper-pagination"></div>
				<?php endif; ?>

			</div>
		</div>
	</section>
<?php
}
