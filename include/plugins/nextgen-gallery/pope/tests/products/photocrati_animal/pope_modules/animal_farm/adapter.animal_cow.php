<?php

class A_Animal_Cow extends Mixin
{
    function speak($arg)
    {
        return 'The cow moos: ' . $arg;
    }
}
