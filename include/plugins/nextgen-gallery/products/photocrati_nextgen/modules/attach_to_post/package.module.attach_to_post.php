<?php
/**
 * Provides AJAX actions for the Attach To Post interface
 * TODO: Need to add authorization checks to each action
 */
class A_Attach_To_Post_Ajax extends Mixin
{
    public $attach_to_post = NULL;
    /**
     * Retrieves the attach to post controller
     */
    public function get_attach_to_post()
    {
        if (is_null($this->attach_to_post)) {
            $this->attach_to_post = C_Attach_Controller::get_instance();
        }
        return $this->attach_to_post;
    }
    /**
     * Returns a list of image sources for the Attach to Post interface
     * @return type
     */
    public function get_attach_to_post_sources_action()
    {
        $response = array();
        if ($this->object->validate_ajax_request('nextgen_edit_displayed_gallery')) {
            $response['sources'] = $this->get_attach_to_post()->get_sources();
        }
        return $response;
    }
    /**
     * Gets existing galleries
     * @return array
     */
    public function get_existing_galleries_action()
    {
        $this->debug = TRUE;
        $response = array();
        if ($this->object->validate_ajax_request('nextgen_edit_displayed_gallery')) {
            $limit = $this->object->param('limit');
            $offset = $this->object->param('offset');
            // We return the total # of galleries, so that the client can make
            // pagination requests
            $mapper = C_Gallery_Mapper::get_instance();
            $response['total'] = $mapper->count();
            $response['limit'] = $limit = $limit ? $limit : 0;
            $response['offset'] = $offset = $offset ? $offset : 0;
            // Get the galleries
            $mapper->select();
            if ($limit) {
                $mapper->limit($limit, $offset);
            }
            $response['items'] = $mapper->run_query();
        } else {
            $response['error'] = 'insufficient access';
        }
        $this->debug = FALSE;
        return $response;
    }
    /**
     * Gets existing albums
     * @return array
     */
    public function get_existing_albums_action()
    {
        $response = array();
        if ($this->object->validate_ajax_request('nextgen_edit_displayed_gallery')) {
            $limit = $this->object->param('limit');
            $offset = $this->object->param('offset');
            // We return the total # of albums, so that the client can make pagination requests
            $mapper = C_Album_Mapper::get_instance();
            $response['total'] = $mapper->count();
            $response['limit'] = $limit = $limit ? $limit : 0;
            $response['offset'] = $offset = $offset ? $offset : 0;
            // Get the albums
            $mapper->select();
            if ($limit) {
                $mapper->limit($limit, $offset);
            }
            $response['items'] = $mapper->run_query();
        }
        return $response;
    }
    /**
     * Gets existing image tags
     * @return array
     */
    public function get_existing_image_tags_action()
    {
        $response = array();
        if ($this->object->validate_ajax_request('nextgen_edit_displayed_gallery')) {
            $limit = $this->object->param('limit');
            $offset = $this->object->param('offset');
            $response['limit'] = $limit = $limit ? $limit : 0;
            $response['offset'] = $offset = $offset ? $offset : 0;
            $response['items'] = array();
            $params = array('number' => $limit, 'offset' => $offset, 'fields' => 'names');
            foreach (get_terms('ngg_tag', $params) as $term) {
                $response['items'][] = array('id' => $term, 'title' => $term, 'name' => $term);
            }
            $response['total'] = count(get_terms('ngg_tag', array('fields' => 'ids')));
        }
        return $response;
    }
    /**
     * Gets entities (such as images) for a displayed gallery (attached gallery)
     */
    public function get_displayed_gallery_entities_action()
    {
        $response = array();
        if ($this->object->validate_ajax_request('nextgen_edit_displayed_gallery') && ($params = $this->object->param('displayed_gallery'))) {
            $limit = $this->object->param('limit');
            $offset = $this->object->param('offset');
            $factory = C_Component_Factory::get_instance();
            $displayed_gallery = $factory->create('displayed_gallery');
            foreach ($params as $key => $value) {
                $displayed_gallery->{$key} = $value;
            }
            $response['limit'] = $limit = $limit ? $limit : 0;
            $response['offset'] = $offset = $offset ? $offset : 0;
            $response['total'] = $displayed_gallery->get_entity_count('both');
            $response['items'] = $displayed_gallery->get_entities($limit, $offset, FALSE, 'both');
            $controller = C_Display_Type_Controller::get_instance();
            $storage = C_Gallery_Storage::get_instance();
            $image_mapper = C_Image_Mapper::get_instance();
            $settings = C_NextGen_Settings::get_instance();
            foreach ($response['items'] as &$entity) {
                $image = $entity;
                if (in_array($displayed_gallery->source, array('album', 'albums'))) {
                    // Set the alttext of the preview image to the
                    // name of the gallery or album
                    if ($image = $image_mapper->find($entity->previewpic)) {
                        if ($entity->is_album) {
                            $image->alttext = sprintf(__('Album: %s', 'nggallery'), $entity->name);
                        } else {
                            $image->alttext = sprintf(__('Gallery: %s', 'nggallery'), $entity->title);
                        }
                    }
                    // Prefix the id of an album with 'a'
                    if ($entity->is_album) {
                        $id = $entity->{$entity->id_field};
                        $entity->{$entity->id_field} = 'a' . $id;
                    }
                }
                // Get the thumbnail
                $entity->thumb_url = $storage->get_image_url($image, 'thumb', TRUE);
                $entity->thumb_html = $storage->get_image_html($image, 'thumb');
                $entity->max_width = $settings->thumbwidth;
                $entity->max_height = $settings->thumbheight;
            }
        } else {
            $response['error'] = __('Missing parameters', 'nggallery');
        }
        return $response;
    }
    /**
     * Saves the displayed gallery
     */
    public function save_displayed_gallery_action()
    {
        $response = array();
        $mapper = C_Displayed_Gallery_Mapper::get_instance();
        // Do we have fields to work with?
        if ($this->object->validate_ajax_request('nextgen_edit_displayed_gallery', true) && ($params = json_decode($this->object->param('displayed_gallery')))) {
            // Existing displayed gallery ?
            if ($id = $this->object->param('id')) {
                $displayed_gallery = $mapper->find($id, TRUE);
                if ($displayed_gallery) {
                    foreach ($params as $key => $value) {
                        $displayed_gallery->{$key} = $value;
                    }
                }
            } else {
                $factory = C_Component_Factory::get_instance();
                $displayed_gallery = $factory->create('displayed_gallery', $params, $mapper);
            }
            // Save the changes
            if ($displayed_gallery) {
                if ($displayed_gallery->save()) {
                    $response['displayed_gallery'] = $displayed_gallery->get_entity();
                } else {
                    $response['validation_errors'] = $this->get_attach_to_post()->show_errors_for($displayed_gallery, TRUE);
                }
            } else {
                $response['error'] = __('Displayed gallery does not exist', 'nggallery');
            }
        } else {
            $response['error'] = __('Invalid request', 'nggallery');
        }
        return $response;
    }
}
class A_Gallery_Storage_Frame_Event extends Mixin
{
    public function generate_thumbnail($image, $params = null, $skip_defaults = false)
    {
        $retval = $this->call_parent('generate_thumbnail', $image, $params, $skip_defaults);
        if (is_admin() && ($image = C_Image_Mapper::get_instance()->find($image))) {
            $controller = C_Display_Type_Controller::get_instance();
            $storage = C_Gallery_Storage::get_instance();
            $app = C_Router::get_instance()->get_routed_app();
            $image->thumb_url = $controller->set_param_for($app->get_routed_url(TRUE), 'timestamp', time(), NULL, $storage->get_thumb_url($image));
            $event = new stdClass();
            $event->pid = $image->{$image->id_field};
            $event->id_field = $image->id_field;
            $event->thumb_url = $image->thumb_url;
            C_Frame_Event_Publisher::get_instance('attach_to_post')->add_event(array('event' => 'thumbnail_modified', 'image' => $event));
        }
        return $retval;
    }
}
class C_Attach_Controller extends C_NextGen_Admin_Page_Controller
{
    static $_instances = array();
    public $_displayed_gallery;
    public $_marked_scripts;
    public $_is_rendering;
    static function &get_instance($context = 'all')
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
    public function define($context)
    {
        if (!is_array($context)) {
            $context = array($context);
        }
        array_unshift($context, 'ngg_attach_to_post');
        parent::define($context);
        $this->add_mixin('Mixin_Attach_To_Post');
        $this->add_mixin('Mixin_Attach_To_Post_Display_Tab');
        $this->implement('I_Attach_To_Post_Controller');
    }
    public function initialize()
    {
        parent::initialize();
        $this->_load_displayed_gallery();
        $this->_marked_scripts = array();
        if (did_action('wp_print_scripts')) {
            $this->_handle_scripts();
        } else {
            add_action('wp_print_scripts', array($this, '_handle_scripts'), 9999);
        }
    }
    public function _handle_scripts()
    {
        if (is_admin() && $this->_is_rendering) {
            global $wp_scripts;
            $queue = $wp_scripts->queue;
            $marked = $this->_marked_scripts;
            foreach ($marked as $tag => $value) {
                $this->_handle_script($tag, $queue);
            }
            foreach ($queue as $extra) {
                wp_dequeue_script($extra);
            }
        }
    }
    public function _handle_script($tag, &$queue)
    {
        global $wp_scripts;
        $registered = $wp_scripts->registered;
        $idx = array_search($tag, $queue);
        if ($idx !== false) {
            unset($queue[$idx]);
        }
        if (isset($registered[$tag])) {
            $script = $registered[$tag];
            if ($script->deps) {
                foreach ($script->deps as $dep) {
                    $this->_handle_script($dep, $queue);
                }
            }
        }
    }
}
class Mixin_Attach_To_Post extends Mixin
{
    public function _load_displayed_gallery()
    {
        $mapper = C_Displayed_Gallery_Mapper::get_instance();
        if (!($this->object->_displayed_gallery = $mapper->find($this->object->param('id'), TRUE))) {
            if (!empty($_REQUEST['id'])) {
                $this->object->_displayed_gallery = $mapper->find($_REQUEST['id'], TRUE);
            }
            if (empty($this->object->_displayed_gallery)) {
                $this->object->_displayed_gallery = $mapper->create();
            }
        }
    }
    public function mark_script($script_tag)
    {
        $this->object->_marked_scripts[$script_tag] = true;
    }
    public function enqueue_backend_resources()
    {
        $this->call_parent('enqueue_backend_resources');
        $this->mark_script('jquery-ui-accordion');
        $this->mark_script('nextgen_display_settings_page_placeholder_stub');
        $this->mark_script('iris');
        $this->mark_script('wp-color-picker');
        $this->mark_script('nextgen_admin_page');
        $this->mark_script('ngg_select2');
        // Enqueue frame event publishing
        wp_enqueue_script('frame_event_publisher');
        $this->object->mark_script('frame_event_publisher');
        // Enqueue JQuery UI libraries
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_script('ngg_tabs', $this->get_static_url('photocrati-attach_to_post#ngg_tabs.js'), FALSE, NGG_SCRIPT_VERSION);
        $this->object->mark_script('jquery-ui-tabs');
        $this->object->mark_script('jquery-ui-sortable');
        $this->object->mark_script('jquery-ui-tooltip');
        $this->object->mark_script('ngg_tabs');
        // Ensure select2
        wp_enqueue_style('ngg_select2');
        wp_enqueue_script('ngg_select2');
        $this->object->mark_script('ngg_select2');
        // Ensure that the Photocrati AJAX library is loaded
        wp_enqueue_script('photocrati_ajax');
        $this->object->mark_script('photocrati_ajax');
        // Enqueue logic for the Attach to Post interface as a whole
        wp_enqueue_script('ngg_attach_to_post', $this->get_static_url('photocrati-attach_to_post#attach_to_post.js'), FALSE, NGG_SCRIPT_VERSION);
        wp_enqueue_style('ngg_attach_to_post', $this->get_static_url('photocrati-attach_to_post#attach_to_post.css'), FALSE, NGG_SCRIPT_VERSION);
        $this->object->mark_script('ngg_attach_to_post');
        // Enqueue backbone.js library, required by the Attach to Post display tab
        wp_enqueue_script('backbone');
        // provided by WP
        $this->object->mark_script('backbone');
        // Ensure underscore sting, a helper utility
        wp_enqueue_script('underscore.string', $this->get_static_url('photocrati-attach_to_post#underscore.string.js'), array('underscore'), NGG_SCRIPT_VERSION);
        $this->object->mark_script('underscore.string');
        // Enqueue the backbone app for the display tab
        $settings = C_NextGen_Settings::get_instance();
        $preview_url = $settings->gallery_preview_url;
        $display_tab_js_url = $settings->attach_to_post_display_tab_js_url;
        if ($this->object->_displayed_gallery->id()) {
            $display_tab_js_url .= '&id=' . $this->object->_displayed_gallery->id();
        }
        wp_enqueue_script('ngg_display_tab', $display_tab_js_url, array('backbone', 'underscore.string', 'photocrati_ajax'), NGG_SCRIPT_VERSION);
        wp_localize_script('ngg_display_tab', 'ngg_displayed_gallery_preview_url', $settings->gallery_preview_url);
        $this->object->mark_script('ngg_display_tab');
        // TODO: for now mark Pro scripts to ensure they are enqueued properly, remove this after Pro upgrade with tagging added
        $display_types = array('photocrati-nextgen_pro_slideshow', 'photocrati-nextgen_pro_horizontal_filmstrip', 'photocrati-nextgen_pro_thumbnail_grid', 'photocrati-nextgen_pro_blog_gallery', 'photocrati-nextgen_pro_film');
        foreach ($display_types as $display_type) {
            $this->object->mark_script($display_type . '-js');
        }
        $this->object->mark_script('nextgen_pro_albums_settings_script');
    }
    /**
     * Renders the interface
     */
    public function index_action($return = FALSE)
    {
        $this->object->enqueue_backend_resources();
        $this->object->do_not_cache();
        // Enqueue resources
        return $this->object->render_view('photocrati-attach_to_post#attach_to_post', array('page_title' => $this->object->_get_page_title(), 'tabs' => $this->object->_get_main_tabs()), $return);
    }
    /**
     * Displays a preview image for the displayed gallery
     */
    public function preview_action()
    {
        $found_preview_pic = FALSE;
        $dyn_thumbs = C_Dynamic_Thumbnails_Manager::get_instance();
        $storage = C_Gallery_Storage::get_instance();
        $image_mapper = C_Image_Mapper::get_instance();
        // Get the first entity from the displayed gallery. We will use this
        // for a preview pic
        $entity = array_pop($this->object->_displayed_gallery->get_included_entities(1));
        $image = FALSE;
        if ($entity) {
            // This is an album or gallery
            if (isset($entity->previewpic)) {
                $image = (int) $entity->previewpic;
                if ($image = $image_mapper->find($image)) {
                    $found_preview_pic = TRUE;
                }
            } else {
                if (isset($entity->galleryid)) {
                    $image = $entity;
                    $found_preview_pic = TRUE;
                }
            }
        }
        // Were we able to find a preview pic? If so, then render it
        $image_size = $dyn_thumbs->get_size_name(array('width' => 300, 'height' => 200, 'quality' => 90, 'type' => 'jpg', 'watermark' => FALSE, 'crop' => TRUE));
        add_filter('ngg_before_save_thumbnail', array(&$this, 'set_igw_placeholder_text'));
        $found_preview_pic = $storage->render_image($image, $image_size, TRUE);
        remove_filter('ngg_before_save_thumbnail', array(&$this, 'set_igw_placeholder_text'));
        // Render invalid image if no preview pic is found
        if (!$found_preview_pic) {
            $filename = $this->object->get_static_abspath('photocrati-attach_to_post#invalid_image.png');
            $this->set_content_type('image/png');
            readfile($filename);
            $this->render();
        }
    }
    /**
     * Filter for ngg_before_save_thumbnail
     */
    public function set_igw_placeholder_text($thumbnail)
    {
        $settings = C_NextGen_Settings::get_instance();
        $thumbnail->applyFilter(IMG_FILTER_BRIGHTNESS, -25);
        $watermark_settings = apply_filters('ngg_igw_placeholder_line_1_settings', array('text' => __('NextGEN Gallery', 'nggallery'), 'font_color' => 'ffffff', 'font' => 'YanoneKaffeesatz-Bold.ttf', 'font_size' => 32));
        if ($watermark_settings) {
            $thumbnail->watermarkText = $watermark_settings['text'];
            $thumbnail->watermarkCreateText($watermark_settings['font_color'], $watermark_settings['font'], $watermark_settings['font_size'], 100);
            $thumbnail->watermarkImage('topCenter', 0, 72);
        }
        $watermark_settings = apply_filters('ngg_igw_placeholder_line_2_settings', array('text' => __('Click to edit', 'nggallery'), 'font_color' => 'ffffff', 'font' => 'YanoneKaffeesatz-Bold.ttf', 'font_size' => 15));
        if ($watermark_settings) {
            $thumbnail->watermarkText = $watermark_settings['text'];
            $thumbnail->watermarkCreateText($watermark_settings['font_color'], $watermark_settings['font'], $watermark_settings['font_size'], 100);
            $thumbnail->watermarkImage('topCenter', 0, 108);
        }
        return $thumbnail;
    }
    /**
     * Returns the page title of the Attach to Post interface
     * @return string
     */
    public function _get_page_title()
    {
        return __('NextGEN Gallery - Attach To Post', 'nggallery');
    }
    /**
     * Returns the main tabs displayed on the Attach to Post interface
     * @returns array
     */
    public function _get_main_tabs()
    {
        $retval = array();
        $security = $this->get_registry()->get_utility('I_Security_Manager');
        $sec_actor = $security->get_current_actor();
        if ($sec_actor->is_allowed('NextGEN Manage gallery')) {
            $retval['displayed_tab'] = array('content' => $this->object->_render_display_tab(), 'title' => __('Display Galleries', 'nggallery'));
        }
        if ($sec_actor->is_allowed('NextGEN Upload images')) {
            $retval['create_tab'] = array('content' => $this->object->_render_create_tab(), 'title' => __('Add Gallery / Images', 'nggallery'));
        }
        if ($sec_actor->is_allowed('NextGEN Manage others gallery') && $sec_actor->is_allowed('NextGEN Manage gallery')) {
            $retval['galleries_tab'] = array('content' => $this->object->_render_galleries_tab(), 'title' => __('Manage Galleries', 'nggallery'));
        }
        if ($sec_actor->is_allowed('NextGEN Edit album')) {
            $retval['albums_tab'] = array('content' => $this->object->_render_albums_tab(), 'title' => __('Manage Albums', 'nggallery'));
        }
        if ($sec_actor->is_allowed('NextGEN Manage tags')) {
            $retval['tags_tab'] = array('content' => $this->object->_render_tags_tab(), 'title' => __('Manage Tags', 'nggallery'));
        }
        return $retval;
    }
    /**
     * Renders a NextGen Gallery page in an iframe, suited for the attach to post
     * interface
     * @param string $page
     * @return string
     */
    public function _render_ngg_page_in_frame($page, $tab_id = null)
    {
        $frame_url = admin_url("/admin.php?page={$page}&attach_to_post");
        $frame_url = nextgen_esc_url($frame_url);
        if ($tab_id) {
            $tab_id = " id='ngg-iframe-{$tab_id}'";
        }
        return "<iframe name='{$page}' frameBorder='0'{$tab_id} class='ngg-attach-to-post ngg-iframe-page-{$page}' scrolling='no' src='{$frame_url}'></iframe>";
    }
    /**
     * Renders the display tab for adjusting how images/galleries will be
     * displayed
     * @return type
     */
    public function _render_display_tab()
    {
        return $this->object->render_partial('photocrati-attach_to_post#display_tab', array('messages' => array(), 'tabs' => $this->object->_get_display_tabs()), TRUE);
    }
    /**
     * Renders the tab used primarily for Gallery and Image creation
     * @return type
     */
    public function _render_create_tab()
    {
        return $this->object->_render_ngg_page_in_frame('ngg_addgallery', 'create_tab');
    }
    /**
     * Renders the tab used for Managing Galleries
     * @return string
     */
    public function _render_galleries_tab()
    {
        return $this->object->_render_ngg_page_in_frame('nggallery-manage-gallery', 'galleries_tab');
    }
    /**
     * Renders the tab used for Managing Albums
     */
    public function _render_albums_tab()
    {
        return $this->object->_render_ngg_page_in_frame('nggallery-manage-album', 'albums_tab');
    }
    /**
     * Renders the tab used for Managing Albums
     * @return string
     */
    public function _render_tags_tab()
    {
        return $this->object->_render_ngg_page_in_frame('nggallery-tags', 'tags_tab');
    }
}
/**
 * Provides the "Display Tab" for the Attach To Post interface/controller
 */
class Mixin_Attach_To_Post_Display_Tab extends Mixin
{
    /**
     * Renders the JS required for the Backbone-based Display Tab
     */
    public function display_tab_js_action($return = FALSE)
    {
        // Cache appropriately
        $this->object->do_not_cache();
        // Ensure that JS is returned
        $this->object->set_content_type('javascript');
        $buffer_limit = 0;
        $zlib = ini_get('zlib.output_compression');
        if (!is_numeric($zlib) && $zlib == 'On') {
            $buffer_limit = 1;
        } else {
            if (is_numeric($zlib) && $zlib > 0) {
                $buffer_limit = 1;
            }
        }
        while (ob_get_level() != $buffer_limit) {
            ob_end_clean();
        }
        // Get all entities used by the display tab
        $context = 'attach_to_post';
        $gallery_mapper = $this->get_registry()->get_utility('I_Gallery_Mapper', $context);
        $album_mapper = $this->get_registry()->get_utility('I_Album_Mapper', $context);
        $image_mapper = $this->get_registry()->get_utility('I_Image_Mapper', $context);
        $display_type_mapper = $this->get_registry()->get_utility('I_Display_Type_Mapper', $context);
        $sources = C_Displayed_Gallery_Source_Manager::get_instance();
        $security = $this->get_registry()->get_utility('I_Security_Manager');
        // Get the nextgen tags
        global $wpdb;
        $tags = $wpdb->get_results("SELECT DISTINCT name AS 'id', name FROM {$wpdb->terms}\n                        WHERE term_id IN (\n                                SELECT term_id FROM {$wpdb->term_taxonomy}\n                                WHERE taxonomy = 'ngg_tag'\n                        )");
        $all_tags = new stdClass();
        $all_tags->name = 'All';
        $all_tags->id = 'All';
        array_unshift($tags, $all_tags);
        $display_types = array();
        $registry = C_Component_Registry::get_instance();
        foreach ($display_type_mapper->find_all() as $display_type) {
            if (isset($display_type->hidden_from_ui) && $display_type->hidden_from_ui) {
                continue;
            }
            $available = $registry->is_module_loaded($display_type->name);
            if (!apply_filters('ngg_atp_show_display_type', $available, $display_type)) {
                continue;
            }
            $display_types[] = $display_type;
        }
        usort($display_types, array($this->object, '_display_type_list_sort'));
        $output = $this->object->render_view('photocrati-attach_to_post#display_tab_js', array('displayed_gallery' => json_encode($this->object->_displayed_gallery->get_entity()), 'sources' => json_encode($sources->get_all()), 'gallery_primary_key' => $gallery_mapper->get_primary_key_column(), 'galleries' => json_encode($gallery_mapper->find_all()), 'albums' => json_encode($album_mapper->find_all()), 'tags' => json_encode($tags), 'display_types' => json_encode($display_types), 'sec_token' => $security->get_request_token('nextgen_edit_displayed_gallery')->get_json(), 'image_primary_key' => $image_mapper->get_primary_key_column()), $return);
        return $output;
    }
    public function _display_type_list_sort($type_1, $type_2)
    {
        $order_1 = $type_1->view_order;
        $order_2 = $type_2->view_order;
        if ($order_1 == null) {
            $order_1 = NGG_DISPLAY_PRIORITY_BASE;
        }
        if ($order_2 == null) {
            $order_2 = NGG_DISPLAY_PRIORITY_BASE;
        }
        if ($order_1 > $order_2) {
            return 1;
        }
        if ($order_1 < $order_2) {
            return -1;
        }
        return 0;
    }
    /**
     * Gets a list of tabs to render for the "Display" tab
     */
    public function _get_display_tabs()
    {
        // The ATP requires more memmory than some applications, somewhere around 60MB.
        // Because it's such an important feature of NextGEN Gallery, we temporarily disable
        // any memory limits
        if (!extension_loaded('suhosin')) {
            @ini_set('memory_limit', -1);
        }
        return array($this->object->_render_display_types_tab(), $this->object->_render_display_source_tab(), $this->object->_render_display_settings_tab(), $this->object->_render_preview_tab());
    }
    /**
     * Renders the accordion tab, "What would you like to display?"
     */
    public function _render_display_source_tab()
    {
        return $this->object->render_partial('photocrati-attach_to_post#accordion_tab', array('id' => 'source_tab', 'title' => __('What would you like to display?', 'nggallery'), 'content' => $this->object->_render_display_source_tab_contents()), TRUE);
    }
    /**
     * Renders the contents of the source tab
     * @return string
     */
    public function _render_display_source_tab_contents()
    {
        return $this->object->render_partial('photocrati-attach_to_post#display_tab_source', array(), TRUE);
    }
    /**
     * Renders the accordion tab for selecting a display type
     * @return string
     */
    public function _render_display_types_tab()
    {
        return $this->object->render_partial('photocrati-attach_to_post#accordion_tab', array('id' => 'display_type_tab', 'title' => __('Select a display type', 'nggallery'), 'content' => $this->object->_render_display_type_tab_contents()), TRUE);
    }
    /**
     * Renders the contents of the display type tab
     */
    public function _render_display_type_tab_contents()
    {
        return $this->object->render_partial('photocrati-attach_to_post#display_tab_type', array(), TRUE);
    }
    /**
     * Renders the display settings tab for the Attach to Post interface
     * @return type
     */
    public function _render_display_settings_tab()
    {
        return $this->object->render_partial('photocrati-attach_to_post#accordion_tab', array('id' => 'display_settings_tab', 'title' => __('Customize the display settings', 'nggallery'), 'content' => $this->object->_render_display_settings_contents()), TRUE);
    }
    /**
     * If editing an existing displayed gallery, retrieves the name
     * of the display type
     * @return string
     */
    public function _get_selected_display_type_name()
    {
        $retval = '';
        if ($this->object->_displayed_gallery) {
            $retval = $this->object->_displayed_gallery->display_type;
        }
        return $retval;
    }
    /**
     * Is the displayed gallery that's being edited using the specified display
     * type?
     * @param string $name	name of the display type
     * @return bool
     */
    public function is_displayed_gallery_using_display_type($name)
    {
        $retval = FALSE;
        if ($this->object->_displayed_gallery) {
            $retval = $this->object->_displayed_gallery->display_type == $name;
        }
        return $retval;
    }
    /**
     * Renders the contents of the display settings tab
     * @return string
     */
    public function _render_display_settings_contents()
    {
        $retval = array();
        // Get all display setting forms
        $form_manager = C_Form_Manager::get_instance();
        $forms = $form_manager->get_forms(NGG_DISPLAY_SETTINGS_SLUG, TRUE);
        // Display each form
        foreach ($forms as $form) {
            // Enqueue the form's static resources
            $form->enqueue_static_resources();
            // Determine which classes to use for the form's "class" attribute
            $model = $form->get_model();
            $current = $this->object->is_displayed_gallery_using_display_type($model->name);
            $css_class = $current ? 'display_settings_form' : 'display_settings_form hidden';
            // If this form is used to provide the display settings for the current
            // displayed gallery, then we need to override the forms settings
            // with the displayed gallery settings
            if ($current) {
                $settings = $this->array_merge_assoc($model->settings, $this->object->_displayed_gallery->display_settings, TRUE);
                $model->settings = $settings;
            }
            // Output the display settings form
            $retval[] = $this->object->render_partial('photocrati-attach_to_post#display_settings_form', array('settings' => $form->render(), 'display_type_name' => $model->name, 'css_class' => $css_class), TRUE);
        }
        // In addition, we'll render a form that will be displayed when no
        // display type has been selected in the Attach to Post interface
        // Render the default "no display type selected" view
        $css_class = $this->object->_get_selected_display_type_name() ? 'display_settings_form hidden' : 'display_settings_form';
        $retval[] = $this->object->render_partial('photocrati-attach_to_post#no_display_type_selected', array('no_display_type_selected' => __('No display type selected', 'nggallery'), 'css_class' => $css_class), TRUE);
        // Return all display setting forms
        return implode('
', $retval);
    }
    /**
     * Renders the tab used to preview included images
     * @return string
     */
    public function _render_preview_tab()
    {
        return $this->object->render_partial('photocrati-attach_to_post#accordion_tab', array('id' => 'preview_tab', 'title' => __('Sort or Exclude Images', 'nggallery'), 'content' => $this->object->_render_preview_tab_contents()), TRUE);
    }
    /**
     * Renders the contents of the "Preview" tab.
     * @return string
     */
    public function _render_preview_tab_contents()
    {
        return $this->object->render_partial('photocrati-attach_to_post#preview_tab', array(), TRUE);
    }
}