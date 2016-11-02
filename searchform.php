<?php
/**
 * Template for displaying search forms
 *
 */

if(file_exists(__DIR__ . '/searchform-custom.php')) :
	include __DIR__ . '/searchform-custom.php'; 
else :
?>
<p>File <span style="font-family: monospace;"><?php echo JPATH_BASE . '/templates/' . $this->template; ?>/searchform-custom.php</span> not found.</p>
<?php endif;?>
