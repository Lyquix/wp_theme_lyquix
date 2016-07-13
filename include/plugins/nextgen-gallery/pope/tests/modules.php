<?php

class Test_Of_Modules extends UnitTestCase
{
    public $str = 'test_string';

    public function test_Modules()
    {
        $registry = C_Component_Registry::get_instance();
        $factory = $registry->get_utility('I_Component_Factory');

        /*
         * photocrati_template is a continuation of the Simple_Template class, now reborn as "Template" that we have
         * put into a product called "Pope". We test it the same way we did our Simple_Template class.
         */
        $obj = $factory->create('photocrati_template', 'Normal');
        $this->assertEqual(
            $this->str,
            $obj->render($this->str),
            'Factory creation of a normal context template did not render correctly'
        );

        $obj = $factory->create('photocrati_template', 'Alternate');
        $this->assertEqual(
            $this->str,
            $obj->render('<p>' . $this->str . '</p>'),
            'Factory creation of an alternate context template did not render correctly'
        );
    }
}
