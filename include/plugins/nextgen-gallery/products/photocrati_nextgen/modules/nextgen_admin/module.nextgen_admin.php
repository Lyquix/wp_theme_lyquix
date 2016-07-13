<?php

/***
{
	Module:	photocrati-nextgen_admin
}
***/

define('NGG_FS_ACCESS_SLUG', 'ngg_fs_access');

class M_NextGen_Admin extends C_Base_Module
{
	/**
	 * Defines the module
	 */
	function define()
	{
		parent::define(
			'photocrati-nextgen_admin',
			'NextGEN Administration',
			'Provides a framework for adding Administration pages',
			'0.9',
			'https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/',
			'Photocrati Media',
			'https://www.imagely.com'
		);

		C_Photocrati_Installer::add_handler($this->module_id, 'C_NextGen_Admin_Installer');

		C_NextGen_Settings::get_instance()->add_option_handler('C_NextGen_Admin_Option_Handler', array(
			'jquery_ui_theme',
			'jquery_ui_theme_version',
			'jquery_ui_theme_url'
		));
        if (is_multisite()) C_NextGen_Global_Settings::get_instance()->add_option_handler('C_NextGen_Admin_Option_Handler', array(
            'jquery_ui_theme',
            'jquery_ui_theme_version',
            'jquery_ui_theme_url'
        ));
	}

	/**
	 * Register utilities necessary for this module (and the plugin)
	 */
	function _register_utilities()
	{
        // Provides a NextGEN Administation page
        $this->get_registry()->add_utility(
            'I_NextGen_Admin_Page',
            'C_NextGen_Admin_Page_Controller'
        );

        $this->get_registry()->add_utility(
            'I_Page_Manager',
            'C_Page_Manager'
        );

        // Provides a form manager
        $this->get_registry()->add_utility(
            'I_Form_Manager',
            'C_Form_Manager'
        );

        // Provides a form
        $this->get_registry()->add_utility(
            'I_Form',
            'C_Form'
        );
	}

	/**
	 * Registers adapters required by this module
	 */
	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_MVC_Controller',
			'A_MVC_Validation'
		);

        if (is_admin()) {
            $this->get_registry('I_NextGen_Admin_Page', 'A_Fs_Access_Page', NGG_FS_ACCESS_SLUG);
            $this->get_registry()->add_adapter(
                'I_Page_Manager',
                'A_NextGen_Admin_Default_Pages'
            );
        }
	}

	/**
	 * Hooks into the WordPress Framework
	 */
	function _register_hooks()
	{
        // Register scripts
        add_action('init', array(&$this, 'register_scripts'), 9);

		// Provides menu options for managing NextGEN Settings
		add_action('admin_menu', array(&$this, 'add_menu_pages'), 999);

        // Define routes
        add_action('ngg_routes', array(&$this, 'define_routes'));

		// Provides admin notices
		$notices = C_Admin_Notification_Manager::get_instance();
		add_action('init', array($notices, 'serve_ajax_request'));
		add_action('admin_footer', array($notices, 'enqueue_scripts'));
		add_action('all_admin_notices', array($notices, 'render'));
	}

    function define_routes($router)
    {
        // TODO: Why is this in the nextgen-admin module? Shouldn't it be in the other options module?
        $router->create_app('/nextgen-settings')
            ->route('/update_watermark_preview', 'I_Settings_Manager_Controller#watermark_update');
    }

    function register_scripts()
    {
        $router = C_Router::get_instance();
        wp_register_script(
	        'gritter',
	        $router->get_static_url('photocrati-nextgen_admin#gritter/gritter.min.js'),
	        array('jquery'),
	        NGG_SCRIPT_VERSION
        );
        wp_register_style(
	        'gritter',
	        $router->get_static_url('photocrati-nextgen_admin#gritter/css/gritter.css'),
	        FALSE,
	        NGG_SCRIPT_VERSION
        );
        wp_register_script(
	        'ngg_progressbar',
	        $router->get_static_url('photocrati-nextgen_admin#ngg_progressbar.js'),
	        array('gritter'),
	        NGG_SCRIPT_VERSION
        );
        wp_register_style(
	        'ngg_progressbar',
	        $router->get_static_url('photocrati-nextgen_admin#ngg_progressbar.css'),
	        array('gritter'),
	        NGG_SCRIPT_VERSION
        );
        wp_register_style(
	        'ngg_select2',
	        $router->get_static_url('photocrati-nextgen_admin#select2/select2.css'),
	        FALSE,
	        NGG_SCRIPT_VERSION
        );
        wp_register_script(
	        'ngg_select2',
	        $router->get_static_url('photocrati-nextgen_admin#select2/select2.modded.js'),
	        FALSE,
	        NGG_SCRIPT_VERSION
        );
        wp_register_script(
            'jquery.nextgen_radio_toggle',
            $router->get_static_url('photocrati-nextgen_admin#jquery.nextgen_radio_toggle.js'),
            array('jquery'),
	        NGG_SCRIPT_VERSION
        );

        if (preg_match("#/wp-admin/post(-new)?.php#", $_SERVER['REQUEST_URI']))
        {
            wp_enqueue_script('ngg_progressbar');
            wp_enqueue_style('ngg_progressbar');
        }

        wp_register_style(
	        'ngg-jquery-ui',
	        $router->get_static_url('photocrati-nextgen_admin#jquery-ui/jquery-ui-1.10.4.custom.css'),
	        FALSE,
	        NGG_SCRIPT_VERSION
        );
    }

	/**
	 * Adds menu pages to manage NextGen Settings
	 * @uses action: admin_menu
	 */
	function add_menu_pages()
	{
		C_Page_Manager::get_instance()->setup();
	}

    function get_type_list()
    {
        return array(
            'A_Fs_Access_Page' => 'adapter.fs_access_page.php',
            'A_MVC_Validation' => 'adapter.mvc_validation.php',
            'C_Nextgen_Admin_Installer' => 'class.nextgen_admin_installer.php',
            'A_Nextgen_Admin_Default_Pages' => 'adapter.nextgen_admin_default_pages.php',
            'A_Nextgen_Settings_Routes' => 'adapter.nextgen_settings_routes.php',
            'C_Form' => 'class.form.php',
            'C_Form_Manager' => 'class.form_manager.php',
            'C_Nextgen_Admin_Page_Controller' => 'class.nextgen_admin_page_controller.php',
            'C_Page_Manager' => 'class.page_manager.php',
	        'C_Admin_Notification_Manager'  =>  'class.admin_notification_manager.php'
        );
    }
}

class C_NextGen_Admin_Installer
{
	function install()
	{
		$settings = C_NextGen_Settings::get_instance();

		// In version 0.2 of this module and earlier, the following values
		// were statically set rather than dynamically using a handler. Therefore, we need
		// to delete those static values
		$module_name = 'photocrati-nextgen_admin';
		$modules = get_option('pope_module_list', array());
		if (!$modules) {
			$modules = $settings->get('pope_module_list', array());
		}

		$cleanup = FALSE;
		foreach ($modules as $module) {
			if (strpos($module, $module_name) !== FALSE) {
				if (version_compare(array_pop(explode('|', $module)), '0.3') == -1) {
					$cleanup = TRUE;
				}
				break;
			}
		}

		if ($cleanup) {
			$keys = array(
				'jquery_ui_theme',
				'jquery_ui_theme_version',
				'jquery_ui_theme_url'
			);
			foreach ($keys as $key) $settings->delete($key);
		}
	}
}

class C_NextGen_Admin_Option_Handler
{
	function get_router()
	{
		return C_Router::get_instance();
	}

	function get($key, $default=NULL)
	{
		$retval = $default;

		switch ($key) {
			case 'jquery_ui_theme':
				$retval = 'jquery-ui-nextgen';
				break;
			case 'jquery_ui_theme_version':
				$retval = '1.8';
				break;
			case 'jquery_ui_theme_url':
				$retval = $this->get_router()->get_static_url('photocrati-nextgen_admin#jquery-ui/jquery-ui-1.10.4.custom.css');
				break;
		}

		return $retval;
	}
}

new M_NextGen_Admin();
