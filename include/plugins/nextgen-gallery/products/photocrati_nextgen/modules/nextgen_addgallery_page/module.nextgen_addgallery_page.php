<?php
/**
{
    Module: photocrati-nextgen_addgallery_page
}
**/

define('NGG_ADD_GALLERY_SLUG', 'ngg_addgallery');

class M_NextGen_AddGallery_Page extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_addgallery_page',
            'NextGEN Add Gallery Page',
            'Provides admin page for adding a gallery and uploading images',
            '0.6',
            'https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/',
            'Photocrati Media',
            'https://www.imagely.com'
        );
    }

    function initialize()
    {
        $forms = C_Form_Manager::get_instance();
        $settings = C_NextGen_Settings::get_instance();
        $forms->add_form(NGG_ADD_GALLERY_SLUG, 'upload_images');
        if (!is_multisite() || (is_multisite() && $settings->get('wpmuImportFolder')))
        {
            $forms->add_form(NGG_ADD_GALLERY_SLUG, 'import_media_library');
            $forms->add_form(NGG_ADD_GALLERY_SLUG, 'import_folder');
        }
    }
    
    function get_type_list()
    {
    	return array(
            'A_Import_Media_Library_Form' => 'adapter.import_media_library_form.php',
    		'A_Import_Folder_Form' => 'adapter.import_folder_form.php',
    		'A_Nextgen_Addgallery_Ajax' => 'adapter.nextgen_addgallery_ajax.php',
    		'A_Nextgen_Addgallery_Controller' => 'adapter.nextgen_addgallery_controller.php',
    		'A_Nextgen_Addgallery_Pages' => 'adapter.nextgen_addgallery_pages.php',
    		'A_Upload_Images_Form' => 'adapter.upload_images_form.php',
    	);
    }

    function _register_adapters()
    {
        // AJAX operations aren't admin requests
        $this->get_registry()->add_adapter('I_Ajax_Controller', 'A_NextGen_AddGallery_Ajax');

        if (is_admin()) {
            $this->get_registry()->add_adapter('I_Page_Manager', 'A_NextGen_AddGallery_Pages');
            $this->get_registry()->add_adapter('I_NextGen_Admin_Page', 'A_NextGen_AddGallery_Controller', NGG_ADD_GALLERY_SLUG);
            $this->get_registry()->add_adapter('I_Form', 'A_Upload_Images_Form', 'upload_images');
            if (!is_multisite() || (is_multisite() && C_NextGen_Settings::get_instance()->get('wpmuImportFolder')))
            {
                $this->get_registry()->add_adapter('I_Form', 'A_Import_Folder_Form', 'import_folder');
                $this->get_registry()->add_adapter('I_Form', 'A_Import_Media_Library_Form', 'import_media_library');
            }
        }
    }

    function _register_hooks()
    {
        add_action('admin_init', array(&$this, 'register_scripts'));
        add_filter('ngg_non_minified_files', array($this, 'do_not_minify'), 10, 2);
    }

    // plupload i18n JS should not be minified
    function do_not_minify($path, $module)
    {
        $retval = FALSE;
        if ($module == 'photocrati-nextgen_addgallery_page' && strpos($path, '/i18n/') !== FALSE)
            $retval = TRUE;
        return $retval;
    }

    function register_scripts()
    {
        if (is_admin())
        {
            $router = C_Router::get_instance();
            wp_register_script(
                'browserplus',
                $router->get_static_url('photocrati-nextgen_addgallery_page#browserplus-2.4.21.min.js'),
                FALSE,
                NGG_SCRIPT_VERSION
            );
            wp_register_script(
                'ngg.plupload.moxie',
                $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/moxie.min.js'),
                FALSE,
                NGG_SCRIPT_VERSION
            );
            wp_register_script(
                'ngg.plupload.full',
                $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/plupload.dev.js'),
                array('ngg.plupload.moxie'),
                NGG_SCRIPT_VERSION
            );
            wp_register_script(
                'ngg.plupload.queue',
                $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/jquery.plupload.queue/jquery.plupload.queue.min.js'),
                array('ngg.plupload.full'),
                NGG_SCRIPT_VERSION
            );
            wp_register_style(
                'ngg.plupload.queue',
                $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/jquery.plupload.queue/css/jquery.plupload.queue.css'),
                FALSE,
                NGG_SCRIPT_VERSION
            );
            wp_register_style(
                'nextgen_addgallery_page',
                $router->get_static_url('photocrati-nextgen_addgallery_page#styles.css'),
                FALSE,
                NGG_SCRIPT_VERSION
            );
            wp_register_script(
                'jquery.filetree',
                $router->get_static_url('photocrati-nextgen_addgallery_page#jquery.filetree/jquery.filetree.js'),
                array('jquery'),
                NGG_SCRIPT_VERSION
            );
            wp_register_style(
                'jquery.filetree',
                $router->get_static_url('photocrati-nextgen_addgallery_page#jquery.filetree/jquery.filetree.css'),
                FALSE,
                NGG_SCRIPT_VERSION
            );
            wp_register_script(
                'nextgen_media_library_import-js',
                $router->get_static_url('photocrati-nextgen_addgallery_page#media-library-import.js'),
                array('jquery', 'ngg_progressbar'),
                NGG_SCRIPT_VERSION
            );
            wp_register_style(
                'nextgen_media_library_import-css',
                $router->get_static_url('photocrati-nextgen_addgallery_page#media-library-import.css'),
                FALSE,
                NGG_SCRIPT_VERSION
            );
        }
    }
}
new M_NextGen_AddGallery_Page();
