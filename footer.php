<?php
$tmpl_url = get_template_directory_uri();
 //Check for override 
if(file_exists(__DIR__ . '/footer-custom.php')) :
	include __DIR__ . '/footer-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo $tmp_url;?>/footer-custom.php</span> not found.</p>
<?php endif;?>
