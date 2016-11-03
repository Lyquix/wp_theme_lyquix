<?php
/**
 * The template for displaying all single posts and attachments
 *
 */
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/single-custom.php')) :
	include __DIR__ . '/single-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo $tmp_url;?>/single-custom.php</span> not found.
	</body>
</html>
<?php endif;?>