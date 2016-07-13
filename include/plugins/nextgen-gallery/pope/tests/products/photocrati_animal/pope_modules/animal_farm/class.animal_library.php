<?php

class C_Animal_Library extends C_Component
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Animal_Default_Speak');
        $this->implement('I_Animal_Library');
    }
}

class Mixin_Animal_Default_Speak
{
    function speak($arg)
    {
        throw new Exception('Expected adapter to override this method');
    }
}
