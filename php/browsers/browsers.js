/**
 * browsers.js - Renders the browser alert
 *
 * @version     3.0.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
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

((browsersData) => {
	if ('outdated' in browsersData && browsersData.outdated) {
		// Load CSS stylesheet
		const css = document.createElement('link');
		css.rel = 'stylesheet';
		css.href = 'BROWSERS_URI' + 'browsers.min.css';
		document.body.appendChild(css);

		// Create alert element
		const elem = jQuery(
			`<section id="browser-alert">
				<h1>Please Update Your Browser</h1>
				<p><strong>You are using an outdated browser, ${browsersData.browser_name} version ${browsersData.user_version}.</strong></p>
				<p>Outdated browsers can make your computer unsafe and may not properly work with this website.&nbsp;
				To ensure security, performance, and full functionality, please upgrade to an up-to-date browser.</p>
				<ul></ul>
			</section>`);

		// Cycle through the list of browsers
		Object.keys(browsersData.info).forEach((browserCode) => {
			const browser = browsersData.info[browserCode];
			const li = jQuery(
				`<li>
					<a href="${browser.url}" title="${browser.long_name}" target="_blank">
						<div class="icon">
							<img src="${'BROWSERS_URI'}../../images/browsers/${browserCode}.svg">
						</div>
						<h2>${browser.name}</h2>
					</a>
					<p class="info"><em>${browser.info}</em></p>
					<p class="version">Latest Version: <strong>${browser.version}</strong></p>
					<p class="website"><a href="${browser.url}" title="${browser.long_name}" target="_blank">Visit Official Website</a></p>
				</li>`);
			elem.find('ul').append(li);
		});

		// Append alert to body
		jQuery('body').append(elem);
	}
})(BROWSERS_DATA);
