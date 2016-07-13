<?php $this->start_element('nextgen_gallery.gallery_container', 'container', $displayed_gallery); ?>

<?php if ($show_thumbnail_link) { ?>
	<!-- Thumbnails Link -->
	<div class="slideshowlink">
        <a href='<?php echo esc_attr($thumbnail_link); ?>'><?php echo esc_html($thumbnail_link_text); ?></a>
	</div>
<?php } ?>

<div class="ngg-slideshow-image-list ngg-slideshow-nojs" id="<?php echo esc_attr($anchor); ?>-image-list">
	<?php
	$this->include_template('photocrati-nextgen_gallery_display#list/before');
	for ($i = 0; $i < count($images); $i++) {
		// Determine image dimensions
		$image = $images[$i];
		$image_size = $storage->get_original_dimensions($image);
		if ($image_size == null)
		{
			$image_size['width'] = $image->meta_data['width'];
			$image_size['height'] = $image->meta_data['height'];
		}

		// Determine whether an image is hidden or not
		if (isset($image->hidden) && $image->hidden) {
			$image->style = 'style="display: none;"';
		}
		else {
			$image->style = '';
		}

		// Determine image aspect ratio
		$image_ratio = $image_size['width'] / $image_size['height'];
		if ($image_ratio > $aspect_ratio)
		{
			if ($image_size['width'] > $gallery_width)
			{
				$image_size['width'] = $gallery_width;
				$image_size['height'] = (int) round($gallery_width / $image_ratio);
			}
		}
		else {
			if ($image_size['height'] > $gallery_height)
			{
				$image_size['width'] = (int) round($gallery_height * $image_ratio);
				$image_size['height'] = $gallery_height;
			}
		}

		$template_params = array(
			'index' => $i,
			'class' => 'ngg-gallery-slideshow-image'
		);
		$template_params = array_merge(get_defined_vars(), $template_params);
		$this->include_template('photocrati-nextgen_gallery_display#image/before', $template_params);
		?>
		<img data-image-id='<?php echo esc_attr($image->pid); ?>'
		     title="<?php echo esc_attr($image->description)?>"
		     alt="<?php echo esc_attr($image->alttext)?>"
		     src="<?php echo esc_attr($storage->get_image_url($image, 'full', TRUE))?>"
		     width="<?php echo esc_attr($image_size['width'])?>"
		     height="<?php echo esc_attr($image_size['height'])?>"/>
		<?php
		$this->include_template('photocrati-nextgen_gallery_display#image/after', $template_params);
	}
	$this->include_template('photocrati-nextgen_gallery_display#list/after');
	?>
</div>
<?php $this->include_template('photocrati-nextgen_gallery_display#container/before'); ?>
<div class="ngg-galleryoverview ngg-slideshow"
     id="<?php echo esc_attr($anchor); ?>"
     data-placeholder="<?php echo nextgen_esc_url($placeholder); ?>"
     style="max-width: <?php echo esc_attr($gallery_width); ?>px; max-height: <?php echo esc_attr($gallery_height); ?>px;">
	<div class="ngg-slideshow-loader"
	     id="<?php echo esc_attr($anchor); ?>-loader"
	     style="width: <?php echo esc_attr($gallery_width); ?>px; height: <?php echo esc_attr($gallery_height); ?>px;">
		<img src="<?php echo esc_attr(NGGALLERY_URLPATH); ?>images/loader.gif" alt=""/>
	</div>
</div>
<?php $this->include_template('photocrati-nextgen_gallery_display#container/after'); ?>
<script type="text/javascript">
	jQuery('#<?php echo esc_attr($anchor); ?>-image-list').hide().removeClass('ngg-slideshow-nojs');
	jQuery(function($) {
		jQuery('#<?php echo esc_attr($anchor); ?>').nggShowSlideshow({
			id: '<?php echo esc_attr($displayed_gallery_id); ?>',
			fx: '<?php echo esc_attr($cycle_effect); ?>',
			width: <?php echo esc_attr($gallery_width); ?>,
			height: <?php echo esc_attr($gallery_height); ?>,
			domain: '<?php echo esc_attr(trailingslashit(home_url())); ?>',
			timeout: <?php echo esc_attr(intval($cycle_interval) * 1000); ?>
		});
	});
</script>
<?php $this->end_element(); ?>
