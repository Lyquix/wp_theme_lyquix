<?php

/***
 {
	Module: photocrati-dynamic_thumbnails
 }
 ***/
class M_Dynamic_Thumbnails extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-dynamic_thumbnails',
			'Dynamic Thumbnails',
			'Adds support for dynamic thumbnails',
			'0.6',
			'https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/',
			'Photocrati Media',
			'https://www.imagely.com'
		);

		C_Photocrati_Installer::add_handler($this->module_id, 'C_Dynamic_Thumbnails_Installer');
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter('I_GalleryStorage_Driver', 'A_Dynamic_Thumbnails_Storage_Driver');
	}

	function _register_utilities()
	{
        $this->get_registry()->add_utility('I_Dynamic_Thumbnails_Manager', 'C_Dynamic_Thumbnails_Manager');
		if (apply_filters('ngg_load_frontend_logic', TRUE, $this->module_id))
            $this->get_registry()->add_utility('I_Dynamic_Thumbnails_Controller', 'C_Dynamic_Thumbnails_Controller');
	}

    function _register_hooks()
    {
        add_action('ngg_routes', array(&$this, 'define_routes'));
    }

    function define_routes($router)
    {
        $app = $router->create_app('/nextgen-image');

        // The C_Dynamic_Thumbnails Controller was created before the new
        // router implementation was conceptualized. It uses it's own mechanism
        // to parse the REQUEST_URI. It should be refactored to use the router's
        // parameter mechanism, but for now - we'll just removed the segments
        // from the router's visibility, and let the Dynamic Thumbnails Controller
        // do it's own parsing
        $app->rewrite('/{*}', '/');
        $app->route('/', 'I_Dynamic_Thumbnails_Controller#index');
    }

    function get_type_list()
    {
        return array(
            'A_Dynamic_Thumbnails_Storage_Driver'	=> 'adapter.dynamic_thumbnails_storage_driver.php',
            'C_Dynamic_Thumbnails_Installer'		=> 'class.dynamic_thumbnails_installer.php',
            'C_Dynamic_Thumbnails_Controller' 		=> 'class.dynamic_thumbnails_controller.php',
            'C_Dynamic_Thumbnails_Manager' 			=> 'class.dynamic_thumbnails_manager.php'
        );
    }
}

class C_Dynamic_Thumbnails_Installer
{
	function __construct()
	{
		$this->settings = C_NextGen_Settings::get_instance();
	}

	function install()
	{
		$this->settings->set_default_value('dynamic_thumbnail_slug', 'nextgen-image');
	}
}

new M_Dynamic_Thumbnails();
