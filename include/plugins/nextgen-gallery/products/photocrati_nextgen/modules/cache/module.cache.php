<?php
/***
{
		Module: photocrati-cache
}
***/
class M_Cache extends C_Base_Module
{
    /**
     * Defines the module name & version
     */
    function define()
	{
		parent::define(
			'photocrati-cache',
			'Cache',
			'Handles clearing of NextGen caches',
			'0.2',
			'https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/',
			'Photocrati Media',
			'https://www.imagely.com'
		);
	}

    /**
     * Register utilities
     */
    function _register_utilities()
    {
        $this->get_registry()->add_utility('I_Cache', 'C_Cache');
    }

    function get_type_list()
    {
        return array(
            'C_Cache' => 'class.cache.php'
        );
    }
}

new M_Cache();
