<div class="ngg-related-gallery">
    <?php foreach ($images as $image) {
        $controller = C_Display_Type_Controller::get_instance();
        $effect_code = $controller->get_effect_code($gallery->displayed_gallery);
        ?>
        <a href="<?php echo $image->imageURL; ?>"
           title="<?php echo stripslashes(M_I18N::translate($image->description, 'pic_' . $image->pid . '_description')); ?>"
           <?php echo $effect_code; ?>>
            <img title="<?php echo stripslashes(M_I18N::translate($image->alttext, 'pic_' . $image->pid . '_alttext')); ?>"
                 alt="<?php echo stripslashes(M_I18N::translate($image->alttext, 'pic_' . $image->pid . '_alttext')); ?>"
                 data-image-id="<?php echo esc_attr($image->{$image->id_field})?>"
                 src="<?php echo $image->thumbURL; ?>"/>
        </a>
    <?php } ?>
</div>