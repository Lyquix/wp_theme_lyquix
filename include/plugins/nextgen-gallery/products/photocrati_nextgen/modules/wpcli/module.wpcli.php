<?php

/***
{
Module: photocrati-wpcli
}
 ***/
class M_WPCLI extends C_Base_Module
{
    function define()
    {
        parent::define(
            'photocrati-wpcli',
            'WP-CLI Integration',
            "Provides additional commands for WP-CLI (https://github.com/wp-cli/wp-cli",
            '0.1',
            'https://www.imagely.com/wordpress-gallery-plugin/nextgen-gallery/',
            'Photocrati Media',
            'https://www.imagely.com'
        );
    }

	function initialize()
	{
		parent::initialize();
	}

    function get_type_list()
    {
        return array();
    }
}

new M_WPCLI();

if (defined('WP_CLI') && WP_CLI && class_exists('WP_CLI_Command', FALSE)) {
    /**
     * Manage NextGen Gallery
     */
    class C_NGG_WPCLI extends WP_CLI_Command
    {
        /**
         * Flushes NextGen Gallery caches
         */
        function flush_cache($args, $assoc_args)
        {
            C_Photocrati_Transient_Manager::flush();
            WP_CLI::success('Flushed all caches');
        }

        /**
         * Create a new gallery
         *
         * @synopsis <gallery-name> --author=<user_login>
         */
        function create_gallery($args, $assoc_args)
        {
            $mapper = C_Gallery_Mapper::get_instance();

            $user = get_user_by('login', $assoc_args['author']);
            if (!$user)
                WP_CLI::error("Unable to find user {$assoc_args['author']}");

            if (($gallery = $mapper->create(array('title' => $args[0], 'author' => $user->ID))) && $gallery->save())
            {
                $gallery_id = $retval = $gallery->id();
                WP_CLI::success("Created gallery with id #{$gallery_id}");
            }
            else {
                WP_CLI::error("Unable to create gallery");
            }
        }

        /**
         * Import an image from the filesystem into NextGen
         *
         * @synopsis --filename=<absolute-path> --gallery=<gallery-id>
         */
        function import_image($args, $assoc_args)
        {
            $mapper = C_Gallery_Mapper::get_instance();
            $storage = C_Gallery_Storage::get_instance();

            if (($gallery = $mapper->find($assoc_args['gallery'], TRUE)))
            {
                $file_data = @file_get_contents($assoc_args['filename']);
                $file_name = M_I18n::mb_basename($assoc_args['filename']);

                if (empty($file_data))
                    WP_CLI::error('Could not load file');

                $image = $storage->upload_base64_image($gallery, $file_data, $file_name);
                $image_id = $image->{$image->id_field};
                if (!$image)
                    WP_CLI::error('Could not import image');
                else
                    WP_CLI::success("Imported image with id #{$image_id}");
            }
            else {
                WP_CLI::error("Gallery not found (with id #{$assoc_args['gallery']}");
            }
        }
    }

    WP_CLI::add_command('ngg', 'C_NGG_WPCLI' );
}
