jQuery(document).ready(function () {
	var slideshowData = jQuery(".cycle-slideshow").data();	
	
	var cycleSlides = "> div.testimonial_slide";
	var cycleTimeout = "4000";
	var cycleFx = "fade";	
	var cycleAutoHeight = "container";
	var cycleRandom = "false";
	var pauseOnHover = "false";
	var cyclePrev = "false";
	var cycleNext = "false";
	var paused = "false";
	
	if (null != slideshowData && typeof slideshowData != "undefined"){
		if (null != slideshowData.cycleSlides && typeof slideshowData.cycleSlides != 'undefined') {
			cycleSlides = slideshowData.cycleSlides;
		} 
		if (null != slideshowData.cycleTimeout && typeof slideshowData.cycleTimeout != 'undefined') {
			cycleTimeout = slideshowData.cycleTimeout;
		}
		if (null != slideshowData.cycleFx && typeof slideshowData.cycleFx != 'undefined') {
			cycleFx = slideshowData.cycleFx;
		}
		if (null != slideshowData.slideshowData && typeof slideshowData.slideshowData != 'undefined') {
			cycleAutoHeight = slideshowData.cycleAutoHeight;
		}
		if (null != slideshowData.cycleRandom && typeof slideshowData.cycleRandom != 'undefined') {
			cycleRandom = slideshowData.cycleRandom;
		}
		if (null != slideshowData.pauseOnHover && typeof slideshowData.pauseOnHover != 'undefined') {
			pauseOnHover = slideshowData.pauseOnHover;
		}
		if (null != slideshowData.paused && typeof slideshowData.paused != 'undefined') {
			paused = slideshowData.paused;
		}
		if (null != slideshowData.cyclePrev && typeof slideshowData.cyclePrev != 'undefined') {
			cyclePrev = slideshowData.cyclePrev;
		}
		if (null != slideshowData.cycleNext && typeof slideshowData.cycleNext != 'undefined') {
			cycleNext = slideshowData.cycleNext;
		}
	}
	
	jQuery(".cycle-slideshow").gp_cycle({
		'slides': cycleSlides,
		'timeout': cycleTimeout,
		'fx': cycleFx,
		'auto-height': cycleAutoHeight,
		'random': cycleRandom,
		'pause-on-hover': pauseOnHover,
		'paused': paused,
		'cycle-prev': cyclePrev,
		'cycle-next': cycleNext
	});
});