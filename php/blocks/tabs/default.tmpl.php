<?php

/**
 * default.tmpl.php - Default template for the Lyquix Tabs block
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
//  Instead, copy it to /php/custom/blocks/tabs/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/tabs/{preset}.tmpl.php

?>
<section
	id="<?= esc_attr($s['anchor']) ?>"
	class="lqx-block-tabs  <?= esc_attr($s['class']) ?>">

	<div
		class="tabs"
		id="<?= esc_attr($s['hash']) ?>"
		data-browser-history="<?= $s['browser_history'] ?>"
		data-convert-to-accordion="<?= implode(',', $s['convert_to_accordion']) ?>"
		data-auto-scroll="<?= implode(',', $s['auto_scroll']) ?>">

		<ul
			class="tabs-list"
			role="tablist"
			aria-hidden="false">
			<?php foreach ($c as $idx => $item) : ?>
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

		<?php foreach ($c as $idx => $item) : ?>
			<section
				class="tab-panel"
				id="<?= $s['hash'] . '-panel-' . $idx ?>"
				aria-labelledby="<?= $s['hash'] . '-tab-' . $idx ?>"
				aria-hidden="<?= $idx == 0 ? 'false' : 'true' ?>"
				role="tabpanel"
				tabindex="0">

				<?php if (count($s['convert_to_accordion'])) : ?>
					<button
						class="accordion-header"
						id="<?= $s['hash'] . '-header-' . $idx ?>"
						aria-expanded="<?= $idx == 0 ? 'true' : 'false' ?>"
						aria-controls="<?= $s['hash'] . '-panel-' . $idx ?>"
						aria-hidden="true">
						<?= $item['label'] ?>
					</button>
				<?php endif; ?>

				<div
					class="tab-content"
					id="<?= $s['hash'] . '-content-' . $idx ?>"
					<?= $s['convert_to_tabs'] == 'y' ? 'aria-hidden="' . ($idx == 0 ? 'false' : 'true') . '"' : '' ?>>
					<?= $item['heading'] ? sprintf('<%s>%s</%s>', $s['heading_style'], $item['heading'], $s['heading_style']) : '' ?>
					<?= $item['content'] ?>
				</div>

			</section>
		<?php endforeach; ?>

	</div>

</section>
