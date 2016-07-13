<?php

class A_Template_Alternate extends Mixin
{
    function render($arg)
    {
        return strip_tags($arg);
    }
}
