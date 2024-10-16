<?php

/**
 * default-pagination-details.tmpl.php - Default template for the Lyquix Filters block, pagination-details sub-template
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
//  Instead, copy it to /php/custom/blocks/filters/default-pagination-details.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-pagination-details.tmpl.php

?>
<div class="pagination-details">
	<div class="page-details">
		Page <?= $p['page'] ?> of <?= $p['total_pages'] ?>
	</div>
	<div class="posts-details">
		Posts <?= (($p['page'] - 1) * $p['posts_per_page']) + 1 ?> to <?= min($p['page'] * $p['posts_per_page'], $p['total_posts']) ?> of <?= $p['total_posts'] ?>
	</div>
</div>
