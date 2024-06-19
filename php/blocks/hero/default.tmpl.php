<?php

/**
 * default.tmpl.php - Default template for the Lyquix Hero block
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
//  Instead, copy it to /php/custom/blocks/hero/default.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/hero/{preset}.tmpl.php

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

		<?php
		require \lqx\blocks\get_template('hero', $s['preset'], 'text');
		if ($s['show_image'] == 'y') require \lqx\blocks\get_template('hero', $s['preset'], 'image');
		?>

	</div>

</section>
