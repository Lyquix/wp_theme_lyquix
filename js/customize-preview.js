/**
 * Live-update changed settings in real time in the Customizer preview.
 */

( function( $ ) {
	var style = $( '#twentysixteen-color-scheme-css' ),
		api = wp.customize;

	if ( ! style.length ) {
		style = $( 'head' ).append( '<style type="text/css" id="twentysixteen-color-scheme-css" />' )
		                    .find( '#twentysixteen-color-scheme-css' );
	}

	// Site title.
	api( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );

	// Site tagline.
	api( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );
	// Minimum Screen Size
	api( 'min_screen', function( value ) {
		value.bind( function( to ) {
			lqx.setOptions({bodyScreenSize: {min: to}});
		} );
	} );
	api( 'fluid_screen', function( value ) {
		$('input[type="checkbox"]').each(function(){
			console.log("it's a checkbox!");
		});
	} );
	// Maximum Screen Size
	api( 'max_screen', function( value ) {
		value.bind( function( to ) {
			lqx.setOptions({bodyScreenSize: {max: to}});
		} );
	} );
	// Add custom-background-image body class when background image is added.
	api( 'background_image', function( value ) {
		value.bind( function( to ) {
			$( 'body' ).toggleClass( 'custom-background-image', '' !== to );
		} );
	} );
	// Color Scheme CSS.
	api.bind( 'preview-ready', function() {
		api.preview.bind( 'update-color-scheme-css', function( css ) {
			style.html( css );
		} );
	} );
	api('header_class',function( value ){
		value.bind( function( to ) {
			$('.site-header-main').attr( 'class' , 'site-header-main ' + to );
		} );	
	} );
	api('main_nav_class',function( value ){
		value.bind( function( to ) {
			$('.main-navigation').attr( 'class' , 'main-navigation ' + to );
		} );	
	} );
	api('main_wrapper_class',function( value ){
		value.bind( function( to ) {
			$('.site-content').attr( 'class' , 'site-content ' + to );
		} );	
	} );
	api('content_wrapper_class',function( value ){
		value.bind( function( to ) {
			$('.content-area').attr( 'class' , 'content-area ' + to );
		} );	
	} );
	api('primary_class',function( value ){
		value.bind( function( to ) {
			$('.site-main').attr( 'class' , 'site-main ' + to );
		} );	
	} );
	api('secondary_class',function( value ){
		value.bind( function( to ) {
			$('.sidebar.widget-area').attr( 'class' , 'sidebar widget-area ' + to );
		} );	
	} );
	api('sidebar_area_1_class',function( value ){
		value.bind( function( to ) {
			$('.widget-area-1').attr( 'class' , 'widget-area-1 ' + to );
		} );	
	} );
	api('sidebar_area_2_class',function( value ){
		value.bind( function( to ) {
			$('.widget-area-2').attr( 'class' , 'widget-area-2 ' + to );
		} );	
	} );
	api('footer_class',function( value ){
		value.bind( function( to ) {
			$('.site-footer').attr( 'class' , 'site-footer ' + to );
		} );	
	} );
} )( jQuery );
