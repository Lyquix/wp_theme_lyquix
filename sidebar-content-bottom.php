<?php
/*
NOTICE: Do not modify this file!
If you need to customize your template, create a file named sidebar-content-bottom-custom.php
*/
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/sidebar-content-bottom-custom.php')) :
	include __DIR__ . '/sidebar-content-bottom-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo $tmp_url;?>/sidebar-content-bottom-custom.php</span> not found.</p>
<?php endif;