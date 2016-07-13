<?php
class A_Import_Folder_Form extends Mixin
{
    public function get_title()
    {
        return __('Import Folder', 'nggallery');
    }
    public function enqueue_static_resources()
    {
        wp_enqueue_style('jquery.filetree');
        wp_enqueue_style('ngg_progressbar');
        wp_enqueue_script('jquery.filetree');
        wp_enqueue_script('ngg_progressbar');
    }
    public function render()
    {
        return $this->object->render_partial('photocrati-nextgen_addgallery_page#import_folder', array('browse_sec_token' => C_WordPress_Security_Manager::get_instance()->get_request_token('nextgen_upload_image'), 'import_sec_token' => C_WordPress_Security_Manager::get_instance()->get_request_token('nextgen_upload_image')), TRUE);
    }
}
class A_Import_Media_Library_Form extends Mixin
{
    public function get_title()
    {
        return __('Import from WordPress Media Library', 'nggallery');
    }
    public function enqueue_static_resources()
    {
        wp_enqueue_media();
        wp_enqueue_script('nextgen_media_library_import-js');
        wp_enqueue_style('nextgen_media_library_import-css');
        $url = admin_url() . 'admin.php?page=nggallery-manage-gallery&mode=edit&gid={gid}';
        $i18n_array = array('admin_url' => admin_url(), 'title' => __('Import Images into NextGen Gallery', 'nggallery'), 'import_multiple' => __('Import %s images', 'nggallery'), 'import_singular' => __('Import 1 image', 'nggallery'), 'imported_multiple' => sprintf(__('{count} images were uploaded successfully. <a href="%s" target="_blank">Manage gallery</a>', 'nggallery'), $url), 'imported_singular' => sprintf(__('1 image was uploaded successfully. <a href="%s" target="_blank">Manage gallery</a>', 'nggallery'), $url), 'imported_none' => __('0 images were uploaded', 'nggallery'), 'progress_title' => __('Importing gallery', 'nggallery'), 'in_progress' => __('In Progress...', 'nggallery'), 'gritter_title' => __('Upload complete', 'nggallery'), 'gritter_error' => __('An unexpected error occured. This is most likely due to a server misconfiguration. Check your PHP error log or ask your hosting provider for assistance.', 'nggallery'));
        foreach (C_WordPress_Security_Manager::get_instance()->get_request_token('nextgen_upload_image')->get_request_list() as $name => $value) {
            $i18n_array['sectoken'][$name] = $value;
        }
        wp_localize_script('nextgen_media_library_import-js', 'ngg_importml_i18n', $i18n_array);
    }
    public function render()
    {
        $i18n = array('select-images-to-continue' => __('Please make a selection to continue', 'nggallery'), 'select-opener' => __('Select images', 'nggallery'), 'selected-image-import' => __('Import %d image(s)', 'nggallery'));
        return $this->object->render_partial('photocrati-nextgen_addgallery_page#import_media_library', array('i18n' => $i18n, 'galleries' => $this->object->get_galleries()), TRUE);
    }
    public function get_galleries()
    {
        $security = $this->get_registry()->get_utility('I_Security_Manager');
        $sec_actor = $security->get_current_actor();
        $galleries = array();
        if ($sec_actor->is_allowed('nextgen_edit_gallery')) {
            $galleries = C_Gallery_Mapper::get_instance()->find_all();
            if (!$sec_actor->is_allowed('nextgen_edit_gallery_unowned')) {
                $galleries_all = $galleries;
                $galleries = array();
                foreach ($galleries_all as $gallery) {
                    if ($sec_actor->is_user() && $sec_actor->get_entity_id() == (int) $gallery->author) {
                        $galleries[] = $gallery;
                    }
                }
            }
        }
        return $galleries;
    }
}
class A_NextGen_AddGallery_Ajax extends Mixin
{
    public function cookie_dump_action()
    {
        foreach ($_COOKIE as $key => &$value) {
            if (is_string($value)) {
                $value = stripslashes($value);
            }
        }
        return array('success' => 1, 'cookies' => $_COOKIE);
    }
    public function upload_image_action()
    {
        $retval = array();
        $created_gallery = FALSE;
        $gallery_id = intval($this->param('gallery_id'));
        $gallery_name = urldecode($this->param('gallery_name'));
        $gallery_mapper = C_Gallery_Mapper::get_instance();
        $error = FALSE;
        if ($this->validate_ajax_request('nextgen_upload_image', TRUE)) {
            // We need to create a gallery
            if ($gallery_id == 0) {
                if (strlen($gallery_name) > 0) {
                    $gallery = $gallery_mapper->create(array('title' => $gallery_name));
                    if (!$gallery->save()) {
                        $retval['error'] = $gallery->get_errors();
                        $error = TRUE;
                    } else {
                        $created_gallery = TRUE;
                        $gallery_id = $gallery->id();
                    }
                } else {
                    $error = TRUE;
                    $retval['error'] = __('No gallery name specified', 'nggallery');
                }
            }
            // Upload the image to the gallery
            if (!$error) {
                $retval['gallery_id'] = $gallery_id;
                $storage = C_Gallery_Storage::get_instance();
                try {
                    if ($storage->is_zip()) {
                        if ($results = $storage->upload_zip($gallery_id)) {
                            $retval = $results;
                        } else {
                            $retval['error'] = __('Failed to extract images from ZIP', 'nggallery');
                        }
                    } elseif ($image = $storage->upload_image($gallery_id)) {
                        $retval['image_ids'] = array($image->id());
                    } else {
                        $retval['error'] = __('Image generation failed', 'nggallery');
                        $error = TRUE;
                    }
                } catch (E_NggErrorException $ex) {
                    $retval['error'] = $ex->getMessage();
                    $error = TRUE;
                    if ($created_gallery) {
                        $gallery_mapper->destroy($gallery_id);
                    }
                } catch (Exception $ex) {
                    $retval['error'] = __('An unexpected error occured.', 'nggallery');
                    $retval['error_details'] = $ex->getMessage();
                    $error = TRUE;
                }
            }
        } else {
            $retval['error'] = __('No permissions to upload images. Try refreshing the page or ensuring that your user account has sufficient roles/privileges.', 'nggallery');
            $error = TRUE;
        }
        if ($error) {
            return $retval;
        } else {
            $retval['gallery_name'] = esc_html($gallery_name);
        }
        return $retval;
    }
    public function get_import_root_abspath()
    {
        if (is_multisite()) {
            $root = C_Gallery_Storage::get_instance()->get_upload_abspath();
        } else {
            $root = NGG_IMPORT_ROOT;
        }
        $root = str_replace('/', DIRECTORY_SEPARATOR, $root);
        return untrailingslashit($root);
    }
    public function browse_folder_action()
    {
        $retval = array();
        $html = array();
        if ($this->validate_ajax_request('nextgen_upload_image', TRUE)) {
            if ($dir = urldecode($this->param('dir'))) {
                $fs = C_Fs::get_instance();
                $root = $this->get_import_root_abspath();
                $browse_path = $fs->join_paths($root, $dir);
                if (strpos(realpath($browse_path), realpath($root)) !== FALSE) {
                    if (@file_exists($browse_path)) {
                        $files = scandir($browse_path);
                        natcasesort($files);
                        if (count($files) > 2) {
                            /* The 2 accounts for . and .. */
                            $html[] = '<ul class="jqueryFileTree" style="display: none;">';
                            foreach ($files as $file) {
                                $file_path = $fs->join_paths($browse_path, $file);
                                $rel_file_path = str_replace($root, '', $file_path);
                                if (@file_exists($file_path) && $file != '.' && $file != '..' && is_dir($file_path)) {
                                    $html[] = '<li class="directory collapsed"><a href="#" rel="' . htmlentities($rel_file_path) . '/">' . htmlentities($file) . '</a></li>';
                                }
                            }
                            $html[] = '</ul>';
                        }
                        $retval['html'] = implode('
', $html);
                    } else {
                        $retval['error'] = __('Directory does not exist.', 'nggallery');
                    }
                } else {
                    $retval['error'] = __('No permissions to browse folders. Try refreshing the page or ensuring that your user account has sufficient roles/privileges.', 'nggallery');
                }
            } else {
                $retval['error'] = __('No directory specified.', 'nggallery');
            }
        } else {
            $retval['error'] = __('No permissions to browse folders. Try refreshing the page or ensuring that your user account has sufficient roles/privileges.', 'nggallery');
        }
        return $retval;
    }
    public function import_folder_action()
    {
        $retval = array();
        if ($this->validate_ajax_request('nextgen_upload_image', TRUE)) {
            if ($folder = $this->param('folder')) {
                $storage = C_Gallery_Storage::get_instance();
                $fs = C_Fs::get_instance();
                try {
                    $keep_files = $this->param('keep_location') == 'on';
                    $root = $this->get_import_root_abspath();
                    $import_path = $fs->join_paths($root, $folder);
                    if (strpos(realpath($import_path), realpath($root)) !== FALSE) {
                        $retval = $storage->import_gallery_from_fs($import_path, FALSE, !$keep_files);
                        if (!$retval) {
                            $retval = array('error' => 'Could not import folder. No images found.');
                        }
                    } else {
                        $retval['error'] = __('No permissions to import folders. Try refreshing the page or ensuring that your user account has sufficient roles/privileges.', 'nggallery');
                    }
                } catch (E_NggErrorException $ex) {
                    $retval['error'] = $ex->getMessage();
                } catch (Exception $ex) {
                    $retval['error'] = __('An unexpected error occured.', 'nggallery');
                    $retval['error_details'] = $ex->getMessage();
                }
            } else {
                $retval['error'] = __('No folder specified', 'nggallery');
            }
        } else {
            $retval['error'] = __('No permissions to import folders. Try refreshing the page or ensuring that your user account has sufficient roles/privileges.', 'nggallery');
        }
        return $retval;
    }
    public function import_media_library_action()
    {
        $retval = array();
        $created_gallery = FALSE;
        $gallery_id = intval($this->param('gallery_id'));
        $gallery_name = urldecode($this->param('gallery_name'));
        $gallery_mapper = C_Gallery_Mapper::get_instance();
        $image_mapper = C_Image_Mapper::get_instance();
        $attachment_ids = $this->param('attachment_ids');
        if ($this->validate_ajax_request('nextgen_upload_image', TRUE)) {
            if (empty($attachment_ids) || !is_array($attachment_ids)) {
                $retval['error'] = __('An unexpected error occured.', 'nggallery');
            }
            if (empty($retval['error']) && $gallery_id == 0) {
                if (strlen($gallery_name) > 0) {
                    $gallery = $gallery_mapper->create(array('title' => $gallery_name));
                    if (!$gallery->save()) {
                        $retval['error'] = $gallery->get_errors();
                    } else {
                        $created_gallery = TRUE;
                        $gallery_id = $gallery->id();
                    }
                } else {
                    $retval['error'] = __('No gallery name specified', 'nggallery');
                }
            }
            if (empty($retval['error'])) {
                $retval['gallery_id'] = $gallery_id;
                $storage = C_Gallery_Storage::get_instance();
                foreach ($attachment_ids as $id) {
                    try {
                        $abspath = get_attached_file($id);
                        $file_data = @file_get_contents($abspath);
                        $file_name = M_I18n::mb_basename($abspath);
                        $attachment = get_post($id);
                        if (empty($file_data)) {
                            $retval['error'] = __('Image generation failed', 'nggallery');
                            break;
                        }
                        $image = $storage->upload_base64_image($gallery_id, $file_data, $file_name);
                        if ($image) {
                            // Potentially import metadata from WordPress
                            $image = $image_mapper->find($image->id());
                            if (!empty($attachment->post_excerpt)) {
                                $image->alttext = $attachment->post_excerpt;
                            }
                            if (!empty($attachment->post_content)) {
                                $image->description = $attachment->post_content;
                            }
                            $image = apply_filters('ngg_medialibrary_imported_image', $image, $attachment);
                            $image_mapper->save($image);
                        } else {
                            $retval['error'] = __('Image generation failed', 'nggallery');
                            break;
                        }
                        $retval['image_ids'][] = $image->{$image->id_field};
                    } catch (E_NggErrorException $ex) {
                        $retval['error'] = $ex->getMessage();
                        if ($created_gallery) {
                            $gallery_mapper->destroy($gallery_id);
                        }
                    } catch (Exception $ex) {
                        $retval['error'] = __('An unexpected error occured.', 'nggallery');
                        $retval['error_details'] = $ex->getMessage();
                    }
                }
            }
        } else {
            $retval['error'] = __('No permissions to upload images. Try refreshing the page or ensuring that your user account has sufficient roles/privileges.', 'nggallery');
        }
        if (!empty($retval['error'])) {
            return $retval;
        } else {
            $retval['gallery_name'] = esc_html($gallery_name);
        }
        return $retval;
    }
}
class A_NextGen_AddGallery_Controller extends Mixin
{
    public function get_page_title()
    {
        return __('Add Gallery / Images', 'nggallery');
    }
    public function get_required_permission()
    {
        return 'NextGEN Upload images';
    }
    public function enqueue_backend_resources()
    {
        $this->call_parent('enqueue_backend_resources');
        wp_enqueue_style('nextgen_addgallery_page');
        wp_enqueue_script('nextgen_addgallery_page');
        wp_enqueue_script('frame_event_publisher');
    }
    public function show_save_button()
    {
        return FALSE;
    }
}
class A_NextGen_AddGallery_Pages extends Mixin
{
    public function setup()
    {
        $this->object->add(NGG_ADD_GALLERY_SLUG, array('adapter' => 'A_NextGen_AddGallery_Controller', 'parent' => NGGFOLDER, 'add_menu' => TRUE, 'before' => 'nggallery-manage-gallery'));
        return $this->call_parent('setup');
    }
}
class A_Upload_Images_Form extends Mixin
{
    public function get_title()
    {
        return __('Upload Images', 'nggallery');
    }
    public function get_i18n_strings()
    {
        return array('no_image_uploaded' => __('No images were uploaded successfully.', 'nggallery'), 'one_image_uploaded' => __('1 image was uploaded successfully.', 'nggallery'), 'x_images_uploaded' => __('{count} images were uploaded successfully.', 'ngallery'), 'manage_gallery' => __('Manage gallery', 'nggallery'));
    }
    /**
     * Plupload stores its i18n JS *mostly* as "en.js" or "ar.js" - but some as zh_CN.js so we must check both if the
     * first does not match.
     *
     * @return bool|string
     */
    public function _find_plupload_i18n()
    {
        $fs = C_Fs::get_instance();
        $router = C_Router::get_instance();
        $locale = get_locale();
        $dir = $fs->find_static_abspath('photocrati-nextgen_addgallery_page#plupload-2.1.1/i18n') . DIRECTORY_SEPARATOR;
        $tmp = explode('_', $locale, 2);
        $retval = FALSE;
        if (file_exists($dir . $tmp[0] . '.js')) {
            $retval = $tmp[0];
        } else {
            if (file_exists($dir . $locale . '.js')) {
                $retval = $locale;
            }
        }
        if ($retval) {
            $retval = $router->get_static_url('photocrati-nextgen_addgallery_page#plupload-2.1.1/i18n/' . $retval . '.js');
        }
        return $retval;
    }
    public function enqueue_static_resources()
    {
        wp_enqueue_style('ngg.plupload.queue');
        wp_enqueue_script('browserplus');
        wp_enqueue_script('ngg.plupload.queue');
        wp_localize_script('ngg.plupload.queue', 'NggUploadImages_i18n', $this->object->get_i18n_strings());
        $i18n = $this->_find_plupload_i18n();
        if (!empty($i18n)) {
            wp_enqueue_script('ngg.plupload.i18n', $i18n, array('ngg.plupload.full'), NGG_SCRIPT_VERSION);
        }
    }
    public function render()
    {
        return $this->object->render_partial('photocrati-nextgen_addgallery_page#upload_images', array('plupload_options' => json_encode($this->object->get_plupload_options()), 'galleries' => $this->object->get_galleries(), 'sec_token' => C_WordPress_Security_Manager::get_instance()->get_request_token('nextgen_upload_image')), TRUE);
    }
    public function get_plupload_options()
    {
        $retval = array();
        $retval['runtimes'] = 'gears,browserplus,html5,flash,silverlight,html4';
        $retval['max_file_size'] = strval(round((int) wp_max_upload_size() / 1024)) . 'kb';
        $retval['filters'] = $this->object->get_plupload_filters();
        $retval['flash_swf_url'] = includes_url('js/plupload/plupload.flash.swf');
        $retval['silverlight_xap_url'] = includes_url('js/plupload/plupload.silverlight.xap');
        $retval['debug'] = TRUE;
        $retval['prevent_duplicates'] = TRUE;
        return $retval;
    }
    public function get_plupload_filters()
    {
        $retval = new stdClass();
        $retval->mime_types = array();
        $imgs = new stdClass();
        $imgs->title = 'Image files';
        $imgs->extensions = 'jpg,jpeg,gif,png,JPG,JPEG,GIF,PNG';
        $retval->mime_types[] = $imgs;
        $settings = C_NextGen_Settings::get_instance();
        if (!is_multisite() || is_multisite() && $settings->get('wpmuZipUpload')) {
            $zips = new stdClass();
            $zips->title = 'Zip files';
            $zips->extensions = 'zip,ZIP';
            $retval->mime_types[] = $zips;
        }
        $retval->xss_protection = TRUE;
        return $retval;
    }
    public function get_galleries()
    {
        $security = $this->get_registry()->get_utility('I_Security_Manager');
        $sec_actor = $security->get_current_actor();
        $galleries = array();
        if ($sec_actor->is_allowed('nextgen_edit_gallery')) {
            $gallery_mapper = C_Gallery_Mapper::get_instance();
            $galleries = $gallery_mapper->find_all();
            if (!$sec_actor->is_allowed('nextgen_edit_gallery_unowned')) {
                $galleries_all = $galleries;
                $galleries = array();
                foreach ($galleries_all as $gallery) {
                    if ($sec_actor->is_user() && $sec_actor->get_entity_id() == (int) $gallery->author) {
                        $galleries[] = $gallery;
                    }
                }
            }
        }
        return $galleries;
    }
}