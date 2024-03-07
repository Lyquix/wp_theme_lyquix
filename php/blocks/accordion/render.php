<?php

/**
 * render.php - Render function for Lyquix accordion block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/accordion/render.php and modify it there

namespace lqx\blocks\accordion;

/**
 * Render function for Lyquix accordion block
 *
 * @param array $settings - block settings
 * @param array $content - block content
 *
 * anchor: The anchor of the accordion
 * class: Additional classes to add to the accordion
 * hash: A unique hash of the accordion
 * open_on_load: Controls if the accordion will open on load
 * open_multiple: Controls if multiple accordions can be open at once
 * browser_history: Controls if interacting with an accordion will add a history entry to the browser
 * heading_style: Sets the heading level for the accordion headings
 * auto_scroll: Sets in what screen sizes the accordion will auto scroll to the top of the open accordion
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
			'open_on_load' => \lqx\util\schema_str_req_n,
			'open_multiple' => \lqx\util\schema_str_req_y,
			'heading_style' => [
				'type' => 'string',
				'required' => true,
				'default' => 'h3',
				'allowed' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']
			],
			'browser_history' => \lqx\util\schema_str_req_n,
			'auto_scroll' => [
				'type' => 'array',
				'required' => true,
				'default' => [],
				'elems' => [
					'type' => 'string',
					'allowed' => ['xs', 'sm', 'md', 'lg', 'xl']
				]
			]
		]
	]);

	// If valid settings, use them, otherwise throw exception
	if ($s['isValid']) $s = $s['data'];
	else throw new \Exception('Invalid block settings: ' . var_export($s, true));

	// Get content and filter our invalid content
	$c = array_filter(array_map(function($item) {
		$v = \lqx\util\validate_data($item,[
			'type' => 'object',
			'required' => true,
			'keys' => [
				'heading' => \lqx\util\schema_str_req_notemp,
				'content' => \lqx\util\schema_str_req_notemp
			]
		]);
		return $v['isValid'] ? $v['data'] : null;
	}, $content));

	if (!empty($c)) : ?>
		<section
			id="<?= esc_attr($s['anchor']) ?>"
			class="lqx-block-accordion <?= esc_attr($s['class']) ?>">

			<div
				class="accordion"
				id="<?= esc_attr($s['hash']) ?>"
				data-open-on-load="<?= $s['open_on_load'] ?>"
				data-open-multiple="<?= $s['open_multiple'] ?>"
				data-browser-history="<?= $s['browser_history'] ?>"
				data-auto-scroll="<?= implode(',', $s['auto_scroll']) ?>">

				<?php foreach ($c as $idx => $item) : ?>
					<<?= $s['heading_style'] ?>>
						<button
							class="accordion-header"
							id="<?= $s['hash'] . '-header-' . $idx ?>"
							aria-expanded="<?= $idx == 0 && $s['open_on_load'] == 'y' ? 'true' : 'false' ?>"
							aria-controls="<?= $s['hash'] . '-panel-' . $idx ?>">
							<?= $item['heading'] ?>
						</button>
					</<?= $s['heading_style'] ?>>

					<section
						class="accordion-panel <?= $idx == 0 && $s['open_on_load'] == 'y' ? '' : 'closed' ?>"
						id="<?= $s['hash'] . '-panel-' . $idx ?>"
						role="region"
						aria-labelledby="<?= $s['hash'] . '-header-' . $idx ?>"
						aria-hidden="<?= $idx == 0 && $s['open_on_load'] == 'y' ? 'false' : 'true' ?>">
						<div>
							<?= $item['content'] ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>

		</section>
<?php endif;
}
