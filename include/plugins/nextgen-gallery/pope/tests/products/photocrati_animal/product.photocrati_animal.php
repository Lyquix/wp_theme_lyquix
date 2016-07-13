<?php

/***
{
Product: photocrati-animal
}
 ***/

class P_Photocrati_Animal extends C_Base_Product
{
	public function define()
	{
        // id, name, description, version, uri, author, author_uri
		parent::define(
			'photocrati-animal',
			'Photocrati Animal',
			'An extremely simple animal-based "Hello World"',
			'0.1',
			'https://www.imagely.com',
			'Photocrati Media',
			'https://www.imagely.com'
		);

        $dir = dirname(__FILE__) . '/pope_modules/';
        $registry = $this->get_registry();
        $registry->set_product_module_path($this->module_id, $dir);
        $registry->add_module_path($dir, True, False);
	}
}

new P_Photocrati_Animal();
