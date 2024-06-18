<?php

/**
 * custom.dist.php - Base template
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
//  Instead make a copy to custom.php and modify that file.

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<?php
	// Meta tags
	\lqx\meta\render();

	// Render GTM head code
	\lqx\js\render_gtm_head_code();

	// WordPress enqueued head meta and scripts
	wp_head();

	// Favicons
	\lqx\favicon\render();
	?>
</head>
<body class="<?= \lqx\body\classes() ?>">
	<a href="#content" class="skip-to-content-link">Skip to Content</a>
	<?php
	// Render GTM body code
	\lqx\js\render_gtm_body_code();

	// Chromeless page template
	if ('chromeless' == ($lqx_page_template ?? '')) :
		// Template router
		\lqx\router\render();

	// Non-chromeless page template
	else : ?>
		<header>

		<?php \lqx\modules\alerts\render(); ?>

			<?php if (has_nav_menu('top-menu')) : ?>
				<nav class="menu top">
					<?php wp_nav_menu(['menu' => 'top-menu']); ?>
				</nav>
			<?php endif; ?>

			<?php if (has_nav_menu('main-menu')) : ?>
				<nav class="menu main">
					<?php wp_nav_menu(['menu' => 'top-main']); ?>
				</nav>
			<?php endif; ?>

			<?php if (has_nav_menu('utility-menu')) : ?>
				<nav class="menu utility">
					<?php wp_nav_menu(['menu' => 'utility-menu']); ?>
				</nav>
			<?php endif; ?>

			<?php if (has_nav_menu('logged-in-menu')) : ?>
				<nav class="menu logged-in">
					<?php wp_nav_menu(['menu' => 'logged-in-menu']); ?>
				</nav>
			<?php endif; ?>

		</header>

		<main id="content">

			<article>

				<?php \lqx\router\render(); ?>

			</article>

			<?php \lqx\modules\cta\render(); ?>

		</main>

		<footer>

			<?php if (has_nav_menu('bottom-menu')) : ?>
				<nav class="menu bottom">
					<?php wp_nav_menu(['menu' => 'bottom-menu']); ?>
				</nav>
			<?php endif; ?>

			<?php if (has_nav_menu('footer-menu')) : ?>
				<nav class="menu footer">
					<?php wp_nav_menu(['menu' => 'footer-menu']); ?>
				</nav>
			<?php endif; ?>

			<?php \lqx\modules\social\render(); ?>

			<?php \lqx\modules\share\render(); ?>

		</footer>

	<?php
	// Popups
	\lqx\modules\popup\render();

	// End of non-chromeless page template
	endif;

	// WordPress enqueued footer scripts
	wp_footer();

	// Render Lyquix and Scripts options
	\lqx\js\render_lyquix_options();

	// Render page custom CSS and JS
	\lqx\css\render_page_custom_css();
	\lqx\js\render_page_custom_js();

	// Outdated browser alert
	\lqx\browsers\render();

	// LiveReload library
	\lqx\livereload\render();
	?>
</body>

</html>
