<?php
/*
 * Template Name: Raw
 *
 * raw.php - page template outputs only the_content()
 *
 * @version     2.1.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

the_post();
echo get_the_content();
