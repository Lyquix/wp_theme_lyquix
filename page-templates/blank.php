<?php
/*
 * Template Name: Blank
 *
 * blank.php - page template outputs <head> and the_content()
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

$lqx_page_template = 'blank';
if(file_exists(get_template_directory(). '/custom.php')) :
	require get_template_directory() . '/custom.php';
else :
?><!DOCTYPE html>
<html>
	<body>
		File <span style="font-family: monospace;"><?php echo get_template_directory();?>/custom.php</span> not found.
	</body>
</html>
<?php endif;

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
