<?php

/*
 * Point this towards your own simpletest installation
 */
require_once(dirname(__FILE__) . '/../../simpletest-for-wordpress/lib/autorun.php');

require_once('lib/autoload.php');

$tests = array(
    'core',
    'pre_hooks',
    'post_hooks',
    'registry',
    'factories',
    'modules',
    'wrappers',
    'advanced',
    'method_properties'
);

foreach ($tests as $test) {
    require_once('tests/'.$test . '.php');
}
