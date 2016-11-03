<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 */
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/page-custom.php')) :
	include __DIR__ . '/page-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo $tmp_url;?>/page-custom.php</span> not found.
	</body>
</html>
<?php endif;?>
