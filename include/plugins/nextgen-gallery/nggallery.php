<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * Plugin Name: NextGEN Gallery
 * Description: The most popular gallery plugin for WordPress and one of the most popular plugins of all time with over 15 million downloads.
 * Version: 2.1.46
 * Author: Imagely
 * Plugin URI: https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/
 * Author URI: https://www.imagely.com
 * License: GPLv2
 * Text Domain: nggallery
 * Domain Path: /products/photocrati_nextgen/modules/i18n/lang
 */

if (!class_exists('E_Clean_Exit')) { class E_Clean_Exit extends RuntimeException {} }
if (!class_exists('E_NggErrorException')) { class E_NggErrorException extends RuntimeException {} }

// This is a temporary function to replace the use of WP's esc_url which strips spaces away from URLs
// TODO: Move this to a better place
if (!function_exists('nextgen_esc_url')) {
	function nextgen_esc_url( $url, $protocols = null, $_context = 'display' ) {
		$original_url = $url;

		if ( '' == $url )
			return $url;
		$url = preg_replace('|[^a-z0-9 \\-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
		$strip = array('%0d', '%0a', '%0D', '%0A');
		$url = _deep_replace($strip, $url);
		$url = str_replace(';//', '://', $url);
		/* If the URL doesn't appear to contain a scheme, we
		 * presume it needs http:// appended (unless a relative
		 * link starting with /, # or ? or a php file).
		 */

		if ( strpos($url, ':') === false && ! in_array( $url[0], array( '/', '#', '?' ) ) &&
		     ! preg_match('/^[a-z0-9-]+?\.php/i', $url) )
			//$url = 'http://' . $url;

		// Replace ampersands and single quotes only when displaying.
		if ( 'display' == $_context ) {
			$url = wp_kses_normalize_entities( $url );
			$url = str_replace( '&amp;', '&#038;', $url );
			$url = str_replace( "'", '&#039;', $url );
			$url = str_replace( ' ', '%20', $url );
		}

		if ( '/' === $url[0] ) {
			$good_protocol_url = $url;
		} else {
			if ( ! is_array( $protocols ) )
				$protocols = wp_allowed_protocols();
			$good_protocol_url = wp_kses_bad_protocol( $url, $protocols );
			if ( strtolower( $good_protocol_url ) != strtolower( $url ) )
				return '';
		}

		return apply_filters('clean_url', $good_protocol_url, $original_url, $_context);
	}
}

/**
 * NextGEN Gallery is built on top of the Photocrati Pope Framework:
 * https://bitbucket.org/photocrati/pope-framework
 *
 * Pope constructs applications by assembling modules.
 *
 * The Bootstrapper. This class performs the following:
 * 1) Loads the Pope Framework
 * 2) Adds a path to the C_Component_Registry instance to search for products
 * 3) Loads all found Products. A Product is a collection of modules with some
 * additional meta data. A Product is responsible for loading any modules it
 * requires.
 * 4) Once all Products (and their associated modules) have been loaded (or in
 * otherwords, "included"), the modules are initialized.
 */
class C_NextGEN_Bootstrap
{
	var $_registry = NULL;
	var $_settings_option_name = 'ngg_options';
	var $_pope_loaded = FALSE;
	static $debug = FALSE;
	var $minimum_ngg_pro_version = '2.0.5';
    var $minimum_ngg_plus_version = '1.0.1';

	static function shutdown($exception=NULL)
	{
		if (is_null($exception)) {
			throw new E_Clean_Exit;
		}
		elseif (!($exception instanceof E_Clean_Exit)) {
			ob_end_clean();
			self::print_exception($exception);
		}

	}

	static function print_exception($exception)
	{
		$klass = get_class($exception);
		echo "<h1>{$klass} thrown</h1>";
		echo "<p>{$exception->getMessage()}</p>";
		if (self::$debug OR (defined('NGG_DEBUG') AND NGG_DEBUG == TRUE)) {
			echo "<h3>Where:</h3>";
			echo "<p>On line <strong>{$exception->getLine()}</strong> of <strong>{$exception->getFile()}</strong></p>";
			echo "<h3>Trace:</h3>";
			echo "<pre>{$exception->getTraceAsString()}</pre>";
			if (method_exists($exception, 'getPrevious')) {
				if (($previous = $exception->getPrevious())) {
					self::print_exception($previous);
				}
			}
		}
	}

	static function get_backtrace($objects=FALSE, $remove_dynamic_calls=TRUE)
	{
		$trace = debug_backtrace($objects);
		if ($remove_dynamic_calls) {
			$skip_methods = array(
				'_exec_cached_method',
				'__call',
				'get_method_property',
				'set_method_property',
				'call_method'
			);
			foreach ($trace as $key => &$value) {
				if (isset($value['class']) && isset($value['function'])) {
					if ($value['class'] == 'ReflectionMethod' && $value['function'] == 'invokeArgs')
						unset($trace[$key]);

					else if ($value['class'] == 'ExtensibleObject' && in_array($value['function'], $skip_methods))
						unset($trace[$key]);
				}
			}
		}

		return $trace;
	}

	function __construct()
	{
		set_exception_handler(__CLASS__.'::shutdown');

		// We only load the plugin if we're outside of the activation request, loaded in an iframe
		// by WordPress. Reason being, if WP_DEBUG is enabled, and another Pope-based plugin (such as
		// the photocrati theme or NextGEN Pro/Plus), then PHP will output strict warnings
		if ($this->is_not_activating()) {
			$this->_define_constants();
			$this->_load_non_pope();
			$this->_register_hooks();
			$this->_load_pope();
		}
	}

	function is_not_activating()
	{
		return !$this->is_activating();
	}

	function is_activating()
	{
		$retval =  strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== FALSE && isset($_REQUEST['action']) && $_REQUEST['action'] == 'activate';
		
		if (!$retval && strpos($_SERVER['REQUEST_URI'], 'update.php') !== FALSE && isset($_REQUEST['action']) && $_REQUEST['action'] == 'install-plugin' && isset($_REQUEST['plugin']) && strpos($_REQUEST['plugin'], 'nextgen-gallery') === 0) {
			$retval = TRUE;
		}
		
		if (!$retval && strpos($_SERVER['REQUEST_URI'], 'update.php') !== FALSE && isset($_REQUEST['action']) && $_REQUEST['action'] == 'activate-plugin' && isset($_REQUEST['plugin']) && strpos($_REQUEST['plugin'], 'nextgen-gallery') === 0) {
			$retval = TRUE;
		}

        // Omitted for now; this was merged at the wrong time
		/* if (!$retval && isset($_REQUEST['tgmpa-activate']) && $_REQUEST['tgmpa-activate'] == 'activate-plugin' && isset($_REQUEST['plugin']) && strtolower($_REQUEST['plugin']) == 'nextgen-gallery') {
			$retval = TRUE;
		} */
		
		return $retval;
	}

	function _load_non_pope()
	{
		// Load caching component
		include_once('non_pope/class.photocrati_transient_manager.php');

		if (isset($_REQUEST['ngg_flush']) OR isset($_REQUEST['ngg_flush_expired'])) {
			C_Photocrati_Transient_Manager::flush();
			die("Flushed all caches");
		}

		// Load Settings Manager
		include_once('non_pope/class.photocrati_settings_manager.php');
		include_once('non_pope/class.nextgen_settings.php');
		C_Photocrati_Global_Settings_Manager::$option_name = $this->_settings_option_name;
		C_Photocrati_Settings_Manager::$option_name = $this->_settings_option_name;

		// Load the installer
		include_once('non_pope/class.photocrati_installer.php');

		// Load the resource manager
		include_once('non_pope/class.photocrati_resource_manager.php');
		C_Photocrati_Resource_Manager::init();

		// Load the style manager
		include_once('non_pope/class.nextgen_style_manager.php');

		// Load the shortcode manager
		include_once('non_pope/class.nextgen_shortcode_manager.php');
	}

	/**
	 * Loads the Pope Framework
	 */
	function _load_pope()
	{
		// No need to initialize pope again
		if ($this->_pope_loaded) return;

		// Pope requires a a higher limit
		$tmp = ini_get('xdebug.max_nesting_level');
		if ($tmp && (int)$tmp <= 300) @ini_set('xdebug.max_nesting_level', 300);

		// Include pope framework
		require_once(implode(
			DIRECTORY_SEPARATOR, array(NGG_PLUGIN_DIR, 'pope','lib','autoload.php')
		));

		// Enable/disable pope caching. For now, the pope cache will not be used in multisite environments
		if (class_exists('C_Pope_Cache')) {
			if ((C_Pope_Cache::$enabled = NGG_POPE_CACHE)) {
				$blogid = (is_multisite() ? get_current_blog_id() : NULL);
				if (isset($_SERVER['SERVER_ADDR']))
					$cache_key_prefix = abs(crc32((implode('|', array($blogid, site_url(), AUTH_KEY, $_SERVER['SERVER_ADDR'])))));
				else
					$cache_key_prefix = abs(crc32(implode('|', array($blogid, site_url(), AUTH_KEY))));

				C_Pope_Cache::set_driver('C_Pope_Cache_SingleFile');
				C_Pope_Cache::add_key_prefix($cache_key_prefix);
			}
		}

		// Enforce interfaces
		if (property_exists('ExtensibleObject', 'enforce_interfaces')) ExtensibleObject::$enforce_interfaces = EXTENSIBLE_OBJECT_ENFORCE_INTERFACES;

		// Get the component registry
		$this->_registry = C_Component_Registry::get_instance();

		// Add the default Pope factory utility, C_Component_Factory
		$this->_registry->add_utility('I_Component_Factory', 'C_Component_Factory');

		// Blacklist any modules which are known NOT to work with this version of NextGEN Gallery
		// We need to check if we have this ability as it's only available with Pope 0.9
		if (method_exists($this->_registry, 'blacklist_module_file')) {
			$this->_registry->blacklist_module_file('module.nextgen_pro_lightbox_legacy.php');
			$this->_registry->blacklist_module_file('module.protect_image.php');
			// TODO: Add module id for protect image
		}

		// If Pro is incompatible, then we need to blacklist all of Pro's modules
		// TODO: Pope needs a better way of introspecting into a product's list of provided modules
		if ($this->is_pro_incompatible()) {
			$pro_modules = array(
				'photocrati-comments',
				'photocrati-galleria',
				'photocrati-nextgen_pro_slideshow',
				'photocrati-nextgen_pro_horizontal_filmstrip',
				'photocrati-nextgen_pro_thumbnail_grid',
				'photocrati-nextgen_pro_blog_gallery',
				'photocrati-nextgen_pro_film',
				'photocrati-nextgen_pro_masonry',
				'photocrati-nextgen_pro_albums',
				'photocrati-nextgen_pro_lightbox',
				'photocrati-nextgen_pro_lightbox_legacy',
				'photocrati-nextgen_pro_ecommerce',
				'photocrati-paypal_express_checkout',
				'photocrati-paypal_standard',
				'photocrati-stripe'
			);
			foreach ($pro_modules as $mod) $this->_registry->blacklist_module_file($mod);
		}

		// Load embedded products. Each product is expected to load any
		// modules required
		$this->_registry->add_module_path(NGG_PRODUCT_DIR, 2, false);
		$this->_registry->load_all_products();

		// Give third-party plugins that opportunity to include their own products
		// and modules
		do_action('load_nextgen_gallery_modules', $this->_registry);

		// Initializes all loaded modules
		$this->_registry->initialize_all_modules();

		$this->_pope_loaded = TRUE;
	}

	function is_pro_compatible()
	{
		$retval = TRUE;

        if (defined('NEXTGEN_GALLERY_PRO_VERSION')) $retval = FALSE;
        if (defined('NEXTGEN_GALLERY_PRO_PLUGIN_BASENAME') && !defined('NGG_PRO_PLUGIN_VERSION')) $retval = FALSE; // 1.0 - 1.0.6
		if (defined('NGG_PRO_PLUGIN_VERSION')  && version_compare(NGG_PRO_PLUGIN_VERSION,  $this->minimum_ngg_pro_version)  < 0) $retval = FALSE;
        if (defined('NGG_PLUS_PLUGIN_VERSION') && version_compare(NGG_PLUS_PLUGIN_VERSION, $this->minimum_ngg_plus_version) < 0) $retval = FALSE;

		return $retval;
	}

	function is_pro_incompatible()
	{
		return !$this->is_pro_compatible();
	}

	function render_incompatibility_warning()
	{
		echo '<div class="updated error"><p>';
		echo esc_html(
			sprintf(
				__("NextGEN Gallery %s is incompatible with this version of NextGEN Pro. Please update NextGEN Pro to version %s or higher to restore NextGEN Pro functionality.",
					'nggallery'
				),
				NGG_PLUGIN_VERSION, $this->minimum_ngg_pro_version
			));
		echo '</p></div>';
	}


	/**
	 * Registers hooks for the WordPress framework necessary for instantiating
	 * the plugin
	 */
	function _register_hooks()
	{
		// Register the deactivation routines
		add_action('deactivate_'.NGG_PLUGIN_BASENAME, array(get_class(), 'deactivate'));

		// Register our test suite
		add_filter('simpletest_suites', array(&$this, 'add_testsuite'));

		// Ensure that settings manager is saved as an array
		add_filter('pre_update_option_'.$this->_settings_option_name, array(&$this, 'persist_settings'));
		add_filter('pre_update_site_option_'.$this->_settings_option_name, array(&$this, 'persist_settings'));

		// This plugin uses jQuery extensively
		if (NGG_FIX_JQUERY) {
			add_action('wp_enqueue_scripts', array(&$this, 'fix_jquery'));
			add_action('wp_print_scripts', array(&$this, 'fix_jquery'));
		}

		// If the selected stylesheet is using an unsafe path, then notify the user
		add_action('all_admin_notices', array(&$this, 'display_stylesheet_notice'));

		// Delete displayed gallery transients periodically
		if (NGG_CRON_ENABLED) {
			add_filter('cron_schedules', array(&$this, 'add_ngg_schedule'));
			add_action('ngg_delete_expired_transients', array(&$this, 'delete_expired_transients'));
			add_action('wp', array(&$this, 'schedule_cron_jobs'));
		}

		// Update modules
		add_action('init', array(&$this, 'update'), PHP_INT_MAX-1);

		// Start the plugin!
		add_action('init', array(&$this, 'route'), 11);

		// Flush pope cache
		add_action('init', array(&$this, 'flush_pope_cache'));

		// Display a warning if an compatible version of NextGEN Pro is installed alongside this
		// version of NextGEN Gallery
		if ($this->is_pro_incompatible()) {
			add_filter('http_request_args', array(&$this, 'fix_autoupdate_api_requests'), 10, 2);
			add_action('all_admin_notices', array(&$this, 'render_incompatibility_warning'));
		}

		add_filter('ngg_load_frontend_logic', array($this, 'disable_frontend_logic'), -10, 2);
	}

	function disable_frontend_logic($enabled, $module_id)
	{
		if (is_admin())
		{
			$settings = C_NextGen_Settings::get_instance();
			if (!$settings->get('always_enable_frontend_logic'))
				$enabled = FALSE;
		}
		return $enabled;
	}

	function fix_autoupdate_api_requests($args, $url)
	{
		// Is this an HTTP request to the licensing server?
		if (preg_match("/api_act=/", $url)) {
			$args['autoupdate'] = TRUE;

			// If we're supposed to pass all Pro modules, then include them here
			if (preg_match("/api_act=(ckups|cklic)/", $url) && isset($args['body']) && is_array($args['body']) && isset($args['body']['module-list'])) {
				$pro_modules = array(
					'photocrati-comments',
					'photocrati-galleria',
					'photocrati-nextgen_pro_slideshow',
					'photocrati-nextgen_pro_horizontal_filmstrip',
					'photocrati-nextgen_pro_thumbnail_grid',
					'photocrati-nextgen_pro_blog_gallery',
					'photocrati-nextgen_pro_film',
					'photocrati-nextgen_pro_masonry',
					'photocrati-nextgen_pro_albums',
					'photocrati-auto_update',
					'photocrati-auto_update-admin',
					'photocrati-nextgen_pro_lightbox',
					'photocrati-nextgen_pro_lightbox_legacy',
					'photocrati-nextgen_pro_ecommerce',
					'photocrati-paypal_express_checkout',
					'photocrati-paypal_standard',
					'photocrati-stripe'
				);
				foreach ($pro_modules as $mod) {
					if (!isset($args['body']['module-list'][$mod])) $args['body']['module-list'][$mod] = '0.1';
				}
			}
		}
		return $args;
	}

	function flush_pope_cache()
	{
		if (is_user_logged_in() && current_user_can('manage_options') && isset($_REQUEST['ngg_flush_pope_cache'])) {
			C_Pope_Cache::get_instance()->flush();
			print "Flushed pope cache";
			exit;
		}
	}

	function schedule_cron_jobs()
	{
		if (!wp_next_scheduled('ngg_delete_expired_transients')) {
			wp_schedule_event(time(), 'ngg_custom', 'ngg_delete_expired_transients');
		}
	}

	/**
	 * Defines a new cron schedule
	 * @param $schedules
	 * @return mixed
	 */
	function add_ngg_schedule($schedules)
	{
		$schedules['ngg_custom'] = array(
			'interval'	=>	NGG_CRON_SCHEDULE,
			'display'	=>	sprintf(__('Every %d seconds', 'nggallery'), NGG_CRON_SCHEDULE)
		);

		return $schedules;
	}


	/**
	 * Flush all expires transients created by the plugin
	 */
	function delete_expired_transients()
	{
		C_Photocrati_Transient_Manager::flush();
	}

	/**
	 * Ensure that C_Photocrati_Settings_Manager gets persisted as an array
	 * @param $settings
	 * @return array
	 */
	function persist_settings($settings)
	{
		if (is_object($settings) && $settings instanceof C_Photocrati_Settings_Manager_Base) {
			$settings = $settings->to_array();
		}
		return $settings;
	}

	/**
	 * Ensures that the version of JQuery used is expected for NextGEN Gallery
	 */
	function fix_jquery()
	{
		global $wp_scripts;

		// Determine which version of jQuery to include
		$src = '/wp-includes/js/jquery/jquery.js';

		// Ensure that jQuery is always set to the default
		if (isset($wp_scripts->registered['jquery'])) {
			$jquery = $wp_scripts->registered['jquery'];

			// There's an exception to the rule. We'll allow the same
			// version of jQuery as included with WP to be fetched from
			// Google AJAX libraries, as we have a systematic means of verifying
			// that won't cause any troubles
			$version = preg_quote($jquery->ver, '#');
			if (!preg_match("#ajax\\.googleapis\\.com/ajax/libs/jquery/{$version}/jquery\\.min\\.js#", $jquery->src)) {
				$jquery->src = FALSE;
				if (array_search('jquery-core', $jquery->deps) === FALSE) {
					$jquery->deps[] = 'jquery-core';
				}
				if (array_search('jquery-migrate', $jquery->deps) === FALSE) {
					$jquery->deps[] = 'jquery-migrate';
				}
			}
		}

		// Ensure that jquery-core is used, as WP intended
		if (isset($wp_scripts->registered['jquery-core'])) {
			$wp_scripts->registered['jquery-core']->src = $src;
		}

		wp_enqueue_script('jquery');
	}

	/**
	 * Displays a notice to the user that the current stylesheet location is unsafe
	 */
	function display_stylesheet_notice()
	{
		if (C_NextGen_Style_Manager::get_instance()->is_directory_unsafe()) {
			$styles		= C_NextGen_Style_Manager::get_instance();
			$filename	= $styles->get_selected_stylesheet();
			$abspath	= $styles->find_selected_stylesheet_abspath();
			$newpath	= $styles->new_dir;

			echo "<div class='updated error'>
                <h3>WARNING: NextGEN Gallery Stylesheet NOT Upgrade-safe</h3>
                <p>
                <strong>{$filename}</strong> is currently stored in <strong>{$abspath}</strong>, which isn't upgrade-safe. Please move the stylesheet to
                <strong>{$newpath}</strong> to ensure that your customizations persist after updates.
            </p></div>";
		}
	}

	/**
	 * Updates all modules
	 */
	function update()
	{
		if ((!(defined('DOING_AJAX') && DOING_AJAX)) && !isset($_REQUEST['doing_wp_cron'])) {

			$this->_load_pope();

			// Try updating all modules
			C_Photocrati_Installer::update();
		}
	}

	/**
	 * Routes access points using the Pope Router
	 * @return boolean
	 */
	function route()
	{
		$this->_load_pope();
		$router = C_Router::get_instance();

		// Set context to path if subdirectory install
		$parts = parse_url($router->get_base_url(FALSE));
		if (isset($parts['path'])) {
			$parts = explode('/index.php', $parts['path']);
			$router->context = array_shift($parts);
		}

		// Provide a means for modules/third-parties to configure routes
		do_action_ref_array('ngg_routes', array(&$router));

		// Serve the routes
		if (!$router->serve_request() && $router->has_parameter_segments()) {
			return $router->passthru();
		}
	}

	/**
	 * Run the uninstaller
	 */
	static function deactivate()
	{
		C_Photocrati_Installer::uninstall(NGG_PLUGIN_BASENAME);
	}

	/**
	 * Defines necessary plugins for the plugin to load correctly
	 */
	function _define_constants()
	{
		define('NGG_PLUGIN', basename($this->directory_path()));
		define('NGG_PLUGIN_BASENAME', plugin_basename(__FILE__));
		define('NGG_PLUGIN_DIR', $this->directory_path());
		define('NGG_PLUGIN_URL', $this->path_uri());
		define('NGG_TESTS_DIR',   implode(DIRECTORY_SEPARATOR, array(rtrim(NGG_PLUGIN_DIR, "/\\"), 'tests')));
		define('NGG_PRODUCT_DIR', implode(DIRECTORY_SEPARATOR, array(rtrim(NGG_PLUGIN_DIR, "/\\"), 'products')));
		define('NGG_MODULE_DIR', implode(DIRECTORY_SEPARATOR, array(rtrim(NGG_PRODUCT_DIR, "/\\"), 'photocrati_nextgen', 'modules')));
		define('NGG_PRODUCT_URL', path_join(str_replace("\\", '/', NGG_PLUGIN_URL), 'products'));
		define('NGG_MODULE_URL', path_join(str_replace("\\", '/', NGG_PRODUCT_URL), 'photocrati_nextgen/modules'));
		define('NGG_PLUGIN_STARTED_AT', microtime());
		define('NGG_PLUGIN_VERSION', '2.1.46');

		if (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)
			define('NGG_SCRIPT_VERSION', (string)mt_rand(0, mt_getrandmax()));
		else
			define('NGG_SCRIPT_VERSION', NGG_PLUGIN_VERSION);

		if (!defined('NGG_HIDE_STRICT_ERRORS')) {
			define('NGG_HIDE_STRICT_ERRORS', TRUE);
		}

		// Should we display E_STRICT errors?
		if (NGG_HIDE_STRICT_ERRORS) {
			$level = error_reporting();
			if ($level != 0) error_reporting($level & ~E_STRICT);
		}

		// Should we display NGG debugging information?
		if (!defined('NGG_DEBUG')) {
			define('NGG_DEBUG', FALSE);
		}
		self::$debug = NGG_DEBUG;

		// User definable constants
		if (!defined('NGG_IMPORT_ROOT')) {
			$path = WP_CONTENT_DIR;
			if (defined('NEXTGEN_GALLERY_IMPORT_ROOT')) {
				$path = NEXTGEN_GALLERY_IMPORT_ROOT;
			}
			define('NGG_IMPORT_ROOT', $path);
		}

		// Should the Photocrati cache be enabled
		if (!defined('PHOTOCRATI_CACHE')) {
			define('PHOTOCRATI_CACHE', TRUE);
		}
		if (!defined('PHOTOCRATI_CACHE_TTL')) {
			define('PHOTOCRATI_CACHE_TTL', 1800);
		}

		// Cron job
		if (!defined('NGG_CRON_SCHEDULE')) {
			define('NGG_CRON_SCHEDULE', 900);
		}

		if (!defined('NGG_CRON_ENABLED')) {
			define('NGG_CRON_ENABLED', TRUE);
		}

		// Don't enforce interfaces
		if (!defined('EXTENSIBLE_OBJECT_ENFORCE_INTERFACES')) {
			define('EXTENSIBLE_OBJECT_ENFORCE_INTERFACES', FALSE);
		}

		// Fix jquery
		if (!defined('NGG_FIX_JQUERY')) {
			define('NGG_FIX_JQUERY', TRUE);
		}

		// Use Pope's new caching mechanism?
		if (!defined('NGG_POPE_CACHE')) {
			define('NGG_POPE_CACHE', FALSE);
		}
	}

	/**
	 * Defines the NextGEN Test Suite
	 * @param array $suites
	 * @return array
	 */
	function add_testsuite($suites=array())
	{
		$tests_dir = NGG_TESTS_DIR;

		if (file_exists($tests_dir)) {

			// Include mock objects
			// TODO: These mock objects should be moved to the appropriate
			// test folder
			require_once(path_join($tests_dir, 'mocks.php'));

			// Define the NextGEN Test Suite
			$suites['nextgen'] = array(
//                path_join($tests_dir, 'mvc'),
				path_join($tests_dir, 'datamapper'),
				path_join($tests_dir, 'nextgen_data'),
				path_join($tests_dir, 'gallery_display')
			);
		}

		return $suites;
	}


	/**
	 * Returns the path to a file within the plugin root folder
	 * @param type $file_name
	 * @return type
	 */
	function file_path($file_name=NULL)
	{
		$path = dirname(__FILE__);

		if ($file_name != null)
		{
			$path .= '/' . $file_name;
		}
		print_r($path);
		return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	}


	/**
	 * Gets the directory path used by the plugin
	 * @return string
	 */
	function directory_path($dir=NULL)
	{
		//$dir = get_template_directory_uri();//. '/include/plugins/nggallery/';
		//print_r($dir);
		//print_r($this->file_path($dir));
		return $this->file_path($dir);
	}


	/**
	 * Determines the location of the plugin - within a theme or plugin
	 * @return string
	 */
	function get_plugin_location()
	{
		$path = dirname(__FILE__);
		$gallery_dir = strtolower($path);
		$gallery_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $gallery_dir);

		$theme_dir = strtolower(get_stylesheet_directory());
		$theme_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $theme_dir);

		$plugin_dir = strtolower(WP_PLUGIN_DIR);
		$plugin_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $plugin_dir);

		$common_dir_theme = substr($gallery_dir, 0, strlen($theme_dir));
		$common_dir_plugin = substr($gallery_dir, 0, strlen($plugin_dir));

		if ($common_dir_theme == $theme_dir)
		{
			print_r('found it in the theme');
			return 'theme';
		}

		if ($common_dir_plugin == $plugin_dir)
		{
			print_r('found it as a plugin');
			return 'plugin';
		}

		$parent_dir = dirname($path);
		print_r($parent_dir);
		if (file_exists($parent_dir . DIRECTORY_SEPARATOR . 'style.css'))
		{
			print_r('found it in the theme');
			return 'theme';
		}
		print_r('found it as a plugin');
		return 'plugin';
	}


	/**
	 * Gets the URI for a particular path
	 * @param string $path
	 * @param boolean $url_encode
	 * @return string
	 */
	function path_uri($path = null, $url_encode = false)
	{
		$location = $this->get_plugin_location();
		$uri = null;

		$path = str_replace(array('/', '\\'), '/', $path);

		if ($url_encode)
		{
			$path_list = explode('/', $path);

			foreach ($path_list as $index => $path_item)
			{
				$path_list[$index] = urlencode($path_item);
			}

			$path = implode('/', $path_list);
		}

		if ($location == 'theme')
		{
			$theme_uri = get_stylesheet_directory_uri();

			$uri = $theme_uri . 'nextgen-gallery';

			if ($path != null)
			{
				$uri .= '/' . $path;
			}
		}
		else
		{
			// XXX Note, paths could not match but STILL being contained in the theme (i.e. WordPress returns the wrong path for the theme directory, either with wrong formatting or wrong encoding)
			$base = basename(dirname(__FILE__));

			if ($base != 'nextgen-gallery')
			{
				// XXX this is needed when using symlinks, if the user renames the plugin folder everything will break though
				$base = 'nextgen-gallery';
			}

			if ($path != null)
			{
				$base .= '/' . $path;
			}

			$uri = plugins_url($base);
		}
		print_r($uri);
		return $uri;
	}

	/**
	 * Returns the URI for a particular file
	 * @param string $file_name
	 * @return string
	 */
	function file_uri($file_name = NULL)
	{
		return $this->path($file_name);
	}
}

#region Freemius

/**
 * Customize the opt-in message.
 *
 * @author Vova Feldman (@svovaf)
 * @since 2.1.32
 *
 * @param string $message
 * @param string $user_first_name
 * @param string $plugin_title
 * @param string $user_login
 * @param string $site_link
 * @param string $freemius_link
 *
 * @return string
 */
function ngg_fs_custom_connect_message(
	$message,
	$user_first_name,
	$plugin_title,
	$user_login,
	$site_link,
	$freemius_link
) {
	return sprintf(
		__fs( 'hey-x' ) . '<br>' .
		__( 'Allow %6$s to collect some usage data with %5$s to make the plugin even more awesome. If you skip this, that\'s okay! %2$s will still work just fine.', 'nggallery' ),
		$user_first_name,
		'<b>' . __('NextGEN Gallery', 'nggallery') . '</b>',
		'<b>' . $user_login . '</b>',
		$site_link,
		$freemius_link,
		'<b>' . __('Imagely', 'nggallery') . '</b>'
	);
}

/**
 * Uninstall cleanup script.
 */
function ngg_fs_uninstall() {
	// Your cleanup script.
}

/**
 * Create a helper function for easy SDK access.
 *
 * @author Vova Feldman (@svovaf)
 * @since 2.1.32
 *
 * @return \Freemius
 */
function ngg_fs() {
	global $ngg_fs;

	$ngg_options = get_option( 'ngg_options' );
	$ngg_run_freemius = get_option('ngg_run_freemius', NULL);

	if ( false === $ngg_options ) {
		// New plugin installation.

		if ( defined('WP_FS__DEV_MODE') && WP_FS__DEV_MODE ) {
			// Always run Freemius in development mode for new plugin installs.
			$run_freemius = true;
		} else {
			// Run Freemius code on 20% of the new installations.
			$random = rand( 1, 10 );
			$run_freemius = ( 1 <= $random && $random <= 2 );
		}

		update_option('ngg_run_freemius', $run_freemius);

	// Compare both bool or string 0/1 because get_option() may give us either
	} else if ((is_bool($ngg_run_freemius) && $ngg_run_freemius) || '1' === $ngg_run_freemius) {
		// If runFreemius was set, use the value.
		$run_freemius = $ngg_run_freemius;
	} else {
		// Don't run Freemius for plugin updates.
		$run_freemius = false;
		if (is_null($ngg_run_freemius))
			update_option('ngg_run_freemius', FALSE);
	}

	if ( ! $run_freemius ) {
		return false;
	}

	if ( ! isset( $ngg_fs ) ) {
		// Include Freemius SDK.
		require_once dirname( __FILE__ ) . '/freemius/start.php';

		$ngg_fs = fs_dynamic_init( array(
			'id'             => '266',
			'slug'           => 'nextgen-gallery',
			'public_key'     => 'pk_009356711cd548837f074e1ef60a4',
			'is_premium'     => false,
			'has_addons'     => false,
			'has_paid_plans' => false,
			'menu'           => array(
				'slug'    => 'nextgen-gallery',
				'account' => false,
				'contact' => false,
				'support' => false,
			),
			'permissions'    => array(
				'newsletter' => true,
			),
		) );
	}

	/*
	// Optional button override.
	if ( function_exists( 'fs_override_i18n' ) ) {
		fs_override_i18n( array(
			'opt-in-connect' => __('OK - I\'m in!', 'nggallery'),
		), 'nextgen-gallery' );
	}
	*/

	// Hook to the custom message filter.
	$ngg_fs->add_filter( 'connect_message', 'ngg_fs_custom_connect_message', 10, 6 );
	$ngg_fs->add_action( 'after_uninstall', 'ngg_fs_uninstall' );

	return $ngg_fs;
}

// Init Freemius.
ngg_fs();

#endregion Freemius

new C_NextGEN_Bootstrap();
