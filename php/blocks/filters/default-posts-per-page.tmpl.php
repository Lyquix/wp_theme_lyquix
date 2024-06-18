<?php

/**
 * default-posts-per-page.tmpl.php - Default template for the Lyquix Filters block, posts-per-page sub-template
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
//  Instead, copy it to /php/custom/blocks/filters/default-posts-per-page.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-posts-per-page.tmpl.php

?>
<div class="posts-per-page-wrapper">
	<label for="<?= $s['hash'] ?>-posts-per-page">Posts per Page</label>
	<select id="<?= $s['hash'] ?>-posts-per-page" class="posts-per-page">
	<?php foreach ($s['pagination']['posts_per_page_options'] as $option) : ?>
		<option value="<?= $option ?>"<?= $option == $s['pagination']['posts_per_page'] ? ' selected' : '' ?>><?= $option ?></option>
	<?php endforeach; ?>
	</select>
</div>
