<?php
/**
 * The template for displaying all single posts and attachments
 *
 */
if(file_exists(__DIR__ . '/single-custom.php')) :
	include __DIR__ . '/single-custom.php'; 
else : 
?><!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" >
	<head>
		<jdoc:include type="head" />
	</head>
	<body>
		File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/single-custom.php</span> not found.
	</body>
</html>
<?php endif;?>