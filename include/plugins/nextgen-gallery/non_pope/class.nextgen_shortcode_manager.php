<?php

class C_NextGen_Shortcode_Manager
{
	private static $_instance = NULL;
	private $_shortcodes = array();
    private $_runlevel = 0;
    private $_has_warned = FALSE;

	/**
	 * Gets an instance of the class
	 * @return C_NextGen_Shortcode_Manager
	 */
	static function get_instance()
	{
		if (is_null(self::$_instance)) {
			$klass = get_class();
			self::$_instance = new $klass;
		}
		return self::$_instance;
	}

	/**
	 * Adds a shortcode
	 * @param $name
	 * @param $callback
	 */
	static function add($name, $callback)
	{
		$manager = self::get_instance();
		$manager->add_shortcode($name, $callback);
	}

	/**
	 * Removes a previously added shortcode
	 * @param $name
	 */
	static function remove($name)
	{
		$manager = self::get_instance();
		$manager->remove_shortcode($name);
	}

	/**
	 * Constructor
	 */
	private function __construct()
	{
        // For theme & plugin compatibility and to prevent the output of our shortcodes from being
        // altered we disable our shortcodes at the beginning of the_content and enable them at the end
        // however a bug in Wordpress (see comments in deactivate_all() below) causes another issue
        // of compatibility causing our shortcodes to not be registered at the time the_content is run.
        // This disables that at the risk that themes may alter our HTML output in an attempt to sanitize it.
        if (defined('NGG_DISABLE_FILTER_THE_CONTENT') && NGG_DISABLE_FILTER_THE_CONTENT)
            return;

		// We have to temporily enable our shortcodes before wptexturize runs, and then
		// disable them again immediately afterwards
		global $wp_filter;

		$filters = $wp_filter['the_content'][10];
		$wp_filter['the_content'][10] = array();
		foreach ($filters as $k=>$v) {
			if ($k == 'wptexturize') {
				$wp_filter['the_content'][10]['before_wptexturize'] = array(
					'function'		=> 	array(&$this, 'activate_all_for_wptexturize'),
					'accepted_args'	=>	1
				);
				$wp_filter['the_content'][10][$k] = $v;
				$wp_filter['the_content'][10]['after_wptexturize'] = array(
					'function'		=> 	array(&$this, 'deactivate_all'),
					'accepted_args'	=>	1
				);

			}
			else $wp_filter['the_content'][10][$k] = $v;
		}

		add_filter('the_content', array(&$this, 'deactivate_all_for_wptexturize'), -(PHP_INT_MAX-1));
        add_filter('the_content', array(&$this, 'parse_content'), PHP_INT_MAX-1);

	}

	function activate_all_for_wptexturize($content)
	{
		$this->activate_all();
		return $content;
	}

	function deactivate_all_for_wptexturize($content)
	{
		return $this->deactivate_all($content);
	}

	/**
	 * Deactivates all shortcodes
	 */
	function deactivate_all($content, $increment_runlevel=TRUE)
	{
		// There is a bug in Wordpress itself: when a hook recurses any hooks meant to execute after it are discarded.
		// For example the following code, despite expectations, will NOT display 'bar' as bar() is never executed.
		// See https://core.trac.wordpress.org/ticket/17817 for more information.
		/* function foo() {
		 *     remove_action('foo', 'foo');
		 * }
		 * function bar() {
		 *     echo('bar');
		 * }
		 * add_action('foo', 'foo');
		 * add_action('foo', 'bar');
		 * do_action('foo');
		 */
		if ($increment_runlevel) $this->_runlevel += 1;
		if ($this->_runlevel > 1 && defined('WP_DEBUG') && WP_DEBUG && !is_admin() && !$this->_has_warned)
		{
			$this->_has_warned = TRUE;
			error_log('Sorry, but recursing filters on "the_content" breaks NextGEN Gallery. Please see https://core.trac.wordpress.org/ticket/17817 and NGG_DISABLE_FILTER_THE_CONTENT');
		}

		foreach (array_keys($this->_shortcodes) as $shortcode) {
			$this->deactivate($shortcode);
		}

		return $content;
	}

	/**
	 * Activates all registered shortcodes
	 */
	function activate_all()
	{
		foreach (array_keys($this->_shortcodes) as $shortcode) {
			$this->activate($shortcode);
		}
	}

	/**
	 * Parses the content for shortcodes and returns the substituted content
	 * @param $content
	 * @return string
	 */
	function parse_content($content)
	{
        $this->_runlevel--;
		$this->activate_all();
		$content = do_shortcode($content);
        $content = apply_filters('ngg_content', $content);

        return $content;
	}

	/**
	 * Adds a shortcode
	 * @param $name
	 * @param $callback
	 */
	function add_shortcode($name, $callback)
	{
		$this->_shortcodes[$name] = $callback;
		$this->activate($name);
	}

	/**
	 * Activates a particular shortcode
	 * @param $shortcode
	 */
	function activate($shortcode)
	{
		if (isset($this->_shortcodes[$shortcode])) {
			add_shortcode($shortcode, array(&$this, "{$shortcode}__callback"));
		}
	}

	/**
	 * Removes a shortcode
	 * @param $name
	 */
	function remove_shortcode($name)
	{
		unset($this->_shortcodes[$name]);
		$this->deactivate($name);
	}

	/**
	 * De-activates a shortcode
	 * @param $shortcode
	 */
	function deactivate($shortcode)
	{
		if (isset($this->_shortcodes[$shortcode]))
			remove_shortcode($shortcode);
	}

	function __call($method, $params)
	{
		$retval = NULL;

		if (strpos($method, '__callback') !== FALSE) {
			$parts = explode('__callback', $method);
			$shortcode = $parts[0];
			$inner_content = isset($params[1]) ? $params[1] : '';
			$params = isset($params[0]) ? $params[0] : array();
			$retval = $this->callback_wrapper($shortcode, $params, $inner_content);
		}

		return $retval;
	}

	function callback_wrapper($shortcode, $params, $inner_content)
	{
		$retval = '';

		if (is_array($params))
		{
			foreach ($params as $key => &$val) {
                $val = trim($val, "„“‚‘«»“”");
				$val = preg_replace("/^(&[^;]+;)?(.*)/", '\2', $val);
				$val = preg_replace("/(&[^;]+;)?$/", '', $val);
			}
		}

		if (isset($this->_shortcodes[$shortcode]))
			$retval = call_user_func($this->_shortcodes[$shortcode], $params, $inner_content);

		return $retval;
	}
}