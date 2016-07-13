<?php
if ( !class_exists('GP_Janus') ):

	class GP_Janus
	{		
		function __construct()
		{
			$this->add_hooks();
		}
				
		function add_hooks()
		{
			add_action( 'admin_enqueue_scripts',  array( $this, 'register_admin_scripts' ) );
			//add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'customize_controls_enqueue_scripts',  array( $this, 'enqueue_admin_scripts' ) );
			
			// go ahead and enqueue Janus on edit pages 
			global $pagenow;
			if( is_admin() )
			{
				add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_admin_scripts' ) );
			}
		}
		
		/* 
		 * Register (but not enqueue) scripts and styles needed to display the popups and media buttons
		 */
		function register_admin_scripts()
		{
			global $pagenow;
			// register the Janus JS & CSS, but only enqueue it later, when/if we see a form that needs it
			wp_register_script(
					'gold-plugins-janus',
					plugins_url('js/gp-janus_v1.js', __FILE__),
					array( 'jquery' , 'jquery-ui-core' , 'jquery-ui-dialog' )
			);	
			
			wp_register_style(
					'gold-plugins-janus',
					plugins_url('css/gp-janus_v1.css', __FILE__)
			);			
		}		
		
		function enqueue_admin_scripts()
		{
			wp_enqueue_script( 'gold-plugins-janus' );
			wp_enqueue_style( 'gold-plugins-janus' );			
		}
					

	} // end class Gold_Plugins_Media_Button
	
endif; //class_exists