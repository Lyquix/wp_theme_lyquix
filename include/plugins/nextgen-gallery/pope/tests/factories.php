<?php

/*
 * In this example we introduce interfaces, factories and the registry. We're going to create a "Simple Template"
 * service and this means we an interface to implement, a class to implement it with, and a couple of mixins to attach
 * to it. In the core unit test we created a service that returned a string wrapped in code comments. We do that again
 * here.
 *
 * Start by creating the interface
 */
interface I_Simple_Template
{
    function render($arg);
}

/*
 * This is the default render() implementation. You may use your own default, but here we're going to requrie users
 * to supply their own render() implementation.
 */
class Mixin_Simple_Template_Default
{
    function render($arg)
    {
        throw new Exception('Expected adapter to override this method');
    }
}

/*
 * Our main class. We now extend C_Component instead of ExtensibleObject and we use $this->implement() to implement
 * the I_Simple_Template interface above. The context that initialize() asks for is used to juggle what kind of object
 * our factories should create; you'll see soon.
 */
class C_Simple_Template extends C_Component
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Simple_Template_Default');
        $this->implement('I_Simple_Template');
    }

    function initialize($context = False)
    {
        parent::initialize($context);
    }
}

class C_Simple_Template_Two extends C_Component
{
    function define()
    {
        parent::define();
        $this->add_mixin('Mixin_Simple_Template_Normal');
    }
}

/*
 * Let's create two implementations of render(). One will just return our string as is, and the other will be filtered
 * through strip_tags()
 */
class A_Simple_Template_Normal extends Mixin
{
    function render($arg)
    {
        return $arg;
    }
}

class A_Simple_Template_Alternate extends Mixin
{
    function render($arg)
    {
        return strip_tags($arg);
    }
}

/*
 * This is our factory; it's functions are called by name when calling the I_Component_Factory->create(). So here we
 * name this function 'photocrati_simple_template' and use that same name again when doing $factory->create().
 */
class A_Simple_Template_Factory extends Mixin
{
    function photocrati_simple_template($context = False)
    {
        return new C_Simple_Template($context);
    }
}

/**
 * The SimpleTest class
 */
class Test_Of_Simple_Template extends UnitTestCase
{

    public $str = 'test_string';

    public function test_simple_template()
    {
        /*
         * We first setup Pope, and then tell I_Component_Factory that our Simple_Template factory exists.
         */
        $registry = C_Component_Registry::get_instance();
        $registry->add_utility('I_Component_Factory', 'C_Component_Factory');
        $registry->add_adapter('I_Component_Factory', 'A_Simple_Template_Factory');

        /*
         * Now we assign the Normal and Alternate implementations to the interface.
         */
        $registry->add_adapter('I_Simple_Template', 'A_Simple_Template_Normal', 'Normal');
        $registry->add_adapter('I_Simple_Template', 'A_Simple_Template_Alternate', 'Alternate');

        /*
         * Now we create our objects. By providing the context "normal" we are asking Pope to use our "normal" render
         * implementation. If we leave out the context parameter here our factory provides us with our default
         * implementation which will throw an exception.
         */
        $factory = $registry->get_utility('I_Component_Factory');
        $obj = $factory->create('photocrati_simple_template', 'Normal');
        $this->assertEqual(
            $this->str,
            $obj->render($this->str),
            'The normal context did not render correctly'
        );

        /*
         * Because our default handler throws an exception when called, let's make certain it works as the fallback
         */
        $obj = $factory->create('photocrati_simple_template', 'Test');
        $error = False;
        try
        {
            $obj->render($this->str);
        } catch (Exception $exception)
        {
            $error = True;
        }
        $this->assertEqual(
            True,
            $error,
            'The default throwback handler did not throw an exception (did not run)'
        );

        /*
         * Now let's add it and test it again
         */
        $registry->add_adapter('I_Simple_Template', 'A_Simple_Template_Normal', 'Test');
        $factory = $registry->get_utility('I_Component_Factory');

        $obj = $factory->create('photocrati_simple_template', 'Test');
        $this->assertEqual(
            $this->str,
            $obj->render($this->str),
            'The test context did not render correctly after add_adapter()'
        );

        /*
         * That worked! Let's delete it and make sure it again throws an exception
         */
        $error = False;
        $registry->del_adapter('I_Simple_Template', 'A_Simple_Template_Normal', 'Test');
        $factory = $registry->get_utility('I_Component_Factory');
        $obj = $factory->create('photocrati_simple_template', 'Test');
        try
        {
            $obj->render($this->str);
        } catch (Exception $exception)
        {
            $error = True;
        }
        $this->assertEqual(
            True,
            $error,
            'Factory creation of a context after calling del_adapter() did not throw an exception'
        );

        /*
         * We now swap out to the Alternate context where we ask for our text to be free of markup
         */
        $obj = $factory->create('photocrati_simple_template', 'Alternate');
        $this->assertEqual(
            $this->str,
            $obj->render('<p>' . $this->str . '</p>'),
            'The alternative context did not render correctly'
        );

        /*
         * We can of course still create our objects outside of the factory generator. Here we moved the implement()
         * call outside of the class definition so that we can test it.
         */
        $obj = new C_Simple_Template_Two();
        $this->assertEqual(
            False,
            $obj->implements_interface('I_Simple_Template'),
            'C_Simple_Template implemented I_Simple_Template_Two when it should not have'
        );
        $obj->implement('I_Simple_Template');
        $this->assertEqual(
            True,
            $obj->implements_interface('I_Simple_Template'),
            'C_Simple_Template_Two did not implement I_Simple_Template when it should have'
        );

        /*
         * That's it. We've created an interface and a couple of implementations of it. We can switch between those
         * implementations based on context, and our factory maker provides the correct implementation.
         */
    }

}
