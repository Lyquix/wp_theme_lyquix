<?php

/***
	{
		Module: photocrati-nextgen_gallery_display
	}
***/

define('NGG_DISPLAY_SETTINGS_SLUG', 'ngg_display_settings');
define('NGG_DISPLAY_PRIORITY_BASE', 10000);
define('NGG_DISPLAY_PRIORITY_STEP', 2000);
if (!defined('NGG_RENDERING_CACHE_TTL')) define('NGG_RENDERING_CACHE_TTL', PHOTOCRATI_CACHE_TTL);
if (!defined('NGG_DISPLAYED_GALLERY_CACHE_TTL')) define('NGG_DISPLAYED_GALLERY_CACHE_TTL', PHOTOCRATI_CACHE_TTL);
if (!defined('NGG_RENDERING_CACHE_ENABLED')) define('NGG_RENDERING_CACHE_ENABLED', PHOTOCRATI_CACHE);
if (!defined('NGG_SHOW_DISPLAYED_GALLERY_ERRORS')) define('NGG_SHOW_DISPLAYED_GALLERY_ERRORS', NGG_DEBUG);

class M_Gallery_Display extends C_Base_Module
{
	function define()
	{
		parent::define(
			'photocrati-nextgen_gallery_display',
			'Gallery Display',
			'Provides the ability to display gallery of images',
			'0.13',
			'https://www.imagely.com',
			'Photocrati Media',
			'https://www.imagely.com'
		);

		C_Photocrati_Installer::add_handler($this->module_id, 'C_Display_Type_Installer');
	}


	/**
	 * Register utilities required for this module
	 */
	function _register_utilities()
	{
        // Register frontend-only components
        if (apply_filters('ngg_load_frontend_logic', TRUE, $this->module_id))
        {
            // This utility provides a controller to render the settings form
            // for a display type, or render the front-end of a display type
            $this->get_registry()->add_utility(
                'I_Display_Type_Controller',
                'C_Display_Type_Controller'
            );

            // This utility provides the capabilities of rendering a display type
            $this->get_registry()->add_utility(
                'I_Displayed_Gallery_Renderer',
                'C_Displayed_Gallery_Renderer'
            );
        }

		// This utility provides a datamapper for Display Types
		$this->get_registry()->add_utility(
			'I_Display_Type_Mapper',
			'C_Display_Type_Mapper'
		);

		// This utility provides a datamapper for Displayed Galleries. A
		// displayed gallery is the association between some entities (images
		//or galleries) and a display type
		$this->get_registry()->add_utility(
			'I_Displayed_Gallery_Mapper',
			'C_Displayed_Gallery_Mapper'
		);
	}

	/**
	 * Registers adapters required for this module
	 */
	function _register_adapters()
	{
		// Provides factory methods for creating display type and
		// displayed gallery instances
		$this->get_registry()->add_adapter(
			'I_Component_Factory', 'A_Gallery_Display_Factory'
		);

        if (is_admin()) {
            $this->get_registry()->add_adapter(
                'I_Page_Manager',
                'A_Display_Settings_Page'
            );

            $this->get_registry()->add_adapter(
                'I_NextGen_Admin_Page',
                'A_Display_Settings_Controller',
                NGG_DISPLAY_SETTINGS_SLUG
            );
        }

        // Frontend-only components
        if (apply_filters('ngg_load_frontend_logic', TRUE, $this->module_id))
        {
            $this->get_registry()->add_adapter('I_MVC_View', 'A_Gallery_Display_View');
            $this->get_registry()->add_adapter('I_MVC_View', 'A_Displayed_Gallery_Trigger_Element');
            $this->get_registry()->add_adapter('I_Display_Type_Controller', 'A_Displayed_Gallery_Trigger_Resources');
        }
	}

	/**
	 * Registers hooks for the WordPress framework
	 */
	function _register_hooks()
	{
        if (apply_filters('ngg_load_frontend_logic', TRUE, $this->module_id))
        {
            C_NextGen_Shortcode_Manager::add('ngg_images', array(&$this, 'display_images'));
            add_action('wp_enqueue_scripts', array(&$this, 'no_resources_mode'), PHP_INT_MAX-1);
            add_filter('the_content', array($this, '_render_related_images'));
        }

        add_action('init', array(&$this, 'register_resources'), 12);
        add_action('admin_bar_menu', array(&$this, 'add_admin_bar_menu'), 100);
		add_filter('run_ngg_resource_manager', array(&$this, 'no_resources_mode'));
        add_action('init', array(&$this, 'serve_fontawesome'), 15);

        // Add hook to delete displayed galleries when removed from a post
        add_action('pre_post_update', array(&$this, 'locate_stale_displayed_galleries'));
        add_action('before_delete_post', array(&$this, 'locate_stale_displayed_galleries'));
        add_action('post_updated',	array(&$this, 'cleanup_displayed_galleries'));
        add_action('after_delete_post', array(&$this, 'cleanup_displayed_galleries'));

        add_action('wp_print_styles', array($this, 'fix_nextgen_custom_css_order'), PHP_INT_MAX-1);
	}

    /**
     * This moves the NextGen custom CSS to the last of the queue
     */
    function fix_nextgen_custom_css_order()
    {
        global $wp_styles;
        if (in_array('nggallery', $wp_styles->queue))
        {
            foreach ($wp_styles->queue as $ndx => $style) {
                if ($style == 'nggallery')
                {
                    unset($wp_styles->queue[$ndx]);
                    $wp_styles->queue[] = 'nggallery';
                    break;
                }
            }
        }
    }

    /**
     * Locates the ids of displayed galleries that have been
     * removed from the post, and flags then for cleanup (deletion)
     * @global array $displayed_galleries_to_cleanup
     * @param int $post_id
     */
    function locate_stale_displayed_galleries($post_id)
    {
        global $displayed_galleries_to_cleanup;
        $displayed_galleries_to_cleanup	= array();
        $post							= get_post($post_id);
        $gallery_preview_url			= C_NextGen_Settings::get_instance()->get('gallery_preview_url');
        $preview_url = preg_quote($gallery_preview_url, '#');
        if (preg_match_all("#{$preview_url}/id--(\d+)#", html_entity_decode($post->post_content), $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $preview_url = preg_quote($match[0], '/');
                // The post was edited, and the displayed gallery placeholder was removed
                if (isset($_REQUEST['post_content']) && (!preg_match("/{$preview_url}/", $_POST['post_content']))) {
                    $displayed_galleries_to_cleanup[] = intval($match[1]);
                }
                // The post was deleted
                elseif (!isset($_REQUEST['action'])) {
                    $displayed_galleries_to_cleanup[] = intval($match[1]);
                }
            }
        }
    }

    /**
     * Deletes any displayed galleries that are no longer associated with a post/page
     *
     * @global array $displayed_galleries_to_cleanup
     * @param int $post_id
     */
    function cleanup_displayed_galleries($post_id)
    {
	    if (!apply_filters('ngg_cleanup_displayed_galleries', true, $post_id))
		    return;

        global $displayed_galleries_to_cleanup;
        $mapper = C_Displayed_Gallery_Mapper::get_instance();
        foreach ($displayed_galleries_to_cleanup as $id) {
	        $mapper->destroy($id);
        }
    }

    /**
     * Serves the fontawesome woff file via PHP. We do this, as IIS won't serve .woff files.
     * @throws E_Clean_Exit
     */
    function serve_fontawesome()
    {
        if (isset($_REQUEST['ngg_serve_fontawesome_woff'])) {
            $fs = C_Fs::get_instance();
            $abspath = $fs->find_static_abspath('photocrati-nextgen_gallery_display#fonts/fontawesome-webfont.woff');
            if ($abspath) {
                header("Content-Type: application/x-font-woff");
                readfile($abspath);
                throw new E_Clean_Exit();
            }
        }
        elseif (isset($_REQUEST['ngg_serve_fontawesome_css'])) {
            $fs = C_Fs::get_instance();
            $abspath = $fs->find_static_abspath('photocrati-nextgen_gallery_display#fontawesome/font-awesome.css');
            if ($abspath) {
	            $router = C_Router::get_instance();
                $file_content = file_get_contents($abspath);
	            $file_content = str_replace('../fonts/fontawesome-webfont.eot',   $router->get_static_url($this->module_id . '#fonts/fontawesome-webfont.eot'),   $file_content);
	            $file_content = str_replace('../fonts/fontawesome-webfont.svg',   $router->get_static_url($this->module_id . '#fonts/fontawesome-webfont.svg'),   $file_content);
	            $file_content = str_replace('../fonts/fontawesome-webfont.ttf',   $router->get_static_url($this->module_id . '#fonts/fontawesome-webfont.ttf'),   $file_content);
	            $file_content = str_replace('../fonts/fontawesome-webfont.woff2', $router->get_static_url($this->module_id . '#fonts/fontawesome-webfont.woff2'), $file_content);
	            $file_content = str_replace('../fonts/fontawesome-webfont.woff', site_url('/?ngg_serve_fontawesome_woff=1'), $file_content);
                header('Content-Type: text/css');
                echo $file_content;
                throw new E_Clean_Exit();
            }
        }
    }

    static function enqueue_fontawesome()
    {
        if (!wp_style_is('fontawesome', 'registered'))
        {
            if (strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'microsoft-iis') !== FALSE) {
                wp_register_style('fontawesome', site_url('/?ngg_serve_fontawesome_css=1'), FALSE, NGG_SCRIPT_VERSION);
            } else {
                $router = C_Router::get_instance();
                wp_register_style(
	                'fontawesome',
	                $router->get_static_url('photocrati-nextgen_gallery_display#fontawesome/font-awesome.css'),
	                FALSE,
	                NGG_SCRIPT_VERSION
                );
            }
        }

        wp_enqueue_style('fontawesome');
    }

	function no_resources_mode($valid_request=TRUE)
	{
		if (isset($_REQUEST['ngg_no_resources'])) {
			global $wp_scripts, $wp_styles;

			// Don't enqueue any stylesheets
			if ($wp_scripts)
				$wp_scripts->queue = $wp_styles->queue = array();

			// Don't run the resource manager
			$valid_request = FALSE;
		}

		return $valid_request;
	}

  static function _render_related_string($sluglist=array(), $maxImages=NULL, $type=NULL)
  {
      $settings = C_NextGen_Settings::get_instance();
      if (is_null($type)) $type = $settings->appendType;
	  if (is_null($maxImages)) $maxImages = $settings->maxImages;

	  if (!$sluglist) {
		  switch ($type) {
			  case 'tags':
				  if (function_exists('get_the_tags'))
				  {
					  $taglist = get_the_tags();
					  if (is_array($taglist)) {
						  foreach ($taglist as $tag) {
							  $sluglist[] = $tag->slug;
						  }
					  }
				  }
				  break;
			  case 'category':
				  $catlist = get_the_category();
				  if (is_array($catlist))
				  {
					  foreach ($catlist as $cat) {
						  $sluglist[] = $cat->category_nicename;
					  }
				  }
				  break;
		  }
	  }

      $taglist = implode(',', $sluglist);

      if ($taglist === 'uncategorized' || empty($taglist))
          return;

      $renderer = C_Displayed_Gallery_Renderer::get_instance();
      $view     = C_Component_Factory::get_instance()->create('mvc_view', '');
      $retval = $renderer->display_images(array(
          'source' => 'tags',
          'container_ids' => $taglist,
          'display_type' => NGG_BASIC_THUMBNAILS,
          'images_per_page' => $maxImages,
          'maximum_entity_count' => $maxImages,
          'template' => $view->get_template_abspath('photocrati-nextgen_gallery_display#related'),
          'show_all_in_lightbox' => FALSE,
          'show_slideshow_link' => FALSE,
          'disable_pagination' => TRUE,
          'display_no_images_error' => FALSE
      ));

      if ($retval) wp_enqueue_style('nextgen_gallery_related_images');

      return apply_filters('ngg_show_related_gallery_content', $retval, $taglist);
  }

	function _render_related_images($content)
	{
    $settings = C_NextGen_Settings::get_instance();
      
		if ($settings->get('activateTags')) {
			$related = self::_render_related_string();
			
			if ($related != null) {
		    $heading = $settings->relatedHeading;
				$content .= $heading . $related;
			}
		}
		
		return $content;
	}

    /**
     * Adds menu item to the admin bar
     */
    function add_admin_bar_menu()
    {
        global $wp_admin_bar;

        if ( current_user_can('NextGEN Change options') ) {
            $wp_admin_bar->add_menu(array(
                'parent' => 'ngg-menu',
                'id' => 'ngg-menu-display_settings',
                'title' => __('Gallery Settings', 'nggallery'),
                'href' => admin_url('admin.php?page=ngg_display_settings')
            ));
        }
    }

    /**
     * Registers our static settings resources so the ATP module can find them later
     */
    function register_resources()
    {
		// Register custom post types for compatibility
        $types = array(
			'displayed_gallery'		=>	'NextGEN Gallery - Displayed Gallery',
			'display_type'			=>	'NextGEN Gallery - Display Type',
			'gal_display_source'	=>	'NextGEN Gallery - Displayed Gallery Source'
		);
		foreach ($types as $type => $label) {
			register_post_type($type, array(
				'label'		=>	$label,
				'publicly_queryable'	=>	FALSE,
				'exclude_from_search'	=>	TRUE,
			));
		}
		$router = C_Router::get_instance();

        wp_register_script(
            'nextgen_gallery_display_settings',
            $router->get_static_url('photocrati-nextgen_gallery_display#nextgen_gallery_display_settings.js'),
            array('jquery-ui-accordion', 'jquery-ui-tooltip'),
	        NGG_SCRIPT_VERSION
        );

        wp_register_style(
            'nextgen_gallery_display_settings',
            $router->get_static_url('photocrati-nextgen_gallery_display#nextgen_gallery_display_settings.css'),
	        FALSE,
	        NGG_SCRIPT_VERSION
        );

        if (apply_filters('ngg_load_frontend_logic', TRUE, $this->module_id))
        {
            wp_register_style(
                'nextgen_gallery_related_images',
                $router->get_static_url('photocrati-nextgen_gallery_display#nextgen_gallery_related_images.css'),
	            FALSE,
	            NGG_SCRIPT_VERSION
            );
            wp_register_script(
	            'ngg_common',
	            $router->get_static_url('photocrati-nextgen_gallery_display#common.js'),
	            array('jquery', 'photocrati_ajax'),
	            NGG_SCRIPT_VERSION,
	            TRUE
            );
            wp_register_style(
	            'ngg_trigger_buttons',
	            $router->get_static_url('photocrati-nextgen_gallery_display#trigger_buttons.css'),
	            FALSE,
	            NGG_SCRIPT_VERSION
            );
        }
    }


	/**
	 * Adds the display settings page to wp-admin
	 */
	function add_display_settings_page()
	{
		add_submenu_page(
			NGGFOLDER,
			__('NextGEN Gallery & Album Settings', 'nggallery'),
			__('Gallery Settings', 'nggallery'),
			'NextGEN Change options',
			NGG_DISPLAY_SETTINGS_SLUG,
			array(&$this->controller, 'index_action')
		);
	}

	/**
	 * Provides the [display_images] shortcode
	 * @param array $params
	 * @param string $inner_content
	 * @return string
	 */
	function display_images($params, $inner_content=NULL)
	{
		$renderer = C_Displayed_Gallery_Renderer::get_instance();
		return $renderer->display_images($params, $inner_content);
	}

    /**
     * Gets a value from the parameter array, and if not available, uses the default value
     *
     * @param string $name
     * @param mixed $default
     * @param array $params
     * @return mixed
     */
    function _get_param($name, $default, $params)
    {
        return (isset($params[$name])) ? $params[$name] : $default;
    }

    function get_type_list()
    {
        return array(
            'C_Displayed_Gallery_Trigger'           => 'class.displayed_gallery_trigger.php',
            'C_Displayed_Gallery_Trigger_Manager'   =>  'class.displayed_gallery_trigger_manager.php',
            'A_Displayed_Gallery_Trigger_Element'   =>  'adapter.displayed_gallery_trigger_element.php',
            'A_Displayed_Gallery_Trigger_Resources' =>  'adapter.displayed_gallery_trigger_resources.php',
            'A_Display_Settings_Controller' => 'adapter.display_settings_controller.php',
            'A_Display_Settings_Page' 		=> 'adapter.display_settings_page.php',
            'A_Gallery_Display_Factory' 	=> 'adapter.gallery_display_factory.php',
            'C_Display_Type_Installer' 	=> 'class.gallery_display_installer.php',
            'A_Gallery_Display_View' 		=> 'adapter.gallery_display_view.php',
            'C_Displayed_Gallery' 			=> 'class.displayed_gallery.php',
            'C_Displayed_Gallery_Mapper' 	=> 'class.displayed_gallery_mapper.php',
            'C_Displayed_Gallery_Renderer' 	=> 'class.displayed_gallery_renderer.php',
            'C_Displayed_Gallery_Source_Manager'    =>  'class.displayed_gallery_source_manager.php',
            'C_Display_Type' 				=> 'class.display_type.php',
            'C_Display_Type_Controller' 	=> 'class.display_type_controller.php',
            'C_Display_Type_Mapper' 		=> 'class.display_type_mapper.php',
            'Hook_Propagate_Thumbnail_Dimensions_To_Settings' => 'hook.propagate_thumbnail_dimensions_to_settings.php',
            'Mixin_Display_Type_Form' 		=> 'mixin.display_type_form.php'
        );
    }
}

class C_Display_Type_Installer
{
	function get_registry()
	{
		return C_Component_Registry::get_instance();
	}

	/**
	 * Installs a display type
	 * @param string $name
	 * @param array $properties
	 */
	function install_display_type($name, $properties=array())
	{
		// Try to find the existing entity. If it doesn't exist, we'll create
		$fs					= C_Fs::get_instance();
		$mapper				= C_Display_Type_Mapper::get_instance();
		$display_type		= $mapper->find_by_name($name);
		if (!$display_type)	$display_type = new stdClass;

		// Update the properties of the display type
		$properties['name'] = $name;
		$properties['installed_at_version'] = NGG_PLUGIN_VERSION;
		foreach ($properties as $key=>$val) {
			if ($key == 'preview_image_relpath') {
				$val = $fs->find_static_abspath($val, FALSE, TRUE);
			}
			$display_type->$key = $val;
		}

		// Save the entity
		$retval = $mapper->save($display_type);
		return $retval;
	}

	/**
	 * Deletes all displayed galleries
	 */
	function uninstall_displayed_galleries()
	{
		$mapper = C_Displayed_Gallery_Mapper::get_instance();
		$mapper->delete()->run_query();
	}

	/**
	 * Uninstalls all display types
	 */
	function uninstall_display_types()
	{
		$mapper = C_Display_Type_Mapper::get_instance();
		$mapper->delete()->run_query();
	}

	/**
	 * Installs displayed gallery sources
	 */
	function install($reset=FALSE)
	{
		// Display types are registered in other modules
	}

	/**
	 * Uninstalls this module
	 */
	function uninstall($hard = FALSE)
	{
		C_Photocrati_Transient_Manager::flush();

		$this->uninstall_display_types();

		// TODO temporary Don't remove galleries on uninstall
		//if ($hard) $this->uninstall_displayed_galleries();
	}
}

/**
 * Show related images for a post/page. Ngglegacy function
 * @param $taglist
 * @param int $maxImages
 */
function nggShowRelatedGallery($taglist, $maxImages = 0)
{
	return M_Gallery_Display::_render_related_string($taglist, $maxImages, $type=NULL);
}

function nggShowRelatedImages($type=NULL, $maxImages=0)
{
	return M_Gallery_Display::_render_related_string(NULL, $maxImages, $type);
}

function the_related_images($type = 'tags', $maxNumbers = 7)
{
	echo nggShowRelatedImages($type, $maxNumbers);
}


new M_Gallery_Display();
