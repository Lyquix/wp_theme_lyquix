<?php
/**
 * The template for the sidebar containing the main widget area
 *
 */
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/sidebar-custom.php')) :
	include __DIR__ . '/sidebar-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo $tmp_url;?>/sidebar-custom.php</span> not found.</p>
<?php endif;?>
