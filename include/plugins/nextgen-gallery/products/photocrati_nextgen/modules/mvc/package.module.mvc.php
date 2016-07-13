<?php
class A_MVC_Factory extends Mixin
{
    public function mvc_view($template, $params = array(), $engine = 'php', $context = FALSE)
    {
        return new C_MVC_View($template, $params, $engine, $context);
    }
}
class A_MVC_Fs extends Mixin
{
    static $_lookups = array();
    static $_non_minified_modules = array();
    public function _get_cache_key()
    {
        return C_Photocrati_Transient_Manager::create_key('MVC', 'find_static_abspath');
    }
    public function initialize()
    {
        register_shutdown_function(array(&$this, 'cache_lookups'));
        //self::$_lookups = C_Photocrati_Transient_Manager::fetch($this->_get_cache_key(), array());
        self::$_non_minified_modules = apply_filters('ngg_non_minified_modules', array());
    }
    public function cache_lookups()
    {
        C_Photocrati_Transient_Manager::update($this->_get_cache_key(), self::$_lookups);
    }
    /**
     * Gets the absolute path to a static resource. If it doesn't exist, then NULL is returned
     *
     * @param string $path
     * @param string $module
     * @param string $relative
     * @return string|NULL
     */
    public function find_static_abspath($path, $module = FALSE, $relative = FALSE, &$found_root = FALSE)
    {
        $retval = NULL;
        $key = $this->_get_static_abspath_key($path, $module, $relative);
        // Have we looked up this resource before?
        if (isset(self::$_lookups[$key])) {
            $retval = self::$_lookups[$key];
        } else {
            // Get the module if we haven't got one yet
            if (!$module) {
                list($path, $module) = $this->object->parse_formatted_path($path);
            }
            // Lookup the module directory
            $mod_dir = $this->object->get_registry()->get_module_dir($module);
            $filter = has_filter('ngg_non_minified_files') ? apply_filters('ngg_non_minified_files', $path, $module) : FALSE;
            if (!defined('SCRIPT_DEBUG')) {
                define('SCRIPT_DEBUG', FALSE);
            }
            if (!SCRIPT_DEBUG && !in_array($module, self::$_non_minified_modules) && strpos($path, 'min.') === FALSE && strpos($path, 'pack.') === FALSE && strpos($path, 'packed.') === FALSE && preg_match('/\\.(js|css)$/', $path) && !$filter) {
                $path = preg_replace('#\\.[^\\.]+$#', '.min\\0', $path);
            }
            // In case NextGen is in a symlink we make $mod_dir relative to the NGG root and then rebuild it
            // using WP_PLUGIN_DIR; without this NGG-in-symlink creates URL that reference the file abspath
            if (is_link($this->object->join_paths(WP_PLUGIN_DIR, basename(NGG_PLUGIN_DIR)))) {
                $mod_dir = str_replace(dirname(NGG_PLUGIN_DIR), '', $mod_dir);
                $mod_dir = $this->object->join_paths(WP_PLUGIN_DIR, $mod_dir);
            }
            // Create the absolute path to the file
            $path = $this->object->join_paths($mod_dir, C_NextGen_Settings::get_instance()->get('mvc_static_dirname'), $path);
            if ($relative) {
                $original_length = strlen($path);
                $roots = array('plugins', 'plugins_mu', 'templates', 'stylesheets');
                $found_root = FALSE;
                foreach ($roots as $root) {
                    $path = str_replace($this->object->get_document_root($root), '', $path);
                    if (strlen($path) != $original_length) {
                        $found_root = $root;
                        break;
                    }
                }
            }
            // Cache result
            $retval = self::$_lookups[$key] = $path;
        }
        return $retval;
    }
    public function _get_static_abspath_key($path, $module = FALSE, $relative = FALSE)
    {
        $key = $path;
        if ($module) {
            $key .= '|' . $module;
        }
        if ($relative) {
            $key .= 'r';
        }
        global $wpdb;
        if ($wpdb) {
            $key .= '|' . $wpdb->blogid;
        }
        return $key;
    }
    /**
     * Gets the relative path to a static resource. If it doesn't exist, then NULL is returned
     *
     * @param string $path
     * @param string $module
     * @return string|NULL
     */
    public function find_static_relpath($path, $module = FALSE)
    {
        return $this->object->find_static_abspath($path, $module, TRUE);
    }
}
class A_MVC_Router extends Mixin
{
    static $_lookups = array();
    public function initialize()
    {
        register_shutdown_function(array(&$this, 'cache_lookups'));
        self::$_lookups = C_Photocrati_Transient_Manager::fetch($this->_get_cache_key(), array());
    }
    public function _get_cache_key()
    {
        return C_Photocrati_Transient_Manager::create_key('MVC', 'get_static_url');
    }
    public function cache_lookups()
    {
        C_Photocrati_Transient_Manager::update($this->_get_cache_key(), self::$_lookups);
    }
    public function _get_static_url_key($path, $module = FALSE)
    {
        $parts = array($path, $module, $this->object->get_base_url('plugins'), $this->object->get_base_url('plugins_mu'), $this->object->get_base_url('templates'), $this->object->get_base_url('stylesheets'));
        return implode('|', $parts);
    }
    /**
     * First tries to find the static file in the 'static' folder
     * @param string $path
     * @param string $module
     * @return string
     */
    public function get_static_url($path, $module = FALSE)
    {
        $retval = NULL;
        $key = $this->object->_get_static_url_key($path, $module);
        // Have we looked up this url before?
        if (isset(self::$_lookups[$key])) {
            $retval = self::$_lookups[$key];
        }
        $fs = C_Fs::get_instance();
        // Check for a user-supplied override
        if (NULL === $retval) {
            $formatted_path = $fs->parse_formatted_path($path);
            $abspath = $fs->join_paths($this->object->get_static_override_dir($formatted_path[1]), $formatted_path[0]);
            if (@is_file($abspath)) {
                $abspath = str_replace($fs->get_document_root('content'), '', $abspath);
                $retval = self::$_lookups[$key] = $this->object->join_paths($this->object->get_base_url('content'), str_replace('\\', '/', $abspath));
            }
        }
        // We'll have to calculate the url from our own modules
        if (NULL === $retval) {
            $path = $fs->find_static_abspath($path, $module);
            $original_length = strlen($path);
            $roots = array('plugins', 'plugins_mu', 'templates', 'stylesheets');
            $found_root = FALSE;
            foreach ($roots as $root) {
                $path = str_replace($fs->get_document_root($root), '', $path);
                if (strlen($path) != $original_length) {
                    $found_root = $root;
                    break;
                }
            }
            // We found the root so we know what base url to prepend
            if ($found_root) {
                $retval = self::$_lookups[$key] = $this->object->join_paths($this->object->get_base_url($found_root), str_replace('\\', '/', $path));
            } else {
                $retval = self::$_lookups[$key] = $this->object->join_paths($this->object->get_base_url('root'), str_replace('\\', '/', $path));
            }
        }
        // For the "Sage" theme and others using the "Soil" plugin "Roots" theme was re-branded to "Sage" theme
        // 2015-02-25; see https://roots.io/new-website-sage-and-the-future/
        if ((current_theme_supports('soil-relative-urls') || current_theme_supports('root-relative-urls')) && strpos($retval, '/') !== 0) {
            $retval = '/' . $retval;
        }
        return $retval;
    }
    /**
     * @param string $module_id
     *
     * @return string $dir
     */
    public function get_static_override_dir($module_id = NULL)
    {
        $fs = C_Fs::get_instance();
        $dir = $fs->join_paths(WP_CONTENT_DIR, 'ngg');
        if (!@file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        $dir = $fs->join_paths($dir, 'modules');
        if (!@file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        if ($module_id) {
            $dir = $fs->join_paths($dir, $module_id);
            if (!@file_exists($dir)) {
                wp_mkdir_p($dir);
            }
            $dir = $fs->join_paths($dir, 'static');
            if (!@file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
        return $dir;
    }
}
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
    die('You are not allowed to call this page directly.');
}
class Mixin_MVC_Controller_Defaults extends Mixin
{
    // Provide a default view
    public function index_action($return = FALSE)
    {
        return $this->render_view('photocrati-mvc#index', array(), $return);
    }
}
/**
 * Provides actions that are executed based on the requested url
 */
abstract class C_MVC_Controller extends C_Component
{
    public $_content_type = 'text/html';
    public $message = '';
    public $debug = FALSE;
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_MVC_Controller_Defaults');
        $this->add_mixin('Mixin_MVC_Controller_Instance_Methods');
        $this->implement('I_MVC_Controller');
    }
}
/**
 * Adds methods for MVC Controller
 */
class Mixin_MVC_Controller_Instance_Methods extends Mixin
{
    public function set_content_type($type)
    {
        switch ($type) {
            case 'html':
            case 'xhtml':
                $type = 'text/html';
                break;
            case 'xml':
                $type = 'text/xml';
                break;
            case 'rss':
            case 'rss2':
                $type = 'application/rss+xml';
                break;
            case 'css':
                $type = 'text/css';
                break;
            case 'javascript':
            case 'jscript':
            case 'emcascript':
                $type = 'text/javascript';
                break;
            case 'json':
                $type = 'application/json';
                break;
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                $type = 'image/jpeg';
                break;
            case 'gif':
                $type = 'image/gif';
                break;
            case 'png':
                $type = 'image/x-png';
                break;
            case 'tiff':
            case 'tif':
                $type = 'image/tiff';
                break;
            case 'pdf':
                $type = 'application/pdf';
                break;
        }
        $this->object->_content_type = $type;
        return $type;
    }
    public function do_not_cache()
    {
        if (!headers_sent()) {
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
        }
    }
    public function expires($time)
    {
        $time = strtotime($time);
        if (!headers_sent()) {
            header('Expires: ' . strftime('%a, %d %b %Y %H:%M:%S %Z', $time));
        }
    }
    public function http_error($message, $code = 501)
    {
        $this->message = $message;
        $method = "http_{$code}_action";
        $this->{$method}();
    }
    public function is_valid_request($method)
    {
        return TRUE;
    }
    public function is_post_request()
    {
        return 'POST' == $this->object->get_router()->get_request_method();
    }
    public function is_get_request()
    {
        return 'GET' == $this->object->get_router()->get_request_method();
    }
    public function is_delete_request()
    {
        return 'DELETE' == $this->object->get_router()->get_request_method();
    }
    public function is_put_request()
    {
        return 'PUT' == $this->object->get_router()->get_request_method();
    }
    public function is_custom_request($type)
    {
        return strtolower($type) == strtolower($this->object->get_router()->get_request_method());
    }
    public function get_router()
    {
        return C_Router::get_instance();
    }
    public function get_routed_app()
    {
        return $this->object->get_router()->get_routed_app();
    }
    /**
     * Returns the value of a parameters
     * @param string $key
     * @return mixed
     */
    public function param($key, $prefix = NULL, $default = NULL)
    {
        return $this->object->get_routed_app()->get_parameter($key, $prefix, $default);
    }
    public function set_param($key, $value, $id = NULL, $use_prefix = FALSE)
    {
        return $this->object->get_routed_app()->set_parameter($key, $value, $id, $use_prefix);
    }
    public function set_param_for($url, $key, $value, $id = NULL, $use_prefix = FALSE)
    {
        return $this->object->get_routed_app()->set_parameter($key, $value, $id, $use_prefix, $url);
    }
    public function remove_param($key, $id = NULL)
    {
        return $this->object->get_routed_app()->remove_parameter($key, $id);
    }
    public function remove_param_for($url, $key, $id = NULL)
    {
        $app = $this->object->get_routed_app();
        $retval = $app->remove_parameter($key, $id, $url);
        return $retval;
    }
    /**
     * Gets the routed url, generated by the Routing App
     * @return string
     */
    public function get_routed_url($with_qs = FALSE)
    {
        return $this->object->get_routed_app()->get_app_url(FALSE, $with_qs);
    }
    /**
     * Gets the absolute path of a static resource
     * @param string $path
     * @param string $module
     * @param boolean $relative
     * @return string
     */
    public function get_static_abspath($path, $module = FALSE, $relative = FALSE)
    {
        return C_Fs::get_instance()->find_static_abspath($path, $module);
    }
    /**
     * Gets the relative path of a static resource
     * @param string $path
     * @param string $module
     * @return string
     */
    public function get_static_relpath($path, $module = FALSE)
    {
        return C_Fs::get_instance()->find_static_abspath($path, $module, TRUE);
    }
    public function get_static_url($path, $module = FALSE)
    {
        return C_Router::get_instance()->get_static_url($path, $module);
    }
    /**
     * Renders a template and outputs the response headers
     * @param string $name
     * @param array $vars
     */
    public function render_view($name, $vars = array(), $return = FALSE)
    {
        $this->object->render();
        return $this->object->render_partial($name, $vars, $return);
    }
    /**
     * Outputs the response headers
     */
    public function render()
    {
        if (!headers_sent()) {
            header('Content-Type: ' . $this->object->_content_type . '; charset=' . get_option('blog_charset'), true);
        }
    }
    /**
     * Renders a view
     */
    public function render_partial($template, $params = array(), $return = FALSE, $context = NULL)
    {
        // We'll use the name of the view as the context if one hasn't been provided
        if (is_null($context)) {
            $context = $template;
        }
        $view = $this->object->create_view($template, $params, $context);
        return $view->render($return);
    }
    public function create_view($template, $params = array(), $context = NULL)
    {
        $factory = C_Component_Factory::get_instance();
        $view = $factory->create('mvc_view', $template, $params, NULL, $context);
        return $view;
    }
}
class C_MVC_View extends C_Component
{
    public $_template = '';
    public $_engine = '';
    public $_params = array();
    public $_queue = array();
    public function define($template, $params = array(), $engine = 'php', $context = FALSE)
    {
        parent::define($context);
        $this->implement('I_MVC_View');
        $this->add_mixin('Mixin_Mvc_View_Instance_Methods');
    }
    /**
     * Initialize the view with some parameters
     * @param array $params
     * @param context $context
     */
    public function initialize($template, $params = array(), $engine = 'php', $context = FALSE)
    {
        parent::initialize($context);
        $this->_template = $template;
        $this->_params = (array) $params;
        $this->_engine = $engine;
    }
}
class Mixin_Mvc_View_Instance_Methods extends Mixin
{
    /**
     * Returns the variables to be used in the template
     * @return array
     */
    public function get_template_vars()
    {
        $retval = array();
        foreach ($this->object->_params as $key => $value) {
            if (strpos($key, '_template') !== FALSE) {
                $value = $this->object->get_template_abspath($value);
            }
            $retval[$key] = $value;
        }
        return $retval;
    }
    /**
     * Returns the abspath of the template to be rendered
     * @param string $key
     * @return string
     */
    public function get_template_abspath($value = NULL)
    {
        if (!$value) {
            $value = $this->object->_template;
        }
        if ($value[0] == '/' && @file_exists($value)) {
        } else {
            $value = $this->object->find_template_abspath($value);
        }
        return $value;
    }
    /**
     * Renders the view (template)
     * @param string $__return
     * @return string|NULL
     */
    public function render($return = FALSE)
    {
        $element = $this->object->render_object();
        $content = $this->object->rasterize_object($element);
        if (!$return) {
            echo $content;
        }
        return $content;
    }
    public function render_object()
    {
        // We use underscores to prefix local variables to avoid conflicts wth
        // template vars
        $__element = $this->start_element($this->object->_template, 'template', $this->object);
        extract($this->object->get_template_vars());
        include $this->object->get_template_abspath();
        $this->end_element();
        return $__element;
    }
    public function rasterize_object($element)
    {
        return $element->rasterize();
    }
    public function start_element($id, $type = null, $context = null)
    {
        if ($type == null) {
            $type = 'element';
        }
        $count = count($this->object->_queue);
        $element = new C_MVC_View_Element($id, $type);
        if ($context != null) {
            if (!is_array($context)) {
                $context = array('object' => $context);
            }
            foreach ($context as $context_name => $context_value) {
                $element->set_context($context_name, $context_value);
            }
        }
        $this->object->_queue[] = $element;
        if ($count > 0) {
            $old_element = $this->object->_queue[$count - 1];
            $content = ob_get_contents();
            ob_clean();
            $old_element->append($content);
            $old_element->append($element);
        }
        ob_start();
        return $element;
    }
    public function end_element()
    {
        $content = ob_get_clean();
        $element = array_pop($this->object->_queue);
        if ($content != null) {
            $element->append($content);
        }
        return $element;
    }
    /**
     * Renders a sub-template for the view
     * @param string $__template
     * @param array $__params
     * @param string $__return
     * @return NULL
     */
    public function include_template($__template, $__params = null, $__return = FALSE)
    {
        // We use underscores to prefix local variables to avoid conflicts wth
        // template vars
        if ($__params == null) {
            $__params = array();
        }
        $__params['template_origin'] = $this->object->_template;
        $__target = $this->object->get_template_abspath($__template);
        $__origin_target = $this->object->get_template_abspath($this->object->_template);
        $__image_before_target = $this->object->get_template_abspath('photocrati-nextgen_gallery_display#image/before');
        $__image_after_target = $this->object->get_template_abspath('photocrati-nextgen_gallery_display#image/after');
        if ($__origin_target != $__target) {
            if ($__target == $__image_before_target) {
                $__image = isset($__params['image']) ? $__params['image'] : null;
                $this->start_element('nextgen_gallery.image_panel', 'item', $__image);
            }
            if ($__target == $__image_after_target) {
                $this->end_element();
            }
            extract($__params);
            include $__target;
            if ($__target == $__image_before_target) {
                $__image = isset($__params['image']) ? $__params['image'] : null;
                $this->start_element('nextgen_gallery.image', 'item', $__image);
            }
            if ($__target == $__image_after_target) {
                $this->end_element();
            }
        }
    }
    /**
     * Gets the absolute path of an MVC template file
     *
     * @param string $path
     * @param string $module
     * @return string
     */
    public function find_template_abspath($path, $module = FALSE)
    {
        $fs = C_Fs::get_instance();
        $settings = C_NextGen_Settings::get_instance();
        // We also accept module_name#path, which needs parsing.
        if (!$module) {
            list($path, $module) = $fs->parse_formatted_path($path);
        }
        // Append the suffix
        $path = $path . '.php';
        // First check if the template is in the override dir
        if (!($retval = $this->object->get_template_override_abspath($module, $path))) {
            $retval = $fs->join_paths($this->object->get_registry()->get_module_dir($module), $settings->mvc_template_dirname, $path);
        }
        if (!@file_exists($retval)) {
            throw new RuntimeException("{$retval} is not a valid MVC template");
        }
        return $retval;
    }
    public function get_template_override_dir($module = NULL)
    {
        $fs = C_Fs::get_instance();
        $dir = $fs->join_paths(WP_CONTENT_DIR, 'ngg');
        if (!@file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        $dir = $fs->join_paths($dir, 'modules');
        if (!@file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        if ($module) {
            $dir = $fs->join_paths($dir, $module);
            if (!@file_exists($dir)) {
                wp_mkdir_p($dir);
            }
            $dir = $fs->join_paths($dir, 'templates');
            if (!@file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
        return $dir;
    }
    public function get_template_override_abspath($module, $filename)
    {
        $fs = C_Fs::get_instance();
        $retval = NULL;
        $abspath = $fs->join_paths($this->object->get_template_override_dir($module), $filename);
        if (@file_exists($abspath)) {
            $retval = $abspath;
        }
        return $retval;
    }
    /**
     * Adds a template parameter
     * @param $key
     * @param $value
     */
    public function set_param($key, $value)
    {
        $this->object->_params[$key] = $value;
    }
    /**
     * Removes a template parameter
     * @param $key
     */
    public function remove_param($key)
    {
        unset($this->object->_params[$key]);
    }
    /**
     * Gets the value of a template parameter
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get_param($key, $default = NULL)
    {
        if (isset($this->object->_params[$key])) {
            return $this->object->_params[$key];
        } else {
            return $default;
        }
    }
}
class C_MVC_View_Element
{
    public $_id;
    public $_type;
    public $_list;
    public $_context;
    public function __construct($id, $type = null)
    {
        $this->_id = $id;
        $this->_type = $type;
        $this->_list = array();
        $this->_context = array();
    }
    public function get_id()
    {
        return $this->_id;
    }
    public function append($child)
    {
        $this->_list[] = $child;
    }
    public function insert($child, $position = 0)
    {
        array_splice($this->_list, $position, 0, $child);
    }
    public function delete($child)
    {
        $index = array_search($child, $this->_list);
        if ($index !== false) {
            array_splice($this->_list, $index, 1);
        }
    }
    public function find($id, $recurse = false)
    {
        $list = array();
        $this->_find($list, $id, $recurse);
        return $list;
    }
    public function _find(array &$list, $id, $recurse = false)
    {
        foreach ($this->_list as $index => $element) {
            if ($element instanceof C_MVC_View_Element) {
                if ($element->get_id() == $id) {
                    $list[] = $element;
                }
                if ($recurse) {
                    $element->_find($list, $id, $recurse);
                }
            }
        }
    }
    public function get_context($name)
    {
        if (isset($this->_context[$name])) {
            return $this->_context[$name];
        }
        return null;
    }
    public function set_context($name, $value)
    {
        $this->_context[$name] = $value;
    }
    public function get_object()
    {
        return $this->get_context('object');
    }
    // XXX not implemented
    public function parse()
    {
    }
    public function rasterize()
    {
        $ret = null;
        foreach ($this->_list as $index => $element) {
            if ($element instanceof C_MVC_View_Element) {
                $ret .= $element->rasterize();
            } else {
                $ret .= (string) $element;
            }
        }
        return $ret;
    }
}