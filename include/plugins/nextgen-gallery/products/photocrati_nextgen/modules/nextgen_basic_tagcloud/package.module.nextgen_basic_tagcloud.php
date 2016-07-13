<?php
class A_NextGen_Basic_Tagcloud extends Mixin
{
    public function validation()
    {
        if ($this->object->name == NGG_BASIC_TAGCLOUD) {
            $this->object->validates_presence_of('display_type');
        }
        return $this->call_parent('validation');
    }
}
class A_NextGen_Basic_Tagcloud_Controller extends Mixin
{
    /**
     * Displays the 'tagcloud' display type
     *
     * @param stdClass|C_Displayed_Gallery|C_DataMapper_Model $displayed_gallery
     */
    public function index_action($displayed_gallery, $return = FALSE)
    {
        $display_settings = $displayed_gallery->display_settings;
        $application = C_Router::get_instance()->get_routed_app();
        $tag = urldecode($this->param('gallerytag'));
        // we're looking at a tag, so show images w/that tag as a thumbnail gallery
        if (!is_home() && !empty($tag)) {
            return C_Displayed_Gallery_Renderer::get_instance()->display_images(array('source' => 'tags', 'container_ids' => array(esc_attr($tag)), 'display_type' => $display_settings['display_type'], 'original_display_type' => $displayed_gallery->display_type, 'original_settings' => $display_settings));
        }
        $defaults = array('exclude' => '', 'format' => 'list', 'include' => $displayed_gallery->get_term_ids_for_tags(), 'largest' => 22, 'link' => 'view', 'number' => $display_settings['number'], 'order' => 'ASC', 'orderby' => 'name', 'smallest' => 8, 'taxonomy' => 'ngg_tag', 'unit' => 'pt');
        $args = wp_parse_args('', $defaults);
        // Always query top tags
        $tags = get_terms($args['taxonomy'], array_merge($args, array('orderby' => 'count', 'order' => 'DESC')));
        foreach ($tags as $key => $tag) {
            $tags[$key]->link = $this->object->set_param_for($application->get_routed_url(TRUE), 'gallerytag', $tag->slug);
            $tags[$key]->id = $tag->term_id;
        }
        $params = $display_settings;
        $params['inner_content'] = $displayed_gallery->inner_content;
        $params['storage'] =& $storage;
        $params['tagcloud'] = wp_generate_tag_cloud($tags, $args);
        $params['displayed_gallery_id'] = $displayed_gallery->id();
        $params = $this->object->prepare_display_parameters($displayed_gallery, $params);
        return $this->object->render_partial('photocrati-nextgen_basic_tagcloud#nextgen_basic_tagcloud', $params, $return);
    }
    /**
     * Enqueues all static resources required by this display type
     *
     * @param C_Displayed_Gallery $displayed_gallery
     */
    public function enqueue_frontend_resources($displayed_gallery)
    {
        $this->call_parent('enqueue_frontend_resources', $displayed_gallery);
        wp_enqueue_style('photocrati-nextgen_basic_tagcloud-style', $this->get_static_url('photocrati-nextgen_basic_tagcloud#nextgen_basic_tagcloud.css'), FALSE, NGG_SCRIPT_VERSION);
        $this->enqueue_ngg_styles();
    }
}
class A_NextGen_Basic_Tagcloud_Form extends Mixin_Display_Type_Form
{
    public function get_display_type_name()
    {
        return NGG_BASIC_TAGCLOUD;
    }
    public function _get_field_names()
    {
        return array('nextgen_basic_tagcloud_number', 'nextgen_basic_tagcloud_display_type');
    }
    public function enqueue_static_resources()
    {
        $path = 'photocrati-nextgen_basic_tagcloud#settings.css';
        wp_enqueue_style('nextgen_basic_tagcloud_settings-css', $this->get_static_url($path), FALSE, NGG_SCRIPT_VERSION);
        $atp = C_Attach_Controller::get_instance();
        if (!is_null($atp)) {
            $atp->mark_script($path);
        }
    }
    public function _render_nextgen_basic_tagcloud_number_field($display_type)
    {
        return $this->_render_number_field($display_type, 'number', __('Maximum number of tags', 'nggallery'), $display_type->settings['number']);
    }
    public function _render_nextgen_basic_tagcloud_display_type_field($display_type)
    {
        $types = array();
        $skip_types = array(NGG_BASIC_TAGCLOUD, NGG_BASIC_SINGLEPIC, NGG_BASIC_COMPACT_ALBUM, NGG_BASIC_EXTENDED_ALBUM);
        $skip_types = apply_filters('ngg_basic_tagcloud_excluded_display_types', $skip_types);
        $mapper = C_Display_Type_Mapper::get_instance();
        $display_types = $mapper->find_all();
        foreach ($display_types as $dt) {
            if (in_array($dt->name, $skip_types)) {
                continue;
            }
            $types[$dt->name] = $dt->title;
        }
        return $this->_render_select_field($display_type, 'display_type', __('Display type', 'nggallery'), $types, $display_type->settings['display_type'], __('The display type that the tagcloud will point its results to', 'nggallery'));
    }
}
class A_NextGen_Basic_TagCloud_Mapper extends Mixin
{
    public function set_defaults($entity)
    {
        $this->call_parent('set_defaults', $entity);
        if (isset($entity->name) && $entity->name == NGG_BASIC_TAGCLOUD) {
            $this->object->_set_default_value($entity, 'settings', 'display_type', NGG_BASIC_THUMBNAILS);
            $this->object->_set_default_value($entity, 'settings', 'number', 45);
            $this->object->_set_default_value($entity, 'settings', 'ngg_triggers_display', 'never');
        }
    }
}
class A_NextGen_Basic_TagCloud_Urls extends Mixin
{
    public function create_parameter_segment($key, $value, $id, $use_prefix)
    {
        if ($key == 'gallerytag') {
            return 'tags/' . $value;
        } else {
            return $this->call_parent('create_parameter_segment', $key, $value, $id, $use_prefix);
        }
    }
    public function set_parameter_value($key, $value, $id = NULL, $use_prefix = FALSE, $url = FALSE)
    {
        $retval = $this->call_parent('set_parameter_value', $key, $value, $id, $use_prefix, $url);
        return $this->_set_tag_cloud_parameters($retval, $key, $id);
    }
    public function remove_parameter($key, $id = NULL, $url = FALSE)
    {
        $retval = $this->call_parent('remove_parameter', $key, $id, $url);
        $retval = $this->_set_tag_cloud_parameters($retval, $key, $id);
        return $retval;
    }
    public function _set_tag_cloud_parameters($retval, $key, $id = NULL)
    {
        // Get the settings manager
        $settings = C_NextGen_Settings::get_instance();
        // Create the regex pattern
        $sep = preg_quote($settings->router_param_separator, '#');
        if ($id) {
            $id = preg_quote($id, '#') . $sep;
        }
        $prefix = preg_quote($settings->router_param_prefix, '#');
        $regex = implode('', array('#//?', $id ? "({$id})?" : "(\\w+{$sep})?", "({$prefix})?gallerytag{$sep}([\\w-_]+)/?#"));
        // Replace any page parameters with the ngglegacy equivalent
        if (preg_match($regex, $retval, $matches)) {
            $retval = rtrim(str_replace($matches[0], "/tags/{$matches[3]}/", $retval), '/');
        }
        return $retval;
    }
}
class C_Taxonomy_Controller extends C_MVC_Controller
{
    static $_instances = array();
    protected $ngg_tag_detection_has_run = FALSE;
    /**
     * Returns an instance of this class
     *
     * @param string $context
     * @return C_Taxonomy_Controller
     */
    static function get_instance($context = FALSE)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->implement('I_Taxonomy_Controller');
    }
    /**
     * Returns the rendered HTML of a gallery based on the provided tag
     *
     * @param string $tag
     * @return string
     */
    public function index_action($tag)
    {
        $mapper = C_Display_Type_Mapper::get_instance();
        // Respect the global display type setting
        $display_type = $mapper->find_by_name(NGG_BASIC_TAGCLOUD, TRUE);
        $display_type = !empty($display_type->settings['display_type']) ? $display_type->settings['display_type'] : NGG_BASIC_THUMBNAILS;
        return "[ngg_images source='tags' container_ids='{$tag}' slug='{$tag}' display_type='{$display_type}']";
    }
    /**
     * Determines if the current page is /ngg_tag/{*}
     *
     * @param $posts Wordpress post objects
     * @return array Wordpress post objects
     */
    public function detect_ngg_tag($posts, $wp_query_local)
    {
        global $wp;
        global $wp_query;
        $wp_query_orig = false;
        if ($wp_query_local != null && $wp_query_local != $wp_query) {
            $wp_query_orig = $wp_query;
            $wp_query = $wp_query_local;
        }
        // This appears to be necessary for multisite installations, but I can't imagine why. More hackery..
        $tag = urldecode(get_query_var('ngg_tag') ? get_query_var('ngg_tag') : get_query_var('name'));
        if (!$this->ngg_tag_detection_has_run && !is_admin() && !empty($tag) && (stripos($wp->request, 'ngg_tag') === 0 || isset($wp_query->query_vars['page_id']) && $wp_query->query_vars['page_id'] === 'ngg_tag')) {
            $this->ngg_tag_detection_has_run = TRUE;
            // Wordpress somewhat-correctly generates several notices, so silence them as they're really unnecessary
            if (!defined('WP_DEBUG') || !WP_DEBUG) {
                error_reporting(0);
            }
            // Without this all url generated from this page lacks the /ngg_tag/(slug) section of the URL
            add_filter('ngg_wprouting_add_post_permalink', '__return_false');
            // create in-code a fake post; we feed it back to Wordpress as the sole result of the "the_posts" filter
            $posts = NULL;
            $posts[] = $this->create_ngg_tag_post($tag);
            $wp_query->is_404 = FALSE;
            $wp_query->is_page = TRUE;
            $wp_query->is_singular = TRUE;
            $wp_query->is_home = FALSE;
            $wp_query->is_archive = FALSE;
            $wp_query->is_category = FALSE;
            unset($wp_query->query['error']);
            $wp_query->query_vars['error'] = '';
        }
        if ($wp_query_orig !== false) {
            $wp_query = $wp_query_orig;
        }
        return $posts;
    }
    public function create_ngg_tag_post($tag)
    {
        $title = sprintf(__('Images tagged &quot;%s&quot;', 'nggallery'), $tag);
        $title = apply_filters('ngg_basic_tagcloud_title', $title, $tag);
        $post = new stdClass();
        $post->post_author = FALSE;
        $post->post_name = 'ngg_tag';
        $post->guid = get_bloginfo('wpurl') . '/' . 'ngg_tag';
        $post->post_title = $title;
        $post->post_content = $this->index_action($tag);
        $post->ID = FALSE;
        $post->post_type = 'page';
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);
        return $post;
    }
}