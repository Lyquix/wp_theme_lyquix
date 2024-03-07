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

namespace lqx\modules\social;

/**
 * Render social icons
 * @param  array $settings - social icons settings
 * 	links - array of links
 * 		url - URL of social profile
 * 	style - style of icons: square, rounded, circle
 * 	background_color
 * 	icon_color
 * 	hover_color
 */
function render_social_icons($settings = null) {
	// Get settings
	if ($settings == null) $settings = get_field('social_icons_module', 'option');

	// Validate settings
	$s = \lqx\util\validate_data($settings, [
		'type' => 'object',
		'required' => true,
		'keys' => [
			'links' => [
				'type' => 'array',
				'required' => true,
				'default' => [],
				'keys' => [
					'url' => \lqx\util\schema_str_req_notemp
				]
			],
			'style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'square',
				'allowed' => ['square', 'circle', 'rounded']
			],
			'background_color' => \lqx\util\schema_hex_color,
			'icon_color' => \lqx\util\schema_hex_color,
			'hover_icon_color' => \lqx\util\schema_hex_color,
			'hover_background_color' => \lqx\util\schema_hex_color
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid module settings: ' . var_export($s, true));

	// Check if there are any social links configured
	if (!count($s['links'])) return;
?>
	<div class="lqx-social-icons">
		<ul class="icons-list <?= $s['style'] ?>" style="<?= get_inline_style($s) ?>">
			<?php foreach ($s['links'] as $l) :
				$platform = get_platform($l['url']);
				if ($platform['code'] !== 'unknown'): ?>
				<li>
					<a class="link-<?= $platform['code'] ?>"
						href="<?= esc_attr($l['url']) ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="Follow us on <?= $platform['name'] ?>">
						<svg aria-hidden="true" class="icon" width="48" height="48">
							<use href="<?= get_template_directory_uri(); ?>/images/social/sprites.svg#<?= $platform['code'] ?>">
						</svg>
					</a>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>
<?php
}

/**
 * Render social share
 * @param  array $settings - social share settings
 * 	platforms - array of sharing platforms
 * 		platform_name - array of platform name and code
 * 			value - platform code
 * 			label - platform name
 * 	style - style of icons: square, rounded, circle
 * 	background_color
 * 	icon_color
 * 	hover_color
 */
function render_social_share($settings = null) {
	// Get settings
	if ($settings == null) $settings = get_field('social_share_module', 'option');

	// Validate settings
	$s = \lqx\util\validate_data($settings, [
		'type' => 'object',
		'required' => true,
		'keys' => [
			'platforms' => [
				'type' => 'array',
				'required' => true,
				'keys' => [
					'platform_name' => [
						'type' => 'object',
						'required' => true,
						'default' => [],
						'keys' => [
							'value' => \lqx\util\schema_str_req_notemp,
							'label' => \lqx\util\schema_str_req_notemp
						]
					]
				]
			],
			'style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'square',
				'allowed' => ['square', 'circle', 'rounded']
			],
			'icon_color' => \lqx\util\schema_hex_color,
			'background_color' => \lqx\util\schema_hex_color,
			'hover_icon_color' => \lqx\util\schema_hex_color,
			'hover_background_color' => \lqx\util\schema_hex_color
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid module settings: ' . var_export($s, true));

	// Check if there are any platforms configured
	if (empty($s['platforms'])) return;
?>
	<div class="lqx-social-share">
		<ul class="share-list <?= $s['style'] ?>" style="<?= get_inline_style($s) ?>">
			<?php foreach ($s['platforms'] as $p) :
				$share_link = get_share_link($p['platform_name']['value']);
				if ($share_link) : ?>
				<li>
					<a class="link-<?= $p['platform_name']['value'] ?>"
						href="<?= $share_link ?>"
						target="_blank"
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
	</div>
<?php
};
