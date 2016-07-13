<?php

/*
 * This is a brief tutorial on how to use Pope through some unit test demonstrations.
 *
 * We begin by creating a simple class with a couple of functions.
 *
 * To help keep some of our classes separated (and to later demonstrate how to use the autoloader and factories)
 * we're going to use the same naming scheme as Pope and prefix our classes with their class type.
 * We start with our core class.
 */
class C_Core extends ExtensibleObject
{
    /*
     * PHP imposes some limits that means our class variables and functions should ALL be public. "Private" variables
     * should be named with a prefixed underscore.
     */

    /*
     * This class exists to save and retrieve this string variable. We change our get/set functions with Mixins
     * which we'll cover soon.
     */
    public $_str;

    public function set_str($str)
    {
        $this->object->_str = $str;
    }

    /*
     * add_mixin() links the functions defined in Mixin_Core to this C_Core class. By assigning Mixin_Core we can
     * call the "get_str" function on a C_Core instance even though this C_Core definition doesn't include a get_str
     * function. You can even later add more Mixins that provide the same function; Pope tracks them by priority
     * to determine name conflicts.
     *
     * define() is run when your class is created and can be used to automatically attach mixins or hooks.
     */
    public function define()
    {
        $this->add_mixin('Mixin_Core');
    }

    /*
     * This function is called at the end of __construct
     */
    public function initialize($str)
    {
        $this->set_str($str);
    }
}

/*
 * Now about mixins: they're like small plugins that can be assigned to give other classes their functions
 * or to override their existing functions. They can access their parent data and can be added or removed
 * and then added & removed again at any time. Let's demonstrate.
 */
class Mixin_Core extends Mixin
{
    public function get_str()
    {
        /*
         * We can access the parent data with $this->object. When we attach this Mixin_Core to C_Core above this
         * function returns C_Core's _str. If we attached this Mixin to any other class this Mixin would return that
         * classes' _str variable.
         */
        return $this->object->_str;
    }
}

/*
 * We may need more than one way to retrieve that same string. This function returns our string inside of an HTML
 * comment. The same C_Core instance can use both this and Mixin_Core's get_str() functions as you need them.
 */
class Mixin_Core_Override extends Mixin
{
    public function get_str($parent = False)
    {
        if (True == $parent)
        {
            return $this->call_parent('get_str');
        } else {
            return '<!-- ' . $this->object->_str . ' -->';
        }
    }
}

/*
 * This tutorial also doubles as a valid unit test with SimpleTest. The parameters for simpleTest's assert functions
 * are (thing to compare one, thing to compare two, error message)
 */
class Test_Of_Core extends UnitTestCase
{

    public $str = 'test_string';

    /*
     * We start with the simplest test possible. We create a C_Core class then we ask whether the string it has
     * stored is the same as the string we gave it. Because we used add_mixin() in C_Core->define() we don't have
     * to do it for every new C_Core we instantiate.
     */
    public function test_Core()
    {
        $core = new C_Core($this->str);

        /*
         * First we'll make sure that the get_str() method has been assigned
         */
        $this->assertEqual(
            True,
            $core->has_method('get_str'),
            'has_method() did not return True on a method that exists'
        );

        /*
         * Just in case has_method() is lying we ask about a method that doesnt exist
         */
        $this->assertEqual(
            False,
            $core->has_method('does_not_exist'),
            'has_method() did not return False on a method that does not exist'
        );

        /*
         * Finally we'll call get_str()
         */
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'Simplest test did not work, probably add_mixin()'
        );

        /*
         * We can ask pope what functions exist for our objects. Here we limit our results to functions provided by
         * Mixin_Core. If you want every function available just remove the name parameter.
         */
        $this->assertEqual(
            array(
                0 => 'get_str'
            ),
            $core->get_instance_methods('Mixin_Core'),
            'get_instance_methods() did not return a correct list'
        );

        /*
         * We can also ask which mixin provides our functions
         */
        $this->assertEqual(
            'Mixin_Core',
            $core->get_mixin_providing('get_str'),
            'get_mixin_providing() returned a wrong result'
        );

        $this->assertEqual(
            $this->str,
            $core->call_method('get_str'),
            'call_method() returned a wrong result'
        );

		/*
		 * Try calling a method for the object that doesn't exist
		 */
		$this->expectException('Exception', "ExtensibleObject did not throw an exception when an undefined method was called");
		$core->this_method_does_not_exist();
    }

    /*
     * That's it! We created a C_Core class, assigned it a Mixin, and then called a function from the Mixin that the
     * C_Core class didn't have. You now have a very simple and easily modifiable plugin system.
     *
     * Now we demonstrate how to work with other Mixins.
     */
    public function test_Override()
    {
        /*
         * By assigning Mixin_Core_Override it is given higher priority than Mixin_Core (first in, last out). Because
         * our override returns the string as an HTML comment, we ask whether the override function ran.
         */
        $core = new C_Core($this->str);
        $core->add_mixin('Mixin_Core_Override');
        $this->assertEqual(
            "<!-- {$this->str} -->",
            $core->get_str(),
            'add_mixin() with an override did not render correctly'
        );

        /*
         * Testing call_method() with our override
         */
        $this->assertEqual(
            "<!-- {$this->str} -->",
            $core->call_method('get_str'),
            'call_method() returned a wrong result'
        );

        /*
         * Our override will use it's call_parent() method when given a True parameter. Mixins and classes in Pope
         * don't really have parents but it does maintain priority for each mixin added. If more than one mixin
         * implements the same function they can call the mixins that are above them in priority. We demonstrate that
         * by telling our override function to call it's parent -- the Mixin_Core->get_str()
         */
        $this->assertEqual(
            $this->str,
            $core->get_str(True),
            'call_parent() did not function correctly'
        );

        /*
         * Again we test call_method, this time while passing a parameter
         */
        $this->assertEqual(
            $this->str,
            $core->call_method('get_str', array(True)),
            'call_method() returned a wrong result (with parameters)'
        );

        /*
         * Just as easily as it was added we remove it and go back to our plain-old Mixin_Core.
         */
        $core->del_mixin('Mixin_Core_Override');
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'del_mixin() did not remove the override mixin correctly'
        );
    }

}
