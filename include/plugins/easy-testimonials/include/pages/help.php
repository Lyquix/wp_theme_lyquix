<style>
	#easy_testimonials_help {
		max-width: 750px;
	}
	#easy_testimonials_help > div {
		padding-top: 25px;
	}
	#easy_testimonials_help > div > h3 {
		border-bottom: 1px solid lightgray;
		max-width: 650px;
		padding-bottom: 7px;
	}
	#easy_testimonials_help p {
		max-width: 550px;
	}
	#easy_testimonials_help li {
		max-width: 550px;
	}
	#easy_testimonials_help ul{
		list-style-type: disc;
		margin-left: 2em;
	}	
	#easy_testimonials_help textarea {
		height: 30px;
		line-height: 1.5em;
		width: 550px;
	}
</style>
<div id="easy_testimonials_help">
	<h3>Help &amp; Instructions</h3>
	<p>This page includes all kinds of helpful tips for getting you up and running.</p>
	<h3>Contents:</h3>
	<ol class="quick_nav">
		<li><a href="#add_a_new_testimonial">Adding a New Testimonial</a></li>
		<li><a href="#edit_a_testimonial">Editing a Testimonial</a></li>
		<li><a href="#testimonial_theme_via_shortcode">Selecting Testimonial Theme via Shortcode</a></li>
		<li><a href="#outputting_random_testimonials">Outputting Random Testimonials</a></li>
		<li><a href="#output_a_list_of_testimonials">Output a List of Testimonials</a></li>
		<li><a href="#output_a_grid_of_testimonials">Output a Grid of Testimonials</a></li>
		<li><a href="#output_a_testimonial_in_the_sidebar">Output a Testimonial in the Sidebar</a></li>
		<li><a href="#outputting_a_testimonial_slider">Outputting a Testimonial Slider</a></li>
		<li><a href="#outputting_a_testimonial_slider_in_the_sidebar">Outputting a Testimonial Slider in the Sidebar</a></li>
		<li><a href="#testimonial_submission">Front End Testimonial Submission</a></li>
		<li><a href="#testimonial_filters">Testimonial Filters</a></li>
		<li><a href="#testimonial_count">Outputting a Count of your Testimonials</a></li>
		<li><a href="#testimonial_aggregate_rating">Outputting an Aggregate Rating of your Testimonials</a></li>
		<li><a href="#testimonial_search">Outputting a Testimonials Search Form</a></li>
		<li><a href="#testimonial_options">Options</a></li>
	</ol>
	<div id="add_a_new_testimonial">
		<h3 id="adding-a-new-testimonial">Adding a New Testimonial</h3>
		<p>Adding a New Testimonial is easy!  There are 3 ways to start adding a new testimonial</p>
		<p><strong>How to Add a New Testimonial</strong></p>
		<ol>
		<li>Click on "+ New" -&gt; Testimonial, from the Admin Bar <em>or</em></li>
		<li>Click on "Add New Testimonial" from the Menu Bar in the WordPress Admin <em>or</em></li>
		<li>Click on "Add New Testimonial" from the top of the list of Testimonials, if you're viewing them all.</li>
		</ol>
		<p><strong>New Testimonial Content</strong></p>
		<p>You have a few things to pay attention to:</p>
		<ul>
		<li><strong>Testimonial Title:</strong> this content can be displayed above your Testimonial.</li>
		<li><strong>Testimonial Body:</strong> this is the content of your Testimonial.  This will be output and displayed about the Testimonial Information fields.</li>
		<li><strong>Client Name:</strong> This field is displayed first, below the Testimonial Body. The field title is just an example use - of course you don't have to put the client name here.</li>
		<li><strong>Position / Location / Other:</strong> This field is displayed second, below the Client Name.  The field title is just an example use - you can put whatever you want here.</li>
		<li><strong>Location / Product Reviewed / Other:</strong>This field is optional and is displayed third, below Position / Location / Other.  The field title is just an example of how it can be used - typically this item is used for the Item the Testimonial is being left about and will match the itemReviewed Schema.</li>
		<li><strong>Rating:</strong> This field is displayed in different locations, depending on your theme, and represents the out of 5 rating for the item reviewed.  If you are collecting ratings with your Testimonials, place them in this field.</li>
		<li><strong>Featured Image:</strong> This image is shown to the left of the testimonial, as a 50px by 50px thumbnail by default.</li>
		<li><strong>Testimonial Category:</strong> This field is useful for grouping and organizing your Testimonials.  This can be useful if you want to display Testimonials about a certain topic as a group.</li>
		</ul>
	</div>
	<div id="edit_a_testimonial">
		<h3 id="editing-a-testimonial">Editing a Testimonial</h3>
		<p><strong>This is as easy as adding a New Testimonial!</strong></p>
		<ol>
		<li>Click on "Testimonials" in the Admin Menu.</li>
		<li>Hover over the Testimonial you want to Edit and click "Edit".</li>
		<li>Change the fields to the desired content and click "Update".</li>
		</ol>
	</div>
	<div id="delete_a_testimonial">
		<h3 id="deleting-a-testimonial">Deleting a Testimonial</h3>
		<p><strong>This is as easy as adding a New Testimonial!</strong></p>
		<ol>
		<li>Click on "Testimonials" in the Admin Menu.</li>
		<li>Hover over the Testimonial you want to Delete and click "Delete".</li>
		</ol>
		<p><strong>You can also change the Status of a Testimonial, if you want to keep it on file.</strong></p>
	</div>
	<div id="testimonial_theme_via_shortcode">
		<h3 id="controlling-testimonial-theme-via-shortcode">Controlling Testimonial Theme via Shortcode</h3>
		<ul>
			<li>To select a theme via the Shortcode, use the following shortcode syntax:
				<textarea>[testimonials theme='card_style-maroon']</textarea></li>
			<li>To get the full list of Themes available to your version of Easy Testimonials, we recommend using the <a href="<?php echo get_admin_url() . "admin.php?page=easy-testimonials-shortcode-generator"; ?>">Shortcode Generator</a>.</li>
		</ul>
	</div>
	<div id="outputting_random_testimonials">
		<h3 id="outputting-random-testimonials">Outputting Random Testimonials</h3>
		<ul>
			<li>To output a Random Testimonial, place the following shortcode in the desired area of the Page or Post Content:<br/>
				<textarea>[random_testimonial]</textarea>
			</li>
			<li>To display more than one Random Testimonial, use the following shortcode, where count is the number of testimonials you want displayed.<br />
				<textarea>[random_testimonial count='3']</textarea>
			</li>
			<li>To display the title above the Random Testimonial and use excerpts (short versions of the Testimonial), use the following shortcode: <br/>
				<textarea>[random_testimonial show_title='1' use_excerpt='1']</textarea>
			</li>
			<li>To output Random Testimonials from a specific Category, with images, use the following shortcode: <br/>
				<textarea>[random_testimonial category='the_category_slug' show_thumbs='1']</textarea>
			</li`>
			<li>To display the rating along with the random testimonial, use the following shortcode:<br/>
				<textarea>[random_testimonial show_rating='stars']</textarea><br/>
				<p class="description">Possible values for show_rating are <code>show_rating=before</code>, for the rating to appear before the testimonial content, <code>show_rating=after</code>, for the rating to appear after the testimonial content, and <code>show_rating=stars</code>, to show the rating in stars format.</p>
			</li>
		</ul>
	</div>
	<div id="output_a_list_of_testimonials">
		<h3 id="output-a-list-of-testimonials">Output a List of Testimonials</h3>
		<ul>
			<li>To output a list of all the Testimonials, place the following shortcode in the desired area of the Page or Post Content:<br/>
				<textarea>[testimonials]</textarea>
			</li>
			<li>To output a list of the 5 most recent Testimonials, use the following shortcode: <br/>
				<textarea>[testimonials count='5' order='DESC' orderby='date']</textarea>
				<p class="description">Acceptable attributes for <code>'order'</code> are <code>'ASC'</code> and <code>'DESC'</code>.</p>
				<p class="description">Acceptable attributes for <code>'orderby'</code> are <code>'none'</code>,<code>'ID'</code>,<code>'author'</code>,<code>'title'</code>,<code>'name'</code>,<code>'date'</code>,<code>'modified'</code>,<code>'parent'</code>, and <code>'rand'</code></p>
			</li>
			<li>To display the title above the list of Testimonials and use excerpts (short versions of the Testimonial), use the following shortcode: <br/>
				<textarea>[testimonials show_title='1' use_excerpt='1']</textarea>
			</li>
			<li>To output Testimonials from a specific Category, with images, use the following shortcode: <br/>
				<textarea>[testimonials category='the_category_slug' show_thumbs='1']</textarea>
			</li>
			<li>To display the rating along with the testimonials, use the following shortcode:<br/>
				<textarea>[testimonials show_rating='stars']</textarea><br/>
				<p class="description">Possible values for show_rating are <code>show_rating=before</code>, for the rating to appear before the testimonial content, <code>show_rating=after</code>, for the rating to appear after the testimonial content, and <code>show_rating=stars</code>, to show the rating in stars format.</p>
			</li>
			<li>To paginate the list of Testimonials, use the following shortcode:
				<textarea>[testimonials paginate='1' testimonials_per_page='5']</textarea>
			</li>
		</ul>
	</div>
	<div id="output_a_grid_of_testimonials">
		<h3 id="output-a-grid-of-testimonials">Output a Grid of Testimonials</h3>
		<ul>
			<li>To output a grid of all the Testimonials, place the following shortcode in the desired area of the Page or Post Content:<br/>
				<textarea>[testimonials_grid]</textarea>
			</li>
			<li>To output a grid of the 5 most recent Testimonials, use the following shortcode: <br/>
				<textarea>[testimonials_grid count='5' order='DESC' orderby='date']</textarea>
				<p class="description">Acceptable attributes for <code>'order'</code> are <code>'ASC'</code> and <code>'DESC'</code>.</p>
				<p class="description">Acceptable attributes for <code>'orderby'</code> are <code>'none'</code>,<code>'ID'</code>,<code>'author'</code>,<code>'title'</code>,<code>'name'</code>,<code>'date'</code>,<code>'modified'</code>,<code>'parent'</code>, and <code>'rand'</code></p>
			</li>
			<li>To display the title above the grid of Testimonials and use excerpts (short versions of the Testimonial), use the following shortcode: <br/>
				<textarea>[testimonials_grid show_title='1' use_excerpt='1']</textarea>
			</li>
			<li>To output Testimonials from a specific Category, with images, use the following shortcode: <br/>
				<textarea>[testimonials_grid category='the_category_slug' show_thumbs='1']</textarea>
			</li>
			<li>To display the rating along with the testimonials, use the following shortcode:<br/>
				<textarea>[testimonials_grid show_rating='stars']</textarea><br/>
				<p class="description">Possible values for show_rating are <code>show_rating=before</code>, for the rating to appear before the testimonial content, <code>show_rating=after</code>, for the rating to appear after the testimonial content, and <code>show_rating=stars</code>, to show the rating in stars format.</p>
			</li>
			<li>To paginate the grid of Testimonials, use the following shortcode:
				<textarea>[testimonials_grid paginate='1' testimonials_per_page='5']</textarea>
			</li>
			<li>To ouput a grid of 4 specific testimonials by ID, use the following shortcode (update the values for ID to match the IDs of the desired testimonials.):
				<textarea>[testimonials_grid ids=5,7,3,4]</textarea>
			</li>	
			<li>To output a grid of Testimonials with 3 columns, spaced 3% apart, and each cell of the grid 25% wide, use the following shortcode:
				<textarea>[testimonials_grid cols='3' grid_spacing="3%" cell_width="25%"]</textarea>
			</li>
			<li>To output a grid of Testimonials with 3 columns, spaced 3% apart, and each cell of the grid with the same height as others in it's row:
				<textarea>[testimonials_grid cols='3' grid_spacing="3%" equal_height_rows="true"]</textarea>
			</li>
		</ul>
	</div>
	<div id="output_a_testimonial_in_the_sidebar">
		<h3 id="output-a-testimonial-in-the-sidebar">Output a Random Testimonial in the Sidebar</h3>
		<ul>
		<li>To output a Random Testimonial in the Sidebar, use the Widgets section of your WordPress Theme, accessible via the Appearance Menu in the WordPress Admin.</li>  
		<li>You can show more than one random testimonial by placing a number in the Count field.</li>
		<li>You can choose a Category to pick the testimonial from, with the Category drop-down.</li>
		<li>You can show the Date of the Testimonial by checking Show Testimonial Date.</li>
		<li>You can show the Rating of the Testimonial by picking an option from the Show Rating drop-down.</li>
		<li>You can show the Testimonial Title above the Testimonial by checking Show Testimonial Title. <strong>Note: The Title Field on the Widget is displayed above the Widget, if your theme supports Widget Titles - this is different than the Testimonial Title.</strong></li>
		</ul>
	</div>
	<div id="outputting_a_testimonial_slider">
		<h3 id="outputting-a-testimonial-slider">Outputting a Testimonial Slider</h3>
		<ul>
			<li><strong>NOTE:</strong> You can view live examples <a href="https://goldplugins.com/documentation/easy-testimonials-documentation/easy-testimonials-examples/" title="Example Testimonial Sliders">here</a>.</li>
			<li>Easy Testimonials Supports Cycle2!  To output a sliding widget, use the following shortcode:<br/>
				<textarea>[testimonials_cycle]</textarea>
			</li>
			<li>The same properties as the list of testimonials, such as Showing the Title and controlling the Count, also apply.  To output a Testimonial Cycle using excerpts, from a specific category, with images, use the following shortcode: <br/>
				<textarea>[testimonials_cycle category='the-category-slug' use_excerpt='1' show_thumbs='1']</textarea>
			</li>
			<li>To show a randomly ordered Testimonial Cycle, use the following shortcode: <br/>
				<textarea>[testimonials_cycle random='true']</textarea>
			</li>
			<li>To show a sliding Testimonial Cycle, with 10 seconds between each transition, use the following shortcode: <br/>
				<textarea>[testimonials_cycle timer='10000' transition='scrollHorz']</textarea>
				<p class="description">To pick from our full list of available transitions, we recommend using the <a href="<?php echo get_admin_url() . "admin.php?page=easy-testimonials-shortcode-generator"; ?>">Shortcode Generator</a>.</p>
			</li>
			<li>To show a sliding Testimonial Cycle, using the 5 most recent Testimonials ordered chronologically, use the following shortcode: <br/>
				<textarea>[testimonials_cycle count='5' order='DESC' orderby='date']</textarea>
				<p class="description">Acceptable attributes for <code>'order'</code> are <code>'ASC'</code> and <code>'DESC'</code>.</p>
				<p class="description">Acceptable attributes for <code>'orderby'</code> are <code>'none'</code>,<code>'ID'</code>,<code>'author'</code>,<code>'title'</code>,<code>'name'</code>,<code>'date'</code>,<code>'modified'</code>,<code>'parent'</code>, and <code>'rand'</code></p>
			</li>
			<li>To show a Testimonial Cycle that automatically changes height to match the Testimonial body, use the following shortcode: <br/>
				<textarea>[testimonials_cycle auto_height='container']</textarea>
			</li>
			<li>To show a Testimonial Cycle that sets the slideshow height to the height of tallest Testimonial, use the following shortcode: <br/>
				<textarea>[testimonials_cycle auto_height='calc']</textarea>
			</li>
			<li>To show a Testimonial Cycle with 3 Testimonials per Slide and Pager Icons below the Slider, use the following shortcode: <br/>
				<textarea>[testimoanials_cycle pager='1' testimonials_per_slide='3']</textarea>
			</li>
			<li>To show a sliding Testimonial Cycle, with Previous and Next buttons, use the following shortcode: <br/>
				<textarea>[testimonials_cycle prev_next='1']</textarea>
			</li>
			<li>To show a sliding Testimonial Cycle, that only transitions when Prev or Next is clicked, use the following shortcode: <br/>
				<textarea>[testimonials_cycle paused='1' prev_next='1']</textarea>
			</li>	 
			<li>To show a sliding Testimonial Cycle, that pauses transitions when the mouse is hovering over the slideshow, use the following shortcode: <br/>
				<textarea>[testimonials_cycle pause_on_hover='1']</textarea>
			</li>
		</ul>
	</div>
	<div id="outputting_a_testimonial_slider_in_the_sidebar">
		<h3 id="outputting-a-testimonial-slider-in-the-sidebar">Outputting a Testimonial Slider in the Sidebar</h3>
		<ul>
			<li>To output a Testimonial Cycle in the Sidebar, use the Widgets section of your WordPress Theme, accessible via the Appearance Menu in the WordPress Admin.</li>  
			<li>You can control how many testimonials are shown by placing a number in the Count field - make sure you have at least 2, if you want them to Cycle!</li>
			<li>You can show the Date of the Testimonial by checking Show Testimonial Date.</li>
			<li>You can control the number of Testimonials Per Slide by using the Testimonials Per Slide input.</li>
			<li>You can show clickable pager icons below the Testimonial slideshow by checking Show Pager Icons.</li>
			<li>You can Randomize the order of the Slideshow by checking Random Testimonial Order.</li>
			<li>You can limit the length of the Testimonial to just the Excerpt by checking Use Testimonial Excerpt.</li>
			<li>You can show the Testimonial Title above the Testimonial by checking Show Testimonial Title.  <strong>Note: The Title Field on the Widget is displayed above the Widget, if your theme supports Widget Titles - this is different than the Testimonial Title.</strong></li>
			<li>You can control the time between transitions using the Timer field - every 1000 equals 1 second.</li>
			<li>You can choose a Category to pick the testimonial from, with the Category drop-down.</li>
			<li>You can show the Rating of the Testimonial by picking an option from the Show Rating drop-down.</li>
		</ul>
	</div>
	<div id="testimonial_submission">
		<h3 id="front-end-testimonial-submission">Front End Testimonial Submission</h3>
		<ul>
			<li><strong>NOTE:</strong> This feature requires the <a href="https://goldplugins.com/our-plugins/easy-testimonials-details/" title="Easy Testimonials Pro">Pro version of Easy Testimonials</a>.</li>
			<li>To display the Testimonial Submission Form, use the following shortcode: <br/> 
				<textarea>[submit_testimonial]</textarea>
			</li>
			<li>Any submissions will be added to your Testimonials list, on the back end, as pending Testimonials.  Only Testimonials that you choose to publish will be displayed publicly.</li>
			<li>Captcha support is enabled by aqcuiring and inputting your <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA API information</a>, or by installation of the <a href="https://wordpress.org/plugins/really-simple-captcha/" title="Really Simple Captcha">Really Simple Captcha plugin</a>.</li>
			<li>Front End Image Submission is enabled via a checkbox on the Submission Form Options tab.</li>
			<li>Labels, descriptions, and visibility of fields can be controlled via the <a href="<?php echo get_admin_url() . "admin.php?page=easy-testimonials-submission-settings"; ?>">Submission Form Options</a> screen.</li>
		</ul>
	</div>
	<div id="testimonial_filters">
		<h3 id="using-filters-to-customize-output">Using Filters to Customize Output</h3>
		<p>We provide the following filters to developers for customizing output even further:</p>
		<ul>
			<li><p><code>easy_t_random_testimonials_html</code> -- random testimonials filter</p></li>
			<li><p><code>easy_t_single_testimonial_html</code> -- single testimonial filter</p></li>
			<li><p><code>easy_t_testimonials_html</code> -- all testimonials list filter</p></li>
			<li><p><code>easy_t_testimonials_cyle_html</code> -- testimonials cycle filter</p></li>
			<li><p><code>easy_t_submission_form</code> -- testimonial submission form</p></li>
		</ul>
	</div>	
	<div id="testimonial_count">
		<h3 id="outputting-a-count-of-your-testimonials">Outputting a Count of your Testimonials</h3>
		<ul>
			<li>To display a numerical Count of your Testimonials, use the following shortcode where you want the Number to appear: <br/>
				<textarea>[testimonials_count]</textarea>
			</li>
			<li>To display a numerical Count of your pending Testimonials from a specific category, use the following shortcode: <br/>
				<textarea>[testimonials_count status='pending' category='gold-plugins']</textarea>
			</li>
			<li>Supported parameters for <code>status</code> are <code>publish</code>,<code>pending</code>,<code>draft</code>,<code>future</code>,<code>private</code>,<code>trash</code>,<code>any</code>.  For example, the following shortcode will display a count of all Testimonials, excluding those in the Trash: <br/>
				<textarea>[testimonials_count status='any']</textarea>
			</li>
			<li><strong>Note:</strong> this Shortcode only displays the numerical count -- you will need to add any words yourself.  For example, "There are currently [testimonials_count] Testimonials on your website!"</li>
		</ul>
	</div>	<div id="testimonial_aggregate_rating">
		<h3 id="outputting-an-aggregate-rating-of-your-testimonials">Outputting an Aggregate Rating of all Testimonials on your Site</h3>
		<ul>
			<li>To display aggregate rating of your Testimonials, use the following shortcode where you want the schema.org markup to appear: <br/>
				<textarea>[testimonials_count show_aggregate_rating='1']</textarea>
			</li>
			<li>**Note:** This will use the Global Item Reviewed option, from your plugin's settings tabs.</li>
		</ul>
	</div>
	<div id="testimonial_search">
		<h3 id="outputting-a-testimonials-search-form">Outputting a Testimonials Search Form</h3>
		<ul>
			<li>To output a Testimonials search form on your site, use the following shortcode where you want the form to appear: <br/>
				<textarea>[easy_t_search_testimonials]</textarea>
			</li>
			<li><strong>Note:</strong> this Shortcode only displays the form -- you'll want to add any additional text yourself!  Search results are displayed on your default search results page.</li>
			<li><strong>Note:</strong> if Show in Search is not checked, you will not see any search results from this form.</li>
		</ul>
	</div>
	<div id="testimonial_options">
		<h3 id="options">Options</h3>
		<ul>
			<li>To control the destination of the "View More" link, set the path in the Testimonials View More Link field.</li>
			<li>To display any Featured Images that you have attached to your Testimonials, check the box next to Show Testimonial Image.</li>
			<li>To display any Testimonial Information above the content, check the box next to Show Testimonial Info Above Testimonial.</li>
			<li>To add any Custom CSS, to further modify the output of the plugin, input the CSS in the textarea labeled Custom CSS.  You do not need to include the opening or closing &lt;style&gt; tags, treat it like you're inside a CSS file.</li>
			<li>To pick a global theme, use our <a href="<?php echo get_admin_url() . "admin.php?page=easy-testimonials-style-settings"; ?>">Themes tab</a> to browse the available choices.</li>
			<li>To control the display of the Testimonial Submission Form, use the <a href="<?php echo get_admin_url() . "admin.php?page=easy-testimonials-submission-settings"; ?>">Submission Form Options tab</a>.</li>
			<li>To customize the appearance of your Testimonials, use the <a href="<?php echo get_admin_url() . "admin.php?page=easy-testimonials-display-settings"; ?>">Display Options tab</a>.</li>
			<li>To Show Testimonials in Public Search, use the Show in Search checkbox on the Basic Settings Page.</li>
			<li>To change your Registered Shortcodes, in case of conflict with theme or other plugins, use the Shortcode Options section on the Basic Settings screen.</li>
		</ul>
	</div>
</div>