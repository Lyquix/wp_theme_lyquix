<?php
/*
Theme customization options
*/
function lqx_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'min_screen' )->transport = 'postMessage';
	$wp_customize->get_setting( 'max_screen' )->transport = 'postMessage';
	$wp_customize->get_setting( 'fluid_screen' )->transport = 'postMessage';
	$wp_customize->get_setting( 'fluid_device' )->transport = 'postMessage';
	$wp_customize->get_setting( 'add_css_libraries' )->transport = 'postMessage';
	$wp_customize->get_setting( 'jQuery' )->transport = 'postMessage';
	$wp_customize->get_setting( 'jQuery_ui' )->transport = 'postMessage';
	$wp_customize->get_setting( 'bootstrap' )->transport = 'postMessage';
	$wp_customize->get_setting( 'lyquix_library_options' )->transport = 'postMessage';
	$wp_customize->get_setting( 'non_min_js' )->transport = 'postMessage';
	$wp_customize->get_setting( 'lessjs' )->transport = 'postMessage';
	$wp_customize->get_setting( 'angularjs' )->transport = 'postMessage';
	$wp_customize->get_setting( 'lodash' )->transport = 'postMessage';
	$wp_customize->get_setting( 'smoothscroll' )->transport = 'postMessage';
	$wp_customize->get_setting( 'momentjs' )->transport = 'postMessage';
	$wp_customize->get_setting( 'dotdotdot' )->transport = 'postMessage';
	$wp_customize->get_setting( 'add_js_libraries' )->transport = 'postMessage';
	$wp_customize->get_setting( 'analytics_account' )->transport = 'postMessage';
	$wp_customize->get_setting( 'ga4_account' )->transport = 'postMessage';
	$wp_customize->get_setting( 'ga_pageview' )->transport = 'postMessage';
	$wp_customize->get_setting( 'ga_use_analytics_js' )->transport = 'postMessage';
	$wp_customize->get_setting( 'using_gtm' )->transport = 'postMessage';
	$wp_customize->get_setting( 'gtm_account' )->transport = 'postMessage';
	$wp_customize->get_setting( 'google_site_verification' )->transport = 'postMessage';
	$wp_customize->get_setting( 'msvalidate' )->transport = 'postMessage';
	$wp_customize->get_setting( 'p_domain_verify' )->transport = 'postMessage';
	$wp_customize->get_setting( 'mobiledetect_method' )->transport = 'postMessage';
	$wp_customize->get_setting( 'ie8_alert' )->transport = 'postMessage';
	$wp_customize->get_setting( 'ie9_alert' )->transport = 'postMessage';

	//Add new sections for lyquix theme settings
	$wp_customize->add_section( 'lqx_responsiveness' , array(
	    'title' => __( 'Responsiveness', 'lyquix_theme' ),
	    'priority' => 30,
	) );
	$wp_customize->add_section( 'lqx_css' , array(
	    'title' => __( 'CSS', 'lyquix_theme' ),
	    'priority' => 30,
	) );
	$wp_customize->add_section( 'lqx_js' , array(
	    'title' => __( 'Javascript', 'lyquix_theme' ),
	    'priority' => 30,
	) );
	$wp_customize->add_section( 'lqx_accounts' , array(
	    'title' => __( 'Accounts', 'lyquix_theme' ),
	    'priority' => 30,
	) );
	$wp_customize->add_section( 'lqx_other' , array(
	    'title' => __( 'Other', 'lyquix_theme' ),
	    'priority' => 30,
	) );
	//end new sections
	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial( 'blogname', array(
			'selector' => '.site-title a',
			'container_inclusive' => false,
			'render_callback' => 'lqx_customize_partial_blogname',
		) );
		$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
			'selector' => '.site-description',
			'container_inclusive' => false,
			'render_callback' => 'lqx_customize_partial_blogdescription',
		) );
	}
	// Remove the core header textcolor control, as it shares the main text color.
	$wp_customize->remove_control( 'header_textcolor' );
	//Add custom functions for Lyquix Theme
	$wp_customize->add_setting( 'min_screen' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	    'default' => '0',
	) );
	$wp_customize->add_control( 'min_screen', array(
		'type' => 'select',
		'label' => __( 'Minimum Screen Size', 'lyquix_theme' ),
		'section' => 'lqx_responsiveness',
		'settings' => 'min_screen',
		'choices' => array(
			'0' => 'XS',
			'1' => 'SM',
			'2' => 'MD',
			'3' => 'LG',
			'4' => 'XL',
		),
	) );
	$wp_customize->add_setting( 'max_screen' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	    'default' => '4',
	) );
	$wp_customize->add_control( 'max_screen', array(
		'type' => 'select',
		'label' => __( 'Maximum Screen Size', 'lyquix_theme' ),
		'section' => 'lqx_responsiveness',
		'settings' => 'max_screen',
		'choices' => array(
			'0' => 'XS',
			'1' => 'SM',
			'2' => 'MD',
			'3' => 'LG',
			'4' => 'XL',
		),
	) );
	$wp_customize->add_setting( 'fluid_screen' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	    'default' => 'none',
	    'sanitize_callback' => 'lqx_sanitize_fluid_screens'
	) );
	$wp_customize->add_control(new lqx_Customize_Control_Checkbox_Multiple( $wp_customize, 'fluid_screen', array(
		'label' => __( 'Fluid Layout Screens', 'lyquix_theme' ),
		'section' => 'lqx_responsiveness',
		'settings' => 'fluid_screen',
		'choices' => array(
			'XS' => 'XS',
			'SM' =>'SM',
			'MD' => 'MD',
			'LG' => 'LG',
			'XL' => 'XL',
		),
	) ) );
	$wp_customize->add_setting( 'fluid_device' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'fluid_device', array(
		'type' => 'radio',
		'label' => __( 'Fluid Layout Devices', 'lyquix_theme' ),
		'section' => 'lqx_responsiveness',
		'settings' => 'fluid_device',
		'choices' => array(
			'any' => 'Any (Mobile and Desktop)',
			'mobile' => 'Mobile only',
			'phone' => 'Phones only',
			'tablet' => 'Tablets only',
		),
	) );
	$wp_customize->add_setting( 'jQuery' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'jQuery', array(
		'type' => 'radio',
		'label' => __( 'Enable jQuery', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'jQuery',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'jQuery_ui' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'jQuery_ui', array(
		'type' => 'radio',
		'label' => __( 'Enable jQuery UI', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'jQuery_ui',
		'choices' => array(
			'0' => 'No',
			'1' => 'Core',
			'2' => 'Core + Sortable'
		),
	) );
	$wp_customize->add_setting( 'bootstrap' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'bootstrap', array(
		'type' => 'radio',
		'label' => __( 'Enable Bootstrap', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'bootstrap',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'lqx_options' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'lqx_options', array(
		'label' => __( 'Lyquix Library Options', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'lqx_options',
	) ) );
	$wp_customize->add_setting( 'add_css_libraries' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'add_css_libraries', array(
		'type' => 'textarea',
		'label' => __( 'Additional CSS Libraries', 'lyquix_theme' ),
		'section' => 'lqx_css',
		'settings' => 'add_css_libraries',
	) );
	$wp_customize->add_setting( 'non_min_js' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'non_min_js', array(
		'type' => 'radio',
		'label' => __( 'Use Original JS', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'non_min_js',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'lessjs' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'lessjs', array(
		'type' => 'radio',
		'label' => __( 'Use less.js', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'lessjs',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'angularjs' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'angularjs', array(
		'type' => 'radio',
		'label' => __( 'Load AngularJS Library', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'angularjs',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'lodash' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'lodash', array(
		'type' => 'radio',
		'label' => __( 'Use Lodash library', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'lodash',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'smoothscroll' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'smoothscroll', array(
		'type' => 'radio',
		'label' => __( 'Use SmoothScroll library', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'smoothscroll',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'momentjs' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'momentjs', array(
		'type' => 'radio',
		'label' => __( 'Use moments.js library', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'momentjs',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'dotdotdot' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'dotdotdot', array(
		'type' => 'radio',
		'label' => __( 'Use DotDotDot library', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'dotdotdot',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'add_js_libraries' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'add_js_libraries', array(
		'type' => 'textarea',
		'label' => __( 'Additional Javascript Libraries', 'lyquix_theme' ),
		'section' => 'lqx_js',
		'settings' => 'add_js_libraries',
	) );
	$wp_customize->add_setting( 'analytics_account' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'analytics_account', array(
		'label' => __( 'Google Analytics Account', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'analytics_account',
	) ) );
	$wp_customize->add_setting( 'ga4_account' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ga4_account', array(
		'label' => __( 'Google Analytics 4 Account', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'ga4_account',
	) ) );
	$wp_customize->add_setting( 'ga_pageview' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'ga_pageview', array(
		'type' => 'radio',
		'label' => __( 'Send Google Analytics Pageview', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'ga_pageview',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'ga_use_analytics_js' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'ga_use_analytics_js', array(
		'type' => 'radio',
		'label' => __( 'Use analytics.js for Universal Analytics', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'ga_use_analytics_js',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'using_gtm' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'using_gtm', array(
		'type' => 'radio',
		'label' => __( 'GA via GTM', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'using_gtm',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'gtm_account' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'gtm_account', array(
		'label' => __( 'GTM Account', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'gtm_account',
	) ) );
	$wp_customize->add_setting( 'google_site_verification' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'google_site_verification', array(
		'label' => __( 'google-site-verification', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'google_site_verification',
	) ) );
	$wp_customize->add_setting( 'msvalidate' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'msvalidate', array(
		'label' => __( 'msvalidate.01', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'msvalidate',
	) ) );
	$wp_customize->add_setting( 'p_domain_verify' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p_domain_verify', array(
		'label' => __( 'p:domain_verify', 'lyquix_theme' ),
		'section' => 'lqx_accounts',
		'settings' => 'p_domain_verify',
	) ) );
	$wp_customize->add_setting( 'mobiledetect_method' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'mobiledetect_method', array(
		'type' => 'radio',
		'label' => __( 'Mobile Detect Method', 'lyquix_theme' ),
		'section' => 'lqx_other',
		'settings' => 'mobiledetect_method',
		'choices' => array(
			'php' => 'Server-Side (PHP)',
			'js' => 'Client-Side (JavaScript)',
		),
	) );
	$wp_customize->add_setting( 'ie8_alert' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'ie8_alert', array(
		'type' => 'radio',
		'label' => __( 'Show IE8 upgrade alert', 'lyquix_theme' ),
		'section' => 'lqx_other',
		'settings' => 'ie8_alert',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'ie9_alert' , array(
		'type' => 'theme_mod',
	    'transport' => 'refresh',
	) );
	$wp_customize->add_control( 'ie9_alert', array(
		'type' => 'radio',
		'label' => __( 'Show IE9 upgrade alert', 'lyquix_theme' ),
		'section' => 'lqx_other',
		'settings' => 'ie9_alert',
		'choices' => array(
			'0' => 'No',
			'1' => 'Yes',
		),
	) );
}
add_action( 'customize_register', 'lqx_customize_register', 11 );

function lqx_sanitize_fluid_screens( $values ) {

    $multi_values = !is_array( $values ) ? explode( ',', $values ) : $values;

    return !empty( $multi_values ) ? array_map( 'sanitize_text_field', $multi_values ) : array();
}
/**
 * Render the site title for the selective refresh partial.
 *
 * @see lqx_customize_register()
 *
 * @return void
 */
function lqx_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @see lqx_customize_register()
 *
 * @return void
 */
function lqx_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

function lqx_customize_preview_js() {
	wp_enqueue_script( 'lqx-customize-preview', get_template_directory_uri() . '/js/customize-preview.js', array( 'customize-preview' ), '20160412', true );
}
add_action( 'customize_preview_init', 'lqx_customize_preview_js' );