<?php
class A_I18N_Album_Translation extends Mixin
{
    public function set_defaults($entity)
    {
        $this->call_parent('set_defaults', $entity);
        if (!is_admin()) {
            if (!empty($entity->name)) {
                $entity->name = M_I18N::translate($entity->name, 'album_' . $entity->{$entity->id_field} . '_name');
            }
            if (!empty($entity->albumdesc)) {
                $entity->albumdesc = M_I18N::translate($entity->albumdesc, 'album_' . $entity->{$entity->id_field} . '_description');
            }
            // these fields are set when the album is a child to another album
            if (!empty($entity->title)) {
                $entity->title = M_I18N::translate($entity->title, 'album_' . $entity->{$entity->id_field} . '_name');
            }
            if (!empty($entity->galdesc)) {
                $entity->galdesc = M_I18N::translate($entity->galdesc, 'album_' . $entity->{$entity->id_field} . '_description');
            }
        }
    }
}
class A_I18N_Displayed_Gallery_Translation extends Mixin
{
    public function _get_image_entities($source_obj, $limit, $offset, $id_only, $returns)
    {
        $results = $this->call_parent('_get_image_entities', $source_obj, $limit, $offset, $id_only, $returns);
        if (!is_admin() && in_array('image', $source_obj->returns)) {
            foreach ($results as $entity) {
                if (!empty($entity->description)) {
                    $entity->description = M_I18N::translate($entity->description, 'pic_' . $entity->pid . '_description');
                }
                if (!empty($entity->alttext)) {
                    $entity->alttext = M_I18N::translate($entity->alttext, 'pic_' . $entity->pid . '_alttext');
                }
            }
        }
        return $results;
    }
}
class A_I18N_Gallery_Translation extends Mixin
{
    public function set_defaults($entity)
    {
        $this->call_parent('set_defaults', $entity);
        if (!is_admin()) {
            if (!empty($entity->title)) {
                $entity->title = M_I18N::translate($entity->title, 'gallery_' . $entity->{$entity->id_field} . '_name');
            }
            if (!empty($entity->galdesc)) {
                $entity->galdesc = M_I18N::translate($entity->galdesc, 'gallery_' . $entity->{$entity->id_field} . '_description');
            }
        }
    }
}
class A_I18N_Image_Translation extends Mixin
{
    public function set_defaults($entity)
    {
        $this->call_parent('set_defaults', $entity);
        if (!is_admin()) {
            if (!empty($entity->description)) {
                $entity->description = M_I18N::translate($entity->description, 'pic_' . $entity->{$entity->id_field} . '_description');
            }
            if (!empty($entity->alttext)) {
                $entity->alttext = M_I18N::translate($entity->alttext, 'pic_' . $entity->{$entity->id_field} . '_alttext');
            }
        }
    }
}
class A_I18N_Routing_App extends Mixin
{
    public function execute_route_handler($handler)
    {
        if (!empty($GLOBALS['q_config']) && defined('QTRANS_INIT')) {
            global $q_config;
            $q_config['hide_untranslated'] = 0;
        }
        return $this->call_parent('execute_route_handler', $handler);
    }
}