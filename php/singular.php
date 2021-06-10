<?php
/**
 * singular.php - Default template for singular pages
 *
 * @version     2.3.1
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2017 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

the_post();
?>
<h1><?php the_title(); ?></h1>
<p><span class="created"><?php the_time('F jS, Y') ?></span><br>
<span class="author"><?php the_author() ?></span></p>
<div class="content"><?php the_content(); ?></div>
