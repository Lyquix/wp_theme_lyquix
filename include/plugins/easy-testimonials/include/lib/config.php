<?php
	//array of themes that are available
	$theme_array = array(
		'dark_style','light_style','clean_style','no_style','bubble_style','bubble_style-brown','bubble_style-pink','bubble_style-blue-orange','bubble_style-red-grey','bubble_style-purple-green','avatar-left-style','avatar-left-style-blue-orange','avatar-left-style-pink','avatar-left-style-brown','avatar-left-style-red-grey','avatar-left-style-purple-green','avatar-left-style-50x50','avatar-left-style-50x50-blue-orange','avatar-left-style-50x50-brown','avatar-left-style-50x50-pink','avatar-left-style-50x50-purple-green','avatar-left-style-50x50-red-grey','avatar-right-style','avatar-right-style-blue-orange','avatar-right-style-pink','avatar-right-style-brown','avatar-right-style-red-grey','avatar-right-style-purple-green','avatar-right-style-50x50','avatar-right-style-50x50-blue-orange','avatar-right-style-50x50-brown','avatar-right-style-50x50-pink','avatar-right-style-50x50-purple-green','avatar-right-style-50x50-red-grey','default_style','card_style','card_style-salmon','card_style-orange','card_style-purple','card_style-slate','elegant_style-sky_blue','elegant_style-graphite','elegant_style-green_hills','elegant_style-salmon','elegant_style-smoke','notepad_style-stone','notepad_style-sea_blue','notepad_style-forest_green','notepad_style-red_rock','notepad_style-purple_gems','business_style-stone','business_style-blue','business_style-green','business_style-red','business_style-grey','modern_style-concept','modern_style-money','modern_style-digitalism','modern_style-power','modern_style-sleek','card_style-slate','elegant_style-tan','elegant_style-navy_blue','elegant_style-plum','elegant_style-smoke','notepad_style-maroon','notepad_style-navy_blue','notepad_style-teal','notepad_style-purple_gems','business_style-teal','business_style-navy_blue','business_style-forest_green','card_style-tan','card_style-navy_blue','card_style-plum','card_style-maroon','card_style-teal','card_style-forest_green','card_style-lavender','elegant_style-tan','elegant_style-navy_blue','elegant_style-plum','elegant_style-maroon','elegant_style-teal','elegant_style-forest_green','elegant_style-lavender','notepad_style-tan','notepad_style-navy_blue','notepad_style-plum','notepad_style-maroon','notepad_style-teal','notepad_style-lavender','business_style-tan','business_style-navy_blue','business_style-plum','business_style-maroon','business_style-teal','business_style-forest_green','business_style-lavender' 	
	);
	
	//array of free themes that are available
	//includes names
	$free_theme_array = array(
		'default_style' => 'Default Style',
		'dark_style' => 'Dark Style',
		'light_style' => 'Light Style',
		'clean_style' => 'Clean Style',
		'no_style' => 'No Style'
	);
	
	//array of pro themes that are available
	//includes names
	$pro_theme_array = array(
		'modern_style' => array(
			'modern_style-concept' => 'Modern Style - Concept',
			'modern_style-money' => 'Modern Style - Money',
			'modern_style-digitalism' => 'Modern Style - Digitalism',
			'modern_style-power' => 'Modern Style - Power',
			'modern_style-sleek' => 'Modern Style - Sleek'
		),
		'card_style' => array(
			'card_style' => 'Card Style - Light Gray',
			'card_style-tan' => 'Card Style - Tan',
			'card_style-navy_blue' => 'Card Style - Navy Blue',
			'card_style-plum' => 'Card Style - Plum',
			'card_style-maroon' => 'Card Style - Maroon',
			'card_style-teal' => 'Card Style - Teal',
			'card_style-forest_green' => 'Card Style - Forest Green',
			'card_style-lavender' => 'Card Style - Lavender',
			'card_style-salmon' => 'Card Style - Salmon',
			'card_style-orange' => 'Card Style - Orange',
			'card_style-purple' => 'Card Style - Purple',
			'card_style-slate' => 'Card Style - Slate'
		),
		'elegant_style' => array(
			'elegant_style-tan' => 'Elegant Style - Tan',
			'elegant_style-navy_blue' => 'Elegant Style - Navy Blue',
			'elegant_style-plum' => 'Elegant Style - Plum',
			'elegant_style-maroon' => 'Elegant Style - Maroon',
			'elegant_style-teal' => 'Elegant Style - Teal',
			'elegant_style-forest_green' => 'Elegant Style - Forest Green',
			'elegant_style-lavender' => 'Elegant Style - Lavender',
			'elegant_style-sky_blue' => 'Elegant Style - Sky Blue',
			'elegant_style-graphite' => 'Elegant Style - Graphite',
			'elegant_style-green_hills' => 'Elegant Style - Green Hills',
			'elegant_style-salmon' => 'Elegant Style - Salmon',
			'elegant_style-smoke' => 'Elegant Style - Smoke'
		),
		'notepad_style' => array(
			'notepad_style-tan' => 'Notepad Style - Tan',
			'notepad_style-navy_blue' => 'Notepad Style - Navy Blue',
			'notepad_style-plum' => 'Notepad Style - Plum',
			'notepad_style-maroon' => 'Notepad Style - Maroon',
			'notepad_style-teal' => 'Notepad Style - Teal',
			'notepad_style-lavender' => 'Notepad Style - Lavender',
			'notepad_style-stone' => 'Notepad Style - Stone',
			'notepad_style-sea_blue' => 'Notepad Style - Sea Blue',
			'notepad_style-forest_green' => 'Notepad Style - Forest Green',
			'notepad_style-red_rock' => 'Notepad Style - Red Rock',
			'notepad_style-purple_gems' => 'Notepad Style - Purple Gems'
		),
		'business_style' => array(
			'business_style-tan' => 'Business Style - Tan',
			'business_style-navy_blue' => 'Business Style - Navy Blue',
			'business_style-plum' => 'Business Style - Plum',
			'business_style-maroon' => 'Business Style - Maroon',
			'business_style-teal' => 'Business Style - Teal',
			'business_style-forest_green' => 'Business Style - Forest Green',
			'business_style-lavender' => 'Business Style - Lavender',
			'business_style-stone' => 'Business Style - Stone',
			'business_style-blue' => 'Business Style - Blue',
			'business_style-green' => 'Business Style - Green',
			'business_style-red' => 'Business Style - Red',
			'business_style-grey' => 'Business Style - Grey'
		),
		'bubble_style' => array(
			'bubble_style' => 'Bubble Style',
			'bubble_style-brown' => 'Bubble Style - Brown',
			'bubble_style-pink' => 'Bubble Style - Pink',
			'bubble_style-blue-orange' => 'Bubble Style - Blue Orange',
			'bubble_style-red-grey' => 'Bubble Style - Red Grey',
			'bubble_style-purple-green' => 'Bubble Style - Purple Green'
		),
		'avatar-left-style-50x50' => array(
			'avatar-left-style-50x50' => 'Left Avatar - 50x50',
			'avatar-left-style-50x50-blue-orange' => 'Left Avatar - 50x50 - Blue Orange',
			'avatar-left-style-50x50-brown' => 'Left Avatar - 50x50 - Brown',
			'avatar-left-style-50x50-pink' => 'Left Avatar - 50x50 - Pink',
			'avatar-left-style-50x50-purple-green' => 'Left Avatar - 50x50 - Purple Green',
			'avatar-left-style-50x50-red-grey' => 'Left Avatar - 50x50 - Red Grey'
		),
		'avatar-left-style' => array(
			'avatar-left-style' => 'Left Avatar - 150x150',
			'avatar-left-style-blue-orange' => 'Left Avatar - 150x150 - Blue Orange',
			'avatar-left-style-pink' => 'Left Avatar - 150x150 - Pink',
			'avatar-left-style-brown' => 'Left Avatar - 150x150 - Brown',
			'avatar-left-style-red-grey' => 'Left Avatar - 150x150 - Red Grey',
			'avatar-left-style-purple-green' => 'Left Avatar - 150x150 - Purple Green'
		),
		'avatar-right-style-50x50' => array(
			'avatar-right-style-50x50' => 'Right Avatar - 50x50',
			'avatar-right-style-50x50-blue-orange' => 'Right Avatar - 50x50 - Blue Orange',
			'avatar-right-style-50x50-brown' => 'Right Avatar - 50x50 - Brown',
			'avatar-right-style-50x50-pink' => 'Right Avatar - 50x50 - Pink',
			'avatar-right-style-50x50-purple-green' => 'Right Avatar - 50x50 - Purple Green',
			'avatar-right-style-50x50-red-grey' => 'Right Avatar - 50x50 - Red Grey'
		),
		'avatar-right-style' => array(
			'avatar-right-style' => 'Right Avatar - 150x150',
			'avatar-right-style-blue-orange' => 'Right Avatar - 150x150 - Blue Orange',
			'avatar-right-style-pink' => 'Right Avatar - 150x150 - Pink',
			'avatar-right-style-brown' => 'Right Avatar - 150x150 - Brown',
			'avatar-right-style-red-grey' => 'Right Avatar - 150x150 - Red Grey',
			'avatar-right-style-purple-green' => 'Right Avatar - 150x150 - Purple Green'
		)
	);
	
	$cycle_transitions = array(
		'scrollHorz' => 
			array(
				'label' => 	'Horizontal Scroll',
				'pro'	=>	false
			),
		'scrollVert' => 
			array(
				'label' => 	'Vertical Scroll',
				'pro'	=>	true
			),
		'fade' => 
			array(
				'label' => 	'Fade',
				'pro'	=>	false
			),
		'fadeout' => 
			array(
				'label' => 	'Fade Out',
				'pro'	=>	true
			),
		'carousel' => 
			array(
				'label' => 	'Carousel',
				'pro'	=>	true
			),
		'flipHorz' => 
			array(
				'label' => 	'Horizontal Flip',
				'pro'	=>	true
			),
		'flipVert' => 
			array(
				'label' => 	'Vertical Flip',
				'pro'	=>	true
			),
		'tileslide' => 
			array(
				'label' => 	'Tile Slide',
				'pro'	=>	true
			)
	);			
