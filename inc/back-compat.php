<?php

function lqx_switch_theme() {
	switch_theme( WP_DEFAULT_THEME, WP_DEFAULT_THEME );

	unset( $_GET['activated'] );

	add_action( 'admin_notices', 'lqx_upgrade_notice' );
}
add_action( 'after_switch_theme', 'lqx_switch_theme' );

/**
 * Adds a message for unsuccessful theme switch.
 *
 * @global string $wp_version WordPress version.
 */
function lqx_upgrade_notice() {
	$message = sprintf( __( 'Lyquix Theme requires at least WordPress version 4.4. You are running version %s. Please upgrade and try again.', 'lqx' ), $GLOBALS['wp_version'] );
	printf( '<div class="error"><p>%s</p></div>', $message );
}

/**
 * Prevents the Customizer from being loaded on WordPress versions prior to 4.4.
 *
 * @global string $wp_version WordPress version.
 */
function lqx_customize() {
	wp_die( sprintf( __( 'Lyquix Theme requires at least WordPress version 4.4. You are running version %s. Please upgrade and try again.', 'lqx' ), $GLOBALS['wp_version'] ), '', array(
		'back_link' => true,
	) );
}
add_action( 'load-customize.php', 'lqx_customize' );

/**
 * Prevents the Theme Preview from being loaded on WordPress versions prior to 4.4.
 *
 * @global string $wp_version WordPress version.
 */
function lqx_preview() {
	if ( isset( $_GET['preview'] ) ) {
		wp_die( sprintf( __( 'Lyquix Theme requires at least WordPress version 4.4. You are running version %s. Please upgrade and try again.', 'lqx' ), $GLOBALS['wp_version'] ) );
	}
}
add_action( 'template_redirect', 'lqx_preview' );
