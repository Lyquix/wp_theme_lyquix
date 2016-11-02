<?php
/**
 * The template for the sidebar containing the main widget area
 *
 */
if(file_exists(__DIR__ . '/sidebar-custom.php')) :
	include __DIR__ . '/sidebar-custom.php'; 
else : 
?>
<p>File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/sidebar-custom.php</span> not found.</p>
<?php endif;?>
