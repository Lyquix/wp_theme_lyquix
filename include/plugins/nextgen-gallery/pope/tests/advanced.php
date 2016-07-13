<?php

class Mixin_Core_Replace_Method extends Mixin
{
    function run()
    {
        $this->object->Replaced_Method = True;
    }
}

class Test_Of_Core_Advanced extends UnitTestCase
{

    public $str = 'test_string';

    public function test_Core_Advanced()
    {
        $core = new C_Core($this->str);

        /*
         * Pope internally caches some metadata about classes and which functions belong in those caches; including
         * previous return values and names.
         */
        $this->assertEqual(
            False,
            $core->is_cached('get_str'),
            'is_cached() returned True on a function not yet run'
        );
        $core->get_str();
        $this->assertEqual(
            True, $core->is_cached('get_str'),
            'is_cached() did not return True on an executed function'
        );

        /*
         * Ensure remove_mixin() works
         */
        $core->remove_mixin('Mixin_Core');
        $this->assertEqual(
            False,
            $core->get_mixin_providing('get_str'),
            'A mixin still provides get_str() after remove_mixin() was called'
        );
        $core->add_mixin('Mixin_Core');

        /*
         * Make sure our classes are coming from the right places.
         */
        $this->assertEqual(
            'core.php',
            basename($core->get_class_definition_file()),
            'get_class_definition_file() did not return core.php'
        );
        $this->assertEqual(
            'class.extensibleobject.php',
            basename($core->get_class_definition_file(True)),
            'get_class_definition_file(True) did not return class.extensibleobject.php'
        );
        $this->assertEqual(
            'tests',
            basename($core->get_class_definition_dir()),
            'get_class_definition_dir() did not return this "tests" directory'
        );
        $this->assertEqual(
            'lib',
            basename($core->get_class_definition_dir(True)),
            'get_class_definition_dir(True) did not return the parent "lib" directory'
        );

        /*
         * replace_method isn't quite what you would imagine; it prevents the original method from running
         * and adds a new pre-hook to that method. While your new method will run the results from your method are
         * not returned. Here we check for an object variable that shouldn't exist, swap out our method for a new one,
         * and make sure it created that variable.
         */
        $this->assertEqual(
            False,
            isset($core->object->Replaced_Method),
            '$this->Replaced_Method was true before it should not have been'
        );
        $core->replace_method('get_str', 'Mixin_Core_Replace_Method', 'run');
        $this->assertEqual(
            null,
            $core->get_str(),
            'replace_method() did not work'
        );
        $this->assertEqual(
            True,
            $core->object->Replaced_Method,
            '$this->Replaced_Method was not true when it should have been (run() was not called)'
        );

        // restore_method() reverts us back to a normal state
        $core->restore_method('get_str');
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'restore_method() did not work'
        );
    }

}
