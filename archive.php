<?php
/*
NOTICE: Do not modify this file!
If you need to customize your template, create a file named archive-custom.php
*/
$tmpl_url = get_template_directory_uri();
if(file_exists(__DIR__ . '/archive-custom.php')) :
	include __DIR__ . '/archive-custom.php'; 
else : 
?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<?php wp_head(); ?>
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo $tmp_url;?>/archive-custom.php</span> not found.
	</body>
</html>
<?php endif;