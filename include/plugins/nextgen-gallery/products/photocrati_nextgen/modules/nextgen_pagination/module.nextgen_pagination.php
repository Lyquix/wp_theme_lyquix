<?php
/*
{
    Module: photocrati-nextgen_pagination
}
*/
class M_NextGen_Pagination extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-nextgen_pagination',
            "Pagination",
            "Provides pagination for display types",
            '0.3',
            "https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/",
            "Photocrati Media",
            "https://www.imagely.com"
        );
    }

		function get_type_list()
		{
			return array(
				'Mixin_Nextgen_Basic_Pagination' => 'mixin.nextgen_basic_pagination.php'
			);
		}
}

new M_NextGen_Pagination;
