<?php

/*
 * Continuing on we now have post-hooks.
 *
 * With our pre-hooks we just modified our string before it was sent, but post-hooks run after get_str() so they won't
 * help much. Instead we make a simple function for triggering our post-hooks, it doesn't do anything but trigger them.
 */
class Mixin_Core_Post extends Mixin
{
    public function do_nothing()
    {
        return null;
    }
}

/*
 * In an alternate reality we need to change our string into a Ruby comment, but only after we've seen the original.
 *  We can do that as a post-hook. Again we create two for our tests and a global hook function.
 */
class Hook_Core_Post extends Hook
{
    public function change_str()
    {
        $this->object->_str = '=begin ' . $this->object->_str . ' =end';
    }
    public function global_change_str()
    {
        $this->object->_str = '; ' . $this->object->_str;
    }
}

class Hook_Core_Post_Second extends Hook
{
    public function change_str()
    {
        $this->object->_str = '-- ' . $this->object->_str;
    }
}

class Test_Of_Post_Hooks extends UnitTestCase
{

    public $str = 'test_string';

    public function test_Post_Hooks()
    {
        $core = new C_Core($this->str);
        $core->add_mixin('Mixin_Core_Post');
        $this->assertEqual(
            True,
            $core->has_method('do_nothing'),
            'has_method() did not return True on a method that does exist'
        );

        $core->add_post_hook('do_nothing', 'Post-Hook Test', 'Hook_Core_Post', 'change_str');
        $core->do_nothing();
        $this->assertEqual(
            "=begin {$this->str} =end",
            $core->get_str(),
            'add_post_hook() did not run correctly'
        );

        // make sure a posthook is registered for do_nothing()
        $this->assertEqual(
            True,
            $core->have_posthook_for('do_nothing'),
            'have_posthook_for() did not return True on a function with a post-hook (one parameter)'
        );

        // and that we have one from "Post-Hook Test" and do_nothing()
        $this->assertEqual(
            True,
            $core->have_posthook_for('do_nothing', 'Post-Hook Test'),
            'have_posthook_for() did not return True on a function with a post-hook (two parameter)'
        );

        // just to be safe, ensure we don't have posthooks that don't exist
        $this->assertEqual(
            False,
            $core->have_posthook_for('do_nothing', 'Does Not Exist'),
            'have_posthook_for() did not return False on a post-hook label that does not exist'
        );

        // del_post_hook
        $core->del_post_hook('do_nothing', 'Post-Hook Test');
        $core->set_str($this->str);
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'del_post_hook() did not run correctly'
        );
        $this->assertEqual(
            False,
            $core->have_posthook_for('do_nothing', 'Post-Hook Test'),
            'have_posthook_for() did not return False after calling del_post_hook()'
        );

        // re-add to continue testing
        $core->add_post_hook('do_nothing', 'Post-Hook Test', 'Hook_Core_Post', 'change_str');

        // disable_post_hooks
        $core->disable_post_hooks('do_nothing');
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'disable_post_hooks() did not run correctly'
        );

        // enable_post_hooks
        $core->enable_post_hooks('do_nothing');
        $core->do_nothing();
        $this->assertEqual(
            "=begin {$this->str} =end",
            $core->get_str(),
            'enable_post_hooks() did not run correctly'
        );

        // are_post_hooks_enabled
        $this->assertEqual(
            True,
            $core->are_post_hooks_enabled('do_nothing'),
            'are_post_hooks_enabled() did not return True on a function with post-hooks'
        );

        // are_post_hooks_enabled
        $core->disable_post_hooks('do_nothing');
        $this->assertEqual(
            False,
            $core->are_post_hooks_enabled('do_nothing'),
            'are_post_hooks_enabled() did not return False after calling disable_post_hooks()'
        );

        // two post hooks
        $core->enable_post_hooks('do_nothing');
        $core->set_str($this->str);
        $core->add_post_hook('do_nothing', 'Post-Hook Test Two', 'Hook_Core_Post_Second', 'change_str');
        $core->do_nothing();
        $this->assertEqual(
            "-- =begin {$this->str} =end",
            $core->get_str(),
            'enable_post_hooks() did not run correctly'
        );

        // del_post_hooks
        $core->del_post_hooks('do_nothing');
        $core->set_str($this->str);
        $this->assertEqual(
            $this->str,
            $core->get_str(),
            'del_post_hooks() did not run correctly'
        );

        // add_global_post_hook
        $core->add_post_hook('do_nothing', 'Post-Hook Test Two', 'Hook_Core_Post_Second', 'change_str');
        $core->add_global_post_hook('Global Post-Hook', 'Hook_Core_Post', 'global_change_str');
        $core->add_post_hook('do_nothing', 'Post-Hook Test', 'Hook_Core_Post', 'change_str');
        $core->do_nothing();
        $this->assertEqual(
            True,
            "; =begin -- ; {$this->str} =end",
            'add_global_post_hook() did not run correctly'
        );
    }
}
