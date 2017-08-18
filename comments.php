<?php
/*
NOTICE: Do not modify this file!
If you need to customize your template, create a file named comments-custom.php
*/
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/comments-custom.php')) :
	include __DIR__ . '/comments-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo $tmp_url;?>/comments-custom.php</span> not found.</p>
<?php endif;