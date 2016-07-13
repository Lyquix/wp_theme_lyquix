=== Easy Testimonials ===
Contributors: richardgabriel, ghuger
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=V7HR8DP4EJSYN
Tags: testimonials, testimonial widget, testimonial feed, random testimonials
Requires at least: 3.1
Tested up to: 4.5.3
Stable tag: 1.36.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Easy Testimonials is a simple-to-use plugin for adding Testimonials to your WordPress Theme, using a shortcode or a widget.

== Description ==

Easy Testimonials is an easy-to-use plugin that allows users to add Testimonials to the sidebar, as a widget, or to embed testimonials into a Page or Post using the shortcode.  Easy Testimonials also allows you to insert a list of all Testimonials or output a Random Testimonial. Easy Testimonials allows you to include an image with each testimonial - this is a great feature for adding a photo of the testimonial author.  Easy Testimonials uses schema.org compliant markup so that your testimonials appear correctly in search results!

= Easy Testimonials is a great plugin for: =
* Adding Random Testimonials to Your Sidebar
* Adding Random Testimonials to Your Page
* Outputting a List of Testimonials
* Outputting a Fading or Sliding Testimonial Widget
* Able To Use Multiple Testimonial Themes on the Same Page!
* Responsive Themes!
* Displaying an Image with a Testimonial
* Displaying a Testimonial with a Rating
* Displaying Testimonials using Schema.org compliant markup
* Options Allow You to Link Your Testimonials to a Page, Such As a Product Page
* Testimonial Categories Allow You To Easily Organize Testimonials!
* Easy-to-use interface allows you to manage, edit, create, and delete Testimonials with no new knowledge!

= Pro Features include: =
* Collect Testimonials: Front-End Testimonial Form Allows Customers to Submit Testimonials on your Website!
* Multiple Testimonial Forms: use multiple forms to send to specific Testimonial Categories!
* Testimonial Form Spam Prevention: support for Really Simple Captcha and ReCaptcha included!
* Designer Themes: 75+ professionally designed themes for front end display!
* Advanced Transitions: including scrolling, flipping, and tiling!
* Custom Typography Settings: perfectly blend your testimonials into your website with a huge selection of fonts, colors, and sizes, including Google fonts!

Easy Testimonials allows you to set the URL of the View More Link, to display the Testimonial Image, control meta field display, and more!  Controlling the URL of the Testimonials view more link enables you to direct visitors to the product info page that the testimonial is about.  Showing an Image next to a Testimonial is a great tool for social proofing!

Easy Testimonials allows display of custom excerpted Testimonials.  Display custom excerpts in your widgets that draw your visitors into your Testimonial archive!

Collecting Testimonials can be a tedious job - fortunately, in the Pro version of Easy Testimonials, adding a form to your website for users to submit Testimonials is a breeze!  Users can even upload an image with their Testimonial!  Easy Testimonials integrates with Really Simple Captcha and ReCaptcha to prevent spam testimonial submissions.

Easy Testimonials is the easiest way to start adding your customer testimonials, right now!  Click the Download button now to get started.  Easy Testimonials will inherit the styling from your Theme - just install and get to work adding your testimonials!

= Premium Support =

The GoldPlugins team does not provide direct support for Easy Testimonials on the WordPress.org forums. One on one email support is available to people who have purchased Easy Testimonials Pro only. Easy Testimonials Pro also includes tons of extra themes and advanced features including a Testimonial Collection Form, so you should [upgrade today!](https://goldplugins.com/our-plugins/easy-testimonials-details/upgrade-to-easy-testimonials-pro/ "Upgrade to Easy Testimonials Pro")

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the contents of `/easy_testimonials/` to the `/wp-content/plugins/` directory
2. Activate Easy Testimonials through the 'Plugins' menu in WordPress
3. Visit [here](https://goldplugins.com/documentation/easy-testimonials-documentation/ "Easy Testimonials Pro Documentation") for information on how to configure the plugin.

### Adding a New Testimonial

Adding a New Testimonial is easy! There are 3 ways to start adding a new testimonial

**How to Add a New Testimonial**

1.  Click on "+ New" -> Testimonial, from the Admin Bar *or*
2.  Click on "Add New Testimonial" from the Menu Bar in the WordPress Admin *or*
3.  Click on "Add New Testimonial" from the top of the list of Testimonials, if you're viewing them all.

**New Testimonial Content**

You have a few things to pay attention to:

-   **Testimonial Title:** this content can be displayed above your Testimonial.
-   **Testimonial Body:** this is the content of your Testimonial. This will be output and displayed about the Testimonial Information fields.
-   **Client Name:** This field is displayed first, below the Testimonial Body. The field title is just an example use - of course you don't have to put the client name here.
-   **Position / Location / Other:** This field is displayed second, below the Client Name. The field title is just an example use - you can put whatever you want here.
-   **Location / Product Reviewed / Other:**This field is optional and is displayed third, below Position / Location / Other. The field title is just an example of how it can be used - typically this item is used for the Item the Testimonial is being left about and will match the itemReviewed Schema.
-   **Rating:** This field is displayed in different locations, depending on your theme, and represents the out of 5 rating for the item reviewed. If you are collecting ratings with your Testimonials, place them in this field.
-   **Featured Image:** This image is shown to the left of the testimonial, as a 50px by 50px thumbnail by default.
-   **Testimonial Category:** This field is useful for grouping and organizing your Testimonials. This can be useful if you want to display Testimonials about a certain topic as a group.

### Editing a Testimonial

**This is as easy as adding a New Testimonial!**

1.  Click on "Testimonials" in the Admin Menu.
2.  Hover over the Testimonial you want to Edit and click "Edit".
3.  Change the fields to the desired content and click "Update".

### Deleting a Testimonial

**This is as easy as adding a New Testimonial!**

1.  Click on "Testimonials" in the Admin Menu.
2.  Hover over the Testimonial you want to Delete and click "Delete".

**You can also change the Status of a Testimonial, if you want to keep it on file.**

### Controlling Testimonial Theme via Shortcode

-   To select a theme via the Shortcode, use the following shortcode syntax: [testimonials theme='card_style-maroon']
-   To get the full list of Themes available to your version of Easy Testimonials, we recommend using the Shortcode Generator.

### Outputting Random Testimonials

-   To output a Random Testimonial, place the following shortcode in the desired area of the Page or Post Content:
     [random_testimonial]
-   To display more than one Random Testimonial, use the following shortcode, where count is the number of testimonials you want displayed.
     [random_testimonial count='3']
-   To display the title above the Random Testimonial and use excerpts (short versions of the Testimonial), use the following shortcode: 
     [random_testimonial show_title='1' use_excerpt='1']
-   To output Random Testimonials from a specific Category, with images, use the following shortcode: 
     [random_testimonial category='the_category_slug' show_thumbs='1']
-   To display the rating along with the random testimonial, use the following shortcode:
     [random_testimonial show_rating='stars']

    Possible values for show_rating are `show_rating=before`, for the rating to appear before the testimonial content, `show_rating=after`, for the rating to appear after the testimonial content, and `show_rating=stars`, to show the rating in stars format.
	
-   By default, the View More Testimonials link is displayed with Random Testimonials.  To hide this link, use the following shortcode: [random_testimonial hide_view_more=1]

### Output a List of Testimonials

-   To output a list of all the Testimonials, place the following shortcode in the desired area of the Page or Post Content:
     [testimonials]
-   To output a list of the 5 most recent Testimonials, use the following shortcode: 
     [testimonials count='5' order='DESC' orderby='date']

    Acceptable attributes for `'order'` are `'ASC'` and `'DESC'`.

    Acceptable attributes for `'orderby'` are `'none'`,`'ID'`,`'author'`,`'title'`,`'name'`,`'date'`,`'modified'`,`'parent'`, and `'rand'`

-   To display the title above the list of Testimonials and use excerpts (short versions of the Testimonial), use the following shortcode: 
     [testimonials show_title='1' use_excerpt='1']
-   To output Testimonials from a specific Category, with images, use the following shortcode: 
     [testimonials category='the_category_slug' show_thumbs='1']
-   To display the rating along with the testimonials, use the following shortcode:
     [testimonials show_rating='stars']

    Possible values for show_rating are `show_rating=before`, for the rating to appear before the testimonial content, `show_rating=after`, for the rating to appear after the testimonial content, and `show_rating=stars`, to show the rating in stars format.

-   To paginate the list of Testimonials, use the following shortcode: [testimonials paginate='1' testimonials_per_page='5']

-   By default, the View More Testimonials link is not displayed in the List of Testimonials.  To display this link, use the following shortcode: [testimonials hide_view_more=0]

### Output a Grid of Testimonials

-	To output a grid of all the Testimonials, place the following shortcode in the desired area of the Page or Post Content:
	[testimonials_grid]
-	To output a grid of the 5 most recent Testimonials, use the following shortcode:
	[testimonials_grid count='5' order='DESC' orderby='date']
	
	Acceptable attributes for `order` are `ASC` and `DESC`.
	Acceptable attributes for `orderby` are `none`,`ID`,`author`,`title`,`name`,`date`,`modified`,`parent`, and `rand`

-	To display the title above the grid of Testimonials and use excerpts (short versions of the Testimonial), use the following shortcode: 
	[testimonials_grid show_title='1' use_excerpt='1']

-	To output Testimonials from a specific Category, with images, use the following shortcode: 
	[testimonials_grid category='the_category_slug' show_thumbs='1']

-	To display the rating along with the testimonials, use the following shortcode:
	[testimonials_grid show_rating='stars']

	Possible values for show_rating are `show_rating=before`, for the rating to appear before the testimonial content, `show_rating=after`, for the rating to appear after the testimonial content, and `show_rating=stars`, to show the rating in stars format.

-	To paginate the grid of Testimonials, use the following shortcode:
	[testimonials_grid paginate='1' testimonials_per_page='5']
	
-	To ouput a grid of 4 specific testimonials by ID, use the following shortcode (update the values for ID to match the IDs of the desired testimonials.):
	[testimonials_grid ids=5,7,3,4]

-	To output a grid of Testimonials with 3 columns, spaced 3% apart, and each cell of the grid 25% wide, use the following shortcode:
	[testimonials_grid cols='3' grid_spacing="3%" cell_width="25%"]

-	To output a grid of Testimonials with 3 columns, spaced 3% apart, and each cell of the grid with the same height as others in it's row:
	[testimonials_grid cols='3' grid_spacing="3%" equal_height_rows="true"]
	
-   By default, the View More Testimonials link is displayed with the Grid of Testimonials.  To hide this link, use the following shortcode: [testimonials_grid hide_view_more=1]

### Output a Random Testimonial in the Sidebar

-   To output a Random Testimonial in the Sidebar, use the Widgets section of your WordPress Theme, accessible via the Appearance Menu in the WordPress Admin.
-   You can show more than one random testimonial by placing a number in the Count field.
-   You can choose a Category to pick the testimonial from, with the Category drop-down.
-   You can show the Date of the Testimonial by checking Show Testimonial Date.
-   You can show the Rating of the Testimonial by picking an option from the Show Rating drop-down.
-   You can show the Testimonial Title above the Testimonial by checking Show Testimonial Title. **Note: The Title Field on the Widget is displayed above the Widget, if your theme supports Widget Titles - this is different than the Testimonial Title.**

### Outputting a Testimonial Slider

-   **NOTE:** You can view live examples [here](https://goldplugins.com/documentation/easy-testimonials-documentation/easy-testimonials-examples/ "Example Testimonial Sliders").
-   Easy Testimonials Supports Cycle2! To output a sliding widget, use the following shortcode:
     [testimonials_cycle]
-   The same properties as the list of testimonials, such as Showing the Title and controlling the Count, also apply. To output a Testimonial Cycle using excerpts, from a specific category, with images, use the following shortcode: 
     [testimonials_cycle category='the-category-slug' use_excerpt='1' show_thumbs='1']
-   To show a randomly ordered Testimonial Cycle, use the following shortcode: 
     [testimonials_cycle random='true']
-   To show a sliding Testimonial Cycle, with 10 seconds between each transition, use the following shortcode: 
     [testimonials_cycle timer='10000' transition='scrollHorz']
-	To show a sliding Testimonial Cycle, with Previous and Next buttons, use the following shortcode:
	 [testimonials_cycle prev_next='1']
-	To show a sliding Testimonial Cycle, that only transitions when Prev or Next is clicked, use the following shortcode:
	 [testimonials_cycle paused='1' prev_next='1']
-	To show a sliding Testimonial Cycle, that pauses transitions when the mouse is hovering over the slideshow, use the following shortcode:
	 [testimonials_cycle pause_on_hover='1']

    To pick from our full list of available transitions, we recommend using the Shortcode Generator.

-   To show a sliding Testimonial Cycle, using the 5 most recent Testimonials ordered chronologically, use the following shortcode: 
     [testimonials_cycle count='5' order='DESC' orderby='date']

    Acceptable attributes for `'order'` are `'ASC'` and `'DESC'`.

    Acceptable attributes for `'orderby'` are `'none'`,`'ID'`,`'author'`,`'title'`,`'name'`,`'date'`,`'modified'`,`'parent'`, and `'rand'`

-   To show a Testimonial Cycle that automatically changes height to match the Testimonial body, use the following shortcode: 
     [testimonials_cycle auto_height='container']
-	To show a Testimonial Cycle that sets the slideshow height to the height of tallest Testimonial, use the following shortcode:
	 [testimonials_cycle auto_height='calc']
-   To show a Testimonial Cycle with 3 Testimonials per Slide and Pager Icons below the Slider, use the following shortcode: 
     [testimoanials_cycle pager='1' testimonials_per_slide='3']
	 
-   By default, the View More Testimonials link is displayed with the Testimonials Cycle.  To hide this link, use the following shortcode: [testimonials_cycle hide_view_more=1]

### Outputting a Testimonial Slider in the Sidebar

-   To output a Testimonial Cycle in the Sidebar, use the Widgets section of your WordPress Theme, accessible via the Appearance Menu in the WordPress Admin.
-   You can control how many testimonials are shown by placing a number in the Count field - make sure you have at least 2, if you want them to Cycle!
-   You can show the Date of the Testimonial by checking Show Testimonial Date.
-   You can control the number of Testimonials Per Slide by using the Testimonials Per Slide input.
-   You can show clickable pager icons below the Testimonial slideshow by checking Show Pager Icons.
-   You can Randomize the order of the Slideshow by checking Random Testimonial Order.
-   You can limit the length of the Testimonial to just the Excerpt by checking Use Testimonial Excerpt.
-   You can show the Testimonial Title above the Testimonial by checking Show Testimonial Title. **Note: The Title Field on the Widget is displayed above the Widget, if your theme supports Widget Titles - this is different than the Testimonial Title.**
-   You can control the time between transitions using the Timer field - every 1000 equals 1 second.
-   You can choose a Category to pick the testimonial from, with the Category drop-down.
-   You can show the Rating of the Testimonial by picking an option from the Show Rating drop-down.

### Front End Testimonial Submission

-   **NOTE:** This feature requires the [Pro version of Easy Testimonials](https://goldplugins.com/our-plugins/easy-testimonials-details/ "Easy Testimonials Pro").
-   To display the Testimonial Submission Form, use the following shortcode: 
     [submit_testimonial]
-   To display the Testimonial Submission Form and have it submit directly to a category, use the following shortcode: 
     [submit_testimonial submit_to_category="desired-category-slug"]
-   Any submissions will be added to your Testimonials list, on the back end, as pending Testimonials. Only Testimonials that you choose to publish will be displayed publicly.
-   Captcha support is enabled with the installation of the [Really Simple Captcha plugin](https://wordpress.org/plugins/really-simple-captcha/ "Really Simple Captcha").
-   Front End Image Submission is enabled via a checkbox on the Submission Form Options tab.
-   Labels, descriptions, and visibility of fields can be controlled via the Submission Form Options screen.

### Using Filters to Customize Output

We provide the following filters to developers for customizing output even further:

-   `easy_t_random_testimonials_html` -- random testimonials filter
-   `easy_t_single_testimonial_html` -- single testimonial filter
-   `easy_t_testimonials_html` -- all testimonials list filter
-   `easy_t_testimonials_cyle_html` -- testimonials cycle filter
-   `easy_t_submission_form` -- testimonial submission form
-	`easy_t_get_single_testimonial_html` -- single testimonial html filter, all output functions use this to build each testimonials html
-   `easy_t_get_pagination_link_template` -- pagination link template filter, use this to modify the display of the previous, next, and page number links in paged testimonials

### Outputting a Count of your Testimonials

-   To display a numerical Count of your Testimonials, use the following shortcode where you want the Number to appear: 
     [testimonials_count]
-   To display a numerical Count of your pending Testimonials from a specific category, use the following shortcode: 
     [testimonials_count status='pending' category='gold-plugins']
-   Supported parameters for `status` are `publish`,`pending`,`draft`,`future`,`private`,`trash`,`any`. For example, the following shortcode will display a count of all Testimonials, excluding those in the Trash: 
     [testimonials_count status='any']
-   **Note:** this Shortcode only displays the numerical count -- you will need to add any words yourself. For example, "There are currently [testimonials_count] Testimonials on your website!"

### Outputting an Aggregate Rating of all Testimonials on your Site

-   To display aggregate rating of your Testimonials, use the following shortcode where you want the schema.org markup to appear: 
     [testimonials_count show_aggregate_rating='1']
-   **Note:** This will use the Global Item Reviewed option, from your plugin's settings tabs.

### Outputting a Testimonials Search Form

-   To display a form that Searches your Testimonials, use the following shortcode:
     [easy_t_search_testimonials]

### Options

-   To control the destination of the "View More" link, set the path in the Testimonials View More Link field.
-   To display any Featured Images that you have attached to your Testimonials, check the box next to Show Testimonial Image.
-   To display any Testimonial Information above the content, check the box next to Show Testimonial Info Above Testimonial.
-   To add any Custom CSS, to further modify the output of the plugin, input the CSS in the textarea labeled Custom CSS. You do not need to include the opening or closing <style> tags, treat it like you're inside a CSS file.
-   To pick a global theme, use our Themes tab to browse the available choices.
-   To control the display of the Testimonial Submission Form, use the Submission Form Options tab.
-   To customize the appearance of your Testimonials, use the Display Options tab.
-   To Show Testimonials in Public Search, use the Show in Search checkbox on the Basic Settings Page.
-   To change your Registered Shortcodes, in case of conflict with theme or other plugins, use the Shortcode Options section on the Basic Settings screen.

== Frequently Asked Questions ==

= Help!  I need more information! =

OK!  We have a great page with some helpful information [here](https://goldplugins.com/documentation/easy-testimonials-documentation/ "Easy Testimonials Pro Documentation").

= I Updated, and my formatting changed! =

Yikes!  Before 1.7.2, we were not respecting the content filter when outputting testimonials.  So, you may have to update the CSS of paragraph tags inside .testimonial_body.  For more information, contact us via our website or support forum.

= Hey!  How do I allow my visitors to submit testimonials? =

Great question!  With the Pro version of the plugin, you can do this with our front end form that is output with a shortcode!  Testimonials will show up as pending on the Dashboard, for admin moderation.  Visit [here](https://goldplugins.com/our-plugins/easy-testimonials-details/ "Easy Testimonials Pro") to purchase the Pro version.

= Urk! When I Activate Easy Testimonials, I start having trouble with my Cycle2 powered JavaScript! =

Oh no!  Check the box that is labeled "Disable Cycle2 Output".  This will cease including our JavaScript.

= Yo!  Your plugin is great - I would really like to change the size of the images that are output.  How do I do it? =

Another good question!  With the Pro version of the plugin, you can do this by controlling the Testimonial Image Size drop down menu on the Settings screen.  Depending on your website, using bigger images may require CSS changes to be made.  Visit [here](https://goldplugins.com/our-plugins/easy-testimonials-details/ "Easy Testimonials Pro") to purchase the Pro version.

= Eek!  I love everything about this plugin... but, I don't know how to use it inside my Template Files!  What do I do? =

Don't worry!  WordPress has a great function, ```do_shortcode()```, that will allow you to use our shortcodes inside your theme files.  For example, to output a Random Testimonial in a Theme file, you would do this: ```<?php echo do_shortcode('[random_testimonial count="1"'); ?>```

= Arg!  When using the testimonial Cycle widget, I get weird overlapping text.  What gives? =

You need to update your CSS.  Try something like ```blockquote.easy_testimonial{ background-color: white; }```

= Ack!  This Testimonials Plugin is too easy to use! Will you make it more complicated? =

Never!  Easy is in our name!  If by complicated you mean new and easy to use features, there are definitely some on the horizon!

= Yikes!  I'm getting a ton of spam! =

Never fear, Captcha support is here!  Go install and activate the plugin Really Simple Captcha.  Once done, make sure you have the "Enable Captcha on Submission Form" box checked on your settings, and you should be good to go!

= Help!  I'm having issues getting the Slider to work on my site! =

Never fear, the "Use Cycle Fix" option is here!  Try checking this option and fully refreshing the page (to make sure any and all caches have cleared) -- hopefully everything is working now!

= Blech!  Some of my testimonials are too tall and the text is cut off by the bottom of the slider!  What gives?! =

Ok!  We have the solution to adjust the height to display all of your testimonial!  Use the attribute ```container='1'``` in your shortcode and the javascript will adjust the height to match the content on each transition.

= Hiyo!  My customers are submitting testimonials but no images are showing up.  What gives? =

As a security precaution, our plugin only allows users to upload images of the following file types: PNG, JPG, or GIF.  If they attempt to upload a different file type, or choose not to upload an image, then no image will be attached to the Testimonial.

= What's Going On?!  When I use the [testimonials] shortcode, I'm not seeing anything that looks right! =

Sometimes, your theme or other plugins have shortcodes in the same namespace as ours.  In case you suspect this is happening, use the Shortcode Options on the Basic Settings screen to change our shortcodes -- typically adding easy_ to our shortcodes will fix the problem!

= Hey! How do I change the Width of my Testimonials?! =

Easy!  Just add the attribute width=500px or width=33% (be sure to use the full value, ie 500px, or 33% - otherwise it won't work!)  If not set, Testimonials will size to their container.

== Screenshots ==

1. This is the Add New Testimonial Page.
2. This is the List of Testimonials - from here you can Edit or Delete a Testimonial.
3. This is the Basic Settings Page.
4. This is the Display Options Settings Page.
5. This is the Themes Selection Page.
6. This is the Submission Form Settings Page.
7. This is the Shortcode Generator.
8. This is the Import & Export Testimonials Page.
9. This is the Help & Instructions Page.
10. This is the Random Testimonial Widget.
11. This is the Testimonial Cycle Widget.
12. This is the Testimonial List Widget.
13. This is the Single Testimonial Widget.
14. This is the Testimonial Grid Widget.

== Changelog ==

= 1.36.1 =
* Update CSS include to prevent validation errors and issues with some optimization methods.
* Fix aggregate rating count to not include testimonials with no rating.
* Validation fixes.

= 1.36 =
* Updates javascript for compatibility.
* Adds option to control author that Testimonials are submitted under.
* Adds aggregate rating.
* Fix issue with pagination on some sites.
* Update CSS includes to load only the needed CSS on site.
* Update CSS to work with various CSS minifiers not following @imports correctly.

= 1.35.6 =
* Bug fix with manually crafted excerpts not appearing correctly.

= 1.35.5 =
* Updates Excerpt customization to properly target testimonials in all scenarios.
* Updates Excerpt output to no longer include additional data, such date and title, in some situations.
* Updates Excerpt output to always link to the correct testimonial.

= 1.35.4 =
* Address notice on some settings panels.
* Update Typography to have better Reset to Default options.
* Better alignment of text in Modern Theme.

= 1.35.3 =
* Address issues with unset font attributes in some themes causing crazy appearances.
* Address issue causing rating input on submission form to error when more than one submission form is on a given page.
* Minor submission form cleanup.
* Adds option to set Global ItemReviewed value
* Compatibile with WP 4.5

= 1.35.2 =
* Adds ability to pass category for submission form via shortcode.
* Updates GP Media Button to prevent conflicts with multiple plugins using it.
* Various admin interface updates.
* Minor Cycled Testimonial updates and fixes.

= 1.35.1 =
* Fix improper image attribute in shortcodes generated by Cycle and Random testimonial widgets.
* Update admin UI.
* Update Cycle2 for compatibility with Avada, preventing slides from disappearing after first transition.
* Add messaging for compatibility options when using Avada theme.

= 1.35 =
* Add options panel to allow customization of Submission Form Error Messages, better organizes submission options, uses better field descriptions.
* Updates archive template compatibility with Avada.
* Defaults Testimonials List to hide the View More Testimonials link, by default.
* Updates Shortcode Generator to be more intuitive.
* Minor fixes.

= 1.34.4 =
* Fix issue with wall of text generated during CSV import.

= 1.34.3 =
* Fixes double-encoded HTML appearing in Testimonial Category dropdown of submission form.
* Fixes issue causing testimonial content to be cut off when using the slider on some themes.
* Updates pagination to be formatted better and to have page number links.
* Adds Pagination controls to List and Grid widgets.

= 1.34.2 =
* Fixes slideshow height issue when using auto height settings.

= 1.34.1 = 
* Address double content display bug.

= 1.34 = 
* Adds new Filter, easy_t_get_single_testimonial_html, allowing for further customization of testimonial output by developers.
* Fixes bug where some content was duplicated when using widgets on single views.
* Updates output filtering to show testimonials using proper theme and style settings on Category, Tag, and Archive index pages.

= 1.33 =
* Adds Testimonial Excerpt controls, allowing control of the length, text, and linkage of Testimonial Excerpts.
* Fixes issues with certain Pro themes not allowing their font styles to be overridden by the Display Options.
* Fixes issues with Pager Icons and Previous/Next buttons expanded past the set width of Testimonials on the Options panel.
* Fixes issue with Pager Icons and Previous/Next buttons controlling every Cycle2 slideshow on the same page.
* Adds option to display Pagers Icons and Previous/Next buttons Above or Below the slideshow.
* Fix validation issues with missing alt text on Gravatars.
* Minor Widget Updates, including defaulting Theme drop down to globally set option.
* Updates Ratings display to allow control of Star Color.
* Adds Typography settings for Location / Item Reviewed.
* Update Random Testimonial function to run much faster.
* Update Metadata display to not output blank fields.
* Update Single Testimonial view to have properly constructed and styled testimonials, for people using Continue Reading links.

= 1.32 =
* Adds Insert Testimonial buttons to the Visual and Text Editors, including all available methods such as Cycled Testimonials, Grid, List, Random, and Single.
* Updates Modern Theme to allow different background colors.
* Updates Widgets to allow collapsable fieldsets, preventing super long widgets interfaces.

= 1.31.12 =
* Various theme fixes.

= 1.31.11 =
* Updates Theme Selection interface
* Fixes issue with custom fields not displaying in some themes.
* Fixes issue with Product Reviewed field not displaying in Widget.

= 1.31.10 =
* Update: adds new Testimonials Grid widget

= 1.31.9 =
* Update: adds text-domain, sets up plugin to be translatable.

= 1.31.8 =
* Fix: Properly reset post data to prevent double testimonials.

= 1.31.7 = 
* CSS fixes for testimonial grid.
* Fix: unclosed row divs in some cases using grid shortcode.

= 1.31.6 =
* Fix: address issue with endless loop on some Divi theme based sites.
* Update: add ability to pass a list of IDs to [testimonials_grid] shortcode.

= 1.31.5 = 
* Adds [testimonials_grid] shortcode for outputting testimonials in a horizontal grid.

= 1.31.4 = 
* Adds reCAPTCHA as an option for the submission form

= 1.31.3 = 
* Updates Widgets to be compatible with WordPress 4.3

= 1.31.2 =
* Compatible with WordPress 4.2.4
* Fix issue with Featured Image setting in Widgets.

= 1.31.1 =
* Compatibility update for older versions of WP.  Minor updates.

= 1.31 =
* New and Improved Testimonial List Widget, Testimonial Cycle Widget, Single Testimonial Widget, and Random Testimonial Widget

= 1.30.3 =
* Fix: output correct labels for theme groups

= 1.30.2 =
* Add Theme Selection to Testimonials List Widget.

= 1.30.1 =
* Quick Fix for CSS positioning issue with Client Name and Client Position.

= 1.30 =
* Shortcode Generator Updates, Fixes
* Adds Gravatar Support
* Adds Widgets: List Testimonials, Single Testimonial, Submit Testimonial

= 1.29.1 =
* Update default text for third custom field.
* Update schema.org markup to properly identify the itemReviewed, if set.
* Update star ratings to output schema.org rating markup.
* Update admin script includes to be compatible with Sonec themes.
* Shortcode Generator Updates.

= 1.29 =
* Adds new slideshow options, including previous and next buttons and option to disable auto transition.
* Updates many Pro themes to be responsive.
* Adds shortcode option to control width of output.


= 1.28.2 =
* Reorder Cycle Fix includes.
* Avada compatibility update.

= 1.28.1 =
* Bugfix for advanced transitions on sites with Cycle Fix enabled.

= 1.28 =
* Add Option to Exclude Testimonials from Site Search.
* Adds Testimonials Search Form shortcode.

= 1.27 =
* Reduces database calls around options to improve performance

= 1.26 =
* Adds Recently Submitted Testimonials Dashboard Widget
* Replace ` tags around shortcode examples with easier to use inputs
* Updates Help Docs for clarity, ease of use
* Swaps out pushpin menu icon for testimonial menu icon

= 1.25.4 =
* Supports Pause on Hover for Cycled Testimonials
* Compatibility check for Testimonials by WooThemes

= 1.25.3 =
* JS Compatibility Update

= 1.25.2 =
* Cycle2 Update - fixes issues with additional Cycle2 plugins

= 1.25.1 =
* CSS Update, Cycle Fix Update, Compatibilty Update

= 1.25 =
* Adds CSV Import and Export
* Updates Cycle2 to 2.1.6
* Compatibility updates, fixes

= 1.24 =
* Make shortcodes easier to copy, and other misc admin UI updates

= 1.23 =
* Adds Many New Color Schemes To Our Pro Themes
* Updates Free Themes to support Star Ratings
* Several bug fixes to themes to prevent layout from breaking
* Font Color, Size, and Style options added

= 1.22 =
* Minor admin UI updates
* User Reported Bug Fixes

= 1.21 =
* Feature: adds support for Categories to front end submission form.
* Feature: adds new shortcode, [testimonials_count], that allows display of number of testimonials in the system.

= 1.20.7 =
* Update: addresses issue that was preventing users from listing a Category full of Testimonials via /category-slug/ lists.

= 1.20.6 =
* Addresses issue with PHP short tags inside shortcode generator.

= 1.20.5 =
* Addresses issue with Hello Testimonials importing.

= 1.20.4 =
* Adds general class to testimonial wrapping div, for better CSS targeting.
* Adds schema.org itemReviewed markup to third custom field, Product Reviewed.
* Adds shortcode attribute, show_other, that defaults to false, to control whether or not the third custom field is displayed.
* Updates Pro Theme styles to support more features.
* Admin Style cleanup.
* Adds attribute based classes to wrapping HTML, to allow better user control of styling.


= 1.20.3 =
* Fix: addresses issue with empty custom fields being displayed, causing issues with certain themes.
* Fix: addresses box-sizing compatibility issue.

= 1.20.2 =
* Fix: addresses issue where "Location / Product Reviewed / Other" custom label and description weren't carrying through to the front end submission form.

= 1.20.1 =
* Fix: address user reported errors.

= 1.20 =
* Feature: Adds option third custom field, Location / Product Reviewed / Other, to allow more information collection and more display customization.
* Feature: Adds option to redirect users to a specific URL after succesfully submitting their Testimonial.
* Fix: Address issue with Cycled Testimonial Widget not displaying dates or remember Ratings as Stars options.
* Update: Alter Cycled Widget to use a random order WP Query when displaying random testimonials - to prevent showing the same 5 testimonials in a random order every page load.
* Update: Updates compatibility to WP 4.1.1

= 1.19.1 =
* Fix: address issue with some incorrectly set settings data.


= 1.19 =
* Feature: Adds ability to set the View More Text
* Feature: Adds copyable Single Testimonial Shortcode display to Single Testimonial Edit screen.

= 1.18 =
* Feature: Updates Testimonial Submission Notification e-mails to send to multiple addresses and optionally include the submitted testimonial.
* Feature: Adds option to display star ratings via the Widgets.
* Feature: Adds option to display the Testimonial Date via the Widgets.
* Fix: Replace "Category Slug" field on Widgets with Category Drop Down selector.
* Fix: Address issue where some options weren't defaulted correctly.

= 1.17.5 =
* Update: Fix incorrect default for custom single_testimonial shortcode.
* Update: Change point that CSS is enqueued to improve compatibility with various caching plugins.

= 1.17.4 =
* Update: add option to control registered shortcodes to allow compatibility adjustments for various themes and plugins.
* Update: adds Themes to shortcode generator.

= 1.17.3 =
* Fix: address issue where stars weren't showing appropriately to logged out users.

= 1.17.2 =
* Update: CSS tweaks.

= 1.17.1 =
* Update: minify new assets.

= 1.17 =
* Update: Adds 25 New Themes!
* Update: Adds Show Publication Date and Show Star Ratings options to Shortcode Generator.
* Fix: Addresses issue where Publication Date was output in an incorrect position when meta data was displayed below the Testimonial.

= 1.16.1 =
* Fix: Address broken images on Style and Theme Options Settings.

= 1.16 =
* Feature: adds ability to display testimonial publication date via shortcode.
* Update: adds new shortcode options to shortcode generator.
* Update: updates compatibility to WP 4.1

= 1.15.2 =
* Fix: hide newsletter signup form from Pro activated users.

= 1.15.1 =
* Fix: address compatibility issue with Tri.be Events Calendar.

= 1.15 =
* Feature: Adds ability to choose a specific theme via the shortcode.
* Feature: Adds integration with Hello Testimonials.
* Feature: Adds output filters for greater developer control over display formatting.
* Fix: Various bug fixes.

= 1.14 =
* Feature: adds ability to use pagination with the list all testimonials shortcode.
* Updates Help & Instructions with more details.
* Updates Shortcode Generator to reflect more shortcode options.

= 1.13 =
* Supports schema.org review markup.
* Address issue with custom fields being lost during quick edit.

= 1.12.3 =
* Address issues with easy-testimonials-admin.js
* Address issue with shortcode generator using the incorrect value for random order.

= 1.12.2 =
* Fix: address issue where the same title was being displayed repeatedly when using the Random Testimonial display functionality.

= 1.12.1 =
* Fix: address issue where Testimonials Read More Link was being displayed in the full list of testimonial, when it is intended only to be displayed in random, single, or cycled testimonials.

= 1.12 =
* Feature: adds shortcode generator to greatly increase ease of implementing plugin.

= 1.11 =
* Feature: adds ability to control the number of testimonials that appear in each slide of the testimonial cycle.  Defaults to one testimonial per slide.
* Adds Help & Instructions Screen.
* Adds submenu to Easy Testimonials Settings for easier navigation.

= 1.10 =
* Feature: adds support for ratings to testimonials and the front-end submission form.
* Cleans up Submission Form Options screen to be more legible.

= 1.9 =
* Feature: adds support for front-end testimonial submission to the submission form.

= 1.8 =
* Update: compatibile with WordPress 4.0.
* Feature: adds support for pagers to cycled testimonials.

= 1.7.7.1 =
* Update: Address 404 error with jquery.cycl2.js.map file in Google Chrome.

= 1.7.7 =
* Update: reworks some things on the settings screen.
* Pro Feature: new pro theme options available.

= 1.7.6 =
* Feature: adds ability to control the order in which Testimonials are displayed.
* Update: adds new compatibilty option to help some users who have slideshow issues.

= 1.7.5.3 =
* Registration update.

= 1.7.5.2 =
* Minor update.

= 1.7.5.1 =
* Update: hides output of all but first testimonial when using cycled testimonial output.

= 1.7.5 =
* Feature: adds ability to randomize the order of the cycled testimonials.

= 1.7.4 =
* Feature: adds Captcha to Front End Testimonial Submission.
* Update: add more classes to output to allow more control with CSS, such as client and position.
* Fix: address undefined index notice in sidebar widget.

= 1.7.3 =
* Update: adds option to apply The Content filter to Testimonial output.
* Update: reposition custom CSS output for validation purposes.

= 1.7.2 =
* Fix: address issue with Continue Reading links leading to 404 pages.
* Update: respect wordpress content formatting in testimonials.
* Fix: change position of Testimonials menu item, so that it doesn't dissappear in some situations.

= 1.7.1 =
* Update: adds wrapping class to submission success message.
* Update: updates form output to properly use output buffering.

= 1.7 =
* Feature: adds ability to control the labels, description, and display of certain fields on the submission form.
* Feature: adds the ability to receive notifications at a specified e-mail address on new submissions.
* Update: restructure queries to load a bit faster.

= 1.6.1 =
* Fix: address deprecated function use in widget.
* Fix: fix issue using Fade transition with the Widget.

= 1.6 =
* Feature: Adds more javascript transitions to Pro version.
* Fix: Addresses a PHP notice.

= 1.5.9.1 =
* Update: Adds ability to use the excerpt with Read More functionality via the cycle shortcode.
* Minor Feature: Adds ability to control output of images in the via the cycle shortcode.

= 1.5.9 =
* Feature: Outputs shortcode to list testimonials in a category inside the Category list in the admin area.
* Feature: Adds ability to control image display via the shortcodes.

= 1.5.8 =
* Pro Feature: Adds the ability to control the size of images that are displayed.

= 1.5.7 =
* Feature: Adds ability to create Categories for Testimonials, and to only display Testimonials by Category.

= 1.5.6.1 =
* Fix: Fixes issue with "Fade" transition being locked out.

= 1.5.6 =
* Feature: Adds option to output Mystery Man avatar, if no other image is available.
* Minor Fix: Address CSS issue with sliding testimonials.

= 1.5.5.2 =
* Pro Feature: Adds Support for more Cycle2 Transitions.

= 1.5.5.1 =
* Compatibilty Option Update: Adds option to disable Cycle2 JavaScript that is included with Easy Testimonials.
* Minor Fix: Address bug in single testimonial shortcode output.

= 1.5.5 =
* Feature: Adds ability to display either the Excerpt or the Full Content of the Testimonial.
* Update: Addresses compatibility issues with the slider on several different Themes by moving Javascript to Footer.
* Pro Version Fix: Address bug with front-end testimonial submission.

= 1.5.4.1 =
* Update: set height of sidebar testimonial cycle container to match height of content inside.

= 1.5.4 =
* Feature: Adds Testimonial Cycle Widget to Appearance section.

= 1.5.3 =
* Update: Shortcode examples to help embed single testimonials.
* Feature: Support for Cycle 2 via Shortcode.

= 1.5.1 =
* Minor Fix: address bug in registration.

= 1.5 =
* New Pro Feature: Submit Testimonials from the front end!

= 1.4.5 =
* Fix: only output testimonial titles in the widget if the option is checked.

= 1.4.4 =
* Fix: output correct title with random testimonials.

= 1.4.3 =
* Feature: ability to output title of the testimonial with the shortcode.

= 1.4.2 =
* Fix: address mistargeted CSS in new theme.

= 1.4.1 =
* New Style Available: Clean Style.  With the clean style, you'll get smooth looking avatars and a clean, clear layout for your testimonial text.  Looks great with the TwentyThirteen theme!
* Update: Adds Classes to paragraph tags in the testimonial list, for easier CSS targeting.

= 1.4 =
* Fix: Featured Image should no longer break in your themes.
* Feature: Ability to set a number of random testimonials to output, with the shortcode or the widget.
* Feature: Ability to set a number of testimonials to appear via the standard testimonial shortcode.
* Feature: Ability to set Custom CSS via the Settings panel.

= 1.3.4.1 =
* Minor Fix: address warning message output when no pre-existing featured image support is found.

= 1.3.4 =
* Fix: address issue where Featured Image support was only applied to Testimonials, after activating this plugin.

= 1.3.3 =
* Fix: address some code quirks that were causing activation errors in certain web environments.

= 1.3.2 =
* Fix: no longer display Read More when looking at full list view.

= 1.3.1 =
* Fix: tiny CSS error.

= 1.3 =
* New Feature: Adds support for themes, for easy styling.  Includes a few themes.

= 1.2.1 =
* Minor edits.

= 1.2 =
* New Feature: Option to Display the Custom Fields above or below the Testimonials.  Defaults to Below.
* Update: Compatible with WordPress 3.6.

= 1.1 =
* New Feature: Testimonials Now Support Images!

= 1.0 =
* Released!

== Upgrade Notice ==

= 1.36.1: Fixes available!