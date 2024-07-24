<?php
/**
 * index.php - Main theme file
 *
 * @version     2.5.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

if(file_exists(get_template_directory(). '/custom.php')) :
	require get_template_directory() . '/custom.php';
else :
?><!DOCTYPE html>
<html>
	<body>
		File <span style="font-family: monospace;"><?php echo get_template_directory();?>/custom.php</span> not found.
	</body>
</html>
<?php endif;
