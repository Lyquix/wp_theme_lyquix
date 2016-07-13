<?php
class A_Non_Cachable_Pro_Film_Controller extends Mixin
{
    public function is_cachable()
    {
        return FALSE;
    }
}