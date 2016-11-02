<?php
/**
 * The template for displaying image attachments
 *
 */
if(file_exists(__DIR__ . '/image-custom.php')) :
	include __DIR__ . '/image-custom.php'; 
else : 
??><!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/image-custom.php</span> not found.
	</body>
</html>
<?php endif;?>