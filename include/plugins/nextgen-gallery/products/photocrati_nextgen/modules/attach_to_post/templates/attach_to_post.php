<!DOCTYPE html>
<html>
    <head>
        <title><?php esc_html_e($page_title)?></title>
		<?php
			wp_print_styles();
			wp_print_scripts();
		?>
    </head>
	<body>
		<div id="attach_to_post_tabs">
            <div class='ui-tabs-icon'><span class="nextgen_logo"><?php esc_html_e('NextGEN')?></span> <span class="nextgen_logo_sub"><?php esc_html_e('Gallery')?></span></div>
			<ul>
            <?php foreach ($tabs as $id => $tab_params): ?>
				<li>
					<a href='#<?php echo esc_attr($id)?>'>
						<?php esc_html_e($tab_params['title']) ?>
					</a>
				</li>
			<?php endforeach ?>
			</ul>
			<?php reset($tabs); foreach ($tabs as $id => $tab_params): ?>
			<div class="main_menu_tab" id="<?php echo esc_attr($id) ?>"><?php echo $tab_params['content'] ?></div>
			<?php endforeach ?>
		</div>

        <div id="adminmenu" style="display:none;" data-wp-responsive="true"></div>
		<?php wp_print_footer_scripts() ?>
	</body>
</html>
