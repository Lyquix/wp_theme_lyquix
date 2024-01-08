<?php

/**
 * render.php - Render function for Lyquix cards block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/cards/render.php and modify it there

namespace lqx\blocks\cards;

/**
 * Render function for Lyquix cards block
 *
 * @param array $settings - block settings
 * @param array $content - block content
 *
 * anchor: The anchor of the cards
 * class: Additional classes to add to the cards
 * hash: A unique hash of the cards
 */
function render($settings, $content) {
	file_put_contents(__DIR__ . '/render.log', json_encode([$settings, $content], JSON_PRETTY_PRINT));

	// Get and validate processed settings
	$s = (\lqx\util\validate_data($settings['processed'], [
		'anchor' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
		'class' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
		'hash' => [
			'type' => 'string',
			'required' => true,
			'default' => 'id-' . md5(json_encode([$settings, $content, random_int(1000, 9999)]))
		],
		'slider' => [
			'type' => 'string',
			'required' => true,
			'default' => 'n'
		],
		'swiper_options_override' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
		'heading_style' => [
			'type' => 'string',
			'required' => true,
			'default' => 'h3'
		],
		'heading_clickable' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y'
		],
		'image_clickable' => [
			'type' => 'string',
			'required' => true,
			'default' => 'y'
		],
		'responsive_rules' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING
	]))['data'];

	// Filter out any content missing heading or content
	if (is_array($content)) {
		$content = array_map(function ($item) {
			return \lqx\util\validate_data($item, [
				'heading' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
				'subheading' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
				'image' => [
					'type' => 'array',
					'required' => false,
					'schema' => LQX_VALIDATE_DATA_SCHEMA_IMAGE
				],
				'icon_image' => [
					'type' => 'array',
					'required' => false,
					'schema' => LQX_VALIDATE_DATA_SCHEMA_IMAGE
				],
				'video' => [
					'type' => 'array',
					'required' => false,
					'schema' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'default' => 'url'
						],
						'url' => [
							'type' => 'string',
							'required' => false,
							'default' => ''
						],
						'upload' => LQX_VALIDATE_DATA_SCHEMA_VIDEO_UPLOAD
					]
				],
				'body' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
				'links' => [
					'type' =>	'array',
					'required' => false,
					'schema' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'default' => 'button'
						],
						'link' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_LINK
					]

				],
				'labels' => [
					'type' =>	'array',
					'required' => false,
					'schema' => [
						'label' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING,
						'value' => LQX_VALIDATE_DATA_SCHEMA_REQUIRED_STRING
					]
				]
			])['data'];
		}, $content);
	}
	else $content = [];

	if (!empty($content)) : ?>
		<section
			id="<?= $s['anchor'] ?>"
			class="lqx-block-cards <?= $s['class'] ?>">

			<div
				class="cards"
				id="<?= $s['hash'] ?>"
				data-slider="<?= $s['slider'] ?>"
				data-swiper-options-override="<?= htmlspecialchars($s['swiper_options_override']) ?>"
				data-heading-style="<?= $s['heading_style'] ?>"
				data-subheading-style="<?= $s['subheading_style'] ?>"
				data-heading-clickable="<?= $s['heading_clickable'] ?>"
				data-image-clickable="<?= $s['image_clickable'] ?>"
				data-responsive-rules="<?= htmlspecialchars(json_encode($s['responsive_rules'])) ?>">

				<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>

					<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'cards-wrapper' ?>">

						<?php foreach ($content as $idx => $item) :
							// Video attributes
							$video_attrs = '';
							if ($item['video']['type'] == 'url' && $item['video']['url']) {
								$video = \lqx\util\get_video_urls($item['video']['url']);
								if ($video['url']) $video_attrs = 'data-lyqbox data-lyqbox-type="video" data-lyqbox-url="' . $video['url'] . '"';
							}

							// First link URL
							$first_link = '';
							if(!empty($item['links'])) {
								$first_link = $item['links'][0]['link']['url'];
							}
						?>

							<li
								class="<?= $s['slider'] == 'y' ? 'swiper-slide' : 'card' ?>"
								id="<?= $s['hash'] . '-' . $idx ?>">
								<?php if (!empty($item['labels'])) : ?>
									<ul class="labels">
										<?php foreach ($item['labels'] as $label) : ?>
											<li
												data-label-value="<?= $label['label']['value'] ? $label['label']['value'] : \lqx\util\slugify($label['label']['label']) ?>">
												<?= $label['label']['label'] ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>

								<?php if ($item['image']) : ?>
									<div class="image" <?= $video_attrs ?>>
										<?php if ($item['video']['type'] == 'upload' && $item['video']['upload']) : ?>
											<video
												autoplay loop muted playsinline
												poster="<?= $item['image']['sizes']['large'] ?>">
												<source src="<?= $item['video']['upload']['url'] ?>" type="<?= $item['video']['upload']['mime_type'] ?>">
											</video>
										<?php else: ?>
											<?= $s['image_clickable'] == 'y' && $first_link ? '<a href="' . $first_link . '">' : '' ?>
											<img
												src="<?= $item['image']['sizes']['large'] ?>"
												alt="<?= htmlspecialchars($item['image']['alt']) ?>">
											<?= $s['image_clickable'] == 'y' && $first_link ? '</a>' : '' ?>
										<?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if ($item['icon_image']) : ?>
									<div class="icon">
										<img
											src="<?= $item['icon_image']['sizes']['large'] ?>"
											alt="<?= htmlspecialchars($item['icon_image']['alt']) ?>">
									</div>
								<?php endif; ?>

								<div class="text">
									<?php if ($item['heading']) : ?>
										<<?= $s['heading_style'] ?>>
											<?= $s['heading_clickable'] == 'y' && $first_link ? sprintf('<a href="%s">%s</a>', $first_link, $item['heading']) : $item['heading'] ?>
										</<?= $s['heading_style'] ?>>
									<?php endif; ?>
									<?php if ($item['subheading']) : ?>
										<<?= $s['subheading_style'] == 'p' ? 'p class="subtitle"><strong' : $s['subheading_style'] ?>>
											<?= $item['subheading'] ?>
										</<?= $s['subheading_style'] == 'p' ? 'strong></p' : $s['subheading_style'] ?>>
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

							</li>

						<?php endforeach; ?>

					</ul>

					<?php if ($s['slider'] == 'y') : ?>
						<div class="controls">
							<div class="swiper-button-prev"></div>
							<div class="swiper-button-next"></div>
						</div>
					<?php endif; ?>

				<?= $s['slider'] == 'y' ? '</div>' : '' ?>

			</div>

		</section>
	<?php endif;
}
