<?php

/***
{
Product: photocrati-pope
}
 ***/

class P_Photocrati_Pope extends C_Base_Product
{
	public function define()
	{
        // id, name, description, version, uri, author, author_uri
		parent::define(
			'photocrati-pope',
			'Photocrati Pope',
			'Photocrati Pope',
			'0.1',
			'https://bitbucket.org/photocrati/pope-framework',
			'Photocrati Media',
			'https://www.imagely.com'
		);

        /*
         * The modules can be stored anywhere under this project. In the Animal product the modules are kept in a dir
         * called "pope_modules"
         */
        $dir = dirname(__FILE__) . '/modules/';
        $registry = $this->get_registry();
        $registry->set_product_module_path($this->module_id, $dir);
        $registry->add_module_path($dir, True, False);
	}
}

new P_Photocrati_Pope();
