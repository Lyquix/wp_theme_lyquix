<?php

/**
 * default-panel.tmpl.php - Default template for the Lyquix Tabs block, panel sub-template
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
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
//  Instead, copy it to /php/custom/blocks/tabs/default-panel.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/tabs/{preset}-panel.tmpl.php

?>
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
