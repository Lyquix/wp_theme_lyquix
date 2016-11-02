<?php
/**
 * The template for the content bottom widget areas on posts and pages
 *
 */
if(file_exists(__DIR__ . '/sidebar-content-bottom-custom.php')) :
	include __DIR__ . '/sidebar-content-bottom-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/sidebar-content-bottom-custom.php</span> not found.</p>
<?php endif;?>