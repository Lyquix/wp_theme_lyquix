<?php

/*
 * Pope will let us extend it's reach to classes we normally wouldn't be able to. In this case we've created a shell
 * called "ThirdPartyLibrary". We're going to extend it to add our own functions; in this case we'll pretend to be
 * adding a newsletter on top of a forum library.
 */
class ThirdPartyLibrary
{
    public $forum_setup = False;

    public function setup_forum()
    {
        $this->forum_setup = True;
    }
}

class Mixin_Third_Party_Lib extends Mixin
{
    function setup_newsletter()
    {
        $this->object->newsletter_setup = True;
    }
}

/*
 * Now that we have our library and our mixin we bring them together. We wrap our mixin to this class and then we wrap
 * our class to the third party library. Here we tell wrap() to use the _create_new() function as it returns
 * ThirdPartyLibrary instances.
 */
class C_Third_Party_Lib_Wrapper extends ExtensibleObject
{
    public function define()
    {
        $this->add_mixin('Mixin_Third_Party_Lib');
        $this->wrap('ThirdPartyLibrary', array(&$this, '_create_new'));
    }

    function _create_new()
    {
        return new ThirdPartyLibrary();
    }
}

class Test_Of_Wrappers extends UnitTestCase
{
    public $str = 'test_string';

    public function test_Wrappers()
    {
        $obj = new C_Third_Party_Lib_Wrapper();

        $obj->setup_forum();
        $obj->setup_newsletter();

        $this->assertEqual(
            True,
            $obj->object->forum_setup,
            'The wrapped class forum_setup() did not run'
        );

        $this->assertEqual(
            True,
            $obj->object->newsletter_setup,
            'The mixin class setup_newsletter() did not run'
        );

        /*
         * Sometimes you need to know if you're dealing with a wrapper
         */
        $core = new C_Core('test_string');
        $this->assertEqual(
            True,
            $obj->is_wrapper(),
            'is_wrapper() returned false on a wrapper'
        );
        $this->assertEqual(
            False,
            $core->is_wrapper(),
            'is_wrapper() returned true on a non-wrapper'
        );

        /*
         * wrapped_class_provides() lets us determine if the function we are calling is from the original class
         * (ThirdPartyLib).
         */
        $this->assertEqual(
            True,
            $obj->wrapped_class_provides('setup_forum'),
            'wrapped_class_provides() did not return True when it should have'
        );

        $this->assertEqual(
            False,
            $obj->wrapped_class_provides('setup_newsletter'),
            'wrapped_class_provides() did not return False when it should have'
        );

        $this->assertEqual(
            False,
            $obj->wrapped_class_provides('does_not_exist'),
            'wrapped_class_provides() did not return False on a non-existent method'
        );
    }

}
