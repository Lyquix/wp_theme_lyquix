<?php

class A_Animal_Dog extends Mixin
{
    function speak($arg)
    {
        return 'The dog barks: ' . $arg;
    }
}
