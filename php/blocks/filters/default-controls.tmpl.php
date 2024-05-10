<?php

/**
 * default-controls.tmpl.php - Default template for the Lyquix Filters block, controls sub-template
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
//  Instead, copy it to /php/custom/blocks/filters/default-controls.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-controls.tmpl.php

if (count($s['controls'])) : ?>

	<div class="controls" id="<?= $s['hash'] ?>-controls">

	<?php if ($s['show_search'] == 'y') : ?>
		<div class="search-wrapper">
			<label for="<?= $s['hash'] ?>-search"><?= $s['search_placeholder'] ?></label>
			<input class="search" id="<?= $s['hash'] ?>-search" placeholder="<?= esc_attr($s['search_placeholder']) ?>">
			<button class="search-button" id="<?= $s['hash'] ?>-search-button"></button>
		</div>
	<?php endif; ?>

	<?php foreach ($s['controls'] as $j => $control) : ?>
		<?php if ($control['visible'] == 'y') : ?>

			<div class="control-wrapper" data-control="<?= $control['slug'] ?>" data-control-type="<?= $control['type'] ?>">

			<?php

			$options = $control['options'];

			switch ($control['presentation']) {
				case 'select': ?>

					<label for="<?= $s['hash'] ?>-control-<?= $j ?>">
						<span><?= $control['label'] ?></span>
						<select name="<?= $control['slug'] ?>" id="<?= $s['hash'] ?>-control-<?= $j ?>">
							<option value=""></option>

							<?php foreach ($options as $option) : ?>
							<option value="<?= esc_attr($option['value']) ?>"<?= $control['selected'] == $option['value'] ? ' selected' : '' ?>><?= $option['text'] ?></option>
							<?php endforeach; ?>

						</select>
					</label>

					<?php break;

				case 'checkbox': ?>

					<fieldset>

						<legend><?= $control['label'] ?></legend>

						<?php foreach ($options as $i => $option) : ?>
							<label for="<?= $s['hash'] ?>-control-<?= $j ?>-<?= $i ?>">
								<input type="checkbox" id="<?= $s['hash'] ?>-control-<?= $j ?>-<?= $i ?>" name="<?= $control['slug'] ?>" value="<?= esc_attr($option['value']) ?>"<?= $control['selected'] == $option['value'] ? ' checked' : '' ?> />
								<span><?= $option['text'] ?></span>
							</label>
						<?php endforeach; ?>

					</fieldset>

					<?php break;

				case 'radio': ?>

					<fieldset>

						<legend><?= $control['label'] ?></legend>

						<?php foreach ($options as $i => $option) : ?>
							<label for="<?= $s['hash'] ?>-control-<?= $j ?>-<?= $i ?>">
								<input type="radio" id="<?= $s['hash'] ?>-control-<?= $j ?>-<?= $i ?>" name="<?= $control['slug'] ?>" value="<?= esc_attr($option['value']) ?>"<?= $control['selected'] == $option['value'] ? ' checked' : '' ?> />
								<span><?= $option['text'] ?></span>
							</label>
						<?php endforeach; ?>

					</fieldset>

					<?php break;

				case 'list': ?>

					<label id="<?= $s['hash'] ?>-control-<?= $j ?>-label"><?= $control['label'] ?></label>

					<ul class="control-list" id="<?= $s['hash'] ?>-control-<?= $j ?>" role="combobox" aria-labelledby="<?= $s['hash'] ?>-control-<?= $j ?>-label">

						<?php foreach ($options as $i => $option) : ?>
						<li id="<?= $s['hash'] ?>-control-<?= $j ?>-<?= $i ?>" class="option<?= $control['selected'] == $option['value'] ? ' selected' : '' ?>" data-value="<?= esc_attr($option['value']) ?>"><?= $option['text'] ?></li>
						<?php endforeach; ?>

					</ul>

					<?php break;
			}
			?>

			</div>

		<?php endif; ?>

	<?php endforeach; ?>

	<?php if ($s['show_clear'] == 'y') : ?>
		<div class="clear-wrapper">
			<button id="<?= $s['hash'] ?>-clear" class="clear"><?= $s['clear_label']?></button>
		</div>
	<?php endif; ?>

	</div>

<?php endif;
