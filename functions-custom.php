<?php 

add_action( 'customize_register', 'lqx_load_customize_controls', 0 );

function lqx_load_customize_controls() {
	
    require get_template_directory()  . '/php/control-checkbox-multiple.php';
	
}

?>