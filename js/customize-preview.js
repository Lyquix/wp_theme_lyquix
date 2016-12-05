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
} )( jQuery );
