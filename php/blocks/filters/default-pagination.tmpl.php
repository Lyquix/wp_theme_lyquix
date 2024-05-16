<?php

/**
 * default-pagination.tmpl.php - Default template for the Lyquix Filters block, pagination sub-template
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
//  Instead, copy it to /php/custom/blocks/filters/default-pagination.tmpl.php to override it
//  You may also create overrides for specific presets, by copying this file to /php/custom/blocks/filters/{preset}-pagination.tmpl.php

$p = $s['pagination'];

if ($p['total_pages'] > 1) : ?>
<div class="pagination" id="<?= $s['hash'] ?>-pagination">

	<ul class="pageslinks">

		<li class="page-first" data-page="1" aria-label="First Page">First</li>
		<li class="page-prev" data-page="<?= $p['page'] > 1 ? $p['page'] - 1 : 1 ?>" aria-label="Previous Page">Prev</li>

		<?php for ($i = 1; $i <= $p['total_pages']; $i++) : ?>
		<li class="page-number <?= $i == $p['page'] ? ' current' : '' ?>" data-page="<?= $i ?>" aria-label="Page <?= $i ?>"><?= $i ?></li>
		<?php endfor;	?>

		<li class="page-next" data-page="<?= $p['page'] < $p['total_pages'] ? $p['page'] + 1 : $p['total_pages'] ?>" aria-label="Next Page">Next</li>
		<li class="page-last" data-page="<?= $i ?>" aria-label="Last Page">Last</li>

	</ul>

	<?php if ($s['pagination']['pagination_details'] == 'y') require \lqx\blocks\get_template('filters', $s['preset'], 'pagination-detail'); ?>

</div>
<?php endif;
