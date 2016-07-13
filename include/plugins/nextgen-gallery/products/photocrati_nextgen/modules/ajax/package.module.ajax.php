<?php
class C_Ajax_Controller extends C_MVC_Controller
{
    static $_instances = array();
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->implement('I_Ajax_Controller');
    }
    public function index_action()
    {
        $retval = NULL;
        // Inform the MVC framework what type of content we're returning
        $this->set_content_type('json');
        // Start an output buffer to avoid displaying any PHP warnings/errors
        ob_start();
        // Get the action requested & find and execute the related method
        if ($action = $this->param('action')) {
            $method = "{$action}_action";
            if ($this->has_method($method)) {
                $retval = $this->call_method($method);
            } else {
                $retval = array('error' => 'Not a valid AJAX action');
            }
        } else {
            $retval = array('error' => 'No action specified');
        }
        // Flush the buffer
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
        // Return the JSON to the browser
        echo json_encode($retval);
    }
    /**
     * Returns an instance of this class
     * @param string $context
     * @return C_Ajax_Controller
     */
    static function get_instance($context = FALSE)
    {
        if (!isset(self::$_instances[$context])) {
            $klass = get_class();
            self::$_instances[$context] = new $klass($context);
        }
        return self::$_instances[$context];
    }
    public function validate_ajax_request($action = NULL, $check_token = false)
    {
        // TODO: remove this. Pro 2.1's proofing calls validate_ajax_request() with a null $action
        if (!$action) {
            return TRUE;
        }
        $valid_request = false;
        $security = $this->get_registry()->get_utility('I_Security_Manager');
        $sec_actor = $security->get_current_actor();
        $sec_token = $security->get_request_token($action);
        if ($sec_actor->is_allowed($action) && (!$check_token || $sec_token->check_current_request())) {
            $valid_request = true;
        }
        return $valid_request;
    }
}