<?php
// TODO: Finish the implementation
class A_Fs_Access_Page extends Mixin
{
    public function index_action()
    {
        $router = C_Router::get_instance();
        $url = $this->param('uri') ? $router->get_url($this->param('uri')) : admin_url('/admin.php?' . $router->get_querystring());
        // Request filesystem credentials from user
        $creds = request_filesystem_credentials($url, '', FALSE, ABSPATH, array());
        if (WP_Filesystem($creds)) {
            global $wp_filesystem;
        }
    }
    /**
     * Determines whether the given paths are writable
     * @return boolean
     */
    public function are_paths_writable()
    {
        $retval = TRUE;
        $path = $this->object->param('path');
        if (!is_array($path)) {
            $path = array($path);
        }
        foreach ($path as $p) {
            if (!is_writable($p)) {
                $retval = FALSE;
                break;
            }
        }
        return $retval;
    }
}
/**
 * Provides validation for datamapper entities within an MVC controller
 */
class A_MVC_Validation extends Mixin
{
    public function show_errors_for($entity, $return = FALSE)
    {
        $retval = '';
        if ($entity->is_invalid()) {
            $retval = $this->object->render_partial('photocrati-nextgen_admin#entity_errors', array('entity' => $entity), $return);
        }
        return $retval;
    }
    public function show_success_for($entity, $message, $return = FALSE)
    {
        $retval = '';
        if ($entity->is_valid()) {
            $retval = $this->object->render_partial('photocrati-nextgen_admin#entity_saved', array('entity' => $entity, 'message' => $message));
        }
        return $retval;
    }
}
class A_NextGen_Admin_Default_Pages extends Mixin
{
    public function setup()
    {
        $this->object->add(NGG_FS_ACCESS_SLUG, array('adapter' => 'A_Fs_Access_Page', 'parent' => NGGFOLDER, 'add_menu' => FALSE));
        return $this->call_parent('setup');
    }
}
class C_Admin_Notification_Manager
{
    public $_notifications = array();
    public $_displayed_notice = FALSE;
    public $_dismiss_url = NULL;
    static $_instance = NULL;
    static function get_instance()
    {
        if (!isset(self::$_instance)) {
            $klass = get_class();
            self::$_instance = new $klass();
        }
        return self::$_instance;
    }
    public function __construct()
    {
        $this->_dismiss_url = site_url('/?ngg_dismiss_notice=1');
    }
    public function has_displayed_notice()
    {
        return $this->_displayed_notice;
    }
    public function add($name, $handler)
    {
        $this->_notifications[$name] = $handler;
    }
    public function remove($name)
    {
        unset($this->_notifications[$name]);
    }
    public function render()
    {
        $output = array();
        foreach (array_keys($this->_notifications) as $notice) {
            if ($html = $this->render_notice($notice)) {
                $output[] = $html;
            }
        }
        echo implode('
', $output);
    }
    public function is_dismissed($name)
    {
        $retval = FALSE;
        $settings = C_NextGen_Settings::get_instance();
        $dismissed = $settings->get('dismissed_notifications', array());
        if (isset($dismissed[$name])) {
            if ($id = get_current_user_id()) {
                if (in_array($id, $dismissed[$name])) {
                    $retval = TRUE;
                } else {
                    if (in_array('unknown', $dismissed[$name])) {
                        $retval = TRUE;
                    }
                }
            }
        }
        return $retval;
    }
    public function dismiss($name)
    {
        $retval = FALSE;
        if ($handler = $this->get_handler_instance($name)) {
            $has_method = method_exists($handler, 'is_dismissable');
            if ($has_method && $handler->is_dismissable() || !$has_method) {
                $settings = C_NextGen_Settings::get_instance();
                $dismissed = $settings->get('dismissed_notifications', array());
                if (!isset($dismissed[$name])) {
                    $dismissed[$name] = array();
                }
                $user_id = get_current_user_id();
                $dismissed[$name][] = $user_id ? $user_id : 'unknown';
                $settings->set('dismissed_notifications', $dismissed);
                $settings->save();
                $retval = TRUE;
            }
        }
        return $retval;
    }
    public function get_handler_instance($name)
    {
        $retval = NULL;
        if (isset($this->_notifications[$name]) && ($handler = $this->_notifications[$name])) {
            if (class_exists($handler)) {
                $retval = call_user_func(array($handler, 'get_instance'), $name);
            }
        }
        return $retval;
    }
    public function enqueue_scripts()
    {
        if ($this->has_displayed_notice()) {
            $router = C_Router::get_instance();
            wp_enqueue_script('ngg_admin_notices', $router->get_static_url('photocrati-nextgen_admin#admin_notices.js'), FALSE, NGG_SCRIPT_VERSION, TRUE);
            wp_localize_script('ngg_admin_notices', 'ngg_dismiss_url', $this->_dismiss_url);
        }
    }
    public function serve_ajax_request()
    {
        $retval = array('failure' => TRUE);
        if (isset($_REQUEST['ngg_dismiss_notice'])) {
            header('Content-Type: application/json');
            //			ob_start();
            if (isset($_REQUEST['name']) && $this->dismiss($_REQUEST['name'])) {
                $retval = array('success' => TRUE);
            } else {
                $retval['msg'] = __('Not a valid notice name', 'nggallery');
            }
            //			ob_end_clean();
            echo json_encode($retval);
            throw new E_Clean_Exit();
        }
    }
    public function render_notice($name)
    {
        $retval = '';
        if (($handler = $this->get_handler_instance($name)) && !$this->is_dismissed($name)) {
            // Does the handler want to render?
            $has_method = method_exists($handler, 'is_renderable');
            if ($has_method && $handler->is_renderable() || !$has_method) {
                $view = new C_MVC_View('photocrati-nextgen_admin#admin_notice', array('css_class' => method_exists($handler, 'get_css_class') ? $handler->get_css_class() : 'updated', 'is_dismissable' => method_exists($handler, 'is_dismissable') ? $handler->is_dismissable() : FALSE, 'html' => method_exists($handler, 'render') ? $handler->render() : '', 'notice_name' => $name));
                $retval = $view->render(TRUE);
                $this->_displayed_notice = TRUE;
            }
        }
        return $retval;
    }
}
class C_Form extends C_MVC_Controller
{
    static $_instances = array();
    public $page = NULL;
    /**
     * Gets an instance of a form
     * @param string $context
     * @return C_Form
     */
    static function &get_instance($context)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
    /**
     * Defines the form
     * @param string $context
     */
    public function define($context)
    {
        parent::define($context);
        $this->add_mixin('Mixin_Form_Instance_Methods');
        $this->add_mixin('Mixin_Form_Field_Generators');
        $this->implement('I_Form');
    }
}
class Mixin_Form_Instance_Methods extends Mixin
{
    /**
     * Enqueues any static resources required by the form
     */
    public function enqueue_static_resources()
    {
    }
    /**
     * Gets a list of fields to render
     * @return array
     */
    public function _get_field_names()
    {
        return array();
    }
    public function get_id()
    {
        return $this->object->context;
    }
    public function get_title()
    {
        return $this->object->context;
    }
    /**
     * Saves the form/model
     * @param array $attributes
     * @return type
     */
    public function save_action($attributes = array())
    {
        if (!$attributes) {
            $attributes = array();
        }
        if ($this->object->has_method('get_model') && $this->object->get_model()) {
            return $this->object->get_model()->save($attributes);
        } else {
            return TRUE;
        }
    }
    /**
     * Returns the rendered form
     */
    public function render($wrap = TRUE)
    {
        $fields = array();
        foreach ($this->object->_get_field_names() as $field) {
            $method = "_render_{$field}_field";
            if ($this->object->has_method($method)) {
                $fields[] = $this->object->{$method}($this->object->get_model());
            }
        }
        return $this->object->render_partial('photocrati-nextgen_admin#form', array('fields' => $fields, 'wrap' => $wrap), TRUE);
    }
    public function get_model()
    {
        return $this->object->page->has_method('get_model') ? $this->object->page->get_model() : NULL;
    }
}
/**
 * Provides some default field generators for forms to use
 */
class Mixin_Form_Field_Generators extends Mixin
{
    public function _render_select_field($display_type, $name, $label, $options = array(), $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_select', array('display_type_name' => $display_type->name, 'name' => $name, 'label' => $label, 'options' => $options, 'value' => $value, 'text' => $text, 'hidden' => $hidden), True);
    }
    public function _render_radio_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_radio', array('display_type_name' => $display_type->name, 'name' => $name, 'label' => $label, 'value' => $value, 'text' => $text, 'hidden' => $hidden), True);
    }
    public function _render_number_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $placeholder = '', $min = NULL, $max = NULL)
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_number', array('display_type_name' => $display_type->name, 'name' => $name, 'label' => $label, 'value' => $value, 'text' => $text, 'hidden' => $hidden, 'placeholder' => $placeholder, 'min' => $min, 'max' => $max), True);
    }
    public function _render_text_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $placeholder = '')
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_text', array('display_type_name' => $display_type->name, 'name' => $name, 'label' => $label, 'value' => $value, 'text' => $text, 'hidden' => $hidden, 'placeholder' => $placeholder), True);
    }
    public function _render_textarea_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE, $placeholder = '')
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_textarea', array('display_type_name' => $display_type->name, 'name' => $name, 'label' => $label, 'value' => $value, 'text' => $text, 'hidden' => $hidden, 'placeholder' => $placeholder), True);
    }
    public function _render_color_field($display_type, $name, $label, $value, $text = '', $hidden = FALSE)
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_color', array('display_type_name' => $display_type->name, 'name' => $name, 'label' => $label, 'value' => $value, 'text' => $text, 'hidden' => $hidden), True);
    }
    public function _render_ajax_pagination_field($display_type)
    {
        return $this->object->_render_radio_field($display_type, 'ajax_pagination', __('Enable AJAX pagination', 'nggallery'), isset($display_type->settings['ajax_pagination']) ? $display_type->settings['ajax_pagination'] : FALSE);
    }
    public function _render_thumbnail_override_settings_field($display_type)
    {
        $hidden = !(isset($display_type->settings['override_thumbnail_settings']) ? $display_type->settings['override_thumbnail_settings'] : FALSE);
        $override_field = $this->_render_radio_field($display_type, 'override_thumbnail_settings', __('Override thumbnail settings', 'nggallery'), isset($display_type->settings['override_thumbnail_settings']) ? $display_type->settings['override_thumbnail_settings'] : FALSE, __('This does not affect existing thumbnails; overriding the thumbnail settings will create an additional set of thumbnails. To change the size of existing thumbnails please visit \'Manage Galleries\' and choose \'Create new thumbnails\' for all images in the gallery.', 'nggallery'));
        $dimensions_field = $this->object->render_partial('photocrati-nextgen_admin#field_generator/thumbnail_settings', array('display_type_name' => $display_type->name, 'name' => 'thumbnail_dimensions', 'label' => __('Thumbnail dimensions', 'nggallery'), 'thumbnail_width' => isset($display_type->settings['thumbnail_width']) ? intval($display_type->settings['thumbnail_width']) : 0, 'thumbnail_height' => isset($display_type->settings['thumbnail_height']) ? intval($display_type->settings['thumbnail_height']) : 0, 'hidden' => $hidden ? 'hidden' : '', 'text' => ''), TRUE);
        /*
        $qualities = array();
        for ($i = 100; $i > 40; $i -= 5) { $qualities[$i] = "{$i}%"; }
        $quality_field = $this->_render_select_field(
            $display_type,
            'thumbnail_quality',
            __('Thumbnail quality', 'nggallery'),
            $qualities,
            isset($display_type->settings['thumbnail_quality']) ? $display_type->settings['thumbnail_quality'] : 100,
            '',
            $hidden
        );
        */
        $crop_field = $this->_render_radio_field($display_type, 'thumbnail_crop', __('Thumbnail crop', 'nggallery'), isset($display_type->settings['thumbnail_crop']) ? $display_type->settings['thumbnail_crop'] : FALSE, '', $hidden);
        /*
        $watermark_field = $this->_render_radio_field(
            $display_type,
            'thumbnail_watermark',
            __('Thumbnail watermark', 'nggallery'),
            isset($display_type->settings['thumbnail_watermark']) ? $display_type->settings['thumbnail_watermark'] : FALSE,
            '',
            $hidden
        );
        */
        $everything = $override_field . $dimensions_field . $crop_field;
        return $everything;
    }
    /**
     * Renders the thumbnail override settings field(s)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    public function _render_image_override_settings_field($display_type)
    {
        $hidden = !(isset($display_type->settings['override_image_settings']) ? $display_type->settings['override_image_settings'] : FALSE);
        $override_field = $this->_render_radio_field($display_type, 'override_image_settings', __('Override image settings', 'nggallery'), isset($display_type->settings['override_image_settings']) ? $display_type->settings['override_image_settings'] : 0, __('Overriding the image settings will create an additional set of images', 'nggallery'));
        $qualities = array();
        for ($i = 100; $i > 40; $i -= 5) {
            $qualities[$i] = "{$i}%";
        }
        $quality_field = $this->_render_select_field($display_type, 'image_quality', __('Image quality', 'nggallery'), $qualities, $display_type->settings['image_quality'], '', $hidden);
        $crop_field = $this->_render_radio_field($display_type, 'image_crop', __('Image crop', 'nggallery'), $display_type->settings['image_crop'], '', $hidden);
        $watermark_field = $this->_render_radio_field($display_type, 'image_watermark', __('Image watermark', 'nggallery'), $display_type->settings['image_watermark'], '', $hidden);
        $everything = $override_field . $quality_field . $crop_field . $watermark_field;
        return $everything;
    }
    /**
     * Renders a pair of fields for width and width-units (px, em, etc)
     *
     * @param C_Display_Type $display_type
     * @return string
     */
    public function _render_width_and_unit_field($display_type)
    {
        return $this->object->render_partial('photocrati-nextgen_admin#field_generator/nextgen_settings_field_width_and_unit', array('display_type_name' => $display_type->name, 'name' => 'width', 'label' => __('Gallery width', 'nggallery'), 'value' => $display_type->settings['width'], 'text' => __('An empty or 0 setting will make the gallery full width', 'nggallery'), 'placeholder' => __('(optional)', 'nggallery'), 'unit_name' => 'width_unit', 'unit_value' => $display_type->settings['width_unit'], 'options' => array('px' => __('Pixels', 'nggallery'), '%' => __('Percent', 'nggallery'))), TRUE);
    }
    public function _get_aspect_ratio_options()
    {
        return array('first_image' => __('First Image', 'nggallery'), 'image_average' => __('Average', 'nggallery'), '1.5' => '3:2 [1.5]', '1.333' => '4:3 [1.333]', '1.777' => '16:9 [1.777]', '1.6' => '16:10 [1.6]', '1.85' => '1.85:1 [1.85]', '2.39' => '2.39:1 [2.39]', '1.81' => '1.81:1 [1.81]', '1' => '1:1 (Square) [1]');
    }
}
class C_Form_Manager extends C_Component
{
    static $_instances = array();
    public $_forms = array();
    /**
     * Returns an instance of the form manager
     * @returns C_Form_Manager
     */
    static function &get_instance($context = FALSE)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
    /**
     * Defines the instance
     * @param mixed $context
     */
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_Form_Manager');
        $this->implement('I_Form_Manager');
    }
}
class Mixin_Form_Manager extends Mixin
{
    /**
     * Adds one or more
     * @param type $type
     * @param type $form_names
     * @return type
     */
    public function add_form($type, $form_names)
    {
        if (!isset($this->object->_forms[$type])) {
            $this->object->_forms[$type] = array();
        }
        if (!is_array($form_names)) {
            $form_names = array($form_names);
        }
        foreach ($form_names as $form) {
            $this->object->_forms[$type][] = $form;
        }
        return $this->object->get_form_count($type);
    }
    /**
     * Alias for add_form() method
     * @param string $type
     * @param string|array $form_names
     * @return int
     */
    public function add_forms($type, $form_names)
    {
        return $this->object->add_form($type, $form_names);
    }
    /**
     * Removes one or more forms of a particular type
     * @param string $type
     * @param string|array $form_names
     * @return int	number of forms remaining for the type
     */
    public function remove_form($type, $form_names)
    {
        $retval = 0;
        if (isset($this->object->_forms[$type])) {
            foreach ($form_names as $form) {
                if ($index = array_search($form, $this->object->_forms[$type])) {
                    unsset($this->object->_forms[$type][$index]);
                }
            }
            $retval = $this->object->get_form_count($type);
        }
        return $retval;
    }
    /**
     * Alias for remove_form() method
     * @param string $type
     * @param string|array $form_names
     * @return int
     */
    public function remove_forms($type, $form_names)
    {
        return $this->object->remove_form($type, $form_names);
    }
    /**
     * Gets known form types
     * @return type
     */
    public function get_known_types()
    {
        return array_keys($this->object->_forms);
    }
    /**
     * Gets forms of a particular type
     * @param string $type
     * @return array
     */
    public function get_forms($type, $instantiate = FALSE)
    {
        $retval = array();
        if (isset($this->object->_forms[$type])) {
            if (!$instantiate) {
                $retval = $this->object->_forms[$type];
            } else {
                foreach ($this->object->_forms[$type] as $context) {
                    $retval[] = $this->get_registry()->get_utility('I_Form', $context);
                }
            }
        }
        return $retval;
    }
    /**
     * Gets the number of forms registered for a particular type
     * @param string $type
     * @return int
     */
    public function get_form_count($type)
    {
        $retval = 0;
        if (isset($this->object->_forms[$type])) {
            $retval = count($this->object->_forms[$type]);
        }
        return $retval;
    }
    /**
     * Gets the index of a particular form
     * @param string $type
     * @param string $name
     * @return FALSE|int
     */
    public function get_form_index($type, $name)
    {
        $retval = FALSE;
        if ($this->object->get_form_count($type) > 0) {
            $retval = array_search($name, $this->object->_forms[$type]);
        }
        return $retval;
    }
    /**
     * Adds one or more forms before a form already registered
     * @param string $type
     * @param string $before
     * @param string|array $form_names
     * @param int $offset
     * @return int
     */
    public function add_form_before($type, $before, $form_names, $offset = 0)
    {
        $retval = 0;
        $index = FALSE;
        $use_add = FALSE;
        // Append the forms
        if ($this->object->get_form_count($type) == 0) {
            $use_add = TRUE;
        } else {
            if (($index = $this->object->get_form_index($type, $name)) == FALSE) {
                $use_add = FALSE;
            }
        }
        if ($use_add) {
            $this->object->add_forms($type, $form_names);
        } else {
            $before = array_slice($this->object->get_forms($type), 0, $offset);
            $after = array_slice($this->object->get_forms($type), $offset);
            $this->object->_forms[$type] = array_merge($before, $form_names, $after);
            $retval = $this->object->get_form_count($type);
        }
        return $retval;
    }
    /**
     * Adds one or more forms after an existing form
     * @param string $type
     * @param string $after
     * @param string|array $form_names
     * @return int
     */
    public function add_form_after($type, $after, $form_names)
    {
        return $this->object->add_form_before($type, $after, $form_names, 1);
    }
}
if (!class_exists('C_NextGen_Admin_Installer')) {
}
class C_NextGen_Admin_Page_Controller extends C_MVC_Controller
{
    static $_instances = array();
    static function &get_instance($context = FALSE)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
    public function define($context = FALSE)
    {
        if (is_array($context)) {
            $this->name = $context[0];
        } else {
            $this->name = $context;
        }
        parent::define($context);
        $this->add_mixin('Mixin_NextGen_Admin_Page_Instance_Methods');
        $this->implement('I_NextGen_Admin_Page');
    }
}
class Mixin_NextGen_Admin_Page_Instance_Methods extends Mixin
{
    /**
     * Authorizes the request
     */
    public function is_authorized_request($privilege = NULL)
    {
        if (!$privilege) {
            $privilege = $this->object->get_required_permission();
        }
        $security = $this->get_registry()->get_utility('I_Security_Manager');
        $retval = $sec_token = $security->get_request_token(str_replace(array(' ', '
', '	'), '_', $privilege));
        $sec_actor = $security->get_current_actor();
        // Ensure that the user has permission to access this page
        if (!$sec_actor->is_allowed($privilege)) {
            $retval = FALSE;
        }
        // Ensure that nonce is valid
        if ($this->object->is_post_request() && !$sec_token->check_current_request()) {
            $retval = FALSE;
        }
        return $retval;
    }
    /**
     * Returns the permission required to access this page
     * @return string
     */
    public function get_required_permission()
    {
        return $this->object->name;
    }
    /**
     * Enqueues resources required by a NextGEN Admin page
     */
    public function enqueue_backend_resources()
    {
        wp_enqueue_script('jquery');
        $this->object->enqueue_jquery_ui_theme();
        wp_enqueue_script('photocrati_ajax');
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_script('nextgen_display_settings_page_placeholder_stub', $this->get_static_url('photocrati-nextgen_admin#jquery.placeholder.min.js'), array('jquery'), NGG_SCRIPT_VERSION, TRUE);
        wp_register_script('iris', $this->get_router()->get_url('/wp-admin/js/iris.min.js', FALSE, TRUE), array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), NGG_SCRIPT_VERSION);
        wp_register_script('wp-color-picker', $this->get_router()->get_url('/wp-admin/js/color-picker.js', FALSE, TRUE), array('iris'), NGG_SCRIPT_VERSION);
        wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array('clear' => __('Clear', 'nggallery'), 'defaultString' => __('Default', 'nggallery'), 'pick' => __('Select Color', 'nggallery'), 'current' => __('Current Color', 'nggallery')));
        wp_enqueue_script('nextgen_admin_page', $this->get_static_url('photocrati-nextgen_admin#nextgen_admin_page.js'), array('wp-color-picker'), NGG_SCRIPT_VERSION);
        wp_enqueue_style('nextgen_admin_page', $this->get_static_url('photocrati-nextgen_admin#nextgen_admin_page.css'), array('wp-color-picker'), NGG_SCRIPT_VERSION);
        // Ensure select2
        wp_enqueue_style('ngg_select2');
        wp_enqueue_script('ngg_select2');
    }
    public function enqueue_jquery_ui_theme()
    {
        $settings = C_NextGen_Settings::get_instance();
        wp_enqueue_style($settings->jquery_ui_theme, is_ssl() ? str_replace('http:', 'https:', $settings->jquery_ui_theme_url) : $settings->jquery_ui_theme_url, FALSE, $settings->jquery_ui_theme_version);
    }
    /**
     * Returns the page title
     * @return string
     */
    public function get_page_title()
    {
        return $this->object->name;
    }
    /**
     * Returns the page heading
     * @return string
     */
    public function get_page_heading()
    {
        return $this->object->get_page_title();
    }
    /**
     * Returns the type of forms to render on this page
     * @return string
     */
    public function get_form_type()
    {
        return is_array($this->object->context) ? $this->object->context[0] : $this->object->context;
    }
    public function get_success_message()
    {
        return __('Saved successfully', 'nggallery');
    }
    /**
     * Returns an accordion tab, encapsulating the form
     * @param I_Form $form
     */
    public function to_accordion_tab($form)
    {
        return $this->object->render_partial('photocrati-nextgen_admin#accordion_tab', array('id' => $form->get_id(), 'title' => $form->get_title(), 'content' => $form->render(TRUE)), TRUE);
    }
    /**
     * Returns the
     * @return type
     */
    public function get_forms()
    {
        $forms = array();
        $form_manager = C_Form_Manager::get_instance();
        foreach ($form_manager->get_forms($this->object->get_form_type()) as $form) {
            $forms[] = $this->get_registry()->get_utility('I_Form', $form);
        }
        return $forms;
    }
    /**
     * Gets the action to be executed
     * @return string
     */
    public function _get_action()
    {
        $retval = preg_quote($this->object->param('action'), '/');
        $retval = strtolower(preg_replace('/[^\\w]/', '_', $retval));
        return preg_replace('/_{2,}/', '_', $retval) . '_action';
    }
    /**
     * Returns the template to be rendered for the index action
     * @return string
     */
    public function index_template()
    {
        return 'photocrati-nextgen_admin#nextgen_admin_page';
    }
    /**
     * Returns a list of parameters to include when rendering the view
     * @return array
     */
    public function get_index_params()
    {
        return array();
    }
    public function show_save_button()
    {
        return TRUE;
    }
    /**
     * Renders a NextGEN Admin Page using jQuery Accordions
     */
    public function index_action()
    {
        $this->object->enqueue_backend_resources();
        if ($token = $this->object->is_authorized_request()) {
            // Get each form. Validate it and save any changes if this is a post
            // request
            $tabs = array();
            $errors = array();
            $action = $this->object->_get_action();
            $success = $this->param('message');
            if ($success) {
                $success = $this->object->get_success_message();
            } else {
                $success = $this->object->is_post_request() ? $this->object->get_success_message() : '';
            }
            // First, process the Post request
            if ($this->object->is_post_request() && $this->has_method($action)) {
                $this->object->{$action}($this->object->param($this->context));
            }
            foreach ($this->object->get_forms() as $form) {
                $form->page = $this->object;
                $form->enqueue_static_resources();
                if ($this->object->is_post_request()) {
                    if ($form->has_method($action)) {
                        $form->{$action}($this->object->param($form->context));
                    }
                }
                $tabs[] = $this->object->to_accordion_tab($form);
                if ($form->has_method('get_model') && $form->get_model()) {
                    if ($form->get_model()->is_invalid()) {
                        if ($form_errors = $this->object->show_errors_for($form->get_model(), TRUE)) {
                            $errors[] = $form_errors;
                        }
                        $form->get_model()->clear_errors();
                    }
                }
            }
            // Render the view
            $index_params = array('page_heading' => $this->object->get_page_heading(), 'tabs' => $tabs, 'errors' => $errors, 'success' => $success, 'form_header' => $token->get_form_html(), 'show_save_button' => $this->object->show_save_button(), 'model' => $this->object->has_method('get_model') ? $this->get_model() : NULL);
            $index_params = array_merge($index_params, $this->object->get_index_params());
            $this->render_partial($this->object->index_template(), $index_params);
        } else {
            $this->render_view('photocrati-nextgen_admin#not_authorized', array('name' => $this->object->name, 'title' => $this->object->get_page_title()));
        }
    }
}
class C_Page_Manager extends C_Component
{
    static $_instance = NULL;
    public $_pages = array();
    /**
     * Gets an instance of the Page Manager
     * @param string $context
     * @return C_Page_Manager
     */
    static function &get_instance($context = FALSE)
    {
        if (is_null(self::$_instance)) {
            $klass = get_class();
            self::$_instance = new $klass($context);
        }
        return self::$_instance;
    }
    /**
     * Defines the instance of the Page Manager
     * @param type $context
     */
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_Page_Manager');
        $this->implement('I_Page_Manager');
    }
}
class Mixin_Page_Manager extends Mixin
{
    public function add($slug, $properties = array())
    {
        if (!isset($properties['adapter'])) {
            $properties['adapter'] = NULL;
        }
        if (!isset($properties['parent'])) {
            $properties['parent'] = NULL;
        }
        if (!isset($properties['add_menu'])) {
            $properties['add_menu'] = TRUE;
        }
        if (!isset($properties['before'])) {
            $properties['before'] = NULL;
        }
        if (!isset($properties['url'])) {
            $properties['url'] = NULL;
        }
        $this->object->_pages[$slug] = $properties;
    }
    public function move_page($slug, $other_slug, $after = false)
    {
        $page_list = $this->object->_pages;
        if (isset($page_list[$slug]) && isset($page_list[$other_slug])) {
            $slug_list = array_keys($page_list);
            $item_list = array_values($page_list);
            $slug_idx = array_search($slug, $slug_list);
            $item = $page_list[$slug];
            unset($slug_list[$slug_idx]);
            unset($item_list[$slug_idx]);
            $slug_list = array_values($slug_list);
            $item_list = array_values($item_list);
            $other_idx = array_search($other_slug, $slug_list);
            array_splice($slug_list, $other_idx, 0, array($slug));
            array_splice($item_list, $other_idx, 0, array($item));
            $this->object->_pages = array_combine($slug_list, $item_list);
        }
    }
    public function remove($slug)
    {
        unset($this->object->_pages[$slug]);
    }
    public function get_all()
    {
        return $this->object->_pages;
    }
    public function setup()
    {
        $registry = $this->get_registry();
        $controllers = array();
        foreach ($this->object->_pages as $slug => $properties) {
            $page_title = 'Unnamed Page';
            $menu_title = 'Unnamed Page';
            $permission = NULL;
            $callback = NULL;
            // There's two type of pages we can have. Some are powered by our controllers, and others
            // are powered by WordPress, such as a custom post type page.
            // Is this powered by a controller? If so, we expect an adapter
            if ($properties['adapter']) {
                $controllers[$slug] = $registry->get_utility('I_NextGen_Admin_Page', $slug);
                $menu_title = $controllers[$slug]->get_page_heading();
                $page_title = $controllers[$slug]->get_page_title();
                $permission = $controllers[$slug]->get_required_permission();
                $callback = array(&$controllers[$slug], 'index_action');
            } elseif ($properties['url']) {
                $slug = $properties['url'];
                if (isset($properties['menu_title'])) {
                    $menu_title = $properties['menu_title'];
                }
                if (isset($properties['permission'])) {
                    $permission = $properties['permission'];
                }
            }
            // Are we to add a menu?
            if ($properties['add_menu'] && current_user_can($permission)) {
                add_submenu_page($properties['parent'], $page_title, $menu_title, $permission, $slug, $callback);
                if ($properties['before']) {
                    global $submenu;
                    if (empty($submenu[$properties['parent']])) {
                        $parent = null;
                    } else {
                        $parent = $submenu[$properties['parent']];
                    }
                    $item_index = -1;
                    $before_index = -1;
                    if ($parent != null) {
                        foreach ($parent as $index => $menu) {
                            // under add_submenu_page, $menu_slug is index 2
                            // $submenu[$parent_slug][] = array ( $menu_title, $capability, $menu_slug, $page_title );
                            if ($menu[2] == $slug) {
                                $item_index = $index;
                            } else {
                                if ($menu[2] == $properties['before']) {
                                    $before_index = $index;
                                }
                            }
                        }
                    }
                    if ($item_index > -1 && $before_index > -1) {
                        $item = $parent[$item_index];
                        unset($parent[$item_index]);
                        $parent = array_values($parent);
                        if ($item_index < $before_index) {
                            $before_index--;
                        }
                        array_splice($parent, $before_index, 0, array($item));
                        $submenu[$properties['parent']] = $parent;
                    }
                }
            }
        }
    }
}