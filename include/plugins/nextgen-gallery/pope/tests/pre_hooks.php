<?php

/*
 * Now we are going to introduce hooks. Hooks extend the Mixin type and work in a similiar way.
 *
 * Like our previous Mixin override we put our string inside a C comment. With this we can filter our string through any
 * number of hooks and not just rely on manually assigning Mixins. We eventually want to test our ability to remove
 * both at once, so we setup hooks two. We also have global hooks (hooks that are assigned to ALL functions) so we
 * create a special method for that also.
 */
class Hook_Core_Pre extends Hook
{
    public function change_str()
    {
        $this->object->_str = '/* ' . $this->object->_str . ' */';
    }

    public function global_change_str()
    {
        $this->object->_str = '// ' . $this->object->_str;
    }
}
class Hook_Core_Pre_Second extends Hook
{
    public function change_str()
    {
        $this->object->_str = '## ' . $this->object->_str;
    }

    /*
     * We can tell our hook functions to call the function they are anchored to.
     */
    public function test_call_anchor()
    {
        $this->object->_str = 'not the test string';
        return $this->call_anchor();
    }
}

/*
 * On to our unit tests
 */
class Test_Of_Pre_Hooks extends UnitTestCase
{

    public $str = 'test_string';

    public function test_Pre_Hooks()
    {
        /*
         * We assign Hook_Core_Pre->change_str to run before the $core->get_str function. Hooks are grouped by
         * name. You'll see when we get to del_pre_hook()
         */
        $core = new C_Core($this->str);
        $core->add_pre_hook('get_str', 'Pre-Hook Test', 'Hook_Core_Pre', 'change_str');
        $this->assertEqual(
            "/* {$this->str} */",
            $core->get_str(),
            'add_pre_hook() did not change our var correctly'
        );

        /*
         * Just to be safe we're going to make sure the hook we just applied really is registered. We're being very
         * cautious today so we'll also make sure there isn't a hook group that shouldn't be there. The first check
         * is true if there's *any* hooks for get_str, the second only if there's a hook in the "Pre-Hook Test" group
         * for get_str.
         */
        $this->assertEqual(
            True,
            $core->have_prehook_for('get_str'),
            'have_prehook_for() with one parameter did not return True when it should have'
        );
        $this->assertEqual(
            True,
            $core->have_prehook_for('get_str','Pre-Hook Test'),
            'have_prehook_for() with both parameters did not return True when it should have'
        );
        $this->assertEqual(
            False,
            $core->have_prehook_for('get_str', 'Does Not Exist'),
            'have_prehook_for() did not return False on a non-existent pre-hook'
        );

        /*
         * We don't actually NEED our string stored as a C comment, let's undo all that. Our filter up above
         * changed the string variable itself, so after deleting our hook we reset the string and then test ourselves
         * again.
         */
        $core->del_pre_hook('get_str', 'Pre-Hook Test');
        $core->set_str($this->str);
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'del_pre_hook() did not run correctly'
        );
        $this->assertEqual(
            False,
            $core->have_prehook_for('get_str', 'Pre-Hook Test'),
            'have_prehook_for() did not return False for a deleted hook'
        );

        /*
         * Wait, I've changed my mind. We need that hook back, but we're going to leave it disabled.
         */
        $core->add_pre_hook('get_str', 'Pre-Hook Test', 'Hook_Core_Pre', 'change_str');
        $core->disable_pre_hooks('get_str');
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'disable_pre_hooks() did not run correctly'
        );

        /*
         * We've proven the hook isn't a renegade robot, so we turn it back on and run another systems check.
         */
        $core->enable_pre_hooks('get_str');
        $this->assertEqual(
            "/* {$this->str} */",
            $core->get_str(),
            'enable_pre_hooks() did not run correctly'
        );

        /*
         * Like proper scientists we check our ability to check our ability to turn our hooks on and off
         */
        $this->assertEqual(
            True,
            $core->are_pre_hooks_enabled('get_str'),
            'are_pre_hooks_enabled() did not return True after enable_pre_hooks() ran'

        );
        $core->disable_pre_hooks('get_str');
        $this->assertEqual(
            False,
            $core->are_pre_hooks_enabled('get_str'),
            'are_pre_hooks_enabled() did not return False after disable_pre_hooks() ran'
        );

        /*
         * Now let's test our abilities to run more than one hook and to turn them all off at once
         */
        $core->enable_pre_hooks('get_str');
        $core->set_str($this->str);
        $core->add_pre_hook('get_str', 'Pre-Hook Test Two', 'Hook_Core_Pre_Second', 'change_str');
        $this->assertEqual(
            "## /* {$this->str} */",
            $core->get_str(),
            'enable_pre_hooks() did not run correctly'
        );

        /*
         * Both hooks ran in the order we wanted, so we turn them both off.
         */
        $core->del_pre_hooks('get_str');
        $core->set_str($this->str);
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'del_pre_hooks() did not run correctly'
        );

        /*
         * Let's add our global pre-hook. Just to demonstrate something, we'll add our "Second" hook first, then
         * our global hook, and then the original pre-hook.  Global hooks are run first, and then regular hooks are
         * run in order they are attached.
         */
        $core->add_pre_hook('get_str', 'Pre-Hook Test Two', 'Hook_Core_Pre_Second', 'change_str');
        $core->add_global_pre_hook('Global Pre-Hook', 'Hook_Core_Pre', 'global_change_str');
        $core->add_pre_hook('get_str', 'Pre-Hook Test', 'Hook_Core_Pre', 'change_str');
        $this->assertEqual(
            "/* ## // {$this->str} */",
            $core->get_str(),
            'add_global_pre_hook() did not run correctly'
        );
    }

    public function test_Pre_Hook_Anchor()
    {
        /*
         * Here we call a pre-hook that uses call_anchor() to invoke get_str() itself-after changing the set
         * string to something entirely different.
         */
        $core = new C_Core($this->str);
        $core->add_mixin('Mixin_Core_Override');
        $core->add_pre_hook('get_str', 'Pre-Hook Anchor Test', 'Hook_Core_Pre_Second', 'test_call_anchor');

        $this->assertEqual(
            '<!-- not the test string -->',
            $core->get_str(),
            'A hooks call_anchor() did not run correctly'
        );
    }

    /*
     * That's all for pre-hooks. Post-hooks work identically so readers may want to skip that file.
     */
}
