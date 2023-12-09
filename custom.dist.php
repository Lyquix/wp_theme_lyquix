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

// Initialize variables
require get_template_directory() . '/php/vars.php';

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
	<?php
	// Meta tags
	require get_template_directory() . '/php/meta.php';

	// Prepare CSS
	require get_template_directory() . '/php/css.php';

	// Prepare JavaScript
	require get_template_directory() . '/php/js.php';

	// Render GTM head code
	lqx\js\render_gtm_head_code();

	// WordPress enqueued head meta and scripts
	wp_head();

	// Favicons
	require get_template_directory() . '/php/favicon.php';

	// head-scripts widget area
	dynamic_sidebar('head-scripts');
	?>
</head>
<?php
// Prepare <body> classes
require get_template_directory() . '/php/body.php';
?>

<body class="<?php echo implode(' ', $body_classes); ?>">
	<?php
	// Render GTM body code
	lqx\js\render_gtm_body_code();

	// Chromeless page template
	if ($lqx_page_template == 'chromeless') :
		// Template router
		require get_template_directory() . '/php/router.php';

	// Non-chromeless page template
	else : ?>
		<header>
			<?php if (is_active_sidebar('header')) dynamic_sidebar('header'); ?>

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

		<main>
			<?php if (is_active_sidebar('top')) : ?>
				<section class="widget top">
					<?php dynamic_sidebar('top'); ?>
				</section>
			<?php endif; ?>

			<?php if (is_active_sidebar('left')) : ?>
				<aside class="widget left">
					<?php dynamic_sidebar('left'); ?>
				</aside>
			<?php endif; ?>

			<article>
				<?php if (is_active_sidebar('before')) : ?>
					<section class="widget before">
						<?php dynamic_sidebar('before'); ?>
					</section>
				<?php endif;

				// Template router
				require get_template_directory() . '/php/router.php';

				if (is_active_sidebar('after')) : ?>
					<section class="widget after">
						<?php dynamic_sidebar('after'); ?>
					</section>
				<?php endif; ?>
			</article>

			<?php if (is_active_sidebar('right')) : ?>
				<aside class="widget right">
					<?php dynamic_sidebar('right'); ?>
				</aside>
			<?php endif; ?>

			<?php if (is_active_sidebar('bottom')) : ?>
				<section class="widget bottom">
					<?php dynamic_sidebar('bottom'); ?>
				</section>
			<?php endif; ?>
		</main>

		<footer>
			<?php if (is_active_sidebar('footer')) dynamic_sidebar('footer'); ?>

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
		</footer>
	<?php
	// Outdated browser alert
	require get_template_directory() . '/php/browser-alert.php';

	// End of non-chromeless page template
	endif;

	// WordPress enqueued footer scripts
	wp_footer();

	if (is_active_sidebar('body-scripts')) dynamic_sidebar('body-scripts');

	// Render Lyquix and Scripts options
	lqx\js\render_lyquix_options();

	// Render page custom CSS and JS
	lqx\css\render_page_custom_css();
	lqx\js\render_page_custom_js();

	// LiveReload library
	require get_template_directory() . '/php/livereload.php';
	?>
</body>

</html>
