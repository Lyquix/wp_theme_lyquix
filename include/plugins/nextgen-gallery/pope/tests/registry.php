<?php

/*
 * This is just an empty module to be used for testing later
 */
class C_Test_Autoload extends C_Base_Module
{
    function define()
    {
        parent::define(
            'C_Test_Autoload',
            'C_Test_Autoload Empty Class',
            'An empty shell to test the base modules autoload()',
            '0.1',
            'https://www.imagely.com/',
            'Photocrati',
            'https://www.imagely.com/'
        );
    }
}


/***
{
Product: photocrati-empty
}
 ***/

class P_Photocrati_Empty extends C_Base_Product
{
    public function define()
    {
        // id, name, description, version, uri, author, author_uri
        parent::define(
            'photocrati-empty',
            'Photocrati Empty',
            'AN empty shell used for unit testing',
            '0.1',
            'https://bitbucket.org/photocrati/pope-framework',
            'Photocrati Media',
            'https://www.imagely.com'
        );
    }
}


class Test_Of_Registry extends UnitTestCase
{
    /** @var string $path Path to the Pope products directory */
    public $path;
    
    public $registry;

    public function __construct()
    {
        $this->path = dirname(__FILE__) . '/products/';
    }

    /**
     * Tests the registration setup for Pope
     */
    public function test_Registry_Setup()
    {
		// Ensure we're working with an entirely new instance
		C_Component_Registry::$_instance = Null;
        $this->registry = C_Component_Registry::get_instance();

        // We *want* to cause an exception for once. This should come without any utilities; we'll check for them again
        // after we've added one
        $factory = false;
        try
        {
            $factory = $this->registry->get_utility('I_Component_Factory');
        } catch (Exception $exception) { }
        $this->assertEqual(
            False,
            $factory,
            'A utility was registered to I_Component_Factory when it should be empty'
        );

        // Make sure we can set and retrieve utilities
        $this->registry->add_utility('I_Component_Factory', 'C_Component_Factory');
        $factory = $this->registry->get_utility('I_Component_Factory');
        $this->assertEqual(
            'C_Component_Factory',
            get_class($factory),
            'add_utility() followed by get_utility() did not return a C_Component_Factory utility'
        );

        // Pope can also handle singleton generation, just provide a get_instance() function for your class
        $factory = $this->registry->get_utility('I_Component_Factory');
        $this->assertEqual(
            'C_Component_Factory',
            get_class($factory),
            'get_utility did not return C_Component_Factory utility'
        );

        // We've just started so our product & module lists should be completely empty
        $this->assertEqual(
            array(),
            $this->registry->get_known_product_list(),
            'No products should be known yet'
        );
        $this->assertEqual(
            array(),
            $this->registry->get_known_module_list(),
            'No modules should be known yet'
        );
        $this->assertEqual(
            array(),
            $this->registry->get_product_list(),
            'The product list should be empty'
        );
        $this->assertEqual(
            array(),
            $this->registry->get_module_list(),
            'The module list should be empty'
        );
    }

    /**
     * Tests path setup and initial module loading
     */
    public function test_Registry_Add_Module_Path()
    {
        /*
         * Not every module has to set its own modules directory, add_module_path() will call set_default_module_path()
         * the first time it runs so that all of your modules will come from the same place.
         */
        $this->assertEqual(
            Null,
            $this->registry->get_default_module_path(),
            'get_default_module_path() returned !null before add_module_path() or set_default_module_path() were called'
        );

        $this->registry->add_module_path($this->path, True, False);

        $this->assertEqual(
            $this->path,
            $this->registry->get_default_module_path(),
            'add_module_path() did not call set_default_module_path() correctly'
        );

        /*
         * get_known_module_list and get_known_product_list return ALL registered objects, even
         * if they haven't been loaded yet (through load_all_products() or load_module() or such.
         * get_product_list & get_module_list returns only loaded objects.
         *
         * Make sure we know of but haven't loaded any products yet
        */
        $this->assertEqual(
            array(
                0 => 'photocrati-animal',
                1 => 'photocrati-pope'
            ),
            $this->registry->get_known_product_list(),
            'Only the Pope and Animal-Farm products should be known'
        );
        $this->assertEqual(
            array(),
            $this->registry->get_product_list(),
            'The product list should be empty'
        );

        // make sure we know about but haven't yet loaded any modules
        $this->assertEqual(
            array(
                0 => 'photocrati-animal',
                1 => 'photocrati-pope'
            ),
            $this->registry->get_known_module_list(),
            'Only the pope product should be known'
        );
        $this->assertEqual(
            array(),
            $this->registry->get_module_list(),
            'The module should should be empty'
        );
    }

    /**
     * Tests loading of (product|module)s
     */
    public function test_Registry_Load()
    {
        // test our ability to load & retrieve products
        $this->registry->load_product('photocrati-pope');
        $this->assertEqual(
            array(
                0 => 'photocrati-pope'
            ),
            $this->registry->get_product_list(),
            'get_product_list() did not return a correct list (see load_product() / add_product()?)'
        );

        // and then our ability to load & retrieve modules from products
        $this->registry->load_module('photocrati-template');
        $this->assertEqual(
            array(
                0 => 'photocrati-pope',
                1 => 'photocrati-template'
            ),
            $this->registry->get_module_list(),
            'get_module_list() did not retrieve a correct list (see load_module() / add_module()?)'
        );

        // make sure we're loading from the correct path
        $this->assertEqual(
            $this->path . 'photocrati_pope/modules/',
            $this->registry->get_product_module_path('photocrati-pope'),
            'get_product_module_path() did not return the correct product-module path'
        );

        // and that the module is also in the right place
        $this->assertEqual(
            $this->path . 'photocrati_pope/modules/template/module.template.php',
            $this->registry->get_module_path('photocrati-template'),
            'get_module_path() did not return the correct module path'
        );
        $this->assertEqual(
            $this->path . 'photocrati_pope/modules/template',
            $this->registry->get_module_dir('photocrati-template'),
            'get_module_dir() did not return the correct module directory'
        );
    }

    /**
     * Tests retrieval of product metadata
     */
    public function test_Registry_Get_Product()
    {
        // do we have the right product?
        $obj = $this->registry->get_product('photocrati-pope');
        $this->assertEqual(
            'P_Photocrati_Pope',
            get_class($obj),
            'get_product() did not return a P_Photocrati_Pope object (check add_product())'
        );
        $this->assertEqual(
            'photocrati-pope',
            $this->registry->get_product_meta('photocrati-pope', 'id'),
            'get_product_meta() did not return the correct product ID meta-information'
        );
        $this->assertEqual(
            array(
                'type' => 'product',
                'id' => 'photocrati-pope',
                'path' => $this->path . 'photocrati_pope/product.photocrati_pope.php',
                'product-module-path' => $this->path . 'photocrati_pope/modules/'),
            $this->registry->get_product_meta_list('photocrati-pope'),
            'get_product_meta_list() did not return the correct product meta-information'
        );
    }

    /**
     * Tests retrieval of module metadata
     */
    public function test_Registry_Get_Module()
    {
        // do we have the right module?
        $obj = $this->registry->get_module('photocrati-template');
        $this->assertEqual(
            'M_Template',
            get_class($obj),
            'get_module did not return a M_Template object'
        );
        $this->assertEqual(
            'photocrati-template',
            $this->registry->get_module_meta('photocrati-template', 'id'),
            'get_module_meta() did not return the correct module ID meta-information'
        );
        $this->assertEqual(
            array(
                'type' => 'module',
                'id' => 'photocrati-template',
                'path' => $this->path . 'photocrati_pope/modules/template/module.template.php'),
            $this->registry->get_module_meta_list('photocrati-template'),
            'get_module_meta_list did not return the correct module meta-information'
        );
    }

    /**
     * Tests module initialization
     */
    public function test_Registry_Initialization()
    {
        $obj = $this->registry->get_module('photocrati-template');

        // we're looking at the correct product and module, let's initialize() it
        $this->assertEqual(
            False,
            (isset($obj->is_initialized) && True == $obj->is_initialized),
            'Module was initialized prematurely'
        );

        $this->registry->initialize_module('photocrati-template');

        $this->assertEqual(
            True,
            (isset($obj->is_initialized) && True == $obj->is_initialized),
            'initialize_module() did not run correctly'
        );
    }

    /**
     * Tests get_utility and factory creation
     */
    public function test_Registry_Get_Utility()
    {
        /*
         * We must again retrieve the factory class after having loaded our products & module.
         */
        $factory = $this->registry->get_utility('I_Component_Factory');
        try
        {
            $obj = $factory->create('photocrati_template', 'Normal');
        } catch (Exception $exception) { }
        $this->assertEqual(
            'C_Template_Library',
            (isset($obj) && get_class($obj)),
            'create() did not return a C_Template_Library object'
        );
    }

    /**
     * Some sanity checks
     */
    public function test_Registry_Paranoid_Check()
    {
        // Just to be paranoid we check to make sure we still have ONLY loaded the Pope product / Template module
        $this->assertEqual(
            array(
                0 => 'photocrati-pope'
            ),
            $this->registry->get_product_list(),
            'A product was loaded that should not have been'
        );
        $this->assertEqual(
            array(
                0 => 'photocrati-pope',
                1 => 'photocrati-template'
            ),
            $this->registry->get_module_list(),
            'A module was loaded that should not have been'
        );
    }

    /**
     * Tests loading of all remaining modules
     */
    public function test_Registry_Load_All()
    {
        // load the remaining (Animal/AnimalFarm) products & modules
        $this->registry->load_all_products();
        $this->registry->load_all_modules();

        $this->assertEqual(
            array(
                0 => 'photocrati-pope',
                1 => 'photocrati-animal'
            ),
            $this->registry->get_product_list(),
            'load_all_products() did not run correctly (Animal product was not loaded)'
        );
        $this->assertEqual(
            array(
                0 => 'photocrati-pope',
                1 => 'photocrati-template',
                2 => 'photocrati-animal',
                3 => 'photocrati-animal-farm'
            ),
            $this->registry->get_module_list(),
            'load_all_modules() did not run correctly (The Animal Farm module was not loaded)'
        );

        $obj = $this->registry->get_module('photocrati-animal-farm');
        $this->assertEqual(
            False,
            (isset($obj->is_initialized) && True == $obj->is_initialized),
            'Module was initialized prematurely'
        );

        $this->registry->initialize_all_modules();

        $obj = $this->registry->get_module('photocrati-animal-farm');
        $this->assertEqual(
            True,
            (isset($obj->is_initialized) && True == $obj->is_initialized),
            'initialize_all_modules() did not run correctly (Animal Farm module was not initialized)'
        );
    }

    /**
     * Tests SPL autoloader
     */
    public function test_Registry_Autoload()
    {
        /*
         * The C_Animal_Empty class exists but is never referenced elsewhere in the code. We call class_exists()
         * on it here to trigger the autoload() function which should have been automatically registered with the SPL.
         */
        $this->assertEqual(
            True,
            class_exists('C_Animal_Empty'),
            'C_Animal_Empty was not found by autoload()'
        );
    }

    /**
     * Tests registry (add|del)_(module|product) functions
     */
    public function test_Registry_CD()
    {
        $tmp = new C_Test_Autoload();

        /*
         * We add our empty module created above and dynamically add it to to our registry
         */
        $this->assertEqual(
            True,
            in_array('C_Test_Autoload', $this->registry->get_module_list()),
            'add_module() did not run correctly for C_Test_Autoload'
        );

        /*
         * We remove it just as easily
         */
        $this->registry->del_module('C_Test_Autoload');
        $this->assertEqual(
            False,
            in_array('C_Test_Autoload', $this->registry->get_module_list()),
            'del_module() did not remove C_Test_Autoload from get_module_list()'
        );

        /*
         * We can also instantiate our products manually and then pass them to Pope
         */
        $this->registry->add_product('photocrati-empty', new P_Photocrati_Empty());

        $this->assertEqual(
            True,
            in_array('photocrati-empty', $this->registry->get_product_list()),
            'add_product() did not add to the get_product_list() results'
        );

        $this->registry->del_product('photocrati-empty');

        $this->assertEqual(
            False,
            in_array('photocrati-empty', $this->registry->get_product_list()),
            'del_product() did not remove from the get_product_list() results'
        );
    }
}
