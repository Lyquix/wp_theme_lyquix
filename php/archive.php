<?php

/**
 * archive.php - Default template for archive pages
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
//  Instead create a file called archive.php in /php/custom/templates/ and add your code there

?>
<h1><?php the_archive_title(); ?></h1>
<?php if (have_posts()) : ?>
	<ul>
		<?php while (have_posts()) :
			the_post(); ?>
			<li>
				<h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
				<p><span class="created"><?php the_time('F jS, Y') ?></span><br>
					<span class="author"><?php the_author() ?></span>
				</p>
				<div class="intro"><?php the_excerpt(); ?></div>
				<p><a class="readmore" href="<?php the_permalink() ?>">Read more &raquo;</a></p>
			</li>
		<?php endwhile; ?>
	</ul>
	<div class="pagination"><?php the_posts_pagination(); ?></div>
<?php endif;
