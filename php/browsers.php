<?php

/**
 * browsers.php - Generates an alert for outdated browsers
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

namespace lqx\browsers;

// Render alert for outdated browsers
function render() {
	if (get_theme_mod('browser_alert', 1)) : ?>
	<script>
	((u) => {
		var s = document.createElement('script');
		s.src = u + '&ua=' + encodeURIComponent(lqx.util.hash(window.navigator.userAgent));
		document.getElementsByTagName('head')[0].appendChild(s);
	})('<?= get_template_directory_uri() ?>/php/browsers/?accepted=<?= get_theme_mod('accepted_browser_versions', 3) ?>');
	</script>
<?php endif;
}
