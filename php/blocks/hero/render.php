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
			'show_image' => \lqx\util\schema_str_req_y,
			'breadcrumbs' => [
				'type' => 'object',
				'required' => true,
				'keys' => [
					'show_breadcrumbs' => \lqx\util\schema_str_req_n,
					'type' => [
						'type' => 'string',
						'required' => true,
						'default' => 'parent',
						'allowed' => ['parent', 'category', 'post-type', 'post-type-category']
					],
					'depth' => [
						'type' => 'integer',
						'required' => true,
						'default' => 3,
						'range' => [1, 5]
					],
					'show_current' => \lqx\util\schema_str_req_n
				]
			]
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid block settings: ' . var_export($s, true));

	// Filter out any content missing heading or content
	$c = \lqx\util\validate_data($content, [
		'type' => 'object',
		'required' => true,
		'keys' => [
			'breadcrumbs_override' => \lqx\util\schema_str_req_emp,
			'heading_override' => \lqx\util\schema_str_req_emp,
			'image_override' => [
				'type' => 'object',
				'default' => [],
				'keys' => \lqx\util\schema_data_image
			],
			'image_mobile' => [
				'type' => 'object',
				'default' => [],
				'keys' => \lqx\util\schema_data_image
			],
			'video' => [
				'type' => 'object',
				'keys' => [
					'type' => [
						'type' => 'string',
						'required' => true,
						'default' => 'url'
					],
					'url' => \lqx\util\schema_str_req_emp,
					'upload' => [
						'type' => 'object',
						'default' => [],
						'keys' => \lqx\util\schema_data_video
					]
				]
			],
			'links' => [
				'type' =>	'array',
				'default' => [],
				'elems' => [
					'type' => 'object',
					'keys' => [
						'type' => [
							'type' => 'string',
							'required' => true,
							'default' => 'button',
							'allowed' => ['button', 'link']
						],
						'link' => [
							'type' => 'object',
							'required' => true,
							'keys' => \lqx\util\schema_data_link
						]
					]
				]
			],
			'intro_text' => \lqx\util\schema_str_req_emp
		]
	]);

	// If valid content, use it, otherwise return
	if ($c['isValid']) $c = $c['data'];
	else return;

	// Video attributes
	$video_attrs = '';
	if ($c['video']['type'] == 'url' && $c['video']['url']) {
		$video = \lqx\util\get_video_urls($c['video']['url']);
		if ($video['url']) $video_attrs = sprintf('data-lyqbox="%s"', htmlentities(json_encode([
			'name' => str_replace('id-', 'hero-video-', $s['hash']),
			'type' => 'video',
			'url' => $c['video']['url'],
			'useHash' => false
		])));
	}

	// Breadcrumbs
	$breadcrumbs = '';
	if ($s['breadcrumbs']['show_breadcrumbs'] == 'y') {
		$breadcrumbs = '<div class="breadcrumbs">';
		if ($c['breadcrumbs_override'] !== '') {
			$breadcrumbs .= $c['breadcrumbs_override'];
		} else {
			$breadcrumbs .= implode(' &raquo; ', array_map(function ($b) {
				if ($b['url']) return sprintf('<a href="%s">%s</a>', esc_attr($b['url']), $b['title']);
				else return $b['title'];
			}, \lqx\util\get_breadcrumbs(get_the_ID(), $s['breadcrumbs']['type'], $s['breadcrumbs']['depth'], $s['breadcrumbs']['show_current'])));
		}
		$breadcrumbs .= '</div>';
	}
?>
	<section
		id="<?= esc_attr($s['anchor']) ?>"
		class="lqx-block-hero <?= esc_attr($s['class']) ?>">

		<div
			class="hero"
			id="<?= esc_attr($s['hash']) ?>"
			data-show-image="<?= $s['show_image'] ?>"
			data-breadcrumbs-show-breadcrumbs="<?= $s['breadcrumbs']['show_breadcrumbs'] ?>"
			data-breadcrumbs-type="<?= $s['breadcrumbs']['type'] ?>"
			data-breadcrumbs-depth="<?= $s['breadcrumbs']['depth'] ?>"
			data-breadcrumbs-show-current="<?= $s['breadcrumbs']['show_current'] ?>"
			>

			<div class="text">
				<?= $breadcrumbs ?>
				<h1 class="title"><?= $c['heading_override'] ? $c['heading_override'] : get_the_title() ?></h1>
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

			<?php if ($s['show_image'] == 'y') : ?>
				<div class="image" <?= $video_attrs ?>>
					<?php if ($c['video']['type'] == 'upload' && $c['video']['upload']) : ?>
						<video
							autoplay loop muted playsinline
							poster="<?= array_key_exists('url', $c['image_override']) ? $c['image_override']['url'] : get_the_post_thumbnail_url() ?>">
							<source
								src="<?= esc_attr($c['video']['upload']['url']) ?>"
								type="<?= $c['video']['upload']['mime_type'] ?>">
						</video>
					<?php else: ?>
						<?php if (array_key_exists('url', $c['image_override'])) : ?>
							<img
								src="<?= esc_attr($c['image_override']['url']) ?>"
								alt="<?= esc_attr($c['image_override']['alt']) ?>"
								class="<?= array_key_exists('url', $c['image_mobile']) ? 'xs:hidden sm:hidden' : '' ?>" />
						<?php else :
							the_post_thumbnail('post-thumbnail', ['class' => $c['image_mobile'] !== false ? 'xs:hidden sm:hidden' : '']);
						endif; ?>
						<?php if (array_key_exists('url', $c['image_mobile'])) : ?>
							<img
								src="<?= esc_attr($c['image_mobile']['url']) ?>"
								alt="<?= esc_attr($c['image_mobile']['alt']) ?>"
								class="md:hidden lg:hidden xl:hidden" />
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>

	</section>
<?php
}
