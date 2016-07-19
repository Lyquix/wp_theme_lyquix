<?php
/**
 * Twenty Sixteen Customizer functionality
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

/**
 * Sets up the WordPress core custom header and custom background features.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see lqx_header_style()
 */
function lqx_custom_header_and_background() {
	$color_scheme             = lqx_get_color_scheme();
	$default_background_color = trim( $color_scheme[0], '#' );
	$default_text_color       = trim( $color_scheme[3], '#' );

	/**
	 * Filter the arguments used when adding 'custom-background' support in Twenty Sixteen.
	 *
	 * @since Twenty Sixteen 1.0
	 *
	 * @param array $args {
	 *     An array of custom-background support arguments.
	 *
	 *     @type string $default-color Default color of the background.
	 * }
	 */
	add_theme_support( 'custom-background', apply_filters( 'lqx_custom_background_args', array(
		'default-color' => $default_background_color,
	) ) );

	/**
	 * Filter the arguments used when adding 'custom-header' support in Twenty Sixteen.
	 *
	 * @since Twenty Sixteen 1.0
	 *
	 * @param array $args {
	 *     An array of custom-header support arguments.
	 *
	 *     @type string $default-text-color Default color of the header text.
	 *     @type int      $width            Width in pixels of the custom header image. Default 1200.
	 *     @type int      $height           Height in pixels of the custom header image. Default 280.
	 *     @type bool     $flex-height      Whether to allow flexible-height header images. Default true.
	 *     @type callable $wp-head-callback Callback function used to style the header image and text
	 *                                      displayed on the blog.
	 * }
	 */
	add_theme_support( 'custom-header', apply_filters( 'lqx_custom_header_args', array(
		'default-text-color'     => $default_text_color,
		'width'                  => 1200,
		'height'                 => 280,
		'flex-height'            => true,
		'wp-head-callback'       => 'lqx_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'lqx_custom_header_and_background' );

if ( ! function_exists( 'lqx_header_style' ) ) :
/**
 * Styles the header text displayed on the site.
 *
 * Create your own lqx_header_style() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see lqx_custom_header_and_background().
 */
function lqx_header_style() {
	// If the header text option is untouched, let's bail.
	if ( display_header_text() ) {
		return;
	}

	// If the header text has been hidden.
	?>
	<style type="text/css" id="twentysixteen-header-css">
		.site-branding {
			margin: 0 auto 0 0;
		}

		.site-branding .site-title,
		.site-description {
			clip: rect(1px, 1px, 1px, 1px);
			position: absolute;
		}
	</style>
	<?php
}
endif; // lqx_header_style

/**
 * Adds postMessage support for site title and description for the Customizer.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
 
function lqx_customize_register( $wp_customize ) {
	$color_scheme = lqx_get_color_scheme();
	$wp_customize->get_setting( 'header_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'main_nav_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'main_wrapper_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'content_wrapper_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'primary_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'secondary_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'widget_area_1_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'widget_area_2_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'footer_class' )->transport = 'postMessage';
	$wp_customize->get_setting( 'analytics_account' )->transport = 'postMessage';
	$wp_customize->get_setting( 'disqus_shortname' )->transport = 'postMessage';
	$wp_customize->get_setting( 'addthis_pubid' )->transport = 'postMessage';
	$wp_customize->get_setting( 'google_site_verification' )->transport = 'postMessage';
	$wp_customize->get_setting( 'msvalidate' )->transport = 'postMessage';
	$wp_customize->get_setting( 'p_domain_verify' )->transport = 'postMessage';
	$wp_customize->get_setting( 'lyquix_library_options' )->transport = 'postMessage';
	$wp_customize->get_setting( 'add_css_libraries' )->transport = 'postMessage';
	$wp_customize->get_setting( 'non_min_js' )->transport = 'postMessage';
	$wp_customize->get_setting( 'lessjs' )->transport = 'postMessage';
	$wp_customize->get_setting( 'angularjs' )->transport = 'postMessage';
	$wp_customize->get_setting( 'lodash' )->transport = 'postMessage';
	$wp_customize->get_setting( 'es5_shim' )->transport = 'postMessage';
	$wp_customize->get_setting( 'es5_es6_shim' )->transport = 'postMessage';
	$wp_customize->get_setting( 'json3' )->transport = 'postMessage';
	$wp_customize->get_setting( 'add_js_libraries' )->transport = 'postMessage';
	$wp_customize->get_setting( 'remove_css_js_libraries' )->transport = 'postMessage';
	$wp_customize->get_setting( 'min_screen' )->transport = 'postMessage';
	$wp_customize->get_setting( 'max_screen' )->transport = 'postMessage';
	$wp_customize->get_setting( 'fluid_screen' )->transport = 'postMessage';
	$wp_customize->get_setting( 'fluid_device' )->transport = 'postMessage';
	$wp_customize->get_setting( 'mobile_detect_method' )->transport = 'postMessage';
	$wp_customize->get_setting( 'ie9_alert' )->transport = 'postMessage';
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';

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

	// Add color scheme setting and control.
	$wp_customize->add_setting( 'color_scheme', array(
		'default'           => 'default',
		'sanitize_callback' => 'lqx_sanitize_color_scheme',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( 'color_scheme', array(
		'label'    => __( 'Base Color Scheme', 'twentysixteen' ),
		'section'  => 'colors',
		'type'     => 'select',
		'choices'  => lqx_get_color_scheme_choices(),
		'priority' => 1,
	) );

	// Add page background color setting and control.
	$wp_customize->add_setting( 'page_background_color', array(
		'default'           => $color_scheme[1],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'page_background_color', array(
		'label'       => __( 'Page Background Color', 'twentysixteen' ),
		'section'     => 'colors',
	) ) );

	// Remove the core header textcolor control, as it shares the main text color.
	$wp_customize->remove_control( 'header_textcolor' );

	// Add link color setting and control.
	$wp_customize->add_setting( 'link_color', array(
		'default'           => $color_scheme[2],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'link_color', array(
		'label'       => __( 'Link Color', 'twentysixteen' ),
		'section'     => 'colors',
	) ) );

	// Add main text color setting and control.
	$wp_customize->add_setting( 'main_text_color', array(
		'default'           => $color_scheme[3],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'main_text_color', array(
		'label'       => __( 'Main Text Color', 'twentysixteen' ),
		'section'     => 'colors',
	) ) );

	// Add secondary text color setting and control.
	$wp_customize->add_setting( 'secondary_text_color', array(
		'default'           => $color_scheme[4],
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
	) );

	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'secondary_text_color', array(
		'label'       => __( 'Secondary Text Color', 'twentysixteen' ),
		'section'     => 'colors',
	) ) );
	//Add custom settings for the lyquix template
	$wp_customize->add_setting( 'header_color' , array(
	    'default'     => $color_scheme[4],
	    'sanitize_callback' => 'sanitize_hex_color',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_color', array(
		'label'        => __( 'Header Color', 'twentysixteen' ),
		'section'    => 'colors',
	) ) );
	//Add custom functions for Lyquix Theme
	$wp_customize->add_section( 'lqx_theme_settings' , array(
	    'title'      => __( 'Lyquix Theme Settings', 'lyquix_theme' ),
	    'priority'   => 30,
	) );
	$wp_customize->add_setting( 'analytics_account' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'analytics_account', array(
		'label'        => __( 'Google Analytics Account', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'analytics_account',
	) ) );
	$wp_customize->add_setting( 'disqus_shortname' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'disqus_shortname', array(
		'label'        => __( 'Disqus Shortname', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'disqus_shortname',
	) ) );
	$wp_customize->add_setting( 'addthis_pubid' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'addthis_pubid', array(
		'label'        => __( 'AddThis PubID', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'addthis_pubid',
	) ) );
	$wp_customize->add_setting( 'google_site_verification' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'google_site_verification', array(
		'label'        => __( 'Google Site Verification', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'google_site_verification',
	) ) );
	$wp_customize->add_setting( 'msvalidate' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'msvalidate', array(
		'label'        => __( 'Microsoft Site Validation', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'msvalidate',
	) ) );
	$wp_customize->add_setting( 'p_domain_verify' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'p_domain_verify', array(
		'label'        => __( 'P Domain Verify', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'p_domain_verify',
	) ) );
	$wp_customize->add_setting( 'lqx_options' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'lqx_options', array(
		'label'        => __( 'Lyquix Library Options', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'lqx_options',
	) ) );
	$wp_customize->add_setting( 'add_css_libraries' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'add_css_libraries', array(
		'type'		 => 'textarea',
		'label'        => __( 'Additional CSS Libraries', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'add_css_libraries',
	) );
	$wp_customize->add_setting( 'non_min_js' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'non_min_js', array(
		'type'		 => 'radio',
		'label'        => __( 'Use Original JS', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'non_min_js',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'lessjs' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'lessjs', array(
		'type'		 => 'radio',
		'label'        => __( 'Use less.js', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'lessjs',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'angularjs' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'angularjs', array(
		'type'		 => 'radio',
		'label'        => __( 'Load AngularJS Library', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'angularjs',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'lodash' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'lodash', array(
		'type'		 => 'radio',
		'label'        => __( 'Use Lodash library', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'lodash',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'es5_shim' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'es5_shim', array(
		'type'		 => 'radio',
		'label'        => __( 'Load ES5 shim library', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'es5_shim',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'es5_es6_shim' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'es5_es6_shim', array(
		'type'		 => 'radio',
		'label'        => __( 'Load ES5 + ES6 shim library', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'es5_es6_shim',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'json3' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'json3', array(
		'type'		 => 'radio',
		'label'        => __( 'Load JSON3 library', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'json3',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	$wp_customize->add_setting( 'add_js_libraries' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'add_js_libraries', array(
		'type'		 => 'textarea',
		'label'        => __( 'Additional Javascript Libraries', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'add_js_libraries',
	) );
	$wp_customize->add_setting( 'remove_css_js_libraries' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'remove_css_js_libraries', array(
		'type'		 => 'textarea',
		'label'        => __( 'Remove Javascript/CSS Libraries', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'remove_css_js_libraries',
	) );
	$wp_customize->add_setting( 'min_screen' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	    'default'     => '0',
	) );			
	$wp_customize->add_control( 'min_screen', array(
		'type'		 => 'select',
		'label'        => __( 'Minimum Screen Size', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'min_screen',
		'choices' => array(
			'0' => 'XS',
			'1'  => 'SM',
			'2' => 'MD',
			'3'  => 'LG',
			'4'  => 'XL',
		),
	) );
	$wp_customize->add_setting( 'max_screen' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	    'default'     => '4',
	) );			
	$wp_customize->add_control( 'max_screen', array(
		'type'		 => 'select',
		'label'        => __( 'Maximum Screen Size', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'max_screen',
		'choices' => array(
			'0' => 'XS',
			'1'  => 'SM',
			'2' => 'MD',
			'3'  => 'LG',
			'4'  => 'XL',
		),
	) );
	$wp_customize->add_setting( 'fluid_screen' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	    'default'     => 'none',
	    'sanitize_callback' => 'lqx_sanitize_fluid_screens'
	) );
	$wp_customize->add_control(new lqx_Customize_Control_Checkbox_Multiple( $wp_customize, 'fluid_screen', array(
		'label'        => __( 'Fluid Layout Screens', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'fluid_screen',
		'choices' => array(
			'XS' => 'XS',
			'SM'  =>'SM',
			'MD' => 'MD',
			'LG'  => 'LG',
			'XL'  => 'XL',
		),
	) ) );
	$wp_customize->add_setting( 'fluid_device' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );	
	$wp_customize->add_control( 'fluid_device', array(
		'type'		 => 'radio',
		'label'        => __( 'Fluid Layout Devices', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'fluid_device',
		'choices' => array(
			'any' => 'Any (Mobile and Desktop)',
			'mobile'  => 'Mobile only',
			'phone' => 'Phones only',
			'tablet'  => 'Tablets only',
		),
	) );
	$wp_customize->add_setting( 'mobiledetect_method' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'mobiledetect_method', array(
		'type'		 => 'radio',
		'label'        => __( 'Mobile Detect Method', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'mobiledetect_method',
		'choices' => array(
			'php' => 'Server-Side (PHP)',
			'js'  => 'Client-Side (JavaScript)',
		),
	) );
	$wp_customize->add_setting( 'ie9_alert' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'refresh',
	) );			
	$wp_customize->add_control( 'ie9_alert', array(
		'type'		 => 'radio',
		'label'        => __( 'Show IE9 upgrade alert', 'lyquix_theme' ),
		'section'    => 'lqx_theme_settings',
		'settings'   => 'ie9_alert',
		'choices' => array(
			'0' => 'No',
			'1'  => 'Yes',
		),
	) );
	/*
	$wp_customize->add_section( 'body_classes' , array(
	    'title'      => __( 'Body Classes', 'lyquix_theme' ),
	    'priority'   => 30,
	) );
	$wp_customize->add_setting( 'header_class' , array(
		'type'        => 'theme_mod',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'header_class', array(
		'label'        => __( 'Header Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'header_class',
	) ) );
	$wp_customize->add_setting( 'main_nav_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'main_nav_class', array(
		'label'        => __( 'Main Nav Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'main_nav_class',
	) ) );
	$wp_customize->add_setting( 'main_wrapper_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'main_wrapper_class', array(
		'label'        => __( 'Main Wrapper Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'main_wrapper_class',
	) ) );
	$wp_customize->add_setting( 'content_wrapper_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'content_wrapper_class', array(
		'label'        => __( 'Content Wrapper Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'content_wrapper_class',
	) ) );
	$wp_customize->add_setting( 'primary_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'primary_class', array(
		'label'        => __( 'Primary Content Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'primary_class',
	) ) );
	$wp_customize->add_setting( 'secondary_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'secondary_class', array(
		'label'        => __( 'Secondary Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'secondary_class',
	) ) );
	$wp_customize->add_setting( 'widget_area_1_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'sidebar_area_1_class', array(
		'label'        => __( 'Widget Area 1 Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'sidebar_area_1_class',
	) ) );
	$wp_customize->add_setting( 'widget_area_2_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'sidebar_area_2_class', array(
		'label'        => __( 'Widget Area 2 Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'sidebar_area_2_class',
	) ) );
	$wp_customize->add_setting( 'footer_class' , array(
	    'default'     => '',
	    'transport'   => 'postMessage',
	) );			
	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'footer_class', array(
		'label'        => __( 'Footer Class', 'lyquix_theme' ),
		'section'    => 'body_classes',
		'settings'   => 'footer_class',
	) ) );*/
}
add_action( 'customize_register', 'lqx_customize_register', 11 );

function lqx_sanitize_fluid_screens( $values ) {

    $multi_values = !is_array( $values ) ? explode( ',', $values ) : $values;

    return !empty( $multi_values ) ? array_map( 'sanitize_text_field', $multi_values ) : array();
}
/**
 * Render the site title for the selective refresh partial.
 *
 * @since Twenty Sixteen 1.2
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
 * @since Twenty Sixteen 1.2
 * @see lqx_customize_register()
 *
 * @return void
 */
function lqx_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Registers color schemes for Twenty Sixteen.
 *
 * Can be filtered with {@see 'lqx_color_schemes'}.
 *
 * The order of colors in a colors array:
 * 1. Main Background Color.
 * 2. Page Background Color.
 * 3. Link Color.
 * 4. Main Text Color.
 * 5. Secondary Text Color.
 *
 * @since Twenty Sixteen 1.0
 *
 * @return array An associative array of color scheme options.
 */
function lqx_get_color_schemes() {
	/**
	 * Filter the color schemes registered for use with Twenty Sixteen.
	 *
	 * The default schemes include 'default', 'dark', 'gray', 'red', and 'yellow'.
	 *
	 * @since Twenty Sixteen 1.0
	 *
	 * @param array $schemes {
	 *     Associative array of color schemes data.
	 *
	 *     @type array $slug {
	 *         Associative array of information for setting up the color scheme.
	 *
	 *         @type string $label  Color scheme label.
	 *         @type array  $colors HEX codes for default colors prepended with a hash symbol ('#').
	 *                              Colors are defined in the following order: Main background, page
	 *                              background, link, main text, secondary text.
	 *     }
	 * }
	 */
	return apply_filters( 'lqx_color_schemes', array(
		'default' => array(
			'label'  => __( 'Default', 'twentysixteen' ),
			'colors' => array(
				'#1a1a1a',
				'#ffffff',
				'#007acc',
				'#1a1a1a',
				'#686868',
			),
		),
		'dark' => array(
			'label'  => __( 'Dark', 'twentysixteen' ),
			'colors' => array(
				'#262626',
				'#1a1a1a',
				'#9adffd',
				'#e5e5e5',
				'#c1c1c1',
			),
		),
		'gray' => array(
			'label'  => __( 'Gray', 'twentysixteen' ),
			'colors' => array(
				'#616a73',
				'#4d545c',
				'#c7c7c7',
				'#f2f2f2',
				'#f2f2f2',
			),
		),
		'red' => array(
			'label'  => __( 'Red', 'twentysixteen' ),
			'colors' => array(
				'#ffffff',
				'#ff675f',
				'#640c1f',
				'#402b30',
				'#402b30',
			),
		),
		'yellow' => array(
			'label'  => __( 'Yellow', 'twentysixteen' ),
			'colors' => array(
				'#3b3721',
				'#ffef8e',
				'#774e24',
				'#3b3721',
				'#5b4d3e',
			),
		),
	) );
}

if ( ! function_exists( 'lqx_get_color_scheme' ) ) :
/**
 * Retrieves the current Twenty Sixteen color scheme.
 *
 * Create your own lqx_get_color_scheme() function to override in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @return array An associative array of either the current or default color scheme HEX values.
 */
function lqx_get_color_scheme() {
	$color_scheme_option = get_theme_mod( 'color_scheme', 'default' );
	$color_schemes       = lqx_get_color_schemes();

	if ( array_key_exists( $color_scheme_option, $color_schemes ) ) {
		return $color_schemes[ $color_scheme_option ]['colors'];
	}

	return $color_schemes['default']['colors'];
}
endif; // lqx_get_color_scheme

if ( ! function_exists( 'lqx_get_color_scheme_choices' ) ) :
/**
 * Retrieves an array of color scheme choices registered for Twenty Sixteen.
 *
 * Create your own lqx_get_color_scheme_choices() function to override
 * in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @return array Array of color schemes.
 */
function lqx_get_color_scheme_choices() {
	$color_schemes                = lqx_get_color_schemes();
	$color_scheme_control_options = array();

	foreach ( $color_schemes as $color_scheme => $value ) {
		$color_scheme_control_options[ $color_scheme ] = $value['label'];
	}

	return $color_scheme_control_options;
}
endif; // lqx_get_color_scheme_choices


if ( ! function_exists( 'lqx_sanitize_color_scheme' ) ) :
/**
 * Handles sanitization for Twenty Sixteen color schemes.
 *
 * Create your own lqx_sanitize_color_scheme() function to override
 * in a child theme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param string $value Color scheme name value.
 * @return string Color scheme name.
 */
function lqx_sanitize_color_scheme( $value ) {
	$color_schemes = lqx_get_color_scheme_choices();

	if ( ! array_key_exists( $value, $color_schemes ) ) {
		return 'default';
	}

	return $value;
}
endif; // lqx_sanitize_color_scheme

/**
 * Enqueues front-end CSS for color scheme.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see wp_add_inline_style()
 */
function lqx_color_scheme_css() {
	$color_scheme_option = get_theme_mod( 'color_scheme', 'default' );

	// Don't do anything if the default color scheme is selected.
	if ( 'default' === $color_scheme_option ) {
		return;
	}

	$color_scheme = lqx_get_color_scheme();

	// Convert main text hex color to rgba.
	$color_textcolor_rgb = lqx_hex2rgb( $color_scheme[3] );

	// If the rgba values are empty return early.
	if ( empty( $color_textcolor_rgb ) ) {
		return;
	}

	// If we get this far, we have a custom color scheme.
	$colors = array(
		'background_color'      => $color_scheme[0],
		'page_background_color' => $color_scheme[1],
		'link_color'            => $color_scheme[2],
		'main_text_color'       => $color_scheme[3],
		'secondary_text_color'  => $color_scheme[4],
		'border_color'          => vsprintf( 'rgba( %1$s, %2$s, %3$s, 0.2)', $color_textcolor_rgb ),

	);

	$color_scheme_css = lqx_get_color_scheme_css( $colors );

	wp_add_inline_style( 'twentysixteen-style', $color_scheme_css );
}
add_action( 'wp_enqueue_scripts', 'lqx_color_scheme_css' );

/**
 * Binds the JS listener to make Customizer color_scheme control.
 *
 * Passes color scheme data as colorScheme global.
 *
 * @since Twenty Sixteen 1.0
 */
function lqx_customize_control_js() {
	wp_enqueue_script( 'color-scheme-control', get_template_directory_uri() . '/js/color-scheme-control.js', array( 'customize-controls', 'iris', 'underscore', 'wp-util' ), '20160412', true );
	wp_localize_script( 'color-scheme-control', 'colorScheme', lqx_get_color_schemes() );
}
add_action( 'customize_controls_enqueue_scripts', 'lqx_customize_control_js' );

/**
 * Binds JS handlers to make the Customizer preview reload changes asynchronously.
 *
 * @since Twenty Sixteen 1.0
 */
function lqx_customize_preview_js() {
	wp_enqueue_script( 'twentysixteen-customize-preview', get_template_directory_uri() . '/js/customize-preview.js', array( 'customize-preview' ), '20160412', true );
}
add_action( 'customize_preview_init', 'lqx_customize_preview_js' );

/**
 * Returns CSS for the color schemes.
 *
 * @since Twenty Sixteen 1.0
 *
 * @param array $colors Color scheme colors.
 * @return string Color scheme CSS.
 */
function lqx_get_color_scheme_css( $colors ) {
	$colors = wp_parse_args( $colors, array(
		'background_color'      => '',
		'page_background_color' => '',
		'link_color'            => '',
		'main_text_color'       => '',
		'secondary_text_color'  => '',
		'border_color'          => '',
	) );

	return <<<CSS
	/* Color Scheme */

	/* Background Color */
	body {
		background-color: {$colors['background_color']};
	}

	/* Page Background Color */
	.site {
		background-color: {$colors['page_background_color']};
	}

	mark,
	ins,
	button,
	button[disabled]:hover,
	button[disabled]:focus,
	input[type="button"],
	input[type="button"][disabled]:hover,
	input[type="button"][disabled]:focus,
	input[type="reset"],
	input[type="reset"][disabled]:hover,
	input[type="reset"][disabled]:focus,
	input[type="submit"],
	input[type="submit"][disabled]:hover,
	input[type="submit"][disabled]:focus,
	.menu-toggle.toggled-on,
	.menu-toggle.toggled-on:hover,
	.menu-toggle.toggled-on:focus,
	.pagination .prev,
	.pagination .next,
	.pagination .prev:hover,
	.pagination .prev:focus,
	.pagination .next:hover,
	.pagination .next:focus,
	.pagination .nav-links:before,
	.pagination .nav-links:after,
	.widget_calendar tbody a,
	.widget_calendar tbody a:hover,
	.widget_calendar tbody a:focus,
	.page-links a,
	.page-links a:hover,
	.page-links a:focus {
		color: {$colors['page_background_color']};
	}

	/* Link Color */
	.menu-toggle:hover,
	.menu-toggle:focus,
	a,
	.main-navigation a:hover,
	.main-navigation a:focus,
	.dropdown-toggle:hover,
	.dropdown-toggle:focus,
	.social-navigation a:hover:before,
	.social-navigation a:focus:before,
	.post-navigation a:hover .post-title,
	.post-navigation a:focus .post-title,
	.tagcloud a:hover,
	.tagcloud a:focus,
	.site-branding .site-title a:hover,
	.site-branding .site-title a:focus,
	.entry-title a:hover,
	.entry-title a:focus,
	.entry-footer a:hover,
	.entry-footer a:focus,
	.comment-metadata a:hover,
	.comment-metadata a:focus,
	.pingback .comment-edit-link:hover,
	.pingback .comment-edit-link:focus,
	.comment-reply-link,
	.comment-reply-link:hover,
	.comment-reply-link:focus,
	.required,
	.site-info a:hover,
	.site-info a:focus {
		color: {$colors['link_color']};
	}

	mark,
	ins,
	button:hover,
	button:focus,
	input[type="button"]:hover,
	input[type="button"]:focus,
	input[type="reset"]:hover,
	input[type="reset"]:focus,
	input[type="submit"]:hover,
	input[type="submit"]:focus,
	.pagination .prev:hover,
	.pagination .prev:focus,
	.pagination .next:hover,
	.pagination .next:focus,
	.widget_calendar tbody a,
	.page-links a:hover,
	.page-links a:focus {
		background-color: {$colors['link_color']};
	}

	input[type="text"]:focus,
	input[type="email"]:focus,
	input[type="url"]:focus,
	input[type="password"]:focus,
	input[type="search"]:focus,
	textarea:focus,
	.tagcloud a:hover,
	.tagcloud a:focus,
	.menu-toggle:hover,
	.menu-toggle:focus {
		border-color: {$colors['link_color']};
	}

	/* Main Text Color */
	body,
	blockquote cite,
	blockquote small,
	.main-navigation a,
	.menu-toggle,
	.dropdown-toggle,
	.social-navigation a,
	.post-navigation a,
	.pagination a:hover,
	.pagination a:focus,
	.widget-title a,
	.site-branding .site-title a,
	.entry-title a,
	.page-links > .page-links-title,
	.comment-author,
	.comment-reply-title small a:hover,
	.comment-reply-title small a:focus {
		color: {$colors['main_text_color']};
	}

	blockquote,
	.menu-toggle.toggled-on,
	.menu-toggle.toggled-on:hover,
	.menu-toggle.toggled-on:focus,
	.post-navigation,
	.post-navigation div + div,
	.pagination,
	.widget,
	.page-header,
	.page-links a,
	.comments-title,
	.comment-reply-title {
		border-color: {$colors['main_text_color']};
	}

	button,
	button[disabled]:hover,
	button[disabled]:focus,
	input[type="button"],
	input[type="button"][disabled]:hover,
	input[type="button"][disabled]:focus,
	input[type="reset"],
	input[type="reset"][disabled]:hover,
	input[type="reset"][disabled]:focus,
	input[type="submit"],
	input[type="submit"][disabled]:hover,
	input[type="submit"][disabled]:focus,
	.menu-toggle.toggled-on,
	.menu-toggle.toggled-on:hover,
	.menu-toggle.toggled-on:focus,
	.pagination:before,
	.pagination:after,
	.pagination .prev,
	.pagination .next,
	.page-links a {
		background-color: {$colors['main_text_color']};
	}

	/* Secondary Text Color */

	/**
	 * IE8 and earlier will drop any block with CSS3 selectors.
	 * Do not combine these styles with the next block.
	 */
	body:not(.search-results) .entry-summary {
		color: {$colors['secondary_text_color']};
	}

	blockquote,
	.post-password-form label,
	a:hover,
	a:focus,
	a:active,
	.post-navigation .meta-nav,
	.image-navigation,
	.comment-navigation,
	.widget_recent_entries .post-date,
	.widget_rss .rss-date,
	.widget_rss cite,
	.site-description,
	.author-bio,
	.entry-footer,
	.entry-footer a,
	.sticky-post,
	.taxonomy-description,
	.entry-caption,
	.comment-metadata,
	.pingback .edit-link,
	.comment-metadata a,
	.pingback .comment-edit-link,
	.comment-form label,
	.comment-notes,
	.comment-awaiting-moderation,
	.logged-in-as,
	.form-allowed-tags,
	.site-info,
	.site-info a,
	.wp-caption .wp-caption-text,
	.gallery-caption,
	.widecolumn label,
	.widecolumn .mu_register label {
		color: {$colors['secondary_text_color']};
	}

	.widget_calendar tbody a:hover,
	.widget_calendar tbody a:focus {
		background-color: {$colors['secondary_text_color']};
	}

	/* Border Color */
	fieldset,
	pre,
	abbr,
	acronym,
	table,
	th,
	td,
	input[type="text"],
	input[type="email"],
	input[type="url"],
	input[type="password"],
	input[type="search"],
	textarea,
	.main-navigation li,
	.main-navigation .primary-menu,
	.menu-toggle,
	.dropdown-toggle:after,
	.social-navigation a,
	.image-navigation,
	.comment-navigation,
	.tagcloud a,
	.entry-content,
	.entry-summary,
	.page-links a,
	.page-links > span,
	.comment-list article,
	.comment-list .pingback,
	.comment-list .trackback,
	.comment-reply-link,
	.no-comments,
	.widecolumn .mu_register .mu_alert {
		border-color: {$colors['main_text_color']}; /* Fallback for IE7 and IE8 */
		border-color: {$colors['border_color']};
	}

	hr,
	code {
		background-color: {$colors['main_text_color']}; /* Fallback for IE7 and IE8 */
		background-color: {$colors['border_color']};
	}

	@media screen and (min-width: 56.875em) {
		.main-navigation li:hover > a,
		.main-navigation li.focus > a {
			color: {$colors['link_color']};
		}

		.main-navigation ul ul,
		.main-navigation ul ul li {
			border-color: {$colors['border_color']};
		}

		.main-navigation ul ul:before {
			border-top-color: {$colors['border_color']};
			border-bottom-color: {$colors['border_color']};
		}

		.main-navigation ul ul li {
			background-color: {$colors['page_background_color']};
		}

		.main-navigation ul ul:after {
			border-top-color: {$colors['page_background_color']};
			border-bottom-color: {$colors['page_background_color']};
		}
	}

CSS;
}


/**
 * Outputs an Underscore template for generating CSS for the color scheme.
 *
 * The template generates the css dynamically for instant display in the
 * Customizer preview.
 *
 * @since Twenty Sixteen 1.0
 */
function lqx_color_scheme_css_template() {
	$colors = array(
		'background_color'      => '{{ data.background_color }}',
		'page_background_color' => '{{ data.page_background_color }}',
		'link_color'            => '{{ data.link_color }}',
		'main_text_color'       => '{{ data.main_text_color }}',
		'secondary_text_color'  => '{{ data.secondary_text_color }}',
		'border_color'          => '{{ data.border_color }}',
	);
	?>
	<script type="text/html" id="tmpl-twentysixteen-color-scheme">
		<?php echo lqx_get_color_scheme_css( $colors ); ?>
	</script>
	<?php
}
add_action( 'customize_controls_print_footer_scripts', 'lqx_color_scheme_css_template' );

/**
 * Enqueues front-end CSS for the page background color.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see wp_add_inline_style()
 */
function lqx_page_background_color_css() {
	$color_scheme          = lqx_get_color_scheme();
	$default_color         = $color_scheme[1];
	$page_background_color = get_theme_mod( 'page_background_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $page_background_color === $default_color ) {
		return;
	}

	$css = '
		/* Custom Page Background Color */
		.site {
			background-color: %1$s;
		}

		mark,
		ins,
		button,
		button[disabled]:hover,
		button[disabled]:focus,
		input[type="button"],
		input[type="button"][disabled]:hover,
		input[type="button"][disabled]:focus,
		input[type="reset"],
		input[type="reset"][disabled]:hover,
		input[type="reset"][disabled]:focus,
		input[type="submit"],
		input[type="submit"][disabled]:hover,
		input[type="submit"][disabled]:focus,
		.menu-toggle.toggled-on,
		.menu-toggle.toggled-on:hover,
		.menu-toggle.toggled-on:focus,
		.pagination .prev,
		.pagination .next,
		.pagination .prev:hover,
		.pagination .prev:focus,
		.pagination .next:hover,
		.pagination .next:focus,
		.pagination .nav-links:before,
		.pagination .nav-links:after,
		.widget_calendar tbody a,
		.widget_calendar tbody a:hover,
		.widget_calendar tbody a:focus,
		.page-links a,
		.page-links a:hover,
		.page-links a:focus {
			color: %1$s;
		}

		@media screen and (min-width: 56.875em) {
			.main-navigation ul ul li {
				background-color: %1$s;
			}

			.main-navigation ul ul:after {
				border-top-color: %1$s;
				border-bottom-color: %1$s;
			}
		}
	';

	wp_add_inline_style( 'twentysixteen-style', sprintf( $css, $page_background_color ) );
}
add_action( 'wp_enqueue_scripts', 'lqx_page_background_color_css', 11 );

/**
 * Enqueues front-end CSS for the link color.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see wp_add_inline_style()
 */
function lqx_link_color_css() {
	$color_scheme    = lqx_get_color_scheme();
	$default_color   = $color_scheme[2];
	$link_color = get_theme_mod( 'link_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $link_color === $default_color ) {
		return;
	}

	$css = '
		/* Custom Link Color */
		.menu-toggle:hover,
		.menu-toggle:focus,
		a,
		.main-navigation a:hover,
		.main-navigation a:focus,
		.dropdown-toggle:hover,
		.dropdown-toggle:focus,
		.social-navigation a:hover:before,
		.social-navigation a:focus:before,
		.post-navigation a:hover .post-title,
		.post-navigation a:focus .post-title,
		.tagcloud a:hover,
		.tagcloud a:focus,
		.site-branding .site-title a:hover,
		.site-branding .site-title a:focus,
		.entry-title a:hover,
		.entry-title a:focus,
		.entry-footer a:hover,
		.entry-footer a:focus,
		.comment-metadata a:hover,
		.comment-metadata a:focus,
		.pingback .comment-edit-link:hover,
		.pingback .comment-edit-link:focus,
		.comment-reply-link,
		.comment-reply-link:hover,
		.comment-reply-link:focus,
		.required,
		.site-info a:hover,
		.site-info a:focus {
			color: %1$s;
		}

		mark,
		ins,
		button:hover,
		button:focus,
		input[type="button"]:hover,
		input[type="button"]:focus,
		input[type="reset"]:hover,
		input[type="reset"]:focus,
		input[type="submit"]:hover,
		input[type="submit"]:focus,
		.pagination .prev:hover,
		.pagination .prev:focus,
		.pagination .next:hover,
		.pagination .next:focus,
		.widget_calendar tbody a,
		.page-links a:hover,
		.page-links a:focus {
			background-color: %1$s;
		}

		input[type="text"]:focus,
		input[type="email"]:focus,
		input[type="url"]:focus,
		input[type="password"]:focus,
		input[type="search"]:focus,
		textarea:focus,
		.tagcloud a:hover,
		.tagcloud a:focus,
		.menu-toggle:hover,
		.menu-toggle:focus {
			border-color: %1$s;
		}

		@media screen and (min-width: 56.875em) {
			.main-navigation li:hover > a,
			.main-navigation li.focus > a {
				color: %1$s;
			}
		}
	';

	wp_add_inline_style( 'twentysixteen-style', sprintf( $css, $link_color ) );
}
add_action( 'wp_enqueue_scripts', 'lqx_link_color_css', 11 );

/**
 * Enqueues front-end CSS for the main text color.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see wp_add_inline_style()
 */
function lqx_main_text_color_css() {
	$color_scheme    = lqx_get_color_scheme();
	$default_color   = $color_scheme[3];
	$main_text_color = get_theme_mod( 'main_text_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $main_text_color === $default_color ) {
		return;
	}

	// Convert main text hex color to rgba.
	$main_text_color_rgb = lqx_hex2rgb( $main_text_color );

	// If the rgba values are empty return early.
	if ( empty( $main_text_color_rgb ) ) {
		return;
	}

	// If we get this far, we have a custom color scheme.
	$border_color = vsprintf( 'rgba( %1$s, %2$s, %3$s, 0.2)', $main_text_color_rgb );

	$css = '
		/* Custom Main Text Color */
		body,
		blockquote cite,
		blockquote small,
		.main-navigation a,
		.menu-toggle,
		.dropdown-toggle,
		.social-navigation a,
		.post-navigation a,
		.pagination a:hover,
		.pagination a:focus,
		.widget-title a,
		.site-branding .site-title a,
		.entry-title a,
		.page-links > .page-links-title,
		.comment-author,
		.comment-reply-title small a:hover,
		.comment-reply-title small a:focus {
			color: %1$s
		}

		blockquote,
		.menu-toggle.toggled-on,
		.menu-toggle.toggled-on:hover,
		.menu-toggle.toggled-on:focus,
		.post-navigation,
		.post-navigation div + div,
		.pagination,
		.widget,
		.page-header,
		.page-links a,
		.comments-title,
		.comment-reply-title {
			border-color: %1$s;
		}

		button,
		button[disabled]:hover,
		button[disabled]:focus,
		input[type="button"],
		input[type="button"][disabled]:hover,
		input[type="button"][disabled]:focus,
		input[type="reset"],
		input[type="reset"][disabled]:hover,
		input[type="reset"][disabled]:focus,
		input[type="submit"],
		input[type="submit"][disabled]:hover,
		input[type="submit"][disabled]:focus,
		.menu-toggle.toggled-on,
		.menu-toggle.toggled-on:hover,
		.menu-toggle.toggled-on:focus,
		.pagination:before,
		.pagination:after,
		.pagination .prev,
		.pagination .next,
		.page-links a {
			background-color: %1$s;
		}

		/* Border Color */
		fieldset,
		pre,
		abbr,
		acronym,
		table,
		th,
		td,
		input[type="text"],
		input[type="email"],
		input[type="url"],
		input[type="password"],
		input[type="search"],
		textarea,
		.main-navigation li,
		.main-navigation .primary-menu,
		.menu-toggle,
		.dropdown-toggle:after,
		.social-navigation a,
		.image-navigation,
		.comment-navigation,
		.tagcloud a,
		.entry-content,
		.entry-summary,
		.page-links a,
		.page-links > span,
		.comment-list article,
		.comment-list .pingback,
		.comment-list .trackback,
		.comment-reply-link,
		.no-comments,
		.widecolumn .mu_register .mu_alert {
			border-color: %1$s; /* Fallback for IE7 and IE8 */
			border-color: %2$s;
		}

		hr,
		code {
			background-color: %1$s; /* Fallback for IE7 and IE8 */
			background-color: %2$s;
		}

		@media screen and (min-width: 56.875em) {
			.main-navigation ul ul,
			.main-navigation ul ul li {
				border-color: %2$s;
			}

			.main-navigation ul ul:before {
				border-top-color: %2$s;
				border-bottom-color: %2$s;
			}
		}
	';

	wp_add_inline_style( 'twentysixteen-style', sprintf( $css, $main_text_color, $border_color ) );
}
add_action( 'wp_enqueue_scripts', 'lqx_main_text_color_css', 11 );

/**
 * Enqueues front-end CSS for the secondary text color.
 *
 * @since Twenty Sixteen 1.0
 *
 * @see wp_add_inline_style()
 */
function lqx_secondary_text_color_css() {
	$color_scheme    = lqx_get_color_scheme();
	$default_color   = $color_scheme[4];
	$secondary_text_color = get_theme_mod( 'secondary_text_color', $default_color );

	// Don't do anything if the current color is the default.
	if ( $secondary_text_color === $default_color ) {
		return;
	}

	$css = '
		/* Custom Secondary Text Color */

		/**
		 * IE8 and earlier will drop any block with CSS3 selectors.
		 * Do not combine these styles with the next block.
		 */
		body:not(.search-results) .entry-summary {
			color: %1$s;
		}

		blockquote,
		.post-password-form label,
		a:hover,
		a:focus,
		a:active,
		.post-navigation .meta-nav,
		.image-navigation,
		.comment-navigation,
		.widget_recent_entries .post-date,
		.widget_rss .rss-date,
		.widget_rss cite,
		.site-description,
		.author-bio,
		.entry-footer,
		.entry-footer a,
		.sticky-post,
		.taxonomy-description,
		.entry-caption,
		.comment-metadata,
		.pingback .edit-link,
		.comment-metadata a,
		.pingback .comment-edit-link,
		.comment-form label,
		.comment-notes,
		.comment-awaiting-moderation,
		.logged-in-as,
		.form-allowed-tags,
		.site-info,
		.site-info a,
		.wp-caption .wp-caption-text,
		.gallery-caption,
		.widecolumn label,
		.widecolumn .mu_register label {
			color: %1$s;
		}

		.widget_calendar tbody a:hover,
		.widget_calendar tbody a:focus {
			background-color: %1$s;
		}
	';

	wp_add_inline_style( 'twentysixteen-style', sprintf( $css, $secondary_text_color ) );
}
add_action( 'wp_enqueue_scripts', 'lqx_secondary_text_color_css', 11 );
