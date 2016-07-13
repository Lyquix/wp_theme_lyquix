<?php

class A_Template_Factory extends Mixin
{
    function photocrati_template($context = False)
    {
        return new C_Template_Library($context);
    }
}
