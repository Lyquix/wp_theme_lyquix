<?php 
add_action('admin_menu', 'lqx_add_pages');

// action function for above hook
function lqx_add_pages() {
    // Add a new submenu under Settings:
    add_dashboard_page(__('Theme Options','menu-theme-options'), __('Theme Options','menu-theme-options'), 'manage_options', 'themeoptions', 'lqx_dashboard');

    // Add a new submenu under Tools:
    add_management_page( __('Test Tools','menu-test'), __('Test Tools','menu-test'), 'manage_options', 'testtools', 'lqx_dashboard');

    // Add a new top-level menu (ill-advised):
    add_menu_page(__('Test Toplevel','menu-test'), __('Test Toplevel','menu-test'), 'manage_options', 'lqx-top-level-handle', 'lqx_toplevel_page' );

    // Add a submenu to the custom top-level menu:
    add_submenu_page('lqx-top-level-handle', __('Test Sublevel','menu-theme-options'), __('Test Sublevel','menu-theme-options'), 'manage_options', 'sub-page', 'lqx_sublevel_page');

    // Add a second submenu to the custom top-level menu:
    //add_submenu_page('lqx-top-level-handle', __('Test Sublevel 2','menu-test'), __('Test Sublevel 2','menu-test'), 'manage_options', 'sub-page2', 'lqx_sublevel_page2');
    add_action( 'admin_init', 'register_mysettings' );
}

// lqx_settings_page() displays the page content for the Test Settings submenu
function lqx_dashboard() {
    echo "<h2>" . __( 'Theme Options', 'menu-test' ) . "</h2>";
?>
<form method="post" action="options.php">
	<?php settings_fields('myoption-group');
	do_settings_sections('myoption-group');
	?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Google Analytics Code</th>
			<td>
			<input type="text" class="mk-upload-url" name="analytics_code" value="<?php echo esc_attr(get_option('analytics_code')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Header Logo</th>
			<td>
			<input type="text" class="mk-upload-url" name="header_logo" value="<?php echo esc_attr(get_option('header_logo')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Header Class</th>
			<td>
			<input type="text" name="header_class" value="<?php echo esc_attr(get_option('header_class')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Main Nav Class</th>
			<td>
			<input type="text" name="main_nav_class" value="<?php echo esc_attr(get_option('main_nav_class')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Main Wrapper Class</th>
			<td>
			<input type="text" name="main_wrapper_class" value="<?php echo esc_attr(get_option('main_wrapper_class')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Content Wrapper Class</th>
			<td>
			<input type="text" name="content_wrapper_class" value="<?php echo esc_attr(get_option('content_wrapper_class')); ?>" />
			</td>
		</tr>	
		<tr valign="top">
			<th scope="row">Primary Content Class</th>
			<td>
			<input type="text" name="primary_class" value="<?php echo esc_attr(get_option('primary_class')); ?>" />
			</td>
		</tr>	
		<tr valign="top">
			<th scope="row">Secondary Class</th>
			<td>
			<input type="text" name="secondary_class" value="<?php echo esc_attr(get_option('secondary_class')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Widget Area 1 Class</th>
			<td>
			<input type="text" name="widget_area_1_class" value="<?php echo esc_attr(get_option('widget_area_1_class')); ?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Widget Area 2 Class</th>
			<td>
			<input type="text" name="widget_area_2_class" value="<?php echo esc_attr(get_option('widget_area_2_class')); ?>" />
			</td>
		</tr>		
		<tr valign="top">
			<th scope="row">Footer Class</th>
			<td>
			<input type="text" name="footer_class" value="<?php echo esc_attr(get_option('footer_class')); ?>" />
			</td>
		</tr>
	</table>
	<?php submit_button(); ?>

</form>
<?php }

	// lqx_tools_page() displays the page content for the Test Tools submenu
	function lqx_tools_page() {
	echo "<h2>" . __( 'Test Tools', 'menu-test' ) . "</h2>";
	}

	// lqx_toplevel_page() displays the page content for the custom Test Toplevel menu
	function lqx_toplevel_page() {
	echo "<h2>" . __( 'Test Toplevel', 'menu-test' ) . "</h2>";
	}

	// lqx_sublevel_page() displays the page content for the first submenu
	// of the custom Test Toplevel menu
	function lqx_sublevel_page() {
	echo "<h2>" . __( 'Test Sublevel', 'menu-test' ) . "</h2>";
	}

	// lqx_sublevel_page2() displays the page content for the second submenu
	// of the custom Test Toplevel menu
	function lqx_sublevel_page2() {
	echo "<h2>" . __( 'Test Sublevel2', 'menu-test' ) . "</h2>";
	}
	function register_mysettings() { // whitelist options
	register_setting( 'myoption-group', 'analytics_code' );
	register_setting( 'myoption-group', 'main_wrapper_class' );
	register_setting( 'myoption-group', 'content_wrapper_class' );
	register_setting( 'myoption-group', 'header_class' );
	register_setting( 'myoption-group', 'main_nav_class' );
	register_setting( 'myoption-group', 'footer_class' );
	register_setting( 'myoption-group', 'primary_class' );
	register_setting( 'myoption-group', 'widget_area_1_class' );
	register_setting( 'myoption-group', 'widget_area_2_class' );
	register_setting( 'myoption-group', 'secondary_class' );
	}
?>