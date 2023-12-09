<?php

/**
 * render.php - Render function for Lyquix tabs block
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
//  If you need a custom renderer, copy this file to php/custom/blocks/module/tabs/render.php and modify it there

namespace lqx\blocks\tabs;

/**
 * Render function for Lyquix tabs block
 *
 * @param array $settings - block settings
 * @param array $content - block content
 *
 * anchor: The anchor of the tabs
 * class: Additional classes to add to the tabs
 * hash: A unique hash of the tabs
 * heading_style: Sets the heading level for the tabs headings
 * browser_history: Controls if interacting with a tab will add a history entry to the browser
 * convert_to_accordion: Controls if tabs will be converted to an accordion in mobile
 * auto_scroll: Sets in what screen sizes the tabs will auto scroll to the top of the open tab
 */
function render($settings, $content) {
	// Get the processed settings
	$s = $settings['processed'];

	if (!empty($content)) : ?>
		<section
			id="<?= $s['anchor'] ?>"
			class="lqx-block-tabs  <?= $s['class'] ?>">
			<div
				class="tabs"
				id="<?= $s['hash'] ?>"
				data-browser-history="<?= $s['browser_history'] ?>"
				data-convert-to-accordion="<?= implode(',', $s['convert_to_accordion']) ?>"
				data-auto-scroll="<?= implode(',', $s['auto_scroll']) ?>">
				<ul
					class="tabs-list"
					role="tablist"
					aria-hidden="false">
					<?php foreach ($content as $idx => $item) : ?>
						<li>
							<button
								class="tab"
								id="<?= $s['hash'] . '-tab-' . $idx ?>"
								aria-controls="<?= $s['hash'] . '-panel-' . $idx ?>"
								aria-selected="<?= $idx == 0 ? 'true' : 'false' ?>"
								role="tab"
								tabindex="<?= $idx == 0 ? '' : '-1' ?>">
								<?= $item['label'] ?>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php foreach ($content as $idx => $item) : ?>
					<section
						class="tab-panel"
						id="<?= $s['hash'] . '-panel-' . $idx ?>"
						aria-labelledby="<?= $s['hash'] . '-tab-' . $idx ?>"
						aria-hidden="<?= $idx == 0 ? 'false' : 'true' ?>"
						role="tabpanel"
						tabindex="0">
						<?php if($s['convert_to_accordion']) : ?>
							<button
								class="accordion-header"
								id="<?= $s['hash'] . '-header-' . $idx ?>"
								aria-expanded="<?= $idx == 0 ? 'true' : 'false' ?>"
								aria-controls="<?= $s['hash'] . '-panel-' . $idx ?>"
								aria-hidden="true">
								<?= $item['label'] ?>
							</button>
							<div
								class="tab-content"
								id="<?= $s['hash'] . '-content-' . $idx ?>"
								aria-hidden="<?= $idx == 0 ? 'false' : 'true' ?>">
								<?php endif;
								if ($item['heading']) {
									echo '<' . $s['heading_style'] . '>' . $item['heading'] . '</' . $s['heading_style'] . '>' . "\n";
								}
								echo $item['content'] . "\n";
								if($s['convert_to_accordion']) : ?>
							</div>
							<?php endif; ?>
					</section>
				<?php endforeach; ?>
			</div>
		</section>
<?php endif;
}
