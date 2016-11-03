<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * NOTICE: Do not modify this file!
 * If you need to customize your template, create a file named custom-index.php
**/

//Check for override 
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/header-custom.php')) :
	include __DIR__ . '/header-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo $tmp_url;?>/header-custom.php</span> not found.
	</body>
</html>
<?php endif;?>