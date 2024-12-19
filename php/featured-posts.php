<?php
// Register the meta field
function register_featured_meta() {
    $post_types = get_post_types(['public' => true], 'names'); // Get all public post types
    foreach ($post_types as $post_type) {
        register_post_meta($post_type, '_is_featured', [
            'type'         => 'boolean',
            'single'       => true,
            'default'      => false,
            'show_in_rest' => true, // Make it available in the REST API for Gutenberg
        ]);
    }
}
add_action('init', 'register_featured_meta');

// Add a meta box for the Classic Editor
function add_featured_meta_box_classic() {
	$post_types = get_post_types(['public' => true], 'names'); // Get all public post types
	foreach ($post_types as $post_type) {
			add_meta_box(
					'featured_meta_box_classic',
					__('Featured', 'featured-posts'),
					'render_featured_meta_box_classic',
					$post_type,
					'side',
					'core'
			);
	}
}
add_action('add_meta_boxes', 'add_featured_meta_box_classic');

function enqueue_custom_meta_field_script() {
	wp_enqueue_script(
			'custom-meta-field',
			get_template_directory_uri() . '/js/featured-posts.js', // Update with your file path
			['wp-plugins', 'wp-edit-post', 'wp-components', 'wp-data', 'wp-element'],
			filemtime(get_template_directory() . '/js/featured-posts.js'), // Cache-busting
			true
	);

	// Add type="module" to the script
	add_filter('script_loader_tag', function($tag, $handle, $src) {
		if ('custom-meta-field' === $handle) {
				$tag = str_replace(' src', ' type="module" src', $tag);
		}
		return $tag;
}, 10, 3);
}
add_action('enqueue_block_editor_assets', 'enqueue_custom_meta_field_script');

// Render the meta box for Classic Editor
function render_featured_meta_box_classic($post) {
	$value = get_post_meta($post->ID, '_is_featured', true);
	wp_nonce_field('save_featured_meta_classic', 'featured_meta_nonce_classic');
	?>
	<label>
			<input type="checkbox" name="is_featured" value="1" <?php checked($value, '1'); ?>>
			<?php _e('Featured', 'featured-posts'); ?>
	</label>
	<?php
}

// Save the meta value for Classic Editor
function save_featured_meta_classic($post_id) {
	if (!isset($_POST['featured_meta_nonce_classic']) || !wp_verify_nonce($_POST['featured_meta_nonce_classic'], 'save_featured_meta_classic')) {
			return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
	}

	if (!current_user_can('edit_post', $post_id)) {
			return;
	}

	if (isset($_POST['is_featured'])) {
			update_post_meta($post_id, '_is_featured', '1');
	} else {
			delete_post_meta($post_id, '_is_featured');
	}
}
add_action('save_post', 'save_featured_meta_classic');

/*Code to add featured as a custom column in the post view*/
add_filter('manage_portfolio_posts_columns', function($columns) {
	$columns['featured'] = __('Featured', 'textdomain');
	return $columns;
});

// Populate the custom column
add_action('manage_portfolio_posts_custom_column', function($column, $post_id) {
	if ($column === 'featured') {
		$meta_value = get_post_meta($post_id, '_is_featured', true);
		$checked = $meta_value ? 'checked' : '';
		echo '<input type="checkbox" class="featured-checkbox" data-post-id="' . esc_attr($post_id) . '" ' . esc_attr($checked) . ' />';
	}
}, 10, 2);

// Enqueue JavaScript to handle checkbox interaction
add_action('admin_enqueue_scripts', function() {
	wp_enqueue_script('featured-checkbox-handler', get_template_directory_uri() . '/js/custom-meta-checkbox.js?v=' . filemtime(get_template_directory() . '/js/custom-meta-checkbox.js'), ['jquery'], null, true);
	wp_localize_script('featured-checkbox-handler', 'CustomMetaAjax', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('featured_nonce')
	]);
});

// Handle AJAX request to update meta field
add_action('wp_ajax_update_featured', function() {
	check_ajax_referer('featured_nonce', 'nonce');

	$post_id = intval($_POST['post_id']);
	$new_value = isset($_POST['checked']) && $_POST['checked'] === 'true' ? 1 : 0;

	if (current_user_can('edit_post', $post_id)) {
			update_post_meta($post_id, '_is_featured', $new_value);
			wp_send_json_success();
	} else {
			wp_send_json_error('You do not have permission to edit this post.');
	}
});

// Modify the query for sorting
add_action('pre_get_posts', function($query) {
	if (!is_admin() || !$query->is_main_query()) {
			return;
	}

	$orderby = $query->get('orderby');
	if ($orderby === 'featured') {
			$query->set('meta_key', '_is_featured');
			$query->set('orderby', 'meta_value');
	}
});

// Filter posts by custom meta field
add_action('pre_get_posts', function($query) {
	if (!is_admin() || !$query->is_main_query()) {
			return;
	}

	if (!empty($_GET['featured_filter'])) {
			$query->set('meta_query', [
					[
							'key' => '_is_featured',
							'value' => sanitize_text_field($_GET['featured_filter']),
							'compare' => '=',
					],
			]);
	}
});

// Helper function to get unique meta values
function get_meta_values($meta_key, $post_type) {
	global $wpdb;
	$results = $wpdb->get_col($wpdb->prepare("
			SELECT DISTINCT meta_value
			FROM {$wpdb->postmeta}
			WHERE meta_key = %s
	", $meta_key));

	return $results;
}

// Add a custom "subsubsub" menu item
add_filter('views_edit-portfolio', function($views) {
		global $wpdb;
    // Get the current meta filter query parameter
    $current = isset($_GET['featured_filter']) ? sanitize_text_field($_GET['featured_filter']) : '';

    // Base URL for links
    $base_url = admin_url('edit.php?post_type=portfolio');
		$with_meta_count = (int) $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(*) FROM {$wpdb->posts}
			INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
			WHERE {$wpdb->posts}.post_type = %s
			AND {$wpdb->posts}.post_status IN ('publish', 'draft', 'pending')
			AND {$wpdb->postmeta}.meta_key = %s
			AND {$wpdb->postmeta}.meta_value = %s
	", 'portfolio', '_is_featured', '1'));
    // Add a custom filter view for "With Checkbox Checked"
    $views['with_meta'] = sprintf(
        '<a href="%s" class="%s">Featured <span class="count">('.$with_meta_count.')</span></a>',
        esc_url(add_query_arg(['featured_filter' => '1'], $base_url)),
        $current === '1' ? 'current' : ''
    );

    return $views;
});

// Modify the query to filter posts based on the selected subsubsub menu item
add_action('pre_get_posts', function($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'portfolio') {
        return;
    }

    // Check if the featured_filter is set
    if (isset($_GET['featured_filter'])) {
        $filter_value = sanitize_text_field($_GET['featured_filter']);

        $query->set('meta_query', [
            [
                'key' => '_is_featured',
                'value' => $filter_value,
                'compare' => '='
            ]
        ]);
    }
});
