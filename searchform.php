<?php
/*
NOTICE: Do not modify this file!
If you need to customize your template, create a file named searchform-custom.php
*/
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/searchform-custom.php')) :
	include __DIR__ . '/searchform-custom.php'; 
else :
?>
<p>File <span style="font-family: monospace;"><?php echo $tmp_url;?>/searchform-custom.php</span> not found.</p>
<?php endif;