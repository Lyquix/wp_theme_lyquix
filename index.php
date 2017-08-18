<?php
/*
NOTICE: Do not modify this file!
If you need to customize your template, create a file named index-custom.php
*/
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/index-custom.php')) :
	include __DIR__ . '/index-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo $tmp_url;?>/index-custom.php</span> not found.
	</body>
</html>
<?php endif;