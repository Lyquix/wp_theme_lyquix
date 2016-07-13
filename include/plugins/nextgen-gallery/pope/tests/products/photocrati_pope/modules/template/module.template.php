<?php

/***
{
Module: photocrati-template
}
 ***/

class M_Template extends C_Base_Module
{
    public $is_initialized = False;

    public function define()
    {
        parent::define(
            'photocrati-template',
            'Photocrati Template',
            'An extremely simple template system',
            '0.1',
            'https://www.imagely.com',
            'Photocrati Media',
            'https://www.imagely.com'
        );
    }

    /*
     * _registery_adapters, _register_hooks(), and _register_utilities are run at the end of parent::define()
     */
    public function _register_adapters()
    {
        $this->get_registry()->add_adapter('I_Component_Factory', 'A_Template_Factory');
        $this->get_registry()->add_adapter('I_Template_Library', 'A_Template_Normal', 'Normal');
        $this->get_registry()->add_adapter('I_Template_Library', 'A_Template_Alternate', 'Alternate');
    }

    public function _register_hooks()
    {
    }

    public function _register_utilities()
    {
    }

    /*
     * You can use initialize() here to act as soon as initialize_module() has been called on it. That way modules
     * can be loaded but only started when desired. The initialize() function is only called once per object.
     */
    public function initialize()
    {
        $this->is_initialized = True;
    }
}

new M_Template();
