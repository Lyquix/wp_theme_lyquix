<?php

class A_Animal_Factory extends Mixin
{
    function animal_farm($context = False)
    {
        return new C_Animal_Library($context);
    }
}
