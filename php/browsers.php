<?php

/**
 * browsers.php - Generates an alert for outdated browsers
 *
 * @version     3.4.0
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

namespace lqx\browsers;

// Render alert for outdated browsers
function render() {
	// Not while critical CSS is being generated: an alert shown to the headless browser would
	// be captured as above-the-fold content for everyone
	if (get_theme_mod('browser_alert', 1) && !isset($_GET['no-critical-path-css'])) :
		ob_start(); ?>
	((u) => {
		// lyquix.js loads deferred: run once lqx is available (both event systems, run-once)
		var done = false;
		var run = () => {
			if (done) return; done = true;
			var s = document.createElement('script');
			s.src = u + '&ua=' + encodeURIComponent(lqx.util.hash(window.navigator.userAgent));
			document.getElementsByTagName('head')[0].appendChild(s);
		};
		if (window.lqx && lqx.util) run();
		else {
			document.addEventListener('lqxload', run);
			if (window.jQuery) jQuery(document).one('lqxload', run);
		}
	})('<?= get_template_directory_uri() ?>/php/browsers/?accepted=<?= esc_js(get_theme_mod('accepted_browser_versions', 3)) ?>');
<?php	wp_print_inline_script_tag(ob_get_clean(), ['id' => 'lqx-browser-alert']);
	endif;
}
