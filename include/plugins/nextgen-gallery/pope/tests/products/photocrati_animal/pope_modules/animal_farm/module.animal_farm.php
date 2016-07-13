<?php

/***
{
Module: photocrati-animal-farm
}
 ***/

class M_Animal_Farm extends C_Base_Module
{
    public $is_initialized = False;

    public function define()
    {
        parent::define(
            'photocrati-animal-farm',
            'Photocrati Animal Farm',
            'An extremely simple animal-based "Hello World"',
            '0.1',
            'https://www.imagely.com',
            'Photocrati Media',
            'https://www.imagely.com'
        );
    }

    public function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Component_Factory', 'A_Animal_Factory');
        $this->get_registry()->add_adapter('I_Animal_Library', 'A_Animal_Cow', 'Cow');
        $this->get_registry()->add_adapter('I_Animal_Library', 'A_Animal_Dog', 'Dog');
    }

    public function initialize()
    {
        $this->is_initialized = True;
    }
}

new M_Animal_Farm();
