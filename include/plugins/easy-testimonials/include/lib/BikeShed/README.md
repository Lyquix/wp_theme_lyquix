BikeShed
========

A forms helper for WordPress Options screens

Example:

	// IMPORTANT: BikeSheed needs to add some WordPress hooks, 
	// so create your BikeShed object early (i.e., in your constructor)


	class MySettingsPageClass
	{
		function __construct()
		{
			// create the object now, so that BikeShed has opportunity to add its hooks
			$this->shed = new \GoldPlugins\BikeShed();
		}

		function output_settings_page()
		{
			// some example options for <select> and radio buttons
			$ikfb_themes = array(
				'style' => 'Default Style',
				'dark_style' => 'Dark Style',
				'light_style' => 'Light Style',
				'blue_style' => 'Blue Style',
				'no_style' => 'No Style',
			);

			// notice the embedded text input on this last option
			// the first key is always "text"
			// the second key is the name of the field
			// the third key is the value (it is optional)
			$radio_options = array(
				'style' => 'Default Style',
				'dark_style' => 'Dark Style',
				'light_style' => 'Light Style',
				'blue_style' => 'Blue Style',
				'no_style' => 'No Style {{text|other_ik_fb_feed_image_width|456}}',
			);

			// now we'll actually output some fields using BikeShed
			$this->shed->typography( array('name' => 'title_*', 'label' =>'Title', 'description' => 'Style of the title.', 'google_fonts' => true, 'default_color' => '#008800') ); ?>
			$this->shed->typography( array('name' => 'heading_*', 'label' =>'Headings', 'description' => 'Style of the headings.', 'google_fonts' => true, 'default_color' => '#000088') ); ?>
			$this->shed->typography( array('name' => 'body_*', 'label' =>'Body Text', 'description' => 'Style  of the body text.', 'google_fonts' => true, 'default_color' => '#008888') ); ?>
			$this->shed->typography( array('name' => 'link_*', 'label' =>'Links', 'description' => 'Style of the links.', 'google_fonts' => true, 'default_color' => '#878787') ); ?>
			$this->shed->color( array('name' => 'favorite_color', 'label' =>'Favorite Color', 'description' => 'Your favorite color, at the moment.') ); ?>
			$this->shed->select( array('name' => 'ik_fb_feed_theme', 'options' => $ikfb_themes, 'label' =>'Feed Theme', 'description' => 'Select which theme you want to use.  If \'No Style\' is selected, only your Theme\'s CSS, and any Custom CSS you\'ve added, will be used.  The settings below will override the defaults set in your selected theme.') ); ?>
			$this->shed->text( array('name' => 'ik_fb_some_text', 'label' =>'Some Text!', 'description' => 'This is an example of a text input.') ); ?>
			$this->shed->textarea( array('name' => 'ik_fb_some_textarea', 'label' =>'Some More Text!', 'description' => 'This is an example of a textarea.') ); ?>
			$this->shed->checkbox( array('name' => 'ik_fb_some_checkbox', 'label' =>'A Checkbox ?!?', 'description' => 'This is an example of a checkbox.', 'inline_label' => 'Check this box to see some wild shit') ); ?>
			$this->shed->radio( array('name' => 'ik_fb_some_radio', 'options' => $radio_options, 'label' =>'Radio Buttons!', 'description' => 'These are some radio buttons. Enjoy them!') ); ?>
		}
	}

	// creating a new object of our example class, so that the constructor is fired
	$my_new_obj = new MySettingsPageClass();
