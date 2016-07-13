<?php

class C_Template_Library extends C_Component
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Template_Default_Render');
        $this->implement('I_Template_Library');
    }
}

class Mixin_Template_Default_Render
{
    function render($arg)
    {
        throw new Exception('Expected adapter to override this method');
    }
}
