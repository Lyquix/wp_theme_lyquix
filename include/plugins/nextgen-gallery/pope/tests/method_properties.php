<?php

/*
 * We can make some significant changes to the Pope internals through the set_method_properties() function.
 *
 * Here we'll create two classes that capitalize & rot13 our string, each with a post and pre function. The capitalize
 * class will also check an object variable.
 *
 * Because Pope caches the return values from executed functions we can also override that return value in our post
 * hooks before it makes it is returned. We can also have one hook disable execution of other hooks and even the
 * original function called.
 */
class Hook_MP_Capitalize extends Hook
{
    public function capitalize()
    {
        $prop = ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE;

        // method, property, value
        $this->object->set_method_property(
            'get_str',
            $prop,
            strtoupper($this->object->get_method_property('get_str', $prop))
        );

        /*
         * The object variables are set later as a manual trigger
         */
        if (True == $this->object->disable_run_post_hooks)
        {
            $this->object->set_method_property('get_str', ExtensibleObject::METHOD_PROPERTY_RUN_POST_HOOKS, False);
        }
    }

    public function pre_capitalize()
    {
        $this->object->_str = strtoupper($this->object->_str);

        if (True == $this->object->disable_run_pre_hooks)
        {
            $this->object->set_method_property('get_str', ExtensibleObject::METHOD_PROPERTY_RUN_PRE_HOOKS, False);
        }

        if (True == $this->object->disable_run)
        {
            $this->object->set_method_property('get_str', ExtensibleObject::METHOD_PROPERTY_RUN, False);
        }
    }
}

/*
 * Now we repeat ourselves, but simpler as this class exists just to be disabled
 */
class Hook_MP_Rot13 extends Hook
{
    public function rot13()
    {
        $prop = ExtensibleObject::METHOD_PROPERTY_RETURN_VALUE;

        // method, property, value
        $this->object->set_method_property(
            'get_str',
            $prop,
            str_rot13($this->object->get_method_property('get_str', $prop))
        );
    }

    public function pre_rot13()
    {
        $this->object->_str = str_rot13($this->object->_str);
    }
}

class Test_Of_Method_Properties extends UnitTestCase
{
    public $str = 'test_string';

    public function test_Method_Properties()
    {
        $core = new C_Core($this->str);
        $core->add_mixin('Mixin_Core_Post');

        /*
         * This hook modifies the original return value to instead return its results directly through get_str()
         */
        $core->add_post_hook('get_str', 'Method Properties Capitalize', 'Hook_MP_Capitalize', 'capitalize');
        $this->assertEqual(
            strtoupper($this->str),
            $core->get_str(),
            'set_method_property(return_value) did not capitalize our string'
        );

        /*
         * We need at least two hooks to test
         */
        $core->add_post_hook('get_str', 'Method Properties Rot13', 'Hook_MP_Rot13', 'rot13');
        $this->assertEqual(
            str_rot13(strtoupper($this->str)),
            $core->get_str(),
            'set_method_property(return_value) did not capitalize and rot13 our string across two hooks'
        );

        /*
         * Our capitalize() function will check for this variable and disable other post hooks through
         * set_method_property()
         */
        $core->object->disable_run_post_hooks = True;
        $this->assertEqual(
            strtoupper($this->str),
            $core->get_str(),
            'set_method_property(run_post_hooks) did not disable the rot13 hook'
        );

        $core->disable_post_hooks('get_str');
        $core->set_str($this->str);

        /*
         * Now we add our pre-hooks and test them
         */
        $core->add_pre_hook('get_str', 'Method Properties Pre-Capitalize', 'Hook_MP_Capitalize', 'pre_capitalize');
        $core->add_pre_hook('get_str', 'Method Properties Pre-Rot13', 'Hook_MP_Rot13', 'pre_rot13');
        $this->assertEqual(
            str_rot13(strtoupper($this->str)),
            $core->get_str(),
            'Something stopped the two pre-hooks from running'
        );

        /*
         * Again our first pre-hook should disable the second from running
         */
        $core->object->disable_run_pre_hooks = True;
        $core->set_str($this->str);
        $this->assertEqual(
            strtoupper($this->str),
            $core->get_str(),
            'set_method_property(run_pre_hooks) did not disable the rot13 hook'
        );

        /*
         * When run == False get_str() should not run and should return null
         */
        $core->set_str($this->str);
        $core->disable_run = True;
        $this->assertEqual(
            Null,
            $core->get_str(),
            'set_method_properties(run) did not stop get_str() from running'
        );

        /*
         * And one last sanity check
         */
        $this->assertEqual(
            strtoupper($this->str),
            $core->object->_str,
            'Something stopped the pre-hook from running'
        );

        /*
         * clear_method_properties() resets the entire method properties array
         */
        $core->reset_method_properties('get_str');;
        $this->assertEqual(
            array(
                ExtensibleObject::METHOD_PROPERTY_RUN => True,
                ExtensibleObject::METHOD_PROPERTY_RUN_PRE_HOOKS => True,
                ExtensibleObject::METHOD_PROPERTY_RUN_POST_HOOKS => True
            ),
            $core->_method_properties['get_str'],
            'clear_method_properties() did not reset the method properties correctly'
        );


    }
}
