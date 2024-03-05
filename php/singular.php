<?php

/**
 * singular.php - Default template for singular pages
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
//  Instead create a file called singular.php in /php/custom/ and add your code there

the_post();

// Check if the page includes the Gutenberg block lqx/hero
$blocks = parse_blocks(get_the_content());
$has_hero_block = false;
foreach($blocks as $b) {
	if ($b['blockName'] == 'lqx/hero') {
		$has_hero_block = true;
		break;
	}
}

// Render the page title if there's no hero block
if (!$has_hero_block) : ?>
	<h1><?php the_title(); ?></h1>
<?php endif;

// Render the date and author for blog posts
if (get_post_type() == 'post') : ?>
<p><span class="created"><?php the_time('F jS, Y') ?></span><br>
	<span class="author"><?php the_author() ?></span>
</p>
<?php endif; ?>
<div class="content"><?php the_content(); ?></div>
<?php
