<?php

/**
 * browser-update.php - Includes alerts for outdated browsers
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

if (get_theme_mod('browser_alert', 1)) : ?>
	<script>
		const lqxBrowserAlert = (outdatedBrowser) => {
			if ('outdated' in outdatedBrowser && outdatedBrowser.outdated) {
				// Load CSS stylesheet
				const css = document.createElement('link');
				css.rel = 'stylesheet';
				css.href = '<?php echo $tmpl_url; ?>/css/browser-alert.css';
				document.body.appendChild(css);

				// Create alert element
				let elem = jQuery('<section id="browser-alert"><h1>Please Update Your Browser</h1><p><strong>You are using an outdated browser.</strong></p><p>Outdated browsers can make your computer unsafe and may not properly work with this website. To ensure security, performance, and full functionality, please upgrade to an up-to-date browser.</p><ul></ul></section>');

				// Cycle through the list of browsers
				Object.keys(outdatedBrowser.info).forEach((browserCode) => {
					let browser = outdatedBrowser.info[browserCode];
					let li = jQuery(`<li id="${browserCode}"><a href="${browser.url}" title="${browser.long_name}" target="_blank"><div class="icon"></div><h2>${browser.name}</h2></a><p class="info"><em>${browser.info}</em></p><p class="version">Latest Version: <strong>${browser.version}</strong></p><p class="website"><a href="${browser.url}" title="${browser.long_name}" target="_blank">Visit Official Website</a></p></li>`);
					elem.find('ul').append(li);
				});

				// Append alert to body
				jQuery('body').append(elem);
			}
		};
	</script>
	<script src="<?php echo $tmpl_url; ?>/php/browsers?accepted=<?php echo get_theme_mod('accepted_browser_versions', 3); ?>"></script>
<?php endif;
