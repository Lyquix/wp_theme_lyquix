<?php

/**
 * livereload.php - Loads the Livereload functionality
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

namespace lqx\livereload;

/**
 * Render the Livereload script
 *
 * @return void
 */
function render() {
	if (get_theme_mod('feat_livereload', '1') === '1' && substr($_SERVER['HTTP_HOST'], -5) === '.test') : ?>
		<script src="http://localhost:35729/livereload.js?snipver=1"></script>
	<?php endif;
}
