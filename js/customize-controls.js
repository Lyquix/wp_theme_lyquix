jQuery( document ).ready( function() {
    /* === Checkbox Multiple Control === */
    jQuery( 'input[type="checkbox"]' ).live('click',function() {
    		jQuery(this).checked == true;
            checkbox_values = jQuery( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map(
                function() {
                    return this.value;
                }
            ).get().join( ',' );

            jQuery( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( checkbox_values ).trigger( 'change' );
        }
    );

} ); // jQuery( document ).ready