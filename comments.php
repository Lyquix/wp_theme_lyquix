<?php
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/comments-custom.php')) :
	include __DIR__ . '/comments-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo $tmp_url;?>/comments-custom.php</span> not found.</p>
<?php endif;?>
