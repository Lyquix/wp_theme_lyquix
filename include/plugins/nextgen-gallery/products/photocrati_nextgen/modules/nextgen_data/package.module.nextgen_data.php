<?php
/**
 * Modifies a custom post datamapper to use the WordPress built-in 'attachment'
 * custom post type, as used by the Media Library
 */
class A_Attachment_DataMapper extends Mixin
{
    public function initialize()
    {
        $this->object->_object_name = 'attachment';
    }
    /**
     * Saves the entity using the wp_insert_attachment function
     * instead of the wp_insert_post
     * @param stdObject $entity
     */
    public function _save_entity($entity)
    {
        $post = $this->object->_convert_entity_to_post($entity);
        $filename = property_exists($entity, 'filename') ? $entity->filename : FALSE;
        $primary_key = $this->object->get_primary_key_column();
        if ($post_id = $attachment_id = wp_insert_attachment($post, $filename)) {
            $new_entity = $this->object->find($post_id);
            foreach ($new_entity as $key => $value) {
                $entity->{$key} = $value;
            }
            // Merge meta data with WordPress Attachment Meta Data
            if (property_exists($entity, 'meta_data')) {
                $meta_data = wp_get_attachment_metadata($attachment_id);
                if (isset($meta_data['image_meta'])) {
                    $entity->meta_data = array_merge_recursive($meta_data['image_meta'], $entity->meta_data);
                    wp_update_attachment_metadata($attachment_id, $entity->meta_data);
                }
            }
            // Save properties are post meta as well
            $this->object->_flush_and_update_postmeta($attachment_id, $entity instanceof stdClass ? $entity : $entity->get_entity(), array('_wp_attached_file', '_wp_attachment_metadata', '_mapper'));
            $entity->id_field = $primary_key;
        }
        return $attachment_id;
    }
    public function select($fields = '*')
    {
        $ret = $this->call_parent('select', $fields);
        $this->object->_query_args['datamapper_attachment'] = true;
        return $ret;
    }
}
class A_NextGen_Data_Factory extends Mixin
{
    public function gallery($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        return new C_Gallery($properties, $mapper, $context);
    }
    public function gallery_image($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        return new C_Image($properties, $mapper, $context);
    }
    public function image($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        return new C_Image($properties, $mapper, $context);
    }
    public function album($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        return new C_Album($properties, $mapper, $context);
    }
    public function ngglegacy_gallery_storage($context = FALSE)
    {
        return new C_NggLegacy_GalleryStorage_Driver($context);
    }
    public function wordpress_gallery_storage($context = FALSE)
    {
        return new C_WordPress_GalleryStorage_Driver($context);
    }
    public function gallery_storage($context = FALSE)
    {
        return new C_Gallery_Storage($context);
    }
    public function extra_fields($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        return new C_Datamapper_Model($mapper, $properties, $context);
    }
    public function gallerystorage($context = FALSE)
    {
        return $this->object->gallery_storage($context);
    }
}
class C_Album extends C_DataMapper_Model
{
    public $_mapper_interface = 'I_Album_Mapper';
    public function define($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        parent::define($mapper, $properties, $context);
        $this->add_mixin('Mixin_NextGen_Album_Instance_Methods');
        $this->implement('I_Album');
    }
    /**
     * Instantiates an Album object
     * @param bool|\C_DataMapper|\FALSE $mapper
     * @param array $properties
     */
    public function initialize($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        // Get the mapper is not specified
        if (!$mapper) {
            $mapper = $this->get_registry()->get_utility($this->_mapper_interface);
        }
        // Initialize
        parent::initialize($mapper, $properties);
    }
}
/**
 * Provides instance methods for the album
 */
class Mixin_NextGen_Album_Instance_Methods extends Mixin
{
    public function validation()
    {
        $this->validates_presence_of('name');
        $this->validates_numericality_of('previewpic');
        return $this->object->is_valid();
    }
    /**
     * Gets all galleries associated with the album
     */
    public function get_galleries($models = FALSE)
    {
        $retval = array();
        $mapper = C_Gallery_Mapper::get_instance();
        $gallery_key = $mapper->get_primary_key_column();
        $retval = $mapper->find_all(array("{$gallery_key} IN %s", $this->object->sortorder), $models);
        return $retval;
    }
}
class C_Album_Mapper extends C_CustomTable_DataMapper_Driver
{
    static $_instance = NULL;
    public function initialize($object_name = FALSE)
    {
        parent::initialize('ngg_album');
    }
    public function define($context = FALSE, $not_used = FALSE)
    {
        // Define the context
        if (!is_array($context)) {
            $context = array($context);
        }
        array_push($context, 'album');
        $this->_primary_key_column = 'id';
        // Define the mapper
        parent::define('ngg_album', $context);
        $this->add_mixin('Mixin_NextGen_Table_Extras');
        $this->add_mixin('Mixin_Album_Mapper');
        $this->implement('I_Album_Mapper');
        $this->set_model_factory_method('album');
        // Define the columns
        $this->define_column('id', 'BIGINT', 0);
        $this->define_column('name', 'VARCHAR(255)');
        $this->define_column('slug', 'VARCHAR(255');
        $this->define_column('previewpic', 'BIGINT', 0);
        $this->define_column('albumdesc', 'TEXT');
        $this->define_column('sortorder', 'TEXT');
        $this->define_column('pageid', 'BIGINT', 0);
        $this->define_column('extras_post_id', 'BIGINT', 0);
        // Mark the columns which should be unserialized
        $this->add_serialized_column('sortorder');
    }
    /**
     * Returns an instance of the album datamapper
     * @param bool|mixed $context
     * @return C_Album_Mapper
     */
    static function get_instance($context = FALSE)
    {
        if (is_null(self::$_instance)) {
            $klass = get_class();
            self::$_instance = new $klass($context);
        }
        return self::$_instance;
    }
}
/**
 * Provides album-specific methods for the datamapper
 */
class Mixin_Album_Mapper extends Mixin
{
    /**
     * Gets the post title when the Custom Post driver is used
     * @param C_DataMapper_Model|C_Album|stdClass $entity
     * @return string
     */
    public function get_post_title($entity)
    {
        return $entity->name;
    }
    public function _save_entity($entity)
    {
        $retval = $this->call_parent('_save_entity', $entity);
        if ($retval) {
            do_action('ngg_album_updated', $entity);
            C_Photocrati_Transient_Manager::flush('displayed_gallery_rendering');
        }
        return $retval;
    }
    /**
     * Sets the defaults for an album
     * @param C_DataMapper_Model|C_Album|stdClass $entity
     */
    public function set_defaults($entity)
    {
        $this->object->_set_default_value($entity, 'name', '');
        $this->object->_set_default_value($entity, 'albumdesc', '');
        $this->object->_set_default_value($entity, 'sortorder', array());
        $this->object->_set_default_value($entity, 'previewpic', 0);
        $this->object->_set_default_value($entity, 'exclude', 0);
        if (isset($entity->name) && !isset($entity->slug)) {
            $entity->slug = nggdb::get_unique_slug(sanitize_title($entity->name), 'album');
        }
    }
}
class Mixin_NextGen_Gallery_Validation
{
    /**
     * Validates whether the gallery can be saved
     */
    public function validation()
    {
        // If a title is present, we can auto-populate some other properties
        if ($this->object->title) {
            // Strip html
            $this->object->title = M_NextGen_Data::strip_html($this->object->title, TRUE);
            $sanitized_title = str_replace(' ', '-', $this->object->title);
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $sanitized_title = remove_accents($sanitized_title);
            }
            // If no name is present, use the title to generate one
            if (!$this->object->name) {
                $this->object->name = apply_filters('ngg_gallery_name', sanitize_file_name($sanitized_title));
            }
            // If no slug is set, use the title to generate one
            if (!$this->object->slug) {
                $this->object->slug = preg_replace('|[^a-z0-9 \\-~+_.#=!&;,/:%@$\\|*\'()\\x80-\\xff]|i', '', $sanitized_title);
                $this->object->slug = nggdb::get_unique_slug($this->object->slug, 'gallery');
            }
        }
        // Set what will be the path to the gallery
        if (!$this->object->path) {
            $storage = C_Gallery_Storage::get_instance();
            $this->object->path = $storage->get_upload_relpath($this->object);
            unset($storage);
        } else {
            $this->object->path = M_NextGen_Data::strip_html($this->object->path);
            $this->object->path = str_replace(array('"', '\'\'', '>', '<'), array('', '', '', ''), $this->object->path);
        }
        $this->object->validates_presence_of('title');
        $this->object->validates_presence_of('name');
        $this->object->validates_uniqueness_of('slug');
        $this->object->validates_numericality_of('author');
        return $this->object->is_valid();
    }
}
/**
 * Creates a model representing a NextGEN Gallery object
 */
class C_Gallery extends C_DataMapper_Model
{
    public $_mapper_interface = 'I_Gallery_Mapper';
    /**
     * Defines the interfaces and methods (through extensions and hooks)
     * that this class provides
     */
    public function define($properties, $mapper = FALSE, $context = FALSE)
    {
        parent::define($mapper, $properties, $context);
        $this->add_mixin('Mixin_NextGen_Gallery_Validation');
        $this->implement('I_Gallery');
    }
    /**
     * Instantiates a new model
     * @param array|stdClass $properties
     * @param C_DataMapper $mapper
     * @param string $context
     */
    public function initialize($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        // Get the mapper is not specified
        if (!$mapper) {
            $mapper = $this->get_registry()->get_utility($this->_mapper_interface);
        }
        // Initialize
        parent::initialize($mapper, $properties);
    }
    public function get_images()
    {
        $mapper = C_Image_Mapper::get_instance();
        return $mapper->select()->where(array('galleryid = %d', $this->gid))->order_by('sortorder')->run_query();
    }
}
/**
 * Provides a datamapper for galleries
 */
class C_Gallery_Mapper extends C_CustomTable_DataMapper_Driver
{
    public static $_instance = NULL;
    /**
     * Define the object
     * @param string $context
     */
    public function define($context = FALSE, $not_used = FALSE)
    {
        // Add 'gallery' context
        if (!is_array($context)) {
            $context = array($context);
        }
        array_push($context, 'gallery');
        $this->_primary_key_column = 'gid';
        // Continue defining the object
        parent::define('ngg_gallery', $context);
        $this->set_model_factory_method('gallery');
        $this->add_mixin('Mixin_NextGen_Table_Extras');
        $this->add_mixin('Mixin_Gallery_Mapper');
        $this->implement('I_Gallery_Mapper');
        // Define the columns
        $this->define_column('gid', 'BIGINT', 0);
        $this->define_column('name', 'VARCHAR(255)');
        $this->define_column('slug', 'VARCHAR(255)');
        $this->define_column('path', 'TEXT');
        $this->define_column('title', 'TEXT');
        $this->define_column('pageid', 'INT', 0);
        $this->define_column('previewpic', 'INT', 0);
        $this->define_column('author', 'INT', 0);
        $this->define_column('extras_post_id', 'BIGINT', 0);
    }
    public function initialize($object_name = FALSE)
    {
        parent::initialize('ngg_gallery');
    }
    /**
     * Returns a singleton of the gallery mapper
     * @param string $context
     * @return C_Gallery_Mapper
     */
    public static function get_instance($context = False)
    {
        if (is_null(self::$_instance)) {
            $klass = get_class();
            self::$_instance = new $klass($context);
        }
        return self::$_instance;
    }
}
class Mixin_Gallery_Mapper extends Mixin
{
    /**
     * Uses the title property as the post title when the Custom Post driver
     * is used
     */
    public function get_post_title($entity)
    {
        return $entity->title;
    }
    public function _save_entity($entity)
    {
        // A bug in NGG 2.1.24 allowed galleries to be created with spaces in the directory name, unreplaced by dashes
        // This causes a few problems everywhere, so we here allow users a way to fix those galleries by just re-saving
        if (FALSE !== strpos($entity->path, ' ')) {
            $storage = C_Gallery_Storage::get_instance();
            $abspath = $storage->get_gallery_abspath($entity->{$entity->id_field});
            $pre_path = $entity->path;
            $entity->path = str_replace(' ', '-', $entity->path);
            $new_abspath = str_replace($pre_path, $entity->path, $abspath);
            // Begin adding -1, -2, etc until we have a safe target: rename() will overwrite existing directories
            if (@file_exists($new_abspath)) {
                $max_count = 100;
                $count = 0;
                $corrected_abspath = $new_abspath;
                while (@file_exists($corrected_abspath) && $count <= $max_count) {
                    $count++;
                    $corrected_abspath = $new_abspath . '-' . $count;
                }
                $new_abspath = $corrected_abspath;
                $entity->path = $entity->path . '-' . $count;
            }
            @rename($abspath, $new_abspath);
        }
        $slug = $entity->slug;
        $entity->slug = str_replace(' ', '-', $entity->slug);
        // Note: we do the following to mimic the behaviour of esc_url so that slugs are always valid in URLs after escaping
        $entity->slug = preg_replace('|[^a-z0-9 \\-~+_.#=!&;,/:%@$\\|*\'()\\x80-\\xff]|i', '', $entity->slug);
        if ($slug != $entity->slug) {
            // creating new slug for the gallery
            $entity->slug = nggdb::get_unique_slug($entity->slug, 'gallery');
        }
        $retval = $this->call_parent('_save_entity', $entity);
        if ($retval) {
            do_action('ngg_created_new_gallery', $entity->{$entity->id_field});
            C_Photocrati_Transient_Manager::flush('displayed_gallery_rendering');
        }
        return $retval;
    }
    public function destroy($gallery, $with_dependencies = FALSE)
    {
        $retval = FALSE;
        if ($gallery) {
            $gallery_id = is_numeric($gallery) ? $gallery : $gallery->{$gallery->id_field};
            // TODO: Look into making this operation more efficient
            if ($with_dependencies) {
                $image_mapper = C_Image_Mapper::get_instance();
                // Delete the image files from the filesystem
                $settings = C_NextGen_Settings::get_instance();
                if ($settings->deleteImg) {
                    $storage = C_Gallery_Storage::get_instance();
                    $storage->delete_gallery($gallery);
                }
                // Delete the image records from the DB
                $image_mapper->delete()->where(array('galleryid = %d', $gallery_id))->run_query();
                $image_key = $image_mapper->get_primary_key_column();
                $image_table = $image_mapper->get_table_name();
                // Delete tag associations no longer needed. The following SQL statement
                // deletes all tag associates for images that no longer exist
                global $wpdb;
                $wpdb->query("\n\t\t\t\t\tDELETE wptr.* FROM {$wpdb->term_relationships} wptr\n\t\t\t\t\tINNER JOIN {$wpdb->term_taxonomy} wptt\n\t\t\t\t\tON wptt.term_taxonomy_id = wptr.term_taxonomy_id\n\t\t\t\t\tWHERE wptt.term_taxonomy_id = wptr.term_taxonomy_id\n\t\t\t\t\tAND wptt.taxonomy = 'ngg_tag'\n\t\t\t\t\tAND wptr.object_id NOT IN (SELECT {$image_key} FROM {$image_table})");
            }
            $retval = $this->call_parent('destroy', $gallery);
            if ($retval) {
                do_action('ngg_delete_gallery', $gallery);
                C_Photocrati_Transient_Manager::flush('displayed_gallery_rendering');
            }
        }
        return $retval;
    }
    public function set_preview_image($gallery, $image, $only_if_empty = FALSE)
    {
        $retval = FALSE;
        // We need the gallery object
        if (is_numeric($gallery)) {
            $gallery = $this->object->find($gallery);
        }
        // We need the image id
        if (!is_numeric($image)) {
            if (method_exists($image, 'id')) {
                $image = $image->id();
            } else {
                $image = $image->{$image->id_field};
            }
        }
        if ($gallery && $image) {
            if ($only_if_empty && !$gallery->previewpic or !$only_if_empty) {
                $gallery->previewpic = $image;
                $retval = $this->object->save($gallery);
            }
        }
        return $retval;
    }
    /**
     * Sets default values for the gallery
     */
    public function set_defaults($entity)
    {
        // If author is missing, then set to the current user id
        // TODO: Using wordpress function. Should use abstraction
        $this->object->_set_default_value($entity, 'author', get_current_user_id());
    }
}
class GalleryStorageDriverNotSelectedException extends RuntimeException
{
    public function __construct($message = '', $code = NULL, $previous = NULL)
    {
        if (!$message) {
            $message = 'No gallery storage driver selected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
class Mixin_GalleryStorage extends Mixin
{
    /**
     * Returns the name of the class which provides the gallerystorage
     * implementation
     * @return string
     */
    public function _get_driver_factory_method($context = FALSE)
    {
        $factory_method = '';
        // No constant has been defined to establish a global gallerystorage driver
        if (!defined('GALLERYSTORAGE_DRIVER')) {
            // Get the datamapper configured in the database
            $factory_method = C_NextGen_Settings::get_instance()->gallerystorage_driver;
            // Define a constant and use this as the global gallerystorage driver,
            // unless running in a SimpleTest Environment
            if (!isset($GLOBALS['SIMPLE_TEST_RUNNING'])) {
                define('GALLERYSTORAGE_DRIVER', $factory_method);
            }
        } else {
            $factory_method = GALLERYSTORAGE_DRIVER;
        }
        return $factory_method;
    }
}
class C_GalleryStorage_Base extends C_Component
{
    /**
     * Gets the url or path of an image of a particular size
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (preg_match('/^get_(\\w+)_(abspath|url|dimensions|html|size_params)$/', $method, $match)) {
            if (isset($match[1]) && isset($match[2]) && !$this->has_method($method)) {
                $method = 'get_image_' . $match[2];
                $args[] = $match[1];
                // array($image, $size)
                return parent::__call($method, $args);
            }
        }
        return parent::__call($method, $args);
    }
}
class C_Gallery_Storage extends C_GalleryStorage_Base
{
    public static $_instances = array();
    public function define($object_name, $context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_GalleryStorage');
        $this->wrap('I_GalleryStorage_Driver', array(&$this, '_get_driver'), array($object_name, $context));
        $this->implement('I_Gallery_Storage');
    }
    static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context])) {
            self::$_instances[$context] = new C_Gallery_Storage($context);
        }
        return self::$_instances[$context];
    }
    /**
     * Returns the implementation for the gallerystorage
     * @param array $args
     * @return mixed
     */
    public function _get_driver($args)
    {
        $object_name = $args[0];
        $context = $args[1];
        $factory_method = $this->_get_driver_factory_method($context);
        $factory = C_Component_Factory::get_instance();
        return $factory->create($factory_method, $object_name, $context);
    }
}
class E_UploadException extends E_NggErrorException
{
    public function __construct($message = '', $code = NULL, $previous = NULL)
    {
        if (!$message) {
            $message = 'There was a problem uploading the file.';
        }
        if (PHP_VERSION_ID >= 50300) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
}
class E_InsufficientWriteAccessException extends E_NggErrorException
{
    public function __construct($message = FALSE, $filename = NULL, $code = NULL, $previous = NULL)
    {
        if (!$message) {
            $message = 'Could not write to file. Please check filesystem permissions.';
        }
        if ($filename) {
            $message .= " Filename: {$filename}";
        }
        if (PHP_VERSION_ID >= 50300) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
}
class E_NoSpaceAvailableException extends E_NggErrorException
{
    public function __construct($message = '', $code = NULL, $previous = NULL)
    {
        if (!$message) {
            $message = 'You have exceeded your storage capacity. Please remove some files and try again.';
        }
        if (PHP_VERSION_ID >= 50300) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
}
class E_No_Image_Library_Exception extends E_NggErrorException
{
    public function __construct($message = '', $code = NULL, $previous = NULL)
    {
        if (!$message) {
            $message = 'The site does not support the GD Image library. Please ask your hosting provider to enable it.';
        }
        if (PHP_VERSION_ID >= 50300) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
}
class Mixin_GalleryStorage_Driver_Base extends Mixin
{
    /**
     * Set correct file permissions (taken from wp core). Should be called
     * after writing any file
     *
     * @class nggAdmin
     * @param string $filename
     * @return bool $result
     */
    public function _chmod($filename = '')
    {
        $stat = @stat(dirname($filename));
        $perms = $stat['mode'] & 438;
        // Remove execute bits for files
        if (@chmod($filename, $perms)) {
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Gets the id of a gallery, regardless of whether an integer
     * or object was passed as an argument
     * @param mixed $gallery_obj_or_id
     */
    public function _get_gallery_id($gallery_obj_or_id)
    {
        $retval = NULL;
        $gallery_key = $this->object->_gallery_mapper->get_primary_key_column();
        if (is_object($gallery_obj_or_id)) {
            if (isset($gallery_obj_or_id->{$gallery_key})) {
                $retval = $gallery_obj_or_id->{$gallery_key};
            }
        } elseif (is_numeric($gallery_obj_or_id)) {
            $retval = $gallery_obj_or_id;
        }
        return $retval;
    }
    /**
     * Gets the id of an image, regardless of whether an integer
     * or object was passed as an argument
     * @param type $image_obj_or_id
     */
    public function _get_image_id($image_obj_or_id)
    {
        $retval = NULL;
        $image_key = $this->object->_image_mapper->get_primary_key_column();
        if (is_object($image_obj_or_id)) {
            if (isset($image_obj_or_id->{$image_key})) {
                $retval = $image_obj_or_id->{$image_key};
            }
        } elseif (is_numeric($image_obj_or_id)) {
            $retval = $image_obj_or_id;
        }
        return $retval;
    }
    public function convert_slashes($path)
    {
        $search = array('/', '\\');
        $replace = array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        return str_replace($search, $replace, $path);
    }
    public function delete_directory($abspath)
    {
        $retval = FALSE;
        if (@file_exists($abspath)) {
            $files = scandir($abspath);
            array_shift($files);
            array_shift($files);
            foreach ($files as $file) {
                $file_abspath = implode(DIRECTORY_SEPARATOR, array(rtrim($abspath, '/\\'), $file));
                if (is_dir($file_abspath)) {
                    $this->object->delete_directory($file_abspath);
                } else {
                    unlink($file_abspath);
                }
            }
            rmdir($abspath);
            $retval = @file_exists($abspath);
        }
        return $retval;
    }
    /**
     * Backs up an image file
     *
     * @param int|object $image
     */
    public function backup_image($image)
    {
        $retval = FALSE;
        $image_path = $this->object->get_image_abspath($image);
        if ($image_path && @file_exists($image_path)) {
            $retval = copy($image_path, $this->object->get_backup_abspath($image));
            // Store the dimensions of the image
            if (function_exists('getimagesize')) {
                $mapper = C_Image_Mapper::get_instance();
                if (!is_object($image)) {
                    $image = $mapper->find($image);
                }
                if ($image) {
                    if (!property_exists($image, 'meta_data')) {
                        $image->meta_data = array();
                    }
                    $dimensions = getimagesize($image_path);
                    $image->meta_data['backup'] = array('filename' => basename($image_path), 'width' => $dimensions[0], 'height' => $dimensions[1], 'generated' => microtime());
                    $mapper->save($image);
                }
            }
        }
        return $retval;
    }
    /**
     * Copies images into another gallery
     * @param array $images
     * @param int|object $gallery
     * @param boolean $db optionally only copy the image files
     * @param boolean $move move the image instead of copying
     */
    public function copy_images($images, $gallery, $db = TRUE, $move = FALSE)
    {
        $retval = FALSE;
        // Ensure we have a valid gallery
        if ($gallery = $this->object->_get_gallery_id($gallery)) {
            $gallery_path = $this->object->get_gallery_abspath($gallery);
            $image_key = $this->object->_image_mapper->get_primary_key_column();
            $retval = TRUE;
            // Iterate through each image to copy...
            foreach ($images as $image) {
                // Copy each image size
                foreach ($this->object->get_image_sizes() as $size) {
                    $image_path = $this->object->get_image_abspath($image, $size);
                    $dst = implode(DIRECTORY_SEPARATOR, array($gallery_path, M_I18n::mb_basename($image_path)));
                    $success = $move ? move($image_path, $dst) : copy($image_path, $dst);
                    if (!$success) {
                        $retval = FALSE;
                    }
                }
                // Copy the db entry
                if ($db) {
                    if (is_numeric($image)) {
                        $this->object->_image_mapper($image);
                    }
                    unset($image->{$image_key});
                    $image->galleryid = $gallery;
                }
            }
        }
        return $retval;
    }
    /**
     * Empties the gallery cache directory of content
     */
    public function flush_cache($gallery)
    {
        $cache = C_Cache::get_instance();
        $cache->flush_directory($this->object->get_cache_abspath($gallery));
    }
    /**
     * Gets the absolute path of the backup of an original image
     * @param string $image
     */
    public function get_backup_abspath($image)
    {
        $retval = null;
        if ($image_path = $this->object->get_image_abspath($image)) {
            $retval = $image_path . '_backup';
        }
        return $retval;
    }
    public function get_backup_dimensions($image)
    {
        return $this->object->get_image_dimensions($image, 'backup');
    }
    public function get_backup_url($image)
    {
        return $this->object->get_image_url($image, 'backup');
    }
    /**
     * Returns the absolute path to the cache directory of a gallery.
     *
     * Without the gallery parameter the legacy (pre 2.0) shared directory is returned.
     *
     * @param int|stdClass|C_Gallery $gallery (optional)
     * @return string Absolute path to cache directory
     */
    public function get_cache_abspath($gallery = FALSE)
    {
        $retval = NULL;
        if (FALSE == $gallery) {
            $gallerypath = C_NextGen_Settings::get_instance()->gallerypath;
            $retval = implode(DIRECTORY_SEPARATOR, array(rtrim(C_Fs::get_instance()->get_document_root('gallery'), '/\\'), rtrim($gallerypath, '/\\'), 'cache'));
        } else {
            if (is_numeric($gallery)) {
                $gallery = $this->object->_gallery_mapper->find($gallery);
            }
            $retval = rtrim(implode(DIRECTORY_SEPARATOR, array($this->object->get_gallery_abspath($gallery), 'dynamic')), '/\\');
        }
        return $retval;
    }
    /**
     * Gets the absolute path where the full-sized image is stored
     * @param int|object $image
     */
    public function get_full_abspath($image)
    {
        return $this->object->get_image_abspath($image, 'full');
    }
    /**
     * Alias to get_image_dimensions()
     * @param int|object $image
     * @return array
     */
    public function get_full_dimensions($image)
    {
        return $this->object->get_image_dimensions($image, 'full');
    }
    /**
     * Alias to get_image_html()
     * @param int|object $image
     * @return string
     */
    public function get_full_html($image)
    {
        return $this->object->get_image_html($image, 'full');
    }
    /**
     * Alias for get_original_url()
     *
     * @param int|stdClass|C_Image $image
     * @return string
     */
    public function get_full_url($image, $check_existance = FALSE)
    {
        return $this->object->get_image_url($image, 'full', $check_existance);
    }
    /**
     * Gets the dimensions for a particular-sized image
     *
     * @param int|object $image
     * @param string $size
     * @return array
     */
    public function get_image_dimensions($image, $size = 'full')
    {
        $retval = NULL;
        // If an image id was provided, get the entity
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        // Ensure we have a valid image
        if ($image) {
            // Adjust size parameter
            switch ($size) {
                case 'original':
                    $size = 'full';
                    break;
                case 'thumbnails':
                case 'thumbnail':
                case 'thumb':
                case 'thumbs':
                    $size = 'thumbnail';
                    break;
            }
            // Image dimensions are stored in the $image->meta_data
            // property for all implementations
            if (isset($image->meta_data) && isset($image->meta_data[$size])) {
                $retval = $image->meta_data[$size];
            } else {
                $abspath = $this->object->get_image_abspath($image, $size);
                if (@file_exists($abspath)) {
                    $dims = getimagesize($abspath);
                    if ($dims) {
                        $retval['width'] = $dims[0];
                        $retval['height'] = $dims[1];
                    }
                } elseif ($size == 'backup') {
                    $retval = $this->object->get_image_dimensions($image, 'full');
                }
            }
        }
        return $retval;
    }
    /**
     * Gets the HTML for an image
     * @param int|object $image
     * @param string $size
     * @return string
     */
    public function get_image_html($image, $size = 'full', $attributes = array())
    {
        $retval = '';
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        if ($image) {
            // Set alt text if not already specified
            if (!isset($attributes['alttext'])) {
                $attributes['alt'] = esc_attr($image->alttext);
            }
            // Set the title if not already set
            if (!isset($attributes['title'])) {
                $attributes['title'] = esc_attr($image->alttext);
            }
            // Set the dimensions if not set already
            if (!isset($attributes['width']) or !isset($attributes['height'])) {
                $dimensions = $this->object->get_image_dimensions($image, $size);
                if (!isset($attributes['width'])) {
                    $attributes['width'] = $dimensions['width'];
                }
                if (!isset($attributes['height'])) {
                    $attributes['height'] = $dimensions['height'];
                }
            }
            // Set the url if not already specified
            if (!isset($attributes['src'])) {
                $attributes['src'] = $this->object->get_image_url($image, $size);
            }
            // Format attributes
            $attribs = array();
            foreach ($attributes as $attrib => $value) {
                $attribs[] = "{$attrib}=\"{$value}\"";
            }
            $attribs = implode(' ', $attribs);
            // Return HTML string
            $retval = "<img {$attribs} />";
        }
        return $retval;
    }
    /**
     * An alias for get_full_abspath()
     * @param int|object $image
     */
    public function get_original_abspath($image, $check_existance = FALSE)
    {
        return $this->object->get_image_abspath($image, 'full', $check_existance);
    }
    /**
     * Alias to get_image_dimensions()
     * @param int|object $image
     * @return array
     */
    public function get_original_dimensions($image)
    {
        return $this->object->get_image_dimensions($image, 'full');
    }
    /**
     * Alias to get_image_html()
     * @param int|object $image
     * @return string
     */
    public function get_original_html($image)
    {
        return $this->object->get_image_html($image, 'full');
    }
    /**
     * Gets the url to the original-sized image
     * @param int|stdClass|C_Image $image
     * @return string
     */
    public function get_original_url($image, $check_existance = FALSE)
    {
        return $this->object->get_image_url($image, 'full', $check_existance);
    }
    /**
     * Gets the upload path, optionally for a particular gallery
     * @param int|C_Gallery|stdClass $gallery
     */
    public function get_upload_relpath($gallery = FALSE)
    {
        $fs = C_Fs::get_instance();
        $retval = str_replace($fs->get_document_root('gallery'), '', $this->object->get_upload_abspath($gallery));
        return DIRECTORY_SEPARATOR . ltrim($retval, '/\\');
    }
    /**
     * Moves images from to another gallery
     * @param array $images
     * @param int|object $gallery
     * @param boolean $db optionally only move the image files, not the db entries
     * @return boolean
     */
    public function move_images($images, $gallery, $db = TRUE)
    {
        return $this->object->copy_images($images, $gallery, $db, TRUE);
    }
    public function is_image_file($filename = NULL)
    {
        $retval = FALSE;
        if (!$filename && isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $filename = $_FILES['file']['tmp_name'];
        }
        $valid_types = array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png');
        // If we can, we'll verify the mime type
        if (function_exists('exif_imagetype')) {
            if (($image_type = @exif_imagetype($filename)) !== FALSE) {
                $retval = in_array(image_type_to_mime_type($image_type), $valid_types);
            }
        } else {
            $file_info = @getimagesize($filename);
            if (isset($file_info[2])) {
                $retval = in_array(image_type_to_mime_type($file_info[2]), $valid_types);
            }
        }
        return $retval;
    }
    public function is_zip()
    {
        $retval = FALSE;
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file_info = $_FILES['file'];
            if (isset($file_info['type'])) {
                $type = $file_info['type'];
                $type_parts = explode('/', $type);
                if (strtolower($type_parts[0]) == 'application') {
                    $spec = $type_parts[1];
                    $spec_parts = explode('-', $spec);
                    $spec_parts = array_map('strtolower', $spec_parts);
                    if (in_array($spec, array('zip', 'octet-stream')) || in_array('zip', $spec_parts)) {
                        $retval = true;
                    }
                }
            }
        }
        return $retval;
    }
    public function upload_zip($gallery_id)
    {
        $memory_limit = intval(ini_get('memory_limit'));
        if (!extension_loaded('suhosin') && $memory_limit < 256) {
            @ini_set('memory_limit', '256M');
        }
        $retval = FALSE;
        if ($this->object->is_zip()) {
            $fs = C_Fs::get_instance();
            // Uses the WordPress ZIP abstraction API
            include_once $fs->join_paths(ABSPATH, 'wp-admin', 'includes', 'file.php');
            WP_Filesystem();
            // Ensure that we truly have the gallery id
            $gallery_id = $this->_get_gallery_id($gallery_id);
            $zipfile = $_FILES['file']['tmp_name'];
            $dest_path = implode(DIRECTORY_SEPARATOR, array(rtrim(get_temp_dir(), '/\\'), 'unpacked-' . M_I18n::mb_basename($zipfile)));
            wp_mkdir_p($dest_path);
            if (unzip_file($zipfile, $dest_path) === TRUE) {
                $dest_dir = $dest_path . DIRECTORY_SEPARATOR;
                $files = glob($dest_dir . '*');
                $size = 0;
                foreach ($files as $file) {
                    if (is_file($dest_dir . $file)) {
                        $size += filesize($dest_dir . $file);
                    }
                }
                if ($size == 0) {
                    $this->object->delete_directory($dest_path);
                    $destination = wp_upload_dir();
                    $destination_path = $destination['basedir'];
                    $dest_path = implode(DIRECTORY_SEPARATOR, array(rtrim($destination_path, '/\\'), 'unpacked-' . M_I18n::mb_basename($zipfile)));
                    wp_mkdir_p($dest_path);
                    if (unzip_file($zipfile, $dest_path) === TRUE) {
                        $retval = $this->object->import_gallery_from_fs($dest_path, $gallery_id);
                    }
                } else {
                    $retval = $this->object->import_gallery_from_fs($dest_path, $gallery_id);
                }
            }
            $this->object->delete_directory($dest_path);
        }
        if (!extension_loaded('suhosin')) {
            @ini_set('memory_limit', $memory_limit . 'M');
        }
        return $retval;
    }
    public function is_current_user_over_quota()
    {
        $retval = FALSE;
        $settings = C_NextGen_Settings::get_instance();
        if (is_multisite() && $settings->get('wpmuQuotaCheck')) {
            require_once ABSPATH . 'wp-admin/includes/ms.php';
            $retval = upload_is_user_over_quota(FALSE);
        }
        return $retval;
    }
    /**
     * Uploads base64 file to a gallery
     * @param int|stdClass|C_Gallery $gallery
     * @param $data base64-encoded string of data representing the image
     * @param type $filename specifies the name of the file
     * @return C_Image
     */
    public function upload_base64_image($gallery, $data, $filename = FALSE, $image_id = FALSE, $override = FALSE)
    {
        $settings = C_NextGen_Settings::get_instance();
        $memory_limit = intval(ini_get('memory_limit'));
        if (!extension_loaded('suhosin') && $memory_limit < 256) {
            @ini_set('memory_limit', '256M');
        }
        $retval = NULL;
        if ($gallery_id = $this->object->_get_gallery_id($gallery)) {
            if ($this->object->is_current_user_over_quota()) {
                $message = sprintf(__('Sorry, you have used your space allocation. Please delete some files to upload more files.', 'nggallery'));
                throw new E_NoSpaceAvailableException($message);
            }
            // Get path information. The use of get_upload_abspath() might
            // not be the best for some drivers. For example, if using the
            // WordPress Media Library for uploading, then the wp_upload_bits()
            // function should perhaps be used
            $upload_dir = $this->object->get_upload_abspath($gallery);
            // Perhaps a filename was given instead of base64 data?
            if (preg_match('#/\\\\#', $data[0]) && @file_exists($data)) {
                if (!$filename) {
                    $filename = M_I18n::mb_basename($data);
                }
                $data = file_get_contents($data);
            }
            // Determine filenames
            $original_filename = $filename;
            $filename = $filename ? sanitize_file_name($original_filename) : uniqid('nextgen-gallery');
            if (preg_match('/\\-(png|jpg|gif|jpeg)$/i', $filename, $match)) {
                $filename = str_replace($match[0], '.' . $match[1], $filename);
            }
            $abs_filename = implode(DIRECTORY_SEPARATOR, array($upload_dir, $filename));
            // Ensure that the filename is valid
            if (!preg_match('/(png|jpeg|jpg|gif)$/i', $abs_filename)) {
                throw new E_UploadException(__('Invalid image file. Acceptable formats: JPG, GIF, and PNG.', 'nggallery'));
            }
            // Prevent duplicate filenames: check if the filename exists and
            // begin appending '-i' until we find an open slot
            if (!ini_get('safe_mode') && @file_exists($abs_filename) && !$override) {
                $file_exists = TRUE;
                $i = 0;
                do {
                    $i++;
                    $parts = explode('.', $filename);
                    $extension = array_pop($parts);
                    $new_filename = implode('.', $parts) . '-' . $i . '.' . $extension;
                    $new_abs_filename = implode(DIRECTORY_SEPARATOR, array($upload_dir, $new_filename));
                    if (!@file_exists($new_abs_filename)) {
                        $file_exists = FALSE;
                        $filename = $new_filename;
                        $abs_filename = $new_abs_filename;
                    }
                } while ($file_exists == TRUE);
            }
            // Create or retrieve the image object
            $image = NULL;
            if ($image_id) {
                $image = $this->object->_image_mapper->find($image_id, TRUE);
                if ($image) {
                    unset($image->meta_data['saved']);
                }
            }
            if (!$image) {
                $image = $this->object->_image_mapper->create();
            }
            $retval = $image;
            // Create or update the database record
            $image->alttext = str_replace('.' . M_I18n::mb_pathinfo($original_filename, PATHINFO_EXTENSION), '', M_I18n::mb_basename($original_filename));
            $image->galleryid = $this->object->_get_gallery_id($gallery);
            $image->filename = $filename;
            $image->image_slug = nggdb::get_unique_slug(sanitize_title_with_dashes($image->alttext), 'image');
            $image_key = $this->object->_image_mapper->get_primary_key_column();
            // If we can't write to the directory, then there's no point in continuing
            if (!@file_exists($upload_dir)) {
                @wp_mkdir_p($upload_dir);
            }
            if (!is_writable($upload_dir)) {
                throw new E_InsufficientWriteAccessException(FALSE, $upload_dir, FALSE);
            }
            // Save the image
            if ($image_id = $this->object->_image_mapper->save($image)) {
                try {
                    // Try writing the image
                    $fp = fopen($abs_filename, 'w');
                    fwrite($fp, $data);
                    fclose($fp);
                    if ($settings->imgBackup) {
                        $this->object->backup_image($image);
                    }
                    if ($settings->imgAutoResize) {
                        $this->object->generate_image_clone($abs_filename, $abs_filename, $this->object->get_image_size_params($image_id, 'full'));
                    }
                    // Ensure that fullsize dimensions are added to metadata array
                    $dimensions = getimagesize($abs_filename);
                    $full_meta = array('width' => $dimensions[0], 'height' => $dimensions[1]);
                    if (!isset($image->meta_data) or is_string($image->meta_data) && strlen($image->meta_data) == 0) {
                        $image->meta_data = array();
                    }
                    $image->meta_data = array_merge($image->meta_data, $full_meta);
                    $image->meta_data['full'] = $full_meta;
                    // Generate a thumbnail for the image
                    $this->object->generate_thumbnail($image);
                    // Set gallery preview image if missing
                    C_Gallery_Mapper::get_instance()->set_preview_image($gallery, $image_id, TRUE);
                    // Notify other plugins that an image has been added
                    do_action('ngg_added_new_image', $image);
                    // delete dirsize after adding new images
                    delete_transient('dirsize_cache');
                    // Seems redundant to above hook. Maintaining for legacy purposes
                    do_action('ngg_after_new_images_added', $gallery_id, array($image->{$image_key}));
                } catch (E_No_Image_Library_Exception $ex) {
                    throw $ex;
                } catch (E_Clean_Exit $ex) {
                } catch (Exception $ex) {
                    throw new E_InsufficientWriteAccessException(FALSE, $abs_filename, FALSE, $ex);
                }
            } else {
                throw new E_InvalidEntityException();
            }
        } else {
            throw new E_EntityNotFoundException();
        }
        if (!extension_loaded('suhosin')) {
            @ini_set('memory_limit', $memory_limit . 'M');
        }
        return $retval;
    }
    public function import_gallery_from_fs($abspath, $gallery_id = FALSE, $create_new_gallerypath = TRUE, $gallery_title = NULL, $filenames = array())
    {
        $retval = FALSE;
        if (@file_exists($abspath)) {
            $fs = C_Fs::get_instance();
            // Ensure that this folder has images
            // Ensure that this folder has images
            $i = 0;
            $files = array();
            foreach (scandir($abspath) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $file_abspath = $fs->join_paths($abspath, $file);
                // The first directory is considered valid
                if (is_dir($file_abspath) && $i === 0) {
                    $files[] = $file_abspath;
                } elseif ($this->is_image_file($file_abspath)) {
                    if ($filenames && array_search($file_abspath, $filenames) !== FALSE) {
                        $files[] = $file_abspath;
                    } else {
                        if (!$filenames) {
                            $files[] = $file_abspath;
                        }
                    }
                }
            }
            if (!empty($files)) {
                // Get needed utilities
                $gallery_mapper = C_Gallery_Mapper::get_instance();
                // Sometimes users try importing a directory, which actually has all images under another directory
                if (is_dir($files[0])) {
                    return $this->import_gallery_from_fs($files[0], $gallery_id, $create_new_gallerypath, $gallery_title, $filenames);
                }
                // If no gallery has been specified, then use the directory name as the gallery name
                if (!$gallery_id) {
                    // Create the gallery
                    $gallery = $gallery_mapper->create(array('title' => $gallery_title ? $gallery_title : M_I18n::mb_basename($abspath)));
                    if (!$create_new_gallerypath) {
                        $gallery->path = str_ireplace(ABSPATH, '', $abspath);
                    }
                    // Save the gallery
                    if ($gallery->save()) {
                        $gallery_id = $gallery->id();
                    }
                }
                // Ensure that we have a gallery id
                if ($gallery_id) {
                    $retval = array('gallery_id' => $gallery_id, 'image_ids' => array());
                    foreach ($files as $file_abspath) {
                        if (!preg_match('/\\.(jpg|jpeg|gif|png)$/i', $file_abspath)) {
                            continue;
                        }
                        $image = null;
                        if ($create_new_gallerypath) {
                            $image = $this->object->upload_base64_image($gallery_id, file_get_contents($file_abspath), str_replace(' ', '_', M_I18n::mb_basename($file_abspath)));
                        } else {
                            // Create the database record ... TODO cleanup, some duplication here from upload_base64_image
                            $factory = C_Component_Factory::get_instance();
                            $image = $factory->create('image');
                            $image->alttext = sanitize_title_with_dashes(str_replace('.' . M_I18n::mb_pathinfo($file_abspath, PATHINFO_EXTENSION), '', M_I18n::mb_basename($file_abspath)));
                            $image->galleryid = $this->object->_get_gallery_id($gallery_id);
                            $image->filename = M_I18n::mb_basename($file_abspath);
                            $image->image_slug = nggdb::get_unique_slug(sanitize_title_with_dashes($image->alttext), 'image');
                            $image_key = $this->object->_image_mapper->get_primary_key_column();
                            $abs_filename = $file_abspath;
                            if ($image_id = $this->object->_image_mapper->save($image)) {
                                try {
                                    if (C_NextGen_settings::get_instance()->imgBackup) {
                                        $this->object->backup_image($image);
                                    }
                                    #															if ($settings->imgAutoResize)
                                    #															    $this->object->generate_image_clone(
                                    #															        $abs_filename,
                                    #															        $abs_filename,
                                    #															        $this->object->get_image_size_params($image_id, 'full')
                                    #															    );
                                    // Ensure that fullsize dimensions are added to metadata array
                                    $dimensions = getimagesize($abs_filename);
                                    $full_meta = array('width' => $dimensions[0], 'height' => $dimensions[1]);
                                    if (!isset($image->meta_data) or is_string($image->meta_data) && strlen($image->meta_data) == 0) {
                                        $image->meta_data = array();
                                    }
                                    $image->meta_data = array_merge($image->meta_data, $full_meta);
                                    $image->meta_data['full'] = $full_meta;
                                    // Generate a thumbnail for the image
                                    $this->object->generate_thumbnail($image);
                                    // Set gallery preview image if missing
                                    C_Gallery_Mapper::get_instance()->set_preview_image($gallery, $image_id, TRUE);
                                    // Notify other plugins that an image has been added
                                    do_action('ngg_added_new_image', $image);
                                    // delete dirsize after adding new images
                                    delete_transient('dirsize_cache');
                                    // Seems redundant to above hook. Maintaining for legacy purposes
                                    do_action('ngg_after_new_images_added', $gallery_id, array($image->{$image_key}));
                                } catch (Exception $ex) {
                                    throw new E_InsufficientWriteAccessException(FALSE, $abs_filename, FALSE, $ex);
                                }
                            } else {
                                throw new E_InvalidEntityException();
                            }
                        }
                        $retval['image_ids'][] = $image->{$image->id_field};
                    }
                    // Add the gallery name to the result
                    $gallery = $gallery_mapper->find($gallery_id);
                    $retval['gallery_name'] = $gallery->title;
                    unset($gallery);
                }
            }
        }
        return $retval;
    }
    public function get_image_format_list()
    {
        $format_list = array(IMAGETYPE_GIF => 'gif', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png');
        return $format_list;
    }
    /**
     * Returns an array of properties of a resulting clone image if and when generated
     * @param string $image_path
     * @param string $clone_path
     * @param array $params
     * @return array
     */
    public function calculate_image_clone_result($image_path, $clone_path, $params)
    {
        $width = isset($params['width']) ? $params['width'] : NULL;
        $height = isset($params['height']) ? $params['height'] : NULL;
        $quality = isset($params['quality']) ? $params['quality'] : NULL;
        $type = isset($params['type']) ? $params['type'] : NULL;
        $crop = isset($params['crop']) ? $params['crop'] : NULL;
        $watermark = isset($params['watermark']) ? $params['watermark'] : NULL;
        $rotation = isset($params['rotation']) ? $params['rotation'] : NULL;
        $reflection = isset($params['reflection']) ? $params['reflection'] : NULL;
        $crop_frame = isset($params['crop_frame']) ? $params['crop_frame'] : NULL;
        $result = NULL;
        // Ensure we have a valid image
        if ($image_path && @file_exists($image_path)) {
            // Ensure target directory exists, but only create 1 subdirectory
            $image_dir = dirname($image_path);
            $clone_dir = dirname($clone_path);
            $image_extension = M_I18n::mb_pathinfo($image_path, PATHINFO_EXTENSION);
            $image_extension_str = null;
            $clone_extension = M_I18n::mb_pathinfo($clone_path, PATHINFO_EXTENSION);
            $clone_extension_str = null;
            if ($image_extension != null) {
                $image_extension_str = '.' . $image_extension;
            }
            if ($clone_extension != null) {
                $clone_extension_str = '.' . $clone_extension;
            }
            $image_basename = M_I18n::mb_basename($image_path, $image_extension_str);
            $clone_basename = M_I18n::mb_basename($clone_path, $clone_extension_str);
            // We use a default suffix as passing in null as the suffix will make WordPress use a default
            $clone_suffix = null;
            $format_list = $this->object->get_image_format_list();
            $clone_format = null;
            // format is determined below and based on $type otherwise left to null
            // suffix is only used to reconstruct paths for image_resize function
            if (strpos($clone_basename, $image_basename) === 0) {
                $clone_suffix = substr($clone_basename, strlen($image_basename));
            }
            if ($clone_suffix != null && $clone_suffix[0] == '-') {
                // WordPress adds '-' on its own
                $clone_suffix = substr($clone_suffix, 1);
            }
            // Get original image dimensions
            $dimensions = getimagesize($image_path);
            if ($width == null && $height == null) {
                if ($dimensions != null) {
                    if ($width == null) {
                        $width = $dimensions[0];
                    }
                    if ($height == null) {
                        $height = $dimensions[1];
                    }
                } else {
                    // XXX Don't think there's any other option here but to fail miserably...use some hard-coded defaults maybe?
                    return null;
                }
            }
            if ($dimensions != null) {
                $dimensions_ratio = $dimensions[0] / $dimensions[1];
                if ($width == null) {
                    $width = (int) round($height * $dimensions_ratio);
                    if ($width == $dimensions[0] - 1) {
                        $width = $dimensions[0];
                    }
                } else {
                    if ($height == null) {
                        $height = (int) round($width / $dimensions_ratio);
                        if ($height == $dimensions[1] - 1) {
                            $height = $dimensions[1];
                        }
                    }
                }
                if ($width > $dimensions[0]) {
                    $width = $dimensions[0];
                }
                if ($height > $dimensions[1]) {
                    $height = $dimensions[1];
                }
                $image_format = $dimensions[2];
                if ($type != null) {
                    if (is_string($type)) {
                        $type = strtolower($type);
                        // Indexes in the $format_list array correspond to IMAGETYPE_XXX values appropriately
                        if (($index = array_search($type, $format_list)) !== false) {
                            $type = $index;
                            if ($type != $image_format) {
                                // Note: this only changes the FORMAT of the image but not the extension
                                $clone_format = $type;
                            }
                        }
                    }
                }
            }
            if ($width == null || $height == null) {
                // Something went wrong...
                return null;
            }
            $result['clone_path'] = $clone_path;
            $result['clone_directory'] = $clone_dir;
            $result['clone_suffix'] = $clone_suffix;
            $result['clone_format'] = $clone_format;
            $result['base_width'] = $dimensions[0];
            $result['base_height'] = $dimensions[1];
            // image_resize() has limitations:
            // - no easy crop frame support
            // - fails if the dimensions are unchanged
            // - doesn't support filename prefix, only suffix so names like thumbs_original_name.jpg for $clone_path are not supported
            //   also suffix cannot be null as that will make WordPress use a default suffix...we could use an object that returns empty string from __toString() but for now just fallback to ngg generator
            if (FALSE) {
                // disabling the WordPress method for Iteration #6
                //			if (($crop_frame == null || !$crop) && ($dimensions[0] != $width && $dimensions[1] != $height) && $clone_suffix != null)
                $result['method'] = 'wordpress';
                $new_dims = image_resize_dimensions($dimensions[0], $dimensions[1], $width, $height, $crop);
                if ($new_dims) {
                    list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $new_dims;
                    $width = $dst_w;
                    $height = $dst_h;
                } else {
                    $result['error'] = new WP_Error('error_getting_dimensions', __('Could not calculate resized image dimensions'));
                }
            } else {
                $result['method'] = 'nextgen';
                $original_width = $dimensions[0];
                $original_height = $dimensions[1];
                $aspect_ratio = $width / $height;
                $orig_ratio_x = $original_width / $width;
                $orig_ratio_y = $original_height / $height;
                if ($crop) {
                    $algo = 'shrink';
                    // either 'adapt' or 'shrink'
                    if ($crop_frame != null) {
                        $crop_x = (int) round($crop_frame['x']);
                        $crop_y = (int) round($crop_frame['y']);
                        $crop_width = (int) round($crop_frame['width']);
                        $crop_height = (int) round($crop_frame['height']);
                        $crop_final_width = (int) round($crop_frame['final_width']);
                        $crop_final_height = (int) round($crop_frame['final_height']);
                        $crop_width_orig = $crop_width;
                        $crop_height_orig = $crop_height;
                        $crop_factor_x = $crop_width / $crop_final_width;
                        $crop_factor_y = $crop_height / $crop_final_height;
                        $crop_ratio_x = $crop_width / $width;
                        $crop_ratio_y = $crop_height / $height;
                        if ($algo == 'adapt') {
                        } else {
                            if ($algo == 'shrink') {
                                if ($crop_ratio_x < $crop_ratio_y) {
                                    $crop_width = max($crop_width, $width);
                                    $crop_height = (int) round($crop_width / $aspect_ratio);
                                } else {
                                    $crop_height = max($crop_height, $height);
                                    $crop_width = (int) round($crop_height * $aspect_ratio);
                                }
                                if ($crop_width == $crop_width_orig - 1) {
                                    $crop_width = $crop_width_orig;
                                }
                                if ($crop_height == $crop_height_orig - 1) {
                                    $crop_height = $crop_height_orig;
                                }
                            }
                        }
                        $crop_diff_x = (int) round(($crop_width_orig - $crop_width) / 2);
                        $crop_diff_y = (int) round(($crop_height_orig - $crop_height) / 2);
                        $crop_x += $crop_diff_x;
                        $crop_y += $crop_diff_y;
                        $crop_max_x = $crop_x + $crop_width;
                        $crop_max_y = $crop_y + $crop_height;
                        // Check if we're overflowing borders
                        //
                        if ($crop_x < 0) {
                            $crop_x = 0;
                        } else {
                            if ($crop_max_x > $original_width) {
                                $crop_x -= $crop_max_x - $original_width;
                            }
                        }
                        if ($crop_y < 0) {
                            $crop_y = 0;
                        } else {
                            if ($crop_max_y > $original_height) {
                                $crop_y -= $crop_max_y - $original_height;
                            }
                        }
                    } else {
                        if ($orig_ratio_x < $orig_ratio_y) {
                            $crop_width = $original_width;
                            $crop_height = (int) round($height * $orig_ratio_x);
                        } else {
                            $crop_height = $original_height;
                            $crop_width = (int) round($width * $orig_ratio_y);
                        }
                        if ($crop_width == $width - 1) {
                            $crop_width = $width;
                        }
                        if ($crop_height == $height - 1) {
                            $crop_height = $height;
                        }
                        $crop_x = (int) round(($original_width - $crop_width) / 2);
                        $crop_y = (int) round(($original_height - $crop_height) / 2);
                    }
                    $result['crop_area'] = array('x' => $crop_x, 'y' => $crop_y, 'width' => $crop_width, 'height' => $crop_height);
                } else {
                    // Just constraint dimensions to ensure there's no stretching or deformations
                    list($width, $height) = wp_constrain_dimensions($original_width, $original_height, $width, $height);
                }
            }
            $result['width'] = $width;
            $result['height'] = $height;
            $result['quality'] = $quality;
            $real_width = $width;
            $real_height = $height;
            if ($rotation && in_array(abs($rotation), array(90, 270))) {
                $real_width = $height;
                $real_height = $width;
            }
            if ($reflection) {
                // default for nextgen was 40%, this is used in generate_image_clone as well
                $reflection_amount = 40;
                // Note, round() would probably be best here but using the same code that C_NggLegacy_Thumbnail uses for compatibility
                $reflection_height = intval($real_height * ($reflection_amount / 100));
                $real_height = $real_height + $reflection_height;
            }
            $result['real_width'] = $real_width;
            $result['real_height'] = $real_height;
        }
        return $result;
    }
    /**
     * Returns an array of dimensional properties (width, height, real_width, real_height) of a resulting clone image if and when generated
     * @param string $image_path
     * @param string $clone_path
     * @param array $params
     * @return array
     */
    public function calculate_image_clone_dimensions($image_path, $clone_path, $params)
    {
        $retval = null;
        $result = $this->object->calculate_image_clone_result($image_path, $clone_path, $params);
        if ($result != null) {
            $retval = array('width' => $result['width'], 'height' => $result['height'], 'real_width' => $result['real_width'], 'real_height' => $result['real_height']);
        }
        return $retval;
    }
    /**
     * Generates a "clone" for an existing image, the clone can be altered using the $params array
     * @param string $image_path
     * @param string $clone_path
     * @param array $params
     * @return object
     */
    public function generate_image_clone($image_path, $clone_path, $params)
    {
        $crop = isset($params['crop']) ? $params['crop'] : NULL;
        $watermark = isset($params['watermark']) ? $params['watermark'] : NULL;
        $reflection = isset($params['reflection']) ? $params['reflection'] : NULL;
        $rotation = isset($params['rotation']) ? $params['rotation'] : NULL;
        $flip = isset($params['flip']) ? $params['flip'] : NULL;
        $destpath = NULL;
        $thumbnail = NULL;
        $result = $this->object->calculate_image_clone_result($image_path, $clone_path, $params);
        // XXX this should maybe be removed and extra settings go into $params?
        $settings = apply_filters('ngg_settings_during_image_generation', C_NextGen_Settings::get_instance()->to_array());
        // Ensure we have a valid image
        if ($image_path && @file_exists($image_path) && $result != null && !isset($result['error'])) {
            $image_dir = dirname($image_path);
            $clone_path = $result['clone_path'];
            $clone_dir = $result['clone_directory'];
            $clone_format = $result['clone_format'];
            $format_list = $this->object->get_image_format_list();
            // Ensure target directory exists, but only create 1 subdirectory
            if (!@file_exists($clone_dir)) {
                if (strtolower(realpath($image_dir)) != strtolower(realpath($clone_dir))) {
                    if (strtolower(realpath($image_dir)) == strtolower(realpath(dirname($clone_dir)))) {
                        wp_mkdir_p($clone_dir);
                    }
                }
            }
            $method = $result['method'];
            $width = $result['width'];
            $height = $result['height'];
            $quality = $result['quality'];
            if ($quality == null) {
                $quality = 100;
            }
            if ($method == 'wordpress') {
                $original = wp_get_image_editor($image_path);
                $destpath = $clone_path;
                if (!is_wp_error($original)) {
                    $original->resize($width, $height, $crop);
                    $original->set_quality($quality);
                    $original->save($clone_path);
                }
            } else {
                if ($method == 'nextgen') {
                    $destpath = $clone_path;
                    $thumbnail = new C_NggLegacy_Thumbnail($image_path, true);
                    if (!$thumbnail->error) {
                        if ($crop) {
                            $crop_area = $result['crop_area'];
                            $crop_x = $crop_area['x'];
                            $crop_y = $crop_area['y'];
                            $crop_width = $crop_area['width'];
                            $crop_height = $crop_area['height'];
                            $thumbnail->crop($crop_x, $crop_y, $crop_width, $crop_height);
                        }
                        $thumbnail->resize($width, $height);
                    } else {
                        $thumbnail = NULL;
                    }
                }
            }
            // We successfully generated the thumbnail
            if (is_string($destpath) && (@file_exists($destpath) || $thumbnail != null)) {
                if ($clone_format != null) {
                    if (isset($format_list[$clone_format])) {
                        $clone_format_extension = $format_list[$clone_format];
                        $clone_format_extension_str = null;
                        if ($clone_format_extension != null) {
                            $clone_format_extension_str = '.' . $clone_format_extension;
                        }
                        $destpath_info = M_I18n::mb_pathinfo($destpath);
                        $destpath_extension = $destpath_info['extension'];
                        if (strtolower($destpath_extension) != strtolower($clone_format_extension)) {
                            $destpath_dir = $destpath_info['dirname'];
                            $destpath_basename = $destpath_info['filename'];
                            $destpath_new = $destpath_dir . DIRECTORY_SEPARATOR . $destpath_basename . $clone_format_extension_str;
                            if (@file_exists($destpath) && rename($destpath, $destpath_new) || $thumbnail != null) {
                                $destpath = $destpath_new;
                            }
                        }
                    }
                }
                if (is_null($thumbnail)) {
                    $thumbnail = new C_NggLegacy_Thumbnail($destpath, true);
                } else {
                    $thumbnail->fileName = $destpath;
                }
                // This is quite odd, when watermark equals int(0) it seems all statements below ($watermark == 'image') and ($watermark == 'text') both evaluate as true
                // so we set it at null if it evaluates to any null-like value
                if ($watermark == null) {
                    $watermark = null;
                }
                if ($watermark == 1 || $watermark === true) {
                    if (in_array(strval($settings['wmType']), array('image', 'text'))) {
                        $watermark = $settings['wmType'];
                    } else {
                        $watermark = 'text';
                    }
                }
                $watermark = strval($watermark);
                if ($watermark == 'image') {
                    $thumbnail->watermarkImgPath = $settings['wmPath'];
                    $thumbnail->watermarkImage($settings['wmPos'], $settings['wmXpos'], $settings['wmYpos']);
                } else {
                    if ($watermark == 'text') {
                        $thumbnail->watermarkText = $settings['wmText'];
                        $thumbnail->watermarkCreateText($settings['wmColor'], $settings['wmFont'], $settings['wmSize'], $settings['wmOpaque']);
                        $thumbnail->watermarkImage($settings['wmPos'], $settings['wmXpos'], $settings['wmYpos']);
                    }
                }
                if ($rotation && in_array(abs($rotation), array(90, 180, 270))) {
                    $thumbnail->rotateImageAngle($rotation);
                }
                $flip = strtolower($flip);
                if ($flip && in_array($flip, array('h', 'v', 'hv'))) {
                    $flip_h = in_array($flip, array('h', 'hv'));
                    $flip_v = in_array($flip, array('v', 'hv'));
                    $thumbnail->flipImage($flip_h, $flip_v);
                }
                if ($reflection) {
                    $thumbnail->createReflection(40, 40, 50, FALSE, '#a4a4a4');
                }
                if ($clone_format != null && isset($format_list[$clone_format])) {
                    // Force format
                    $thumbnail->format = strtoupper($format_list[$clone_format]);
                }
                $thumbnail = apply_filters('ngg_before_save_thumbnail', $thumbnail);
                $thumbnail->save($destpath, $quality);
                // IF the original contained IPTC metadata we should attempt to copy it
                if (isset($detailed_size['APP13']) && function_exists('iptcembed')) {
                    $metadata = @iptcembed($detailed_size['APP13'], $destpath);
                    $fp = @fopen($destpath, 'wb');
                    @fwrite($fp, $metadata);
                    @fclose($fp);
                }
            }
        }
        return $thumbnail;
    }
}
class C_GalleryStorage_Driver_Base extends C_GalleryStorage_Base
{
    public static $_instances = array();
    public function define($context)
    {
        parent::define($context);
        $this->add_mixin('Mixin_GalleryStorage_Driver_Base');
        $this->implement('I_GalleryStorage_Driver');
    }
    public function initialize()
    {
        parent::initialize();
        $this->_gallery_mapper = C_Gallery_Mapper::get_instance();
        $this->_image_mapper = C_Image_Mapper::get_instance();
    }
    public static function get_instance($context = False)
    {
        if (!isset(self::$_instances[$context])) {
            self::$_instances[$context] = new C_GalleryStorage_Driver_Base($context);
        }
        return self::$_instances[$context];
    }
    /**
     * Gets the class name of the driver used
     * @return string
     */
    public function get_driver_class_name()
    {
        return get_called_class();
    }
}
class Mixin_NextGen_Gallery_Image_Validation extends Mixin
{
    public function validation()
    {
        // Additional checks...
        if (isset($this->object->description)) {
            $this->object->description = M_NextGen_Data::strip_html($this->object->description, TRUE);
        }
        if (isset($this->object->alttext)) {
            $this->object->alttext = M_NextGen_Data::strip_html($this->object->alttext, TRUE);
        }
        $this->validates_presence_of('galleryid', 'filename', 'alttext', 'exclude', 'sortorder', 'imagedate');
        $this->validates_numericality_of('galleryid');
        $this->validates_numericality_of($this->id());
        $this->validates_numericality_of('sortorder');
        return $this->object->is_valid();
    }
}
/**
 * Model for NextGen Gallery Images
 */
class C_Image extends C_DataMapper_Model
{
    public $_mapper_interface = 'I_Image_Mapper';
    public function define($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        parent::define($mapper, $properties, $context);
        $this->add_mixin('Mixin_NextGen_Gallery_Image_Validation');
        $this->implement('I_Image');
    }
    /**
     * Instantiates a new model
     * @param array|stdClass $properties
     * @param C_DataMapper $mapper
     * @param string $context
     */
    public function initialize($properties = array(), $mapper = FALSE, $context = FALSE)
    {
        // Get the mapper is not specified
        if (!$mapper) {
            $mapper = $this->get_registry()->get_utility($this->_mapper_interface);
        }
        // Initialize
        parent::initialize($mapper, $properties);
    }
    /**
     * Returns the model representing the gallery associated with this image
     * @return C_Gallery|stdClass
     */
    public function get_gallery($model = FALSE)
    {
        $gallery_mapper = C_Gallery_Mapper::get_instance();
        return $gallery_mapper->find($this->galleryid, $model);
    }
}
class C_Image_Mapper extends C_CustomTable_DataMapper_Driver
{
    public static $_instance = NULL;
    /**
     * Defines the gallery image mapper
     * @param type $context
     */
    public function define($context = FALSE, $not_used = FALSE)
    {
        // Add 'attachment' context
        if (!is_array($context)) {
            $context = array($context);
        }
        array_push($context, 'attachment');
        // Define the mapper
        $this->_primary_key_column = 'pid';
        parent::define('ngg_pictures', $context);
        $this->add_mixin('Mixin_NextGen_Table_Extras');
        $this->add_mixin('Mixin_Gallery_Image_Mapper');
        $this->implement('I_Image_Mapper');
        $this->set_model_factory_method('image');
        // Define the columns
        $this->define_column('pid', 'BIGINT', 0);
        $this->define_column('image_slug', 'VARCHAR(255)');
        $this->define_column('post_id', 'BIGINT', 0);
        $this->define_column('galleryid', 'BIGINT', 0);
        $this->define_column('filename', 'VARCHAR(255)');
        $this->define_column('description', 'TEXT');
        $this->define_column('alttext', 'TEXT');
        $this->define_column('imagedate', 'DATETIME');
        $this->define_column('exclude', 'INT', 0);
        $this->define_column('sortorder', 'BIGINT', 0);
        $this->define_column('meta_data', 'TEXT');
        $this->define_column('extras_post_id', 'BIGINT', 0);
        $this->define_column('updated_at', 'BIGINT');
        // Mark the columns which should be unserialized
        $this->add_serialized_column('meta_data');
    }
    public function initialize($object_name = FALSE)
    {
        parent::initialize('ngg_pictures');
    }
    static function get_instance($context = False)
    {
        if (is_null(self::$_instance)) {
            $klass = get_class();
            self::$_instance = new $klass($context);
        }
        return self::$_instance;
    }
}
/**
 * Sets the alttext property as the post title
 */
class Mixin_Gallery_Image_Mapper extends Mixin
{
    public function destroy($image)
    {
        $retval = $this->call_parent('destroy', $image);
        // Delete tag associations with the image
        if (!is_numeric($image)) {
            $image = $image->{$image->id_field};
        }
        wp_delete_object_term_relationships($image, 'ngg_tag');
        C_Photocrati_Transient_Manager::flush('displayed_gallery_rendering');
        return $retval;
    }
    public function _save_entity($entity)
    {
        $entity->updated_at = time();
        // If successfully saved, then import metadata and
        $retval = $this->call_parent('_save_entity', $entity);
        if ($retval) {
            include_once NGGALLERY_ABSPATH . '/admin/functions.php';
            $image_id = $this->get_id($entity);
            if (!isset($entity->meta_data['saved'])) {
                nggAdmin::import_MetaData($image_id);
            }
            C_Photocrati_Transient_Manager::flush('displayed_gallery_rendering');
        }
        return $retval;
    }
    public function reimport_metadata($image_or_id)
    {
        // Get the image
        $image = NULL;
        if (is_int($image_or_id)) {
            $image = $this->object->find($image_or_id);
        } else {
            $image = $image_or_id;
        }
        // Reset all image details that would have normally been imported
        $image->alttext = '';
        $image->description = '';
        if (is_array($image->meta_data)) {
            unset($image->meta_data['saved']);
        }
        wp_delete_object_term_relationships($image->{$image->id_field}, 'ngg_tag');
        nggAdmin::import_MetaData($image);
        return $this->object->save($image);
    }
    /**
     * Retrieves the id from an image
     * @param $image
     * @return bool
     */
    public function get_id($image)
    {
        $retval = FALSE;
        // Have we been passed an entity and is the id_field set?
        if ($image instanceof stdClass) {
            if (isset($image->id_field)) {
                $retval = $image->{$image->id_field};
            }
        } else {
            $retval = $image->id();
        }
        // If we still don't have an id, then we'll lookup the primary key
        // and try fetching it manually
        if (!$retval) {
            $key = $this->object->get_primary_key_column();
            $retval = $image->{$key};
        }
        return $retval;
    }
    public function get_post_title($entity)
    {
        return $entity->alttext;
    }
    public function set_defaults($entity)
    {
        // If not set already, we'll add an exclude property. This is used
        // by NextGEN Gallery itself, as well as the Attach to Post module
        $this->object->_set_default_value($entity, 'exclude', 0);
        // Ensure that the object has a description attribute
        $this->object->_set_default_value($entity, 'description', '');
        // If not set already, set a default sortorder
        $this->object->_set_default_value($entity, 'sortorder', 0);
        // The imagedate must be set
        if (!isset($entity->imagedate) or is_null($entity->imagedate) or $entity->imagedate == '0000-00-00 00:00:00') {
            $entity->imagedate = date('Y-m-d H:i:s');
        }
        // If a filename is set, and no alttext is set, then set the alttext
        // to the basename of the filename (legacy behavior)
        if (isset($entity->filename)) {
            $path_parts = M_I18n::mb_pathinfo($entity->filename);
            $alttext = !isset($path_parts['filename']) ? substr($path_parts['basename'], 0, strpos($path_parts['basename'], '.')) : $path_parts['filename'];
            $this->object->_set_default_value($entity, 'alttext', $alttext);
        }
        // Set unique slug
        if (isset($entity->alttext) && empty($entity->image_slug)) {
            $entity->image_slug = nggdb::get_unique_slug(sanitize_title_with_dashes($entity->alttext), 'image');
        }
        // Ensure that the exclude parameter is an integer or boolean-evaluated
        // value
        if (is_string($entity->exclude)) {
            $entity->exclude = intval($entity->exclude);
        }
        // Trim alttext and description
        $entity->description = trim($entity->description);
        $entity->alttext = trim($entity->alttext);
    }
    /**
     * Finds all images for a gallery
     * @param $gallery
     * @param bool $model
     *
     * @return array
     */
    public function find_all_for_gallery($gallery, $model = FALSE)
    {
        $retval = array();
        $gallery_id = 0;
        if (is_object($gallery)) {
            if (isset($gallery->id_field)) {
                $gallery_id = $gallery->{$gallery->id_field};
            } else {
                $key = $this->object->get_primary_key_column();
                if (isset($gallery->{$key})) {
                    $gallery_id = $gallery->{$key};
                }
            }
        }
        if ($gallery_id) {
            $retval = $this->object->select()->where(array('galleryid = %s'), $gallery_id)->run_query(FALSE, $model);
        }
        return $retval;
    }
}
/**
 * This class provides a lazy-loading wrapper to the NextGen-Legacy "nggImage" class for use in legacy style templates
 */
class C_Image_Wrapper
{
    public $_cache;
    // cache of retrieved values
    public $_settings;
    // I_Settings_Manager cache
    public $_storage;
    // I_Gallery_Storage cache
    public $_galleries;
    // cache of I_Gallery_Mapper (plural)
    public $_orig_image;
    // original provided image
    public $_orig_image_id;
    // original image ID
    public $_cache_overrides;
    // allow for forcing variable values
    public $_legacy = FALSE;
    public $_displayed_gallery;
    // cached object
    /**
     * Constructor. Converts the image class into an array and fills from defaults any missing values
     *
     * @param object $gallery Individual result from displayed_gallery->get_entities()
     * @param object $displayed_gallery Displayed gallery -- MAY BE NULL
     * @param bool $legacy Whether the image source is from NextGen Legacy or NextGen
     * @return void
     */
    public function __construct($image, $displayed_gallery = NULL, $legacy = FALSE)
    {
        // for clarity
        if ($displayed_gallery && isset($displayed_gallery->display_settings['number_of_columns'])) {
            $columns = $displayed_gallery->display_settings['number_of_columns'];
        } else {
            $columns = 0;
        }
        // Public variables
        $defaults = array('errmsg' => '', 'error' => FALSE, 'imageURL' => '', 'thumbURL' => '', 'imagePath' => '', 'thumbPath' => '', 'href' => '', 'thumbPrefix' => 'thumbs_', 'thumbFolder' => '/thumbs/', 'galleryid' => 0, 'pid' => 0, 'filename' => '', 'description' => '', 'alttext' => '', 'imagedate' => '', 'exclude' => '', 'thumbcode' => '', 'name' => '', 'path' => '', 'title' => '', 'pageid' => 0, 'previewpic' => 0, 'style' => $columns > 0 ? 'style="width:' . floor(100 / $columns) . '%;"' : '', 'hidden' => FALSE, 'permalink' => '', 'tags' => '');
        // convert the image to an array and apply the defaults
        $this->_orig_image = $image;
        $image = (array) $image;
        foreach ($defaults as $key => $val) {
            if (!isset($image[$key])) {
                $image[$key] = $val;
            }
        }
        // cache the results
        ksort($image);
        $id_field = !empty($image['id_field']) ? $image['id_field'] : 'pid';
        $this->_cache = (array) apply_filters('ngg_image_object', (object) $image, $image[$id_field]);
        $this->_orig_image_id = $image[$id_field];
        $this->_legacy = $legacy;
        $this->_displayed_gallery = $displayed_gallery;
    }
    public function __set($name, $value)
    {
        $this->_cache[$name] = $value;
    }
    public function __isset($name)
    {
        return isset($this->_cache[$name]);
    }
    public function __unset($name)
    {
        unset($this->_cache[$name]);
    }
    /**
     * Lazy-loader for image variables.
     *
     * @param string $name Parameter name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_cache_overrides[$name])) {
            return $this->_cache_overrides[$name];
        }
        // at the bottom we default to returning $this->_cache[$name].
        switch ($name) {
            case 'alttext':
                $this->_cache['alttext'] = empty($this->_cache['alttext']) ? ' ' : html_entity_decode(stripslashes($this->_cache['alttext']));
                return $this->_cache['alttext'];
            case 'author':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['author'] = $gallery->name;
                return $this->_cache['author'];
            case 'caption':
                $caption = html_entity_decode(stripslashes($this->__get('description')));
                if (empty($caption)) {
                    $caption = '&nbsp;';
                }
                $this->_cache['caption'] = $caption;
                return $this->_cache['caption'];
            case 'description':
                $this->_cache['description'] = empty($this->_cache['description']) ? ' ' : html_entity_decode(stripslashes($this->_cache['description']));
                return $this->_cache['description'];
            case 'galdesc':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['galdesc'] = $gallery->name;
                return $this->_cache['galdesc'];
            case 'gid':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['gid'] = $gallery->{$gallery->id_field};
                return $this->_cache['gid'];
            case 'href':
                return $this->__get('imageHTML');
            case 'id':
                return $this->_orig_image_id;
            case 'imageHTML':
                $tmp = '<a href="' . $this->__get('imageURL') . '" title="' . htmlspecialchars(stripslashes($this->__get('description'))) . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->__get('alttext') . '" src="' . $this->__get('imageURL') . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['imageHTML'] = $tmp;
                return $this->_cache['imageHTML'];
            case 'imagePath':
                $storage = $this->get_storage();
                $this->_cache['imagePath'] = $storage->get_image_abspath($this->_orig_image, 'full');
                return $this->_cache['imagePath'];
            case 'imageURL':
                $storage = $this->get_storage();
                $this->_cache['imageURL'] = $storage->get_image_url($this->_orig_image, 'full');
                return $this->_cache['imageURL'];
            case 'linktitle':
                $this->_cache['linktitle'] = htmlspecialchars(stripslashes($this->__get('description')));
                return $this->_cache['linktitle'];
            case 'name':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['name'] = $gallery->name;
                return $this->_cache['name'];
            case 'pageid':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['pageid'] = $gallery->name;
                return $this->_cache['pageid'];
            case 'path':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['path'] = $gallery->name;
                return $this->_cache['path'];
            case 'permalink':
                $this->_cache['permalink'] = $this->__get('imageURL');
                return $this->_cache['permalink'];
            case 'pid':
                return $this->_orig_image_id;
            case 'id_field':
                $this->_cache['id_field'] = !empty($this->_orig_image->id_field) ? $this->_orig_image->id_field : 'pid';
                return $this->_cache['id_field'];
            case 'pidlink':
                $application = C_Router::get_instance()->get_routed_app();
                $controller = C_Display_Type_Controller::get_instance();
                $this->_cache['pidlink'] = $controller->set_param_for($application->get_routed_url(TRUE), 'pid', $this->__get('image_slug'));
                return $this->_cache['pidlink'];
            case 'previewpic':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['previewpic'] = $gallery->name;
                return $this->_cache['previewpic'];
            case 'size':
                $w = 0;
                $h = 0;
                if ($this->_displayed_gallery && isset($this->_displayed_gallery->display_settings)) {
                    $ds = $this->_displayed_gallery->display_settings;
                    if (isset($ds['override_thumbnail_settings']) && $ds['override_thumbnail_settings']) {
                        $w = $ds['thumbnail_width'];
                        $h = $ds['thumbnail_height'];
                    }
                }
                if (!$w || !$h) {
                    if (is_string($this->_orig_image->meta_data)) {
                        $this->_orig_image = C_Image_Mapper::get_instance()->unserialize($this->_orig_image->meta_data);
                    }
                    if (!isset($this->_orig_image->meta_data['thumbnail'])) {
                        $storage = $this->get_storage();
                        $storage->generate_thumbnail($this->_orig_image);
                    }
                    $w = $this->_orig_image->meta_data['thumbnail']['width'];
                    $h = $this->_orig_image->meta_data['thumbnail']['height'];
                }
                return "width='{$w}' height='{$h}'";
            case 'slug':
                $gallery = $this->get_legacy_gallery($this->__get('galleryid'));
                $this->_cache['slug'] = $gallery->name;
                return $this->_cache['slug'];
            case 'tags':
                $this->_cache['tags'] = wp_get_object_terms($this->__get('id'), 'ngg_tag', 'fields=all');
                return $this->_cache['tags'];
            case 'thumbHTML':
                $tmp = '<a href="' . $this->__get('imageURL') . '" title="' . htmlspecialchars(stripslashes($this->__get('description'))) . '" ' . $this->get_thumbcode($this->__get('name')) . '>' . '<img alt="' . $this->__get('alttext') . '" src="' . $this->thumbURL . '"/>' . '</a>';
                $this->_cache['href'] = $tmp;
                $this->_cache['thumbHTML'] = $tmp;
                return $this->_cache['thumbHTML'];
            case 'thumbPath':
                $storage = $this->get_storage();
                $this->_cache['thumbPath'] = $storage->get_image_abspath($this->_orig_image, 'thumbnail');
                return $this->_cache['thumbPath'];
            case 'thumbnailURL':
                $storage = $this->get_storage();
                $thumbnail_size_name = 'thumbnail';
                if ($this->_displayed_gallery && isset($this->_displayed_gallery->display_settings)) {
                    $ds = $this->_displayed_gallery->display_settings;
                    if (isset($ds['override_thumbnail_settings']) && $ds['override_thumbnail_settings']) {
                        $dynthumbs = C_Component_Registry::get_instance()->get_utility('I_Dynamic_Thumbnails_Manager');
                        $dyn_params = array('width' => $ds['thumbnail_width'], 'height' => $ds['thumbnail_height']);
                        if ($ds['thumbnail_quality']) {
                            $dyn_params['quality'] = $ds['thumbnail_quality'];
                        }
                        if ($ds['thumbnail_crop']) {
                            $dyn_params['crop'] = TRUE;
                        }
                        if ($ds['thumbnail_watermark']) {
                            $dyn_params['watermark'] = TRUE;
                        }
                        $thumbnail_size_name = $dynthumbs->get_size_name($dyn_params);
                    }
                }
                $this->_cache['thumbnailURL'] = $storage->get_image_url($this->_orig_image, $thumbnail_size_name);
                return $this->_cache['thumbnailURL'];
            case 'thumbcode':
                if ($this->_displayed_gallery && isset($this->_displayed_gallery->display_settings) && isset($this->_displayed_gallery->display_settings['use_imagebrowser_effect']) && $this->_displayed_gallery->display_settings['use_imagebrowser_effect'] && !empty($this->_orig_image->thumbcode)) {
                    $this->_cache['thumbcode'] = $this->_orig_image->thumbcode;
                } else {
                    $this->_cache['thumbcode'] = $this->get_thumbcode($this->__get('name'));
                }
                return $this->_cache['thumbcode'];
            case 'thumbURL':
                return $this->__get('thumbnailURL');
            case 'title':
                $this->_cache['title'] = stripslashes($this->__get('name'));
                return $this->_cache['title'];
            case 'url':
                $storage = $this->get_storage();
                $this->_cache['url'] = $storage->get_image_url($this->_orig_image, 'full');
                return $this->_cache['url'];
            default:
                return $this->_cache[$name];
        }
    }
    // called on initial nggLegacy image at construction. not sure what to do with it now.
    public function construct_ngg_Image($gallery)
    {
        do_action_ref_array('ngg_get_image', array(&$this));
        unset($this->tags);
    }
    /**
     * Retrieves and caches an I_Settings_Manager instance
     *
     * @return mixed
     */
    public function get_settings()
    {
        if (is_null($this->_settings)) {
            $this->_settings = C_NextGen_Settings::get_instance();
        }
        return $this->_settings;
    }
    /**
     * Retrieves and caches an I_Gallery_Storage instance
     *
     * @return mixed
     */
    public function get_storage()
    {
        if (is_null($this->_storage)) {
            $this->_storage = C_Gallery_Storage::get_instance();
        }
        return $this->_storage;
    }
    /**
     * Retrieves I_Gallery_Mapper instance.
     *
     * @param int $gallery_id Gallery ID
     * @return mixed
     */
    public function get_gallery($gallery_id)
    {
        if (isset($this->container) && method_exists($this->container, 'get_gallery')) {
            return $this->container->get_gallery($gallery_id);
        }
        return C_Gallery_Mapper::get_instance()->find($gallery_id);
    }
    /**
     * Retrieves I_Gallery_Mapper instance.
     *
     * @param int $gallery_id Gallery ID
     * @return mixed
     */
    public function get_legacy_gallery($gallery_id)
    {
        return C_Gallery_Mapper::get_instance()->find($gallery_id);
    }
    /**
     * Get the thumbnail code (to add effects on thumbnail click)
     *
     * Applies the filter 'ngg_get_thumbcode'
     */
    public function get_thumbcode($gallery_name = '')
    {
        if (empty($this->_displayed_gallery)) {
            $effect_code = C_NextGen_Settings::get_instance()->thumbCode;
            $effect_code = str_replace('%GALLERY_ID%', $gallery_name, $effect_code);
            $effect_code = str_replace('%GALLERY_NAME%', $gallery_name, $effect_code);
            $retval = $effect_code;
        } else {
            $controller = C_Display_Type_Controller::get_instance();
            $retval = $controller->get_effect_code($this->_displayed_gallery);
            // This setting requires that we disable the effect code
            $ds = $this->_displayed_gallery->display_settings;
            if (isset($ds['use_imagebrowser_effect']) && $ds['use_imagebrowser_effect']) {
                $retval = '';
            }
        }
        $retval = apply_filters('ngg_get_thumbcode', $retval, $this);
        // ensure some additional data- fields are added; provides Pro-Lightbox compatibility
        $retval .= ' data-image-id="' . $this->__get('id') . '"';
        $retval .= ' data-src="' . $this->__get('imageURL') . '"';
        $retval .= ' data-thumbnail="' . $this->__get('thumbnailURL') . '"';
        $retval .= ' data-title="' . esc_attr($this->__get('alttext')) . '"';
        $retval .= ' data-description="' . esc_attr($this->__get('description')) . '"';
        $this->_cache['thumbcode'] = $retval;
        return $retval;
    }
    /**
     * For compatibility support
     *
     * @return mixed
     */
    public function get_href_link()
    {
        return $this->__get('imageHTML');
    }
    /**
     * For compatibility support
     *
     * @return mixed
     */
    public function get_href_thumb_link()
    {
        return $this->__get('thumbHTML');
    }
    /**
     * Function exists for legacy support but has been gutted to not do anything
     *
     * @param int $width
     * @param int $height
     * @param string $mode could be watermark | web20 | crop
     * @return the url for the image or false if failed
     */
    public function cached_singlepic_file($width = '', $height = '', $mode = '')
    {
        $dynthumbs = C_Dynamic_Thumbnails_Manager::get_instance();
        $storage = $this->get_storage();
        // determine what to do with 'mode'
        $display_reflection = FALSE;
        $display_watermark = FALSE;
        if (!is_array($mode)) {
            $mode = explode(',', $mode);
        }
        if (in_array('web20', $mode)) {
            $display_reflection = TRUE;
        }
        if (in_array('watermark', $mode)) {
            $display_watermark = TRUE;
        }
        // and go for it
        $params = array('width' => $width, 'height' => $height, 'watermark' => $display_watermark, 'reflection' => $display_reflection);
        return $storage->get_image_url((object) $this->_cache, $dynthumbs->get_size_name($params));
    }
    /**
     * Get the tags associated to this image
     */
    public function get_tags()
    {
        return $this->__get('tags');
    }
    /**
     * Get the permalink to the image
     *
     * TODO: Get a permalink to a page presenting the image
     */
    public function get_permalink()
    {
        return $this->__get('permalink');
    }
    /**
     * Returns the _cache array; used by nggImage
     * @return array
     */
    public function _get_image()
    {
        return $this->_cache;
    }
}
class C_Image_Wrapper_Collection implements ArrayAccess
{
    public $container = array();
    public $galleries = array();
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    public function offsetSet($offset, $value)
    {
        if (is_object($value)) {
            $value->container = $this;
        }
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    /**
     * Retrieves and caches an I_Gallery_Mapper instance for this gallery id
     *
     * @param int $gallery_id Gallery ID
     * @return mixed
     */
    public function get_gallery($gallery_id)
    {
        if (!isset($this->galleries[$gallery_id]) || is_null($this->galleries[$gallery_id])) {
            $this->galleries[$gallery_id] = C_Gallery_Mapper::get_instance();
        }
        return $this->galleries[$gallery_id];
    }
}
class C_NextGen_Data_Installer extends C_NggLegacy_Installer
{
    public function get_registry()
    {
        return C_Component_Registry::get_instance();
    }
    public function install()
    {
        $this->remove_table_extra_options();
    }
    public function remove_table_extra_options()
    {
        global $wpdb;
        $likes = array('option_name LIKE \'%ngg_gallery%\'', 'option_name LIKE \'%ngg_pictures%\'', 'option_name LIKE \'%ngg_album%\'');
        $sql = "DELETE FROM {$wpdb->options} WHERE " . implode(' OR ', $likes);
        $wpdb->query($sql);
    }
    public function uninstall($hard = FALSE)
    {
        if ($hard) {
        }
    }
}
class C_NextGen_Metadata extends C_Component
{
    // Image data
    public $image = '';
    // The image object
    public $file_path = '';
    // Path to the image file
    public $size = FALSE;
    // The image size
    public $exif_data = FALSE;
    // EXIF data array
    public $iptc_data = FALSE;
    // IPTC data array
    public $xmp_data = FALSE;
    // XMP data array
    // Filtered Data
    public $exif_array = FALSE;
    // EXIF data array
    public $iptc_array = FALSE;
    // IPTC data array
    public $xmp_array = FALSE;
    // XMP data array
    public $sanitize = FALSE;
    // sanitize meta data on request
    /**
     * Class constructor
     * 
     * @param int $image Image ID
     * @param bool $onlyEXIF TRUE = will parse only EXIF data
     * @return bool FALSE if the file does not exist or metadat could not be read
     */
    public function __construct($image, $onlyEXIF = FALSE)
    {
        if (is_numeric($image)) {
            $image = C_Image_Mapper::get_instance()->find($image);
        }
        $this->image = apply_filters('ngg_find_image_meta', $image);
        $this->file_path = C_Gallery_Storage::get_instance()->get_image_abspath($this->image);
        if (!@file_exists($this->file_path)) {
            return FALSE;
        }
        $this->size = @getimagesize($this->file_path, $metadata);
        if ($this->size && is_array($metadata)) {
            // get exif - data
            if (is_callable('exif_read_data')) {
                $this->exif_data = @exif_read_data($this->file_path, NULL, TRUE);
            }
            // stop here if we didn't need other meta data
            if ($onlyEXIF) {
                return TRUE;
            }
            // get the iptc data - should be in APP13
            if (is_callable('iptcparse') && isset($metadata['APP13'])) {
                $this->iptc_data = @iptcparse($metadata['APP13']);
            }
            // get the xmp data in a XML format
            if (is_callable('xml_parser_create')) {
                $this->xmp_data = $this->extract_XMP($this->file_path);
            }
            return TRUE;
        }
        return FALSE;
    }
    /**
     * return the saved meta data from the database
     *
     * @since 1.4.0
     * @param string $object (optional)
     * @return array|mixed return either the complete array or the single object
     */
    public function get_saved_meta($object = false)
    {
        $meta = $this->image->meta_data;
        if (!isset($meta['saved'])) {
            $meta['saved'] = FALSE;
        }
        //check if we already import the meta data to the database
        if (!is_array($meta) || $meta['saved'] != true) {
            return false;
        }
        // return one element if requested
        if ($object) {
            return $meta[$object];
        }
        //removed saved parameter we don't need that to show
        unset($meta['saved']);
        // and remove empty tags or arrays
        foreach ($meta as $key => $value) {
            if (empty($value) or is_array($value)) {
                unset($meta[$key]);
            }
        }
        // on request sanitize the output
        if ($this->sanitize == true) {
            array_walk($meta, create_function('&$value', '$value = esc_html($value);'));
        }
        return $meta;
    }
    /**
     * nggMeta::get_EXIF()
     * See also http://trac.wordpress.org/changeset/6313
     *
     * @return structured EXIF data
     */
    public function get_EXIF($object = false)
    {
        if (!$this->exif_data) {
            return false;
        }
        if (!is_array($this->exif_array)) {
            $meta = array();
            if (isset($this->exif_data['EXIF'])) {
                $exif = $this->exif_data['EXIF'];
                if (!empty($exif['FNumber'])) {
                    $meta['aperture'] = 'F ' . round($this->exif_frac2dec($exif['FNumber']), 2);
                }
                if (!empty($exif['Model'])) {
                    $meta['camera'] = trim($exif['Model']);
                }
                if (!empty($exif['DateTimeDigitized'])) {
                    $meta['created_timestamp'] = $this->exif_date2ts($exif['DateTimeDigitized']);
                } else {
                    if (!empty($exif['DateTimeOriginal'])) {
                        $meta['created_timestamp'] = $this->exif_date2ts($exif['DateTimeOriginal']);
                    } else {
                        if (!empty($exif['FileDateTime'])) {
                            $meta['created_timestamp'] = $this->exif_date2ts($exif['FileDateTime']);
                        }
                    }
                }
                if (!empty($exif['FocalLength'])) {
                    $meta['focal_length'] = $this->exif_frac2dec($exif['FocalLength']) . __(' mm', 'nggallery');
                }
                if (!empty($exif['ISOSpeedRatings'])) {
                    $meta['iso'] = $exif['ISOSpeedRatings'];
                }
                if (!empty($exif['ExposureTime'])) {
                    $meta['shutter_speed'] = $this->exif_frac2dec($exif['ExposureTime']);
                    $meta['shutter_speed'] = ($meta['shutter_speed'] > 0.0 and $meta['shutter_speed'] < 1.0) ? '1/' . round(1 / $meta['shutter_speed'], -1) : $meta['shutter_speed'];
                    $meta['shutter_speed'] .= __(' sec', 'nggallery');
                }
                //Bit 0 indicates the flash firing status
                if (!empty($exif['Flash'])) {
                    $meta['flash'] = $exif['Flash'] & 1 ? __('Fired', 'nggallery') : __('Not fired', ' nggallery');
                }
            }
            // additional information
            if (isset($this->exif_data['IFD0'])) {
                $exif = $this->exif_data['IFD0'];
                if (!empty($exif['Model'])) {
                    $meta['camera'] = $exif['Model'];
                }
                if (!empty($exif['Make'])) {
                    $meta['make'] = $exif['Make'];
                }
                if (!empty($exif['ImageDescription'])) {
                    $meta['title'] = $this->utf8_encode($exif['ImageDescription']);
                }
                if (!empty($exif['Orientation'])) {
                    $meta['Orientation'] = $exif['Orientation'];
                }
            }
            // this is done by Windows
            if (isset($this->exif_data['WINXP'])) {
                $exif = $this->exif_data['WINXP'];
                if (!empty($exif['Title']) && empty($meta['title'])) {
                    $meta['title'] = $this->utf8_encode($exif['Title']);
                }
                if (!empty($exif['Author'])) {
                    $meta['author'] = $this->utf8_encode($exif['Author']);
                }
                if (!empty($exif['Keywords'])) {
                    $meta['tags'] = $this->utf8_encode($exif['Keywords']);
                }
                if (!empty($exif['Subject'])) {
                    $meta['subject'] = $this->utf8_encode($exif['Subject']);
                }
                if (!empty($exif['Comments'])) {
                    $meta['caption'] = $this->utf8_encode($exif['Comments']);
                }
            }
            $this->exif_array = $meta;
        }
        // return one element if requested
        if ($object == true) {
            $value = isset($this->exif_array[$object]) ? $this->exif_array[$object] : false;
            return $value;
        }
        // on request sanitize the output
        if ($this->sanitize == true) {
            array_walk($this->exif_array, create_function('&$value', '$value = esc_html($value);'));
        }
        return $this->exif_array;
    }
    // convert a fraction string to a decimal
    public function exif_frac2dec($str)
    {
        @(list($n, $d) = explode('/', $str));
        if (!empty($d)) {
            return $n / $d;
        }
        return $str;
    }
    // convert the exif date format to a unix timestamp
    public function exif_date2ts($str)
    {
        $retval = is_numeric($str) ? $str : @strtotime($str);
        if (!$retval && $str) {
            @(list($date, $time) = explode(' ', trim($str)));
            @(list($y, $m, $d) = explode(':', $date));
            $retval = strtotime("{$y}-{$m}-{$d} {$time}");
        }
        return $retval;
    }
    /**
     * nggMeta::readIPTC() - IPTC Data Information for EXIF Display
     *
     * @param mixed $output_tag
     * @return IPTC-tags
     */
    public function get_IPTC($object = false)
    {
        if (!$this->iptc_data) {
            return false;
        }
        if (!is_array($this->iptc_array)) {
            // --------- Set up Array Functions --------- //
            $iptcTags = array('2#005' => 'title', '2#007' => 'status', '2#012' => 'subject', '2#015' => 'category', '2#025' => 'keywords', '2#055' => 'created_date', '2#060' => 'created_time', '2#080' => 'author', '2#085' => 'position', '2#090' => 'city', '2#092' => 'location', '2#095' => 'state', '2#100' => 'country_code', '2#101' => 'country', '2#105' => 'headline', '2#110' => 'credit', '2#115' => 'source', '2#116' => 'copyright', '2#118' => 'contact', '2#120' => 'caption');
            $meta = array();
            foreach ($iptcTags as $key => $value) {
                if (isset($this->iptc_data[$key])) {
                    $meta[$value] = trim($this->utf8_encode(implode(', ', $this->iptc_data[$key])));
                }
            }
            $this->iptc_array = $meta;
        }
        // return one element if requested
        if ($object) {
            return isset($this->iptc_array[$object]) ? $this->iptc_array[$object] : NULL;
        }
        // on request sanitize the output
        if ($this->sanitize == true) {
            array_walk($this->iptc_array, create_function('&$value', '$value = esc_html($value);'));
        }
        return $this->iptc_array;
    }
    /**
     * nggMeta::extract_XMP()
     * get XMP DATA
     * code by Pekka Saarinen http://photography-on-the.net
     *
     * @param mixed $filename
     * @return XML data
     */
    public function extract_XMP($filename)
    {
        //TODO:Require a lot of memory, could be better
        ob_start();
        @readfile($filename);
        $source = ob_get_contents();
        ob_end_clean();
        $start = strpos($source, '<x:xmpmeta');
        $end = strpos($source, '</x:xmpmeta>');
        if (!$start === false && !$end === false) {
            $lenght = $end - $start;
            $xmp_data = substr($source, $start, $lenght + 12);
            unset($source);
            return $xmp_data;
        }
        unset($source);
        return false;
    }
    /**
     * nggMeta::get_XMP()
     *
     * @package Taken from http://php.net/manual/en/function.xml-parse-into-struct.php
     * @author Alf Marius Foss Olsen & Alex Rabe
     * @return XML Array or object
     *
     */
    public function get_XMP($object = false)
    {
        if (!$this->xmp_data) {
            return false;
        }
        if (!is_array($this->xmp_array)) {
            $parser = xml_parser_create();
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            // Dont mess with my cAsE sEtTings
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
            // Dont bother with empty info
            xml_parse_into_struct($parser, $this->xmp_data, $values);
            xml_parser_free($parser);
            $xmlarray = array();
            // The XML array
            $this->xmp_array = array();
            // The returned array
            $stack = array();
            // tmp array used for stacking
            $list_array = array();
            // tmp array for list elements
            $list_element = false;
            // rdf:li indicator
            foreach ($values as $val) {
                if ($val['type'] == 'open') {
                    array_push($stack, $val['tag']);
                } elseif ($val['type'] == 'close') {
                    // reset the compared stack
                    if ($list_element == false) {
                        array_pop($stack);
                    }
                    // reset the rdf:li indicator & array
                    $list_element = false;
                    $list_array = array();
                } elseif ($val['type'] == 'complete') {
                    if ($val['tag'] == 'rdf:li') {
                        // first go one element back
                        if ($list_element == false) {
                            array_pop($stack);
                        }
                        $list_element = true;
                        // do not parse empty tags
                        if (empty($val['value'])) {
                            continue;
                        }
                        // save it in our temp array
                        $list_array[] = $val['value'];
                        // in the case it's a list element we seralize it
                        $value = implode(',', $list_array);
                        $this->setArrayValue($xmlarray, $stack, $value);
                    } else {
                        array_push($stack, $val['tag']);
                        // do not parse empty tags
                        if (!empty($val['value'])) {
                            $this->setArrayValue($xmlarray, $stack, $val['value']);
                        }
                        array_pop($stack);
                    }
                }
            }
            // foreach
            // don't parse a empty array
            if (empty($xmlarray) || empty($xmlarray['x:xmpmeta'])) {
                return false;
            }
            // cut off the useless tags
            $xmlarray = $xmlarray['x:xmpmeta']['rdf:RDF']['rdf:Description'];
            // --------- Some values from the XMP format--------- //
            $xmpTags = array('xap:CreateDate' => 'created_timestamp', 'xap:ModifyDate' => 'last_modfied', 'xap:CreatorTool' => 'tool', 'dc:format' => 'format', 'dc:title' => 'title', 'dc:creator' => 'author', 'dc:subject' => 'keywords', 'dc:description' => 'caption', 'photoshop:AuthorsPosition' => 'position', 'photoshop:City' => 'city', 'photoshop:Country' => 'country');
            foreach ($xmpTags as $key => $value) {
                // if the kex exist
                if (isset($xmlarray[$key])) {
                    switch ($key) {
                        case 'xap:CreateDate':
                        case 'xap:ModifyDate':
                            $this->xmp_array[$value] = strtotime($xmlarray[$key]);
                            break;
                        default:
                            $this->xmp_array[$value] = $xmlarray[$key];
                    }
                }
            }
        }
        // return one element if requested
        if ($object != false) {
            return isset($this->xmp_array[$object]) ? $this->xmp_array[$object] : false;
        }
        // on request sanitize the output
        if ($this->sanitize == true) {
            array_walk($this->xmp_array, create_function('&$value', '$value = esc_html($value);'));
        }
        return $this->xmp_array;
    }
    public function setArrayValue(&$array, $stack, $value)
    {
        if ($stack) {
            $key = array_shift($stack);
            $this->setArrayValue($array[$key], $stack, $value);
            return $array;
        } else {
            $array = $value;
        }
    }
    /**
     * nggMeta::get_META() - return a meta value form the available list
     *
     * @param string $object
     * @return mixed $value
     */
    public function get_META($object = false)
    {
        // defined order first look into database, then XMP, IPTC and EXIF.
        if ($value = $this->get_saved_meta($object)) {
            return $value;
        }
        if ($value = $this->get_XMP($object)) {
            return $value;
        }
        if ($object == 'created_timestamp' && ($d = $this->get_IPTC('created_date')) && ($t = $this->get_IPTC('created_time'))) {
            return $this->exif_date2ts($d . ' ' . $t);
        }
        if ($value = $this->get_IPTC($object)) {
            return $value;
        }
        if ($value = $this->get_EXIF($object)) {
            return $value;
        }
        // nothing found ?
        return false;
    }
    /**
     * nggMeta::i8n_name() -  localize the tag name
     *
     * @param mixed $key
     * @return translated $key
     */
    public function i18n_name($key)
    {
        $tagnames = array('aperture' => __('Aperture', 'nggallery'), 'credit' => __('Credit', 'nggallery'), 'camera' => __('Camera', 'nggallery'), 'caption' => __('Caption', 'nggallery'), 'created_timestamp' => __('Date/Time', 'nggallery'), 'copyright' => __('Copyright', 'nggallery'), 'focal_length' => __('Focal length', 'nggallery'), 'iso' => __('ISO', 'nggallery'), 'shutter_speed' => __('Shutter speed', 'nggallery'), 'title' => __('Title', 'nggallery'), 'author' => __('Author', 'nggallery'), 'tags' => __('Tags', 'nggallery'), 'subject' => __('Subject', 'nggallery'), 'make' => __('Make', 'nggallery'), 'status' => __('Edit Status', 'nggallery'), 'category' => __('Category', 'nggallery'), 'keywords' => __('Keywords', 'nggallery'), 'created_date' => __('Date Created', 'nggallery'), 'created_time' => __('Time Created', 'nggallery'), 'position' => __('Author Position', 'nggallery'), 'city' => __('City', 'nggallery'), 'location' => __('Location', 'nggallery'), 'state' => __('Province/State', 'nggallery'), 'country_code' => __('Country code', 'nggallery'), 'country' => __('Country', 'nggallery'), 'headline' => __('Headline', 'nggallery'), 'credit' => __('Credit', 'nggallery'), 'source' => __('Source', 'nggallery'), 'copyright' => __('Copyright Notice', 'nggallery'), 'contact' => __('Contact', 'nggallery'), 'last_modfied' => __('Last modified', 'nggallery'), 'tool' => __('Program tool', 'nggallery'), 'format' => __('Format', 'nggallery'), 'width' => __('Image Width', 'nggallery'), 'height' => __('Image Height', 'nggallery'), 'flash' => __('Flash', 'nggallery'));
        if (isset($tagnames[$key])) {
            $key = $tagnames[$key];
        }
        return $key;
    }
    /**
     * Return the Timestamp from the image , if possible it's read from exif data
     * @return int
     */
    public function get_date_time()
    {
        $date = time();
        // Try getting the created_timestamp field
        $date = $this->exif_date2ts($this->get_META('created_timestamp'));
        if (!$date) {
            $image_path = C_Gallery_Storage::get_instance()->get_backup_abspath($this->image);
            $date = @filectime($image_path);
        }
        // Failback
        if (!$date) {
            $date = time();
        }
        // Return the MySQL format
        $date_time = date('Y-m-d H:i:s', $date);
        return $date_time;
    }
    /**
     * This function return the most common metadata, via a filter we can add more
     * Reason : GD manipulation removes that options
     *
     * @since V1.4.0
     * @return void
     */
    public function get_common_meta()
    {
        global $wpdb;
        $meta = array('aperture' => 0, 'credit' => '', 'camera' => '', 'caption' => '', 'created_timestamp' => 0, 'copyright' => '', 'focal_length' => 0, 'iso' => 0, 'shutter_speed' => 0, 'flash' => 0, 'title' => '', 'keywords' => '');
        $meta = apply_filters('ngg_read_image_metadata', $meta);
        // meta should be still an array
        if (!is_array($meta)) {
            return false;
        }
        foreach ($meta as $key => $value) {
            $meta[$key] = $this->get_META($key);
        }
        //let's add now the size of the image
        $meta['width'] = $this->size[0];
        $meta['height'] = $this->size[1];
        return $meta;
    }
    /**
     * If needed sanitize each value before output
     *
     * @return void
     */
    public function sanitize()
    {
        $this->sanitize = true;
    }
    /**
     * Wrapper to utf8_encode() that avoids double encoding
     *
     * Regex adapted from http://www.w3.org/International/questions/qa-forms-utf-8.en.php
     * to determine if the given string is already UTF-8. mb_detect_encoding() is not
     * always available and is limited in accuracy
     *
     * @param string $str
     * @return string
     */
    public function utf8_encode($str)
    {
        $is_utf8 = preg_match('%^(?:
              [\\x09\\x0A\\x0D\\x20-\\x7E]            # ASCII
            | [\\xC2-\\xDF][\\x80-\\xBF]             # non-overlong 2-byte
            |  \\xE0[\\xA0-\\xBF][\\x80-\\xBF]        # excluding overlongs
            | [\\xE1-\\xEC\\xEE\\xEF][\\x80-\\xBF]{2}  # straight 3-byte
            |  \\xED[\\x80-\\x9F][\\x80-\\xBF]        # excluding surrogates
            |  \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}     # planes 1-3
            | [\\xF1-\\xF3][\\x80-\\xBF]{3}          # planes 4-15
            |  \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2}     # plane 16
            )*$%xs', $str);
        if (!$is_utf8) {
            utf8_encode($str);
        }
        return $str;
    }
}
class Mixin_NggLegacy_GalleryStorage_Driver extends Mixin
{
    /**
     * Returns the named sizes available for images
     * @return array
     */
    public function get_image_sizes()
    {
        return array('full', 'thumbnail');
    }
    public function get_upload_abspath($gallery = FALSE)
    {
        // Base upload path
        $retval = C_NextGen_Settings::get_instance()->gallerypath;
        $fs = C_Fs::get_instance();
        // If a gallery has been specified, then we'll
        // append the slug
        if ($gallery) {
            $retval = $this->get_gallery_abspath($gallery);
        }
        // We need to make this an absolute path
        if (strpos($retval, $fs->get_document_root('gallery')) !== 0) {
            $retval = rtrim($fs->join_paths($fs->get_document_root('gallery'), $retval), '/\\');
        }
        // Convert slashes
        return $this->object->convert_slashes($retval);
    }
    /**
     * Get the gallery path persisted in the database for the gallery
     * @param int|stdClass|C_NextGen_Gallery $gallery
     */
    public function get_gallery_abspath($gallery)
    {
        $retval = NULL;
        $fs = C_Fs::get_instance();
        // Get the gallery entity from the database
        if ($gallery) {
            if (is_numeric($gallery)) {
                $gallery = $this->object->_gallery_mapper->find($gallery);
            }
        }
        // It just doesn't exist
        if (!$gallery || is_numeric($gallery)) {
            return $retval;
        }
        // We we have a gallery, determine it's path
        if ($gallery) {
            if (isset($gallery->path)) {
                $retval = $gallery->path;
            } elseif (isset($gallery->slug)) {
                $fs = C_Fs::get_instance();
                $basepath = C_NextGen_Settings::get_instance()->gallerypath;
                $retval = $fs->join_paths($basepath, $gallery->slug);
            }
        }
        $root_type = defined('NGG_GALLERY_ROOT_TYPE') ? NGG_GALLERY_ROOT_TYPE : 'site';
        if ($root_type == 'content') {
            // This requires explanation: in case our content root ends with the same directory name
            // that the gallery path begins with we remove the duplicate name from $retval. This is
            // necessary because the default WP_CONTENT_DIR setting ends in /wp-content/ and
            // NextGEN's default gallery path begins with /wp-content/. This also allows gallery
            // paths to also be expressed as simply "/gallery-name/"
            $exploded_root = explode(DIRECTORY_SEPARATOR, trim($fs->get_document_root('content'), '/\\'));
            $exploded_gallery = explode(DIRECTORY_SEPARATOR, trim($retval, '/\\'));
            $exploded_gallery = array_values($exploded_gallery);
            $last_gallery_dirname = $exploded_gallery[0];
            $last_root_dirname = end($exploded_root);
            if ($last_root_dirname === $last_gallery_dirname) {
                unset($exploded_gallery[0]);
                $retval = implode(DIRECTORY_SEPARATOR, $exploded_gallery);
            }
        }
        // Ensure that the path is absolute
        if (strpos($retval, $fs->get_document_root('gallery')) !== 0) {
            $retval = rtrim($fs->join_paths($fs->get_document_root('gallery'), $retval), '/\\');
        }
        return $this->object->convert_slashes(rtrim($retval, '/\\'));
    }
    /**
     * Gets the absolute path where the image is stored
     * Can optionally return the path for a particular sized image
     */
    public function get_image_abspath($image, $size = 'full', $check_existance = FALSE)
    {
        $retval = NULL;
        $fs = C_Fs::get_instance();
        // Ensure that we have a size
        if (!$size) {
            $size = 'full';
        }
        // If we have the id, get the actual image entity
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        // Ensure we have the image entity - user could have passed in an
        // incorrect id
        if (is_object($image)) {
            if ($gallery_path = $this->object->get_gallery_abspath($image->galleryid)) {
                $folder = $prefix = $size;
                switch ($size) {
                    # Images are stored in the associated gallery folder
                    case 'full':
                    case 'original':
                    case 'image':
                        $retval = $fs->join_paths($gallery_path, $image->filename);
                        break;
                    case 'backup':
                        $retval = $fs->join_paths($gallery_path, $image->filename . '_backup');
                        if ($check_existance && !@file_exists($retval)) {
                            $retval = $fs->join_paths($gallery_path, $image->filename);
                        }
                        break;
                    case 'thumbnails':
                    case 'thumbnail':
                    case 'thumb':
                    case 'thumbs':
                        $size = 'thumbnail';
                        $folder = 'thumbs';
                        $prefix = 'thumbs';
                    // deliberately no break here
                    // We assume any other size of image is stored in the a
                    //subdirectory of the same name within the gallery folder
                    // gallery folder, but with the size appended to the filename
                    default:
                        $image_path = $fs->join_paths($gallery_path, $folder);
                        // NGG 2.0 stores relative filenames in the meta data of
                        // an image. It does this because it uses filenames
                        // that follow conventional WordPress naming scheme.
                        if (isset($image->meta_data) && isset($image->meta_data[$size]) && isset($image->meta_data[$size]['filename'])) {
                            $image_path = $fs->join_paths($image_path, $image->meta_data[$size]['filename']);
                        } else {
                            $image_path = $fs->join_paths($image_path, "{$prefix}_{$image->filename}");
                        }
                        $retval = $image_path;
                        break;
                }
            }
        }
        // Check the existance of the file
        if ($retval && $check_existance) {
            if (!@file_exists($retval)) {
                $retval = NULL;
            }
        }
        return $retval ? rtrim($retval, '/\\') : $retval;
    }
    /**
     * Gets the url of a particular-sized image
     * @param int|object $image
     * @param string $size
     * @returns array
     */
    public function get_image_url($image, $size = 'full', $check_existance = FALSE, $image_abspath = FALSE)
    {
        $retval = NULL;
        $fs = C_Fs::get_instance();
        $router = C_Router::get_instance();
        if (!$image_abspath) {
            $image_abspath = $this->object->get_image_abspath($image, $size, $check_existance);
        }
        if ($image_abspath) {
            // Use multibyte pathinfo() in case of UTF8 gallery or file names
            $parts = M_I18n::mb_pathinfo($image_abspath);
            $image_abspath = $parts['dirname'] . DIRECTORY_SEPARATOR . $parts['basename'];
            $doc_root = $fs->get_document_root('gallery');
            if ($doc_root != null) {
                $doc_root = rtrim($doc_root, '/\\') . DIRECTORY_SEPARATOR;
            }
            // if docroot is "/" we would generate urls like /wp-contentpluginsnextgen-galleryetcetc
            if ($doc_root !== '/') {
                $request_uri = str_replace($doc_root, '', $image_abspath);
            } else {
                $request_uri = $image_abspath;
            }
            $request_uri = '/' . ltrim(str_replace('\\', '/', $request_uri), '/');
            // Because like%@this.jpg is a valid directory and filename
            $request_uri = explode('/', $request_uri);
            foreach ($request_uri as $ndx => $segment) {
                $request_uri[$ndx] = rawurlencode($segment);
            }
            $request_uri = implode('/', $request_uri);
            $retval = $router->remove_url_segment('/index.php', $router->get_url($request_uri, FALSE, 'gallery'));
        }
        return apply_filters('ngg_get_image_url', $retval, $image, $size);
    }
    /**
     * Uploads an image for a particular gallerys
     * @param int|stdClass|C_NextGEN_Gallery $gallery
     * @param type $filename, specifies the name of the file
     * @param type $data if specified, expects base64 encoded string of data
     * @return C_Image
     */
    public function upload_image($gallery, $filename = FALSE, $data = FALSE)
    {
        $retval = NULL;
        // Ensure that we have the data present that we require
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            //		$_FILES = Array(
            //		 [file]	=>	Array (
            //            [name] => Canada_landscape4.jpg
            //            [type] => image/jpeg
            //            [tmp_name] => /private/var/tmp/php6KO7Dc
            //            [error] => 0
            //            [size] => 64975
            //         )
            //
            $file = $_FILES['file'];
            if ($this->object->is_zip()) {
                $retval = $this->object->upload_zip($gallery);
            } else {
                if ($this->is_image_file()) {
                    $retval = $this->object->upload_base64_image($gallery, file_get_contents($file['tmp_name']), $filename ? $filename : (isset($file['name']) ? $file['name'] : FALSE));
                } else {
                    // Remove the non-valid (and potentially insecure) file from the PHP upload directory
                    if (isset($_FILES['file']['tmp_name'])) {
                        $filename = $_FILES['file']['tmp_name'];
                        @unlink($filename);
                    }
                    throw new E_UploadException(__('Invalid image file. Acceptable formats: JPG, GIF, and PNG.', 'nggallery'));
                }
            }
        } elseif ($data) {
            $retval = $this->object->upload_base64_image($filename, $data);
        } else {
            throw new E_UploadException();
        }
        return $retval;
    }
    public function get_image_size_params($image, $size, $params = null, $skip_defaults = false)
    {
        // Get the image entity
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        $params = apply_filters('ngg_get_image_size_params', $params, $size, $image);
        // Ensure we have a valid image
        if ($image) {
            $settings = C_NextGen_Settings::get_instance();
            if (!$skip_defaults) {
                // Get default settings
                if ($size == 'full') {
                    if (!isset($params['quality'])) {
                        $params['quality'] = $settings->imgQuality;
                    }
                } else {
                    if (!isset($params['crop'])) {
                        $params['crop'] = $settings->thumbfix;
                    }
                    if (!isset($params['quality'])) {
                        $params['quality'] = $settings->thumbquality;
                    }
                }
            }
            // width and height when omitted make generate_image_clone create a clone with original size, so try find defaults regardless of $skip_defaults
            if (!isset($params['width']) || !isset($params['height'])) {
                // First test if this is a "known" image size, i.e. if we store these sizes somewhere when users re-generate these sizes from the UI...this is required to be compatible with legacy
                // try the 2 default built-in sizes, first thumbnail...
                if ($size == 'thumbnail') {
                    if (!isset($params['width'])) {
                        $params['width'] = $settings->thumbwidth;
                    }
                    if (!isset($params['height'])) {
                        $params['height'] = $settings->thumbheight;
                    }
                } else {
                    if ($size == 'full') {
                        if (!isset($params['width'])) {
                            if ($settings->imgAutoResize) {
                                $params['width'] = $settings->imgWidth;
                            }
                        }
                        if (!isset($params['height'])) {
                            if ($settings->imgAutoResize) {
                                $params['height'] = $settings->imgHeight;
                            }
                        }
                    } else {
                        if (isset($image->meta_data) && isset($image->meta_data[$size])) {
                            $dimensions = $image->meta_data[$size];
                            if (!isset($params['width'])) {
                                $params['width'] = $dimensions['width'];
                            }
                            if (!isset($params['height'])) {
                                $params['height'] = $dimensions['height'];
                            }
                        }
                    }
                }
            }
            if (!isset($params['crop_frame'])) {
                $crop_frame_size_name = 'thumbnail';
                if (isset($image->meta_data[$size]['crop_frame'])) {
                    $crop_frame_size_name = $size;
                }
                if (isset($image->meta_data[$crop_frame_size_name]['crop_frame'])) {
                    $params['crop_frame'] = $image->meta_data[$crop_frame_size_name]['crop_frame'];
                    if (!isset($params['crop_frame']['final_width'])) {
                        $params['crop_frame']['final_width'] = $image->meta_data[$crop_frame_size_name]['width'];
                    }
                    if (!isset($params['crop_frame']['final_height'])) {
                        $params['crop_frame']['final_height'] = $image->meta_data[$crop_frame_size_name]['height'];
                    }
                }
            } else {
                if (!isset($params['crop_frame']['final_width'])) {
                    $params['crop_frame']['final_width'] = $params['width'];
                }
                if (!isset($params['crop_frame']['final_height'])) {
                    $params['crop_frame']['final_height'] = $params['height'];
                }
            }
        }
        return $params;
    }
    /**
     * Returns an array of dimensional properties (width, height, real_width, real_height) of a resulting clone image if and when generated
     * @param string $image_path
     * @param string $clone_path
     * @param array $params
     * @return array
     */
    public function calculate_image_size_dimensions($image, $size, $params = null, $skip_defaults = false)
    {
        $retval = FALSE;
        // Get the image entity
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        // Ensure we have a valid image
        if ($image) {
            $params = $this->object->get_image_size_params($image, $size, $params, $skip_defaults);
            // Get the image filename
            $image_path = $this->object->get_original_abspath($image, 'original');
            $clone_path = $this->object->get_image_abspath($image, $size);
            $retval = $this->object->calculate_image_clone_dimensions($image_path, $clone_path, $params);
        }
        return $retval;
    }
    /**
     * Generates a specific size for an image
     * @param int|stdClass|C_Image $image
     * @return bool|object
     */
    public function generate_image_size($image, $size, $params = null, $skip_defaults = false)
    {
        $retval = FALSE;
        // Get the image entity
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        // Ensure we have a valid image
        if ($image) {
            $params = $this->object->get_image_size_params($image, $size, $params, $skip_defaults);
            $settings = C_NextGen_Settings::get_instance();
            // Get the image filename
            $filename = $this->object->get_original_abspath($image, 'original');
            $thumbnail = null;
            if ($size == 'full' && $settings->imgBackup == 1) {
                // XXX change this? 'full' should be the resized path and 'original' the _backup path
                $backup_path = $this->object->get_backup_abspath($image);
                if (!@file_exists($backup_path)) {
                    @copy($filename, $backup_path);
                }
            }
            // Generate the thumbnail using WordPress
            $existing_image_abpath = $this->object->get_image_abspath($image, $size);
            $existing_image_dir = dirname($existing_image_abpath);
            // removing the old thumbnail is actually not needed as generate_image_clone() will replace it, leaving commented in as reminder in case there are issues in the future
            if (@file_exists($existing_image_abpath)) {
            }
            wp_mkdir_p($existing_image_dir);
            $clone_path = $existing_image_abpath;
            $thumbnail = $this->object->generate_image_clone($filename, $clone_path, $params);
            // We successfully generated the thumbnail
            if ($thumbnail != null) {
                $clone_path = $thumbnail->fileName;
                if (function_exists('getimagesize')) {
                    $dimensions = getimagesize($clone_path);
                } else {
                    $dimensions = array($params['width'], $params['height']);
                }
                if (!isset($image->meta_data)) {
                    $image->meta_data = array();
                }
                $size_meta = array('width' => $dimensions[0], 'height' => $dimensions[1], 'filename' => M_I18n::mb_basename($clone_path), 'generated' => microtime());
                if (isset($params['crop_frame'])) {
                    $size_meta['crop_frame'] = $params['crop_frame'];
                }
                $image->meta_data[$size] = $size_meta;
                if ($size == 'full') {
                    $image->meta_data['width'] = $size_meta['width'];
                    $image->meta_data['height'] = $size_meta['height'];
                }
                $retval = $this->object->_image_mapper->save($image);
                do_action('ngg_generated_image', $image, $size, $params);
                if ($retval == 0) {
                    $retval = false;
                }
                if ($retval) {
                    $retval = $thumbnail;
                }
            } else {
            }
        }
        return $retval;
    }
    /**
     * Generates a thumbnail for an image
     * @param int|stdClass|C_Image $image
     * @return bool
     */
    public function generate_thumbnail($image, $params = null, $skip_defaults = false)
    {
        $sized_image = $this->object->generate_image_size($image, 'thumbnail', $params, $skip_defaults);
        $retval = false;
        if ($sized_image != null) {
            $retval = true;
            $sized_image->destruct();
        }
        return $retval;
    }
    /**
     * Outputs/renders an image
     * @param int|stdClass|C_NextGen_Gallery_Image $image
     * @return bool
     */
    public function render_image($image, $size = FALSE)
    {
        $format_list = $this->object->get_image_format_list();
        $abspath = $this->get_image_abspath($image, $size, true);
        if ($abspath == null) {
            $thumbnail = $this->object->generate_image_size($image, $size);
            if ($thumbnail != null) {
                $abspath = $thumbnail->fileName;
                $thumbnail->destruct();
            }
        }
        if ($abspath != null) {
            $data = @getimagesize($abspath);
            $format = 'jpg';
            if ($data != null && is_array($data) && isset($format_list[$data[2]])) {
                $format = $format_list[$data[2]];
            }
            // Clear output
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $format = strtolower($format);
            // output image and headers
            header('Content-type: image/' . $format);
            readfile($abspath);
            return true;
        }
        return false;
    }
    public function delete_gallery($gallery)
    {
        $retval = FALSE;
        $fs = C_Fs::get_instance();
        $safe_dirs = array(DIRECTORY_SEPARATOR, $fs->get_document_root('plugins'), $fs->get_document_root('plugins_mu'), $fs->get_document_root('templates'), $fs->get_document_root('stylesheets'), $fs->get_document_root('content'), $fs->get_document_root('galleries'), $fs->get_document_root());
        $abspath = $this->object->get_gallery_abspath($gallery);
        if ($abspath && file_exists($abspath) && !in_array(stripslashes($abspath), $safe_dirs)) {
            // delete the directory and everything in it
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($abspath), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $file) {
                if (in_array($file->getBasename(), array('.', '..'))) {
                    continue;
                } elseif ($file->isDir()) {
                    rmdir($file->getPathname());
                } elseif ($file->isFile() || $file->isLink()) {
                    unlink($file->getPathname());
                }
            }
            $retval = @rmdir($abspath);
        }
        return $retval;
    }
    public function delete_image($image, $size = FALSE)
    {
        $retval = FALSE;
        // Ensure that we have the image entity
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        if ($image) {
            $image_id = $image->{$image->id_field};
            do_action('ngg_delete_image', $image_id, $size);
            // Delete only a particular image size
            if ($size) {
                $abspath = $this->object->get_image_abspath($image, $size);
                if ($abspath && @file_exists($abspath)) {
                    unlink($abspath);
                }
                if (isset($image->meta_data) && isset($image->meta_data[$size])) {
                    unset($image->meta_data[$size]);
                    $this->object->_image_mapper->save($image);
                }
            } else {
                // Get the paths to fullsize and thumbnail files
                $abspaths = array($this->object->get_full_abspath($image), $this->object->get_thumb_abspath($image), $this->object->get_backup_abspath($image));
                if (isset($image->meta_data)) {
                    foreach (array_keys($image->meta_data) as $size) {
                        $abspaths[] = $this->object->get_image_abspath($image, $size);
                    }
                }
                // Delete each image
                foreach ($abspaths as $abspath) {
                    if ($abspath && @file_exists($abspath)) {
                        unlink($abspath);
                    }
                }
                // Delete the entity
                $this->object->_image_mapper->destroy($image);
            }
            $retval = TRUE;
        }
        return $retval;
    }
    public function set_post_thumbnail($post, $image)
    {
        $attachment_id = null;
        // Get the post id
        $post_id = $post;
        if (is_object($post)) {
            if (property_exists($post, 'ID')) {
                $post_id = $post->ID;
            } elseif (property_exists($post, 'post_id')) {
                $post_id = $post->post_id;
            }
        } elseif (is_array($post)) {
            if (isset($post['ID'])) {
                $post_id = $post['ID'];
            } elseif (isset($post['post_id'])) {
                $post_id = $post['post_id'];
            }
        }
        // Get the image object
        if (is_int($image)) {
            $image = C_Image_Mapper::get_instance()->find($image);
        }
        // Do we have what we need?
        if ($image && is_int($post_id)) {
            $args = array('post_type' => 'attachment', 'meta_key' => '_ngg_image_id', 'meta_compare' => '==', 'meta_value' => $image->{$image->id_field});
            $upload_dir = wp_upload_dir();
            $basedir = $upload_dir['basedir'];
            $thumbs_dir = implode(DIRECTORY_SEPARATOR, array($basedir, 'ngg_featured'));
            $gallery_abspath = $this->object->get_gallery_abspath($image->galleryid);
            $image_abspath = $this->object->get_full_abspath($image);
            $target_path = null;
            $copy_image = TRUE;
            // Have we previously set the post thumbnail?
            if ($posts = get_posts($args)) {
                $attachment_id = $posts[0]->ID;
                $attachment_file = get_attached_file($attachment_id);
                $target_path = $attachment_file;
                if (filemtime($image_abspath) > filemtime($target_path)) {
                    $copy_image = TRUE;
                }
            } else {
                $url = $this->object->get_full_url($image);
                $target_relpath = null;
                $target_basename = M_I18n::mb_basename($image_abspath);
                if (strpos($image_abspath, $gallery_abspath) === 0) {
                    $target_relpath = substr($image_abspath, strlen($gallery_abspath));
                } else {
                    if ($image->galleryid) {
                        $target_relpath = path_join(strval($image->galleryid), $target_basename);
                    } else {
                        $target_relpath = $target_basename;
                    }
                }
                $target_relpath = trim($target_relpath, '\\/');
                $target_path = path_join($thumbs_dir, $target_relpath);
                $max_count = 100;
                $count = 0;
                while (@file_exists($target_path) && $count <= $max_count) {
                    $count++;
                    $pathinfo = M_I18n::mb_pathinfo($target_path);
                    $dirname = $pathinfo['dirname'];
                    $filename = $pathinfo['filename'];
                    $extension = $pathinfo['extension'];
                    $rand = mt_rand(1, 9999);
                    $basename = $filename . '_' . sprintf('%04d', $rand) . '.' . $extension;
                    $target_path = path_join($dirname, $basename);
                }
                if (@file_exists($target_path)) {
                }
                $target_dir = dirname($target_path);
                wp_mkdir_p($target_dir);
            }
            if ($copy_image) {
                @copy($image_abspath, $target_path);
                if (!$attachment_id) {
                    $size = @getimagesize($target_path);
                    $image_type = $size ? $size['mime'] : 'image/jpeg';
                    $title = sanitize_file_name($image->alttext);
                    $caption = sanitize_file_name($image->description);
                    $attachment = array('post_title' => $title, 'post_content' => $caption, 'post_status' => 'attachment', 'post_parent' => 0, 'post_mime_type' => $image_type, 'guid' => $url);
                    $attachment_id = wp_insert_attachment($attachment, $target_path);
                }
                update_post_meta($attachment_id, '_ngg_image_id', $image->{$image->id_field});
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $target_path));
            }
        }
        return $attachment_id;
    }
    /**
     * Copies (or moves) images into another gallery
     *
     * @param array $images
     * @param int|object $gallery
     * @param boolean $db optionally only copy the image files
     * @param boolean $move move the image instead of copying
     * @return mixed NULL on failure, array|image-ids on success
     */
    public function copy_images($images, $gallery, $db = TRUE, $move = FALSE)
    {
        $new_image_pids = array();
        // the return value
        // legacy requires passing just a numeric ID
        if (is_numeric($gallery)) {
            $gallery = $this->object->_gallery_mapper->find($gallery);
        }
        // move_images() is a wrapper to this function so we implement both features here
        $func = $move ? 'rename' : 'copy';
        // legacy allows for arrays of just the ID
        if (!is_array($images)) {
            $images = array($images);
        }
        // Ensure we have a valid gallery
        $gallery_id = $this->object->_get_gallery_id($gallery);
        if (!$gallery_id) {
            return array();
        }
        $image_key = $this->object->_image_mapper->get_primary_key_column();
        $gallery_abspath = $this->object->get_gallery_abspath($gallery);
        // Check for folder permission
        if (!is_dir($gallery_abspath) && !wp_mkdir_p($gallery_abspath)) {
            echo sprintf(__('Unable to create directory %s.', 'nggallery'), esc_html($gallery_abspath));
            return $new_image_pids;
        }
        if (!is_writable($gallery_abspath)) {
            echo sprintf(__('Unable to write to directory %s. Is this directory writable by the server?', 'nggallery'), esc_html($gallery_abspath));
            return $new_image_pids;
        }
        $old_gallery_ids = array();
        $image_pid_map = array();
        foreach ($images as $image) {
            if ($this->object->is_current_user_over_quota()) {
                throw new E_NoSpaceAvailableException(__('Sorry, you have used your space allocation. Please delete some files to upload more files.', 'nggallery'));
            }
            // again legacy requires that it be able to pass just a numeric ID
            if (is_numeric($image)) {
                $image = $this->object->_image_mapper->find($image);
            }
            $old_gallery_ids[] = $image->galleryid;
            $old_pid = $image->{$image_key};
            // update the DB if requested
            $new_image = clone $image;
            $new_pid = $old_pid;
            if ($db) {
                unset($new_image->extras_post_id);
                $new_image->galleryid = $gallery_id;
                if (!$move) {
                    $new_image->image_slug = nggdb::get_unique_slug(sanitize_title_with_dashes($image->alttext), 'image');
                    unset($new_image->{$image_key});
                }
                $new_pid = $this->object->_image_mapper->save($new_image);
            }
            if (!$new_pid) {
                echo sprintf(__('Failed to copy database row for picture %s', 'nggallery'), $old_pid) . '<br />';
                continue;
            }
            // Copy each image size
            foreach ($this->object->get_image_sizes() as $size) {
                // if backups are off there's no backup file to copy
                if (!C_NextGen_Settings::get_instance()->imgBackup && $size == 'backup') {
                    continue;
                }
                $orig_path = $this->object->get_image_abspath($image, $size, TRUE);
                if (!$orig_path || !@file_exists($orig_path)) {
                    echo sprintf(__('Failed to get image path for %s', 'nggallery'), esc_html(M_I18n::mb_basename($orig_path))) . '<br/>';
                    continue;
                }
                $new_path = $this->object->get_image_abspath($new_image, $size, FALSE);
                // Prevent duplicate filenames: check if the filename exists and begin appending '-#'
                if (!ini_get('safe_mode') && @file_exists($new_path)) {
                    // prevent get_image_abspath() from using the thumbnail filename in metadata
                    unset($new_image->meta_data['thumbnail']['filename']);
                    $file_exists = TRUE;
                    $i = 0;
                    do {
                        $i++;
                        $parts = explode('.', $image->filename);
                        $extension = array_pop($parts);
                        $tmp_filename = implode('.', $parts) . '-' . $i . '.' . $extension;
                        $new_image->filename = $tmp_filename;
                        $tmp_path = $this->object->get_image_abspath($new_image, $size, FALSE);
                        if (!@file_exists($tmp_path)) {
                            $file_exists = FALSE;
                            $new_path = $tmp_path;
                            if ($db) {
                                $this->object->_image_mapper->save($new_image);
                            }
                        }
                    } while ($file_exists == TRUE);
                }
                // Copy files
                if (!@$func($orig_path, $new_path)) {
                    echo sprintf(__('Failed to copy image %1$s to %2$s', 'nggallery'), esc_html($orig_path), esc_html($new_path)) . '<br/>';
                    continue;
                }
                // disabling: this is a bit too verbose
                // if (!empty($tmp_path))
                //     echo sprintf(__('Image %1$s (%2$s) copied as image %3$s (%4$s) &raquo; The file already existed in the destination gallery.', 'nggallery'), $old_pid, esc_html($orig_path), $new_pid, esc_html($new_path)) . '<br />';
                // else
                //     echo sprintf(__('Image %1$s (%2$s) copied as image %3$s (%4$s)', 'nggallery'), $old_pid, esc_html($orig_path), $new_pid, esc_html($new_path)) . '<br />';
                // Copy tags
                if ($db) {
                    $tags = wp_get_object_terms($old_pid, 'ngg_tag', 'fields=ids');
                    $tags = array_map('intval', $tags);
                    wp_set_object_terms($new_pid, $tags, 'ngg_tag', true);
                }
            }
            $new_image_pids[] = $new_pid;
            $image_pid_map[$old_pid] = $new_pid;
        }
        $old_gallery_ids = array_unique($old_gallery_ids);
        if ($move) {
            do_action('ngg_moved_images', $images, $old_gallery_ids, $gallery_id);
        } else {
            do_action('ngg_copied_images', $image_pid_map, $old_gallery_ids, $gallery_id);
        }
        $title = '<a href="' . admin_url() . 'admin.php?page=nggallery-manage-gallery&mode=edit&gid=' . $gallery_id . '" >';
        $title .= $gallery->title;
        $title .= '</a>';
        echo '<hr/>' . sprintf(__('Copied %1$s picture(s) to gallery %2$s .', 'nggallery'), count($new_image_pids), $title);
        return $new_image_pids;
    }
    /**
     * Recover image from backup copy and reprocess it
     *
     * @param int|stdClass|C_Image $image
     * @return string result code
     */
    public function recover_image($image)
    {
        $retval = FALSE;
        if (is_numeric($image)) {
            $image = $this->object->_image_mapper->find($image);
        }
        if ($image) {
            $full_abspath = $this->object->get_image_abspath($image);
            $backup_abspath = $this->object->get_image_abspath($image, 'backup');
            if ($backup_abspath != $full_abspath && @file_exists($backup_abspath)) {
                if (is_writable($full_abspath) && is_writable(dirname($full_abspath))) {
                    // Copy the backup
                    if (@copy($backup_abspath, $full_abspath)) {
                        // Re-create non-fullsize image sizes
                        foreach ($this->object->get_image_sizes($image) as $named_size) {
                            if ($named_size == 'full') {
                                continue;
                            }
                            $this->object->generate_image_clone($backup_abspath, $this->object->get_image_abspath($image, $named_size), $this->object->get_image_size_params($image, $named_size));
                        }
                        do_action('ngg_recovered_image', $image);
                        // Reimport all metadata
                        $retval = $this->object->_image_mapper->reimport_metadata($image);
                    }
                }
            }
        }
        return $retval;
    }
}
class C_NggLegacy_GalleryStorage_Driver extends C_GalleryStorage_Driver_Base
{
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_NggLegacy_GalleryStorage_Driver');
    }
}
/**
 * gd.thumbnail.inc.php
 * 
 * @author 		Ian Selby (ian@gen-x-design.com)
 * @copyright 	Copyright 2006-2011
 * @version 	1.3.0 (based on 1.1.3)
 * @modded      by Alex Rabe
 * 
 */
/**
 * PHP class for dynamically resizing, cropping, and rotating images for thumbnail purposes and either displaying them on-the-fly or saving them.
 *
 */
class C_NggLegacy_Thumbnail
{
    /**
     * Error message to display, if any
     *
     * @var string
     */
    public $errmsg;
    /**
     * Whether or not there is an error
     *
     * @var boolean
     */
    public $error;
    /**
     * Format of the image file
     *
     * @var string
     */
    public $format;
    /**
     * File name and path of the image file
     *
     * @var string
     */
    public $fileName;
    /**
     * Current dimensions of working image
     *
     * @var array
     */
    public $currentDimensions;
    /**
     * New dimensions of working image
     *
     * @var array
     */
    public $newDimensions;
    /**
     * Image resource for newly manipulated image
     *
     * @var resource
     * @access private
     */
    public $newImage;
    /**
     * Image resource for image before previous manipulation
     *
     * @var resource
     * @access private
     */
    public $oldImage;
    /**
     * Image resource for image being currently manipulated
     *
     * @var resource
     * @access private
     */
    public $workingImage;
    /**
     * Percentage to resize image by
     *
     * @var int
     * @access private
     */
    public $percent;
    /**
     * Maximum width of image during resize
     *
     * @var int
     * @access private
     */
    public $maxWidth;
    /**
     * Maximum height of image during resize
     *
     * @var int
     * @access private
     */
    public $maxHeight;
    /**
     * Image for Watermark
     *
     * @var string
     * 
     */
    public $watermarkImgPath;
    /**
     * Text for Watermark
     *
     * @var string
     * 
     */
    public $watermarkText;
    /**
     * Image Resource ID for Watermark
     *
     * @var string
     * 
     */
    public function __construct($fileName, $no_ErrorImage = false)
    {
        //make sure the GD library is installed
        if (!function_exists('gd_info')) {
            echo 'You do not have the GD Library installed.  This class requires the GD library to function properly.' . '
';
            echo 'visit http://us2.php.net/manual/en/ref.image.php for more information';
            throw new E_No_Image_Library_Exception();
        }
        //initialize variables
        $this->errmsg = '';
        $this->error = false;
        $this->currentDimensions = array();
        $this->newDimensions = array();
        $this->fileName = $fileName;
        $this->percent = 100;
        $this->maxWidth = 0;
        $this->maxHeight = 0;
        $this->watermarkImgPath = '';
        $this->watermarkText = '';
        //check to see if file exists
        if (!@file_exists($this->fileName)) {
            $this->errmsg = 'File not found';
            $this->error = true;
        } elseif (!is_readable($this->fileName)) {
            $this->errmsg = 'File is not readable';
            $this->error = true;
        }
        //if there are no errors, determine the file format
        if ($this->error == false) {
            @ini_set('memory_limit', -1);
            $data = @getimagesize($this->fileName);
            if (isset($data) && is_array($data)) {
                $extensions = array('1' => 'GIF', '2' => 'JPG', '3' => 'PNG');
                $extension = array_key_exists($data[2], $extensions) ? $extensions[$data[2]] : '';
                if ($extension) {
                    $this->format = $extension;
                } else {
                    $this->errmsg = 'Unknown file format';
                    $this->error = true;
                }
            } else {
                $this->errmsg = 'File is not an image';
                $this->error = true;
            }
        }
        // increase memory-limit if possible, GD needs this for large images
        if (!extension_loaded('suhosin')) {
            @ini_set('memory_limit', '512M');
        }
        if ($this->error == false) {
            // Check memory consumption if file exists
            $this->checkMemoryForImage($this->fileName);
        }
        //initialize resources if no errors
        if ($this->error == false) {
            switch ($this->format) {
                case 'GIF':
                    $this->oldImage = @ImageCreateFromGif($this->fileName);
                    break;
                case 'JPG':
                    $this->oldImage = @ImageCreateFromJpeg($this->fileName);
                    break;
                case 'PNG':
                    $this->oldImage = @ImageCreateFromPng($this->fileName);
                    break;
            }
            if (!$this->oldImage) {
                $this->errmsg = 'Create Image failed. Check memory limit';
                $this->error = true;
            } else {
                $size = GetImageSize($this->fileName);
                $this->currentDimensions = array('width' => $size[0], 'height' => $size[1]);
                $this->newImage = $this->oldImage;
            }
        }
        if ($this->error == true) {
            if (!$no_ErrorImage) {
                $this->showErrorImage();
            }
            return;
        }
    }
    /**
     * Calculate the memory limit
     *
     */
    public function checkMemoryForImage($filename)
    {
        if (function_exists('memory_get_usage') && ini_get('memory_limit')) {
            $imageInfo = getimagesize($filename);
            switch ($this->format) {
                case 'GIF':
                    // measured factor 1 is better
                    $CHANNEL = 1;
                    break;
                case 'JPG':
                    $CHANNEL = $imageInfo['channels'];
                    break;
                case 'PNG':
                    // didn't get the channel for png
                    $CHANNEL = 3;
                    break;
            }
            $MB = 1048576;
            // number of bytes in 1M
            $K64 = 65536;
            // number of bytes in 64K
            $TWEAKFACTOR = 1.68;
            // Or whatever works for you
            $bits = !empty($imageInfo['bits']) ? $imageInfo['bits'] : 32;
            // imgInfo[bits] is not always available
            $memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $bits * $CHANNEL / 8 + $K64) * $TWEAKFACTOR);
            $memoryNeeded = memory_get_usage() + $memoryNeeded;
            // get memory limit
            $memory_limit = ini_get('memory_limit');
            // PHP docs : Note that to have no memory limit, set this directive to -1.
            if ($memory_limit == -1) {
                return;
            }
            // Just check megabyte limits, not higher
            if (strtolower(substr($memory_limit, -1)) == 'm') {
                if ($memory_limit != '') {
                    $memory_limit = substr($memory_limit, 0, -1) * 1024 * 1024;
                }
                if ($memoryNeeded > $memory_limit) {
                    $memoryNeeded = round($memoryNeeded / 1024 / 1024, 2);
                    $this->errmsg = 'Exceed Memory limit. Require : ' . $memoryNeeded . ' MByte';
                    $this->error = true;
                }
            }
        }
        return;
    }
    public function __destruct()
    {
        $this->destruct();
    }
    /**
     * Must be called to free up allocated memory after all manipulations are done
     *
     */
    public function destruct()
    {
        if (is_resource($this->newImage)) {
            @ImageDestroy($this->newImage);
        }
        if (is_resource($this->oldImage)) {
            @ImageDestroy($this->oldImage);
        }
        if (is_resource($this->workingImage)) {
            @ImageDestroy($this->workingImage);
        }
    }
    /**
     * Returns the current width of the image
     *
     * @return int
     */
    public function getCurrentWidth()
    {
        return $this->currentDimensions['width'];
    }
    /**
     * Returns the current height of the image
     *
     * @return int
     */
    public function getCurrentHeight()
    {
        return $this->currentDimensions['height'];
    }
    /**
     * Calculates new image width
     *
     * @param int $width
     * @param int $height
     * @return array
     */
    public function calcWidth($width, $height)
    {
        $newWp = 100 * $this->maxWidth / $width;
        $newHeight = $height * $newWp / 100;
        if (intval($newHeight) == $this->maxHeight - 1) {
            $newHeight = $this->maxHeight;
        }
        return array('newWidth' => intval($this->maxWidth), 'newHeight' => intval($newHeight));
    }
    /**
     * Calculates new image height
     *
     * @param int $width
     * @param int $height
     * @return array
     */
    public function calcHeight($width, $height)
    {
        $newHp = 100 * $this->maxHeight / $height;
        $newWidth = $width * $newHp / 100;
        if (intval($newWidth) == $this->maxWidth - 1) {
            $newWidth = $this->maxWidth;
        }
        return array('newWidth' => intval($newWidth), 'newHeight' => intval($this->maxHeight));
    }
    /**
     * Calculates new image size based on percentage
     *
     * @param int $width
     * @param int $height
     * @return array
     */
    public function calcPercent($width, $height)
    {
        $newWidth = $width * $this->percent / 100;
        $newHeight = $height * $this->percent / 100;
        return array('newWidth' => intval($newWidth), 'newHeight' => intval($newHeight));
    }
    /**
     * Calculates new image size based on width and height, while constraining to maxWidth and maxHeight
     *
     * @param int $width
     * @param int $height
     */
    public function calcImageSize($width, $height)
    {
        // $width and $height are the CURRENT image resolutions
        $ratio_w = $this->maxWidth / $width;
        $ratio_h = $this->maxHeight / $height;
        if ($ratio_w >= $ratio_h) {
            $width = $this->maxWidth;
            $height = (int) round($height * $ratio_h, 0);
        } else {
            $height = $this->maxHeight;
            $width = (int) round($width * $ratio_w, 0);
        }
        $this->newDimensions = array('newWidth' => $width, 'newHeight' => $height);
    }
    /**
     * Calculates new image size based percentage
     *
     * @param int $width
     * @param int $height
     */
    public function calcImageSizePercent($width, $height)
    {
        if ($this->percent > 0) {
            $this->newDimensions = $this->calcPercent($width, $height);
        }
    }
    /**
     * Displays error image
     *
     */
    public function showErrorImage()
    {
        header('Content-type: image/png');
        $errImg = ImageCreate(220, 25);
        $bgColor = imagecolorallocate($errImg, 0, 0, 0);
        $fgColor1 = imagecolorallocate($errImg, 255, 255, 255);
        $fgColor2 = imagecolorallocate($errImg, 255, 0, 0);
        imagestring($errImg, 3, 6, 6, 'Error:', $fgColor2);
        imagestring($errImg, 3, 55, 6, $this->errmsg, $fgColor1);
        imagepng($errImg);
        imagedestroy($errImg);
    }
    /**
     * Resizes image to fixed Width x Height
     * 
     * @param int $Width
     * @param int $Height
     */
    public function resizeFix($Width = 0, $Height = 0, $deprecated = 3)
    {
        $this->newWidth = $Width;
        $this->newHeight = $Height;
        if (function_exists('ImageCreateTrueColor')) {
            $this->workingImage = ImageCreateTrueColor($this->newWidth, $this->newHeight);
        } else {
            $this->workingImage = ImageCreate($this->newWidth, $this->newHeight);
        }
        //		ImageCopyResampled(
        $this->imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->currentDimensions['width'], $this->currentDimensions['height']);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        $this->currentDimensions['width'] = $this->newWidth;
        $this->currentDimensions['height'] = $this->newHeight;
    }
    /**
     * Resizes image to maxWidth x maxHeight
     *
     * @param int $maxWidth
     * @param int $maxHeight
     */
    public function resize($maxWidth = 0, $maxHeight = 0, $deprecated = 3)
    {
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->calcImageSize($this->currentDimensions['width'], $this->currentDimensions['height']);
        if (function_exists('ImageCreateTrueColor')) {
            $this->workingImage = ImageCreateTrueColor($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
        } else {
            $this->workingImage = ImageCreate($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
        }
        //		ImageCopyResampled(
        $this->imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, 0, 0, $this->newDimensions['newWidth'], $this->newDimensions['newHeight'], $this->currentDimensions['width'], $this->currentDimensions['height']);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        $this->currentDimensions['width'] = $this->newDimensions['newWidth'];
        $this->currentDimensions['height'] = $this->newDimensions['newHeight'];
    }
    /**
     * Resizes the image by $percent percent
     *
     * @param int $percent
     */
    public function resizePercent($percent = 0)
    {
        $this->percent = $percent;
        $this->calcImageSizePercent($this->currentDimensions['width'], $this->currentDimensions['height']);
        if (function_exists('ImageCreateTrueColor')) {
            $this->workingImage = ImageCreateTrueColor($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
        } else {
            $this->workingImage = ImageCreate($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
        }
        $this->ImageCopyResampled($this->workingImage, $this->oldImage, 0, 0, 0, 0, $this->newDimensions['newWidth'], $this->newDimensions['newHeight'], $this->currentDimensions['width'], $this->currentDimensions['height']);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        $this->currentDimensions['width'] = $this->newDimensions['newWidth'];
        $this->currentDimensions['height'] = $this->newDimensions['newHeight'];
    }
    /**
     * Crops the image from calculated center in a square of $cropSize pixels
     *
     * @param int $cropSize
     */
    public function cropFromCenter($cropSize)
    {
        if ($cropSize > $this->currentDimensions['width']) {
            $cropSize = $this->currentDimensions['width'];
        }
        if ($cropSize > $this->currentDimensions['height']) {
            $cropSize = $this->currentDimensions['height'];
        }
        $cropX = intval(($this->currentDimensions['width'] - $cropSize) / 2);
        $cropY = intval(($this->currentDimensions['height'] - $cropSize) / 2);
        if (function_exists('ImageCreateTrueColor')) {
            $this->workingImage = ImageCreateTrueColor($cropSize, $cropSize);
        } else {
            $this->workingImage = ImageCreate($cropSize, $cropSize);
        }
        $this->imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $cropX, $cropY, $cropSize, $cropSize, $cropSize, $cropSize);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        $this->currentDimensions['width'] = $cropSize;
        $this->currentDimensions['height'] = $cropSize;
    }
    /**
     * Advanced cropping function that crops an image using $startX and $startY as the upper-left hand corner.
     *
     * @param int $startX
     * @param int $startY
     * @param int $width
     * @param int $height
     */
    public function crop($startX, $startY, $width, $height)
    {
        //make sure the cropped area is not greater than the size of the image
        if ($width > $this->currentDimensions['width']) {
            $width = $this->currentDimensions['width'];
        }
        if ($height > $this->currentDimensions['height']) {
            $height = $this->currentDimensions['height'];
        }
        //make sure not starting outside the image
        if ($startX + $width > $this->currentDimensions['width']) {
            $startX = $this->currentDimensions['width'] - $width;
        }
        if ($startY + $height > $this->currentDimensions['height']) {
            $startY = $this->currentDimensions['height'] - $height;
        }
        if ($startX < 0) {
            $startX = 0;
        }
        if ($startY < 0) {
            $startY = 0;
        }
        if (function_exists('ImageCreateTrueColor')) {
            $this->workingImage = ImageCreateTrueColor($width, $height);
        } else {
            $this->workingImage = ImageCreate($width, $height);
        }
        $this->imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $startX, $startY, $width, $height, $width, $height);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        $this->currentDimensions['width'] = $width;
        $this->currentDimensions['height'] = $height;
    }
    /**
     * Outputs the image to the screen, or saves to $name if supplied.  Quality of JPEG images can be controlled with the $quality variable
     *
     * @param int $quality
     * @param string $name
     */
    public function show($quality = 100, $name = '')
    {
        switch ($this->format) {
            case 'GIF':
                if ($name != '') {
                    @ImageGif($this->newImage, $name) or $this->error = true;
                } else {
                    header('Content-type: image/gif');
                    ImageGif($this->newImage);
                }
                break;
            case 'JPG':
                if ($name != '') {
                    @ImageJpeg($this->newImage, $name, $quality) or $this->error = true;
                } else {
                    header('Content-type: image/jpeg');
                    ImageJpeg($this->newImage, NULL, $quality);
                }
                break;
            case 'PNG':
                if ($name != '') {
                    @ImagePng($this->newImage, $name) or $this->error = true;
                } else {
                    header('Content-type: image/png');
                    ImagePng($this->newImage);
                }
                break;
        }
    }
    /**
     * Saves image as $name (can include file path), with quality of # percent if file is a jpeg
     *
     * @param string $name
     * @param int $quality
     * @return bool errorstate
     */
    public function save($name, $quality = 100)
    {
        $this->show($quality, $name);
        if ($this->error == true) {
            $this->errmsg = 'Create Image failed. Check safe mode settings';
            return false;
        }
        if (function_exists('do_action')) {
            do_action('ngg_ajax_image_save', $name);
        }
        return true;
    }
    /**
     * Creates Apple-style reflection under image, optionally adding a border to main image
     *
     * @param int $percent
     * @param int $reflection
     * @param int $white
     * @param bool $border
     * @param string $borderColor
     */
    public function createReflection($percent, $reflection, $white, $border = true, $borderColor = '#a4a4a4')
    {
        $width = $this->currentDimensions['width'];
        $height = $this->currentDimensions['height'];
        $reflectionHeight = intval($height * ($reflection / 100));
        $newHeight = $height + $reflectionHeight;
        $reflectedPart = $height * ($percent / 100);
        $this->workingImage = ImageCreateTrueColor($width, $newHeight);
        ImageAlphaBlending($this->workingImage, true);
        $colorToPaint = ImageColorAllocateAlpha($this->workingImage, 255, 255, 255, 0);
        ImageFilledRectangle($this->workingImage, 0, 0, $width, $newHeight, $colorToPaint);
        imagecopyresampled($this->workingImage, $this->newImage, 0, 0, 0, $reflectedPart, $width, $reflectionHeight, $width, $height - $reflectedPart);
        $this->imageFlipVertical();
        imagecopy($this->workingImage, $this->newImage, 0, 0, 0, 0, $width, $height);
        imagealphablending($this->workingImage, true);
        for ($i = 0; $i < $reflectionHeight; $i++) {
            $colorToPaint = imagecolorallocatealpha($this->workingImage, 255, 255, 255, ($i / $reflectionHeight * -1 + 1) * $white);
            imagefilledrectangle($this->workingImage, 0, $height + $i, $width, $height + $i, $colorToPaint);
        }
        if ($border == true) {
            $rgb = $this->hex2rgb($borderColor, false);
            $colorToPaint = imagecolorallocate($this->workingImage, $rgb[0], $rgb[1], $rgb[2]);
            imageline($this->workingImage, 0, 0, $width, 0, $colorToPaint);
            //top line
            imageline($this->workingImage, 0, $height, $width, $height, $colorToPaint);
            //bottom line
            imageline($this->workingImage, 0, 0, 0, $height, $colorToPaint);
            //left line
            imageline($this->workingImage, $width - 1, 0, $width - 1, $height, $colorToPaint);
        }
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        $this->currentDimensions['width'] = $width;
        $this->currentDimensions['height'] = $newHeight;
    }
    /**
     * Flip an image.
     *
     * @param bool $horz flip the image in horizontal mode
     * @param bool $vert flip the image in vertical mode
     */
    public function flipImage($horz = false, $vert = false)
    {
        $sx = $vert ? $this->currentDimensions['width'] - 1 : 0;
        $sy = $horz ? $this->currentDimensions['height'] - 1 : 0;
        $sw = $vert ? -$this->currentDimensions['width'] : $this->currentDimensions['width'];
        $sh = $horz ? -$this->currentDimensions['height'] : $this->currentDimensions['height'];
        $this->workingImage = imagecreatetruecolor($this->currentDimensions['width'], $this->currentDimensions['height']);
        $this->imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $sx, $sy, $this->currentDimensions['width'], $this->currentDimensions['height'], $sw, $sh);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        return true;
    }
    /**
     * Rotate an image clockwise or counter clockwise
     *
     * @param string $direction could be CW or CCW
     */
    public function rotateImage($dir = 'CW')
    {
        $angle = $dir == 'CW' ? 90 : -90;
        return $this->rotateImageAngle($angle);
    }
    /**
     * Rotate an image clockwise or counter clockwise
     *
     * @param string $direction could be CW or CCW
     */
    public function rotateImageAngle($angle = 90)
    {
        if (function_exists('imagerotate')) {
            $this->workingImage = imagerotate($this->oldImage, 360 - $angle, 0);
            // imagerotate() rotates CCW
            $this->currentDimensions['width'] = imagesx($this->workingImage);
            $this->currentDimensions['height'] = imagesy($this->workingImage);
            $this->oldImage = $this->workingImage;
            $this->newImage = $this->workingImage;
            return true;
        }
        $this->workingImage = imagecreatetruecolor($this->currentDimensions['height'], $this->currentDimensions['width']);
        imagealphablending($this->workingImage, false);
        imagesavealpha($this->workingImage, true);
        switch ($angle) {
            case 90:
                for ($x = 0; $x < $this->currentDimensions['width']; $x++) {
                    for ($y = 0; $y < $this->currentDimensions['height']; $y++) {
                        if (!imagecopy($this->workingImage, $this->oldImage, $this->currentDimensions['height'] - $y - 1, $x, $x, $y, 1, 1)) {
                            return false;
                        }
                    }
                }
                break;
            case -90:
                for ($x = 0; $x < $this->currentDimensions['width']; $x++) {
                    for ($y = 0; $y < $this->currentDimensions['height']; $y++) {
                        if (!imagecopy($this->workingImage, $this->oldImage, $y, $this->currentDimensions['width'] - $x - 1, $x, $y, 1, 1)) {
                            return false;
                        }
                    }
                }
                break;
            default:
                return false;
        }
        $this->currentDimensions['width'] = imagesx($this->workingImage);
        $this->currentDimensions['height'] = imagesy($this->workingImage);
        $this->oldImage = $this->workingImage;
        $this->newImage = $this->workingImage;
        return true;
    }
    /**
     * Inverts working image, used by reflection function
     * 
     * @access	private
     */
    public function imageFlipVertical()
    {
        $x_i = imagesx($this->workingImage);
        $y_i = imagesy($this->workingImage);
        for ($x = 0; $x < $x_i; $x++) {
            for ($y = 0; $y < $y_i; $y++) {
                imagecopy($this->workingImage, $this->workingImage, $x, $y_i - $y - 1, $x, $y, 1, 1);
            }
        }
    }
    /**
     * Converts hexidecimal color value to rgb values and returns as array/string
     *
     * @param string $hex
     * @param bool $asString
     * @return array|string
     */
    public function hex2rgb($hex, $asString = false)
    {
        // strip off any leading #
        if (0 === strpos($hex, '#')) {
            $hex = substr($hex, 1);
        } else {
            if (0 === strpos($hex, '&H')) {
                $hex = substr($hex, 2);
            }
        }
        // break into hex 3-tuple
        $cutpoint = ceil(strlen($hex) / 2) - 1;
        $rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);
        // convert each tuple to decimal
        $rgb[0] = isset($rgb[0]) ? hexdec($rgb[0]) : 0;
        $rgb[1] = isset($rgb[1]) ? hexdec($rgb[1]) : 0;
        $rgb[2] = isset($rgb[2]) ? hexdec($rgb[2]) : 0;
        return $asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb;
    }
    /**
     * Based on the Watermark function by Marek Malcherek  
     * http://www.malcherek.de
     *
     * @param string $color
     * @param string $wmFont
     * @param int $wmSize
     * @param int $wmOpaque
     */
    public function watermarkCreateText($color = '000000', $wmFont, $wmSize = 10, $wmOpaque = 90)
    {
        // set font path
        $wmFontPath = NGGALLERY_ABSPATH . 'fonts/' . $wmFont;
        if (!is_readable($wmFontPath)) {
            return;
        }
        // This function requires both the GD library and the FreeType library.
        if (!function_exists('ImageTTFBBox')) {
            return;
        }
        $words = preg_split('/ /', $this->watermarkText);
        $lines = array();
        $line = '';
        $watermark_image_width = 0;
        // attempt adding a new word until the width is too large; then start a new line and start again
        foreach ($words as $word) {
            // sanitize the text being input; imagettftext() can be sensitive
            $TextSize = $this->ImageTTFBBoxDimensions($wmSize, 0, $wmFontPath, $line . preg_replace('~^(&([a-zA-Z0-9]);)~', htmlentities('${1}'), mb_convert_encoding($word, 'HTML-ENTITIES', 'UTF-8')));
            if ($watermark_image_width == 0) {
                $watermark_image_width = $TextSize['width'];
            }
            if ($TextSize['width'] > $this->newDimensions['newWidth']) {
                $lines[] = trim($line);
                $line = '';
            } else {
                if ($TextSize['width'] > $watermark_image_width) {
                    $watermark_image_width = $TextSize['width'];
                }
            }
            $line .= $word . ' ';
        }
        $lines[] = trim($line);
        // use this string to determine our largest possible line height
        $line_dimensions = $this->ImageTTFBBoxDimensions($wmSize, 0, $wmFontPath, 'MXQJALYmxqjabdfghjklpqry019`@$^&*(,!132');
        $line_height = $line_dimensions['height'] * 1.05;
        // Create an image to apply our text to
        $this->workingImage = ImageCreateTrueColor($watermark_image_width, count($lines) * $line_height);
        ImageSaveAlpha($this->workingImage, true);
        ImageAlphaBlending($this->workingImage, false);
        $bgText = imagecolorallocatealpha($this->workingImage, 255, 255, 255, 127);
        imagefill($this->workingImage, 0, 0, $bgText);
        $wmTransp = 127 - $wmOpaque * 1.27;
        $rgb = $this->hex2rgb($color, false);
        $TextColor = imagecolorallocatealpha($this->workingImage, $rgb[0], $rgb[1], $rgb[2], $wmTransp);
        // Put text on the image, line-by-line
        $y_pos = $wmSize;
        foreach ($lines as $line) {
            imagettftext($this->workingImage, $wmSize, 0, 0, $y_pos, $TextColor, $wmFontPath, $line);
            $y_pos += $line_height;
        }
        $this->watermarkImgPath = $this->workingImage;
        return;
    }
    /**
     * Calculates the width & height dimensions of ImageTTFBBox().
     *
     * Note: ImageTTFBBox() is unreliable with large font sizes
     * @param $wmSize
     * @param $fontAngle
     * @param $wmFontPath
     * @param $text
     * @return array
     */
    public function ImageTTFBBoxDimensions($wmSize, $fontAngle, $wmFontPath, $text)
    {
        $box = @ImageTTFBBox($wmSize, $fontAngle, $wmFontPath, $text) or die;
        $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
        $max_y = max(array($box[1], $box[3], $box[5], $box[7]));
        $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
        $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
        return array('width' => $max_x - $min_x, 'height' => $max_y - $min_y);
    }
    public function applyFilter($filterType)
    {
        $args = func_get_args();
        array_unshift($args, $this->newImage);
        return call_user_func_array('imagefilter', $args);
    }
    /**
     * Modfied Watermark function by Steve Peart 
     * http://parasitehosting.com/
     *
     * @param string $relPOS
     * @param int $xPOS
     * @param int $yPOS
     */
    public function watermarkImage($relPOS = 'botRight', $xPOS = 0, $yPOS = 0)
    {
        // if it's a resource ID take it as watermark text image
        if (is_resource($this->watermarkImgPath)) {
            $this->workingImage = $this->watermarkImgPath;
        } else {
            // (possibly) search for the file from the document root
            if (!is_file($this->watermarkImgPath)) {
                $fs = C_Fs::get_instance();
                if (is_file($fs->join_paths($fs->get_document_root('content'), $this->watermarkImgPath))) {
                    $this->watermarkImgPath = $fs->get_document_root('content') . $this->watermarkImgPath;
                }
            }
            // Would you really want to use anything other than a png?
            $this->workingImage = @imagecreatefrompng($this->watermarkImgPath);
            // if it's not a valid file die...
            if (empty($this->workingImage) or !$this->workingImage) {
                return;
            }
        }
        imagealphablending($this->workingImage, false);
        imagesavealpha($this->workingImage, true);
        $sourcefile_width = imageSX($this->oldImage);
        $sourcefile_height = imageSY($this->oldImage);
        $watermarkfile_width = imageSX($this->workingImage);
        $watermarkfile_height = imageSY($this->workingImage);
        switch (substr($relPOS, 0, 3)) {
            case 'top':
                $dest_y = 0 + $yPOS;
                break;
            case 'mid':
                $dest_y = $sourcefile_height / 2 - $watermarkfile_height / 2;
                break;
            case 'bot':
                $dest_y = $sourcefile_height - $watermarkfile_height - $yPOS;
                break;
            default:
                $dest_y = 0;
                break;
        }
        switch (substr($relPOS, 3)) {
            case 'Left':
                $dest_x = 0 + $xPOS;
                break;
            case 'Center':
                $dest_x = $sourcefile_width / 2 - $watermarkfile_width / 2;
                break;
            case 'Right':
                $dest_x = $sourcefile_width - $watermarkfile_width - $xPOS;
                break;
            default:
                $dest_x = 0;
                break;
        }
        // debug
        // $this->errmsg = 'X '.$dest_x.' Y '.$dest_y;
        // $this->showErrorImage();
        // if a gif, we have to upsample it to a truecolor image
        if ($this->format == 'GIF') {
            $tempimage = imagecreatetruecolor($sourcefile_width, $sourcefile_height);
            imagecopy($tempimage, $this->oldImage, 0, 0, 0, 0, $sourcefile_width, $sourcefile_height);
            $this->newImage = $tempimage;
        }
        $this->imagecopymerge_alpha($this->newImage, $this->workingImage, $dest_x, $dest_y, 0, 0, $watermarkfile_width, $watermarkfile_height, 100);
    }
    /**
     * Wrapper to imagecopymerge() that allows PNG transparency
     */
    public function imagecopymerge_alpha($destination_image, $source_image, $destination_x, $destination_y, $source_x, $source_y, $source_w, $source_h, $pct)
    {
        $cut = imagecreatetruecolor($source_w, $source_h);
        imagecopy($cut, $destination_image, 0, 0, $destination_x, $destination_y, $source_w, $source_h);
        imagecopy($cut, $source_image, 0, 0, $source_x, $source_y, $source_w, $source_h);
        imagecopymerge($destination_image, $cut, $destination_x, $destination_y, 0, 0, $source_w, $source_h, $pct);
    }
    /**
     * Modfied imagecopyresampled function to save transparent images
     * See : http://www.akemapa.com/2008/07/10/php-gd-resize-transparent-image-png-gif/
     * @since 1.9.0
     * 
     * @param resource $dst_image
     * @param resource $src_image
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $dst_w
     * @param int $dst_h
     * @param int $src_w
     * @param int $src_h
     * @return bool
     */
    public function imagecopyresampled(&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
    {
        // Check if this image is PNG or GIF, then set if Transparent
        if ($this->format == 'GIF' || $this->format == 'PNG') {
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
            $transparent = imagecolorallocatealpha($dst_image, 255, 255, 255, 127);
            imagefilledrectangle($dst_image, 0, 0, $dst_w, $dst_h, $transparent);
        }
        imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        return true;
    }
}
class Mixin_WordPress_GalleryStorage_Driver extends Mixin
{
    /**
     * Returns the named sizes available for images
     * @global array $_wp_additional_image_sizese
     * @return array
     */
    public function get_image_sizes()
    {
        global $_wp_additional_image_sizes;
        $_wp_additional_image_sizes[] = 'full';
        return $_wp_additional_image_sizes;
    }
    /**
     * Gets the upload path for new images in this gallery
     * This will always be the date-based directory
     * @param type $gallery
     * @return type
     */
    public function get_upload_abspath($gallery = FALSE)
    {
        // Gallery is used for this driver, as the upload path is
        // the same, regardless of what gallery is used
        $retval = FALSE;
        $dir = wp_upload_dir(time());
        if ($dir) {
            $retval = $dir['path'];
        }
        return $retval;
    }
    /**
     * Will always return the same as get_upload_abspath(), as
     * WordPress storage is not organized by gallery but by date
     * @param int|object $gallery
     */
    public function get_gallery_abspath($gallery = FALSE)
    {
        return $this->object->get_upload_abspath();
    }
    /**
     * Gets the url of a particular sized image
     * @param int|object $image
     * @param type $size
     * @return string
     */
    public function get_image_url($image = FALSE, $size = 'full')
    {
        $retval = NULL;
        $image_key = C_Displayed_Gallery_Mapper::get_instance()->get_primary_key_column();
        if ($image && ($image_id = $this->object->_get_image_id($image))) {
            $parts = wp_get_attachment_image_src($image->{$image_key});
            if ($parts) {
                $retval = $parts['url'];
            }
        }
        return apply_filters('ngg_get_image_url', $retval, $image, $size);
    }
}
class C_WordPress_GalleryStorage_Driver extends C_GalleryStorage_Driver_Base
{
    public function define($context = FALSE)
    {
        parent::define($context);
        $this->add_mixin('Mixin_WordPress_GalleryStorage_Driver');
    }
}
class Mixin_NextGen_Table_Extras extends Mixin
{
    const CUSTOM_POST_NAME = __CLASS__;
    public function initialize()
    {
        // Each record in a NextGEN Gallery table has an associated custom post in the wp_posts table
        $this->object->_custom_post_mapper = new C_CustomPost_DataMapper_Driver($this->object->get_object_name());
        $this->object->_custom_post_mapper->set_model_factory_method('extra_fields');
    }
    /**
     * Defines a column for the mapper
     * @param $name
     * @param $data_type
     * @param null $default_value
     * @param bool $extra
     */
    public function define_column($name, $data_type, $default_value = NULL, $extra = FALSE)
    {
        $this->call_parent('define_column', $name, $data_type, $default_value);
        if ($extra) {
            $this->object->_columns[$name]['extra'] = TRUE;
        } else {
            $this->object->_columns[$name]['extra'] = FALSE;
        }
    }
    /**
     * Gets a list of all the extra columns defined for this table
     * @return array
     */
    public function get_extra_columns()
    {
        $retval = array();
        foreach ($this->object->_columns as $key => $properties) {
            if ($properties['extra']) {
                $retval[] = $key;
            }
        }
        return $retval;
    }
    /**
     * Adds a column to the database
     * @param $column_name
     * @param $datatype
     * @param null $default_value
     */
    public function _add_column($column_name, $datatype, $default_value = NULL)
    {
        $skip = FALSE;
        if (isset($this->object->_columns[$column_name]) and $this->object->_columns[$column_name]['extra']) {
            $skip = TRUE;
        }
        if (!$skip) {
            $this->call_parent('_add_column', $column_name, $datatype, $default_value);
        }
        return !$skip;
    }
    public function create_custom_post_entity($entity)
    {
        $custom_post_entity = new stdClass();
        // If the custom post entity already exists then it needs
        // an ID
        if (isset($entity->extras_post_id)) {
            $custom_post_entity->ID = $entity->extras_post_id;
        }
        // If a property isn't a column for the table, then
        // it belongs to the custom post record
        foreach (get_object_vars($entity) as $key => $value) {
            if (!$this->object->has_column($key)) {
                unset($entity->{$key});
                if ($this->object->has_defined_column($key) && $key != $this->object->get_primary_key_column()) {
                    $custom_post_entity->{$key} = $value;
                }
            }
        }
        // Used to help find these type of records
        $custom_post_entity->post_name = self::CUSTOM_POST_NAME;
        return $custom_post_entity;
    }
    /**
     * Creates a new record in the custom table, as well as a custom post record
     * @param $entity
     */
    public function _create($entity)
    {
        $retval = FALSE;
        $custom_post_entity = $this->create_custom_post_entity($entity);
        // Try persisting the custom post type record first
        if ($custom_post_id = $this->object->_custom_post_mapper->save($custom_post_entity)) {
            // Try saving the custom table record. If that fails, then destroy the previously
            // created custom post type record
            if (!($retval = $this->call_parent('_create', $entity))) {
                $this->object->_custom_post_mapper->destroy($custom_post_id);
            } else {
                $entity->extras_post_id = $custom_post_id;
            }
        }
        return $retval;
    }
    // Updates a custom table record and it's associated custom post type record in the database
    public function _update($entity)
    {
        $retval = FALSE;
        $custom_post_entity = $this->create_custom_post_entity($entity);
        $custom_post_id = $this->object->_custom_post_mapper->save($custom_post_entity);
        $entity->extras_post_id = $custom_post_id;
        $retval = $this->call_parent('_update', $entity);
        foreach ($this->get_extra_columns() as $key) {
            if (isset($custom_post_entity->{$key})) {
                $entity->{$key} = $custom_post_entity->{$key};
            }
        }
        return $retval;
    }
    public function destroy($entity)
    {
        if (isset($entity->extras_post_id)) {
            wp_delete_post($entity->extras_post_id, TRUE);
        }
        return $this->call_parent('destroy', $entity);
    }
    public function _regex_replace($in)
    {
        global $wpdb;
        $from = 'FROM `' . $this->object->get_table_name() . '`';
        $out = str_replace('FROM', ', GROUP_CONCAT(CONCAT_WS(\'@@\', meta_key, meta_value)) AS \'extras\' FROM', $in);
        $out = str_replace($from, "{$from} LEFT OUTER JOIN `{$wpdb->postmeta}` ON `{$wpdb->postmeta}`.`post_id` = `extras_post_id` ", $out);
        return $out;
    }
    /**
     * Gets the generated query
     */
    public function get_generated_query()
    {
        // Add extras column
        if ($this->object->is_select_statement() && stripos($this->object->_select_clause, 'count(') === FALSE) {
            $table_name = $this->object->get_table_name();
            $primary_key = "{$table_name}.{$this->object->get_primary_key_column()}";
            if (stripos($this->object->_select_clause, 'DISTINCT') === FALSE) {
                $this->object->_select_clause = str_replace('SELECT', 'SELECT DISTINCT', $this->object->_select_clause);
            }
            $this->object->group_by($primary_key);
            $sql = $this->call_parent('get_generated_query');
            // Sections may be omitted by wrapping them in mysql/C style comments
            if (stripos($sql, '/*NGG_NO_EXTRAS_TABLE*/') !== FALSE) {
                $parts = explode('/*NGG_NO_EXTRAS_TABLE*/', $sql);
                foreach ($parts as $ndx => $row) {
                    if ($ndx % 2 != 0) {
                        continue;
                    }
                    $parts[$ndx] = $this->_regex_replace($row);
                }
                $sql = implode('', $parts);
            } else {
                $sql = $this->_regex_replace($sql);
            }
        } else {
            $sql = $this->call_parent('get_generated_query');
        }
        return $sql;
    }
    public function _convert_to_entity($entity)
    {
        // Add extra columns to entity
        if (isset($entity->extras)) {
            $extras = $entity->extras;
            unset($entity->extras);
            foreach (explode(',', $extras) as $extra) {
                if ($extra) {
                    list($key, $value) = explode('@@', $extra);
                    if ($this->object->has_defined_column($key) && !isset($entity->key)) {
                        $entity->{$key} = $value;
                    }
                }
            }
        }
        // Cast custom_post_id as integer
        if (isset($entity->extras_post_id)) {
            $entity->extras_post_id = intval($entity->extras_post_id);
        } else {
            $entity->extras_post_id = 0;
        }
        $retval = $this->call_parent('_convert_to_entity', $entity);
        return $entity;
    }
}