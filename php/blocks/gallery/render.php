<?php

/**
 * render.php - Lyquix Socials module render functions
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
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
//  If you need a custom renderer, copy this file to php/custom/blocks/gallery/render.php and modify it there

namespace lqx\blocks\gallery;

/**
 * Render gallery
 * @param  array $settings - gallery settings
 * 	slider - boolean, whether to use a slider
 *  swiper_options_override - string, a JSON object to override Swiper options
 * 	browser_history - boolean, whether to use browser history
 */
function render($settings, $content) {
	// Get and validate processed settings
	$s = \lqx\util\validate_data($settings['processed'], [
		'type' => 'object',
		'required' => true,
		'keys' => [
			'anchor' => \lqx\util\schema_str_req_emp,
			'class' => \lqx\util\schema_str_req_emp,
			'hash' => [
				'type' => 'string',
				'required' => true,
				'default' => 'id-' . md5(json_encode([$settings, $content, random_int(1000, 9999)]))
			],
			'slider' => \lqx\util\schema_str_req_n,
			'swiper_options_override' => \lqx\util\schema_str_req_emp,
			'heading_style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'h3',
				'allowed' => ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6']
			],
			'browser_history' => \lqx\util\schema_str_req_n
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid block settings: ' . var_export($s, true));

	// Get the top level items first
	$c = \lqx\util\validate_data($content, [
		'type' => 'object',
		'required' => true,
		'keys' => [
			'lightbox_slug' => \lqx\util\schema_str_req_emp,
			'slides' => [
				'type' =>	'array',
				'required' => true,
				'default' => []
			]
		]
	])['data'];

	// Get content and filter our invalid content
	$c['slides'] = array_filter(array_map(function($item) {
		$v = \lqx\util\validate_data($item, [
			'type' => 'object',
			'keys' => [
				'title' => \lqx\util\schema_str_req_emp,
				'slug' => \lqx\util\schema_str_req_emp,
				'image' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_image
				],
				'video' => \lqx\util\schema_str_req_emp,
				'caption' => \lqx\util\schema_str_req_emp,
				'thumbnail' => [
					'type' => 'object',
					'default' => [],
					'keys' => \lqx\util\schema_data_image
				],
				'teaser' => \lqx\util\schema_str_req_emp
			]
		]);

		// Skip if data is not valid
		if (!$v['isValid']) return null;

		// Skip slide if both image and video are missing
		if (!$v['data']['image']['url'] && !$v['data']['video']) return null;

		return $v['data'];
	}, $c['slides']));

	// Heading style
	$heading_tag_open  = $s['heading_style'] == 'p' ? '<p class="title"><strong>' : '<' . $s['heading_style'] . '>';
	$heading_tag_close  = $s['heading_style'] == 'p' ? '</strong></p>' : '</' . $s['heading_style'] . '>';
	?>
	<section
		id="<?= $s['anchor'] ?>"
		class="lqx-block-gallery <?= $s['class'] ?>">

		<div
			class="gallery <?= $s['slider'] == 'y' ? 'slider' : '' ?>"
			id="<?= $s['hash'] ?>"
			data-slider="<?= $s['slider'] ?>"
			data-swiper-options-override="<?= htmlspecialchars($s['swiper_options_override']) ?>"
			data-heading-style="<?= $s['heading_style'] ?>"
			data-browser-history="<?= $s['browser_history'] ?>">

			<?= $s['slider'] == 'y' ? '<div class="swiper">' : '' ?>
				<ul class="<?= $s['slider'] == 'y' ? 'swiper-wrapper' : 'gallery-wrapper' ?>">
					<?php foreach ($c['slides'] as $idx => $item) : ?>
						<?php if ($item['video']) $video = \lqx\util\get_video_urls($item['video']); ?>
						<li
							class="<?= $s['slider'] == 'y' ? 'swiper-slide' : 'gallery-slide' ?>"
							id="<?= $item['slug'] ? $item['slug'] : $s['hash'] . '-' . $idx ?>"
							data-lyqbox="<?= htmlentities(json_encode([
								'name' => $c['lightbox_slug'],
								'type' => $video['url'] ? 'video' : 'image',
								'url' => $video['url'] ? $video['url'] : $item['image']['sizes']['large'],
								'title' => $item['title'],
								'caption' => $item['caption']
							])) ?>">
							<img
								src="<?= $item['thumbnail']['sizes']['large'] ?>"
								alt="<?= htmlspecialchars($item['image']['alt']) ?>">
							<?= $heading_tag_open . $item['title'] . $heading_tag_close ?>
							<?= '<p>' . $item['teaser'] . '</p>' ?>
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
	<?php
}
