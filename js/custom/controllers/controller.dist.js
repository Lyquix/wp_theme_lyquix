/**
 * controller.dist.js - Sample Vue controller
 *
 * @version     2.0.0
 * @package     wp_lyquix_theme
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2018 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_lyquix_theme
 */

document.addEventListener('DOMContentLoaded', function(){
	if(document.querySelector('#my-controller')) {
		var myapp = new Vue({
			el: '#my-controller',
			data: {
				message: 'Hello World!'
			}
		});
	}
});