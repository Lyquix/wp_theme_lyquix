<?php

/**
 * 404.dist.php - Default template for the 404 page
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
//  Instead, copy it to /php/custom/templates/404.php to override it

?>
<section class="content">
	<h1>Sorry, we can't find that page</h2>
	<p>You requested <?php
		$request_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
		$request_port = ($_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') ? ':' . $_SERVER['SERVER_PORT'] : '';
		$url = $request_protocol . $_SERVER['HTTP_HOST'] . $request_port . $_SERVER['REQUEST_URI'];
		echo sprintf('<a href="%s">%s</a>', $url, $url);
	?>, but we cannot find that page.</p>
	<p>This error can happen when there is a typo in the address (whether you clicked on a link, or typed it yourself, or copy-pasted it), or when the address is no longer up to date because the page was moved or unpublished.</p>
	<p>You can <a href="<?= get_home_url(); ?>">go to the Homepage</a>, or use the search bar below to find the content you are looking for.</p>
	<?php get_search_form(); ?>
</div>
