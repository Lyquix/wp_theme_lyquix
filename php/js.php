<?php

/**
 * js.php - Enqueue JavaScript libraries and render GTM code, and custom JS
 *
 * @version     3.4.0
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2024 Lyquix
 * @license     GNU General Public License version 2 or later
 * @link        https://github.com/Lyquix/wp_theme_lyquix
 */

//    .d8888b. 88888888888 .d88888b.  8888888b.   888
//   d88P  Y88b    888    d88P" "Y88b 888   Y88b  888
//   Y88b.         888    888     888 888    888  888
//    "Y888b.      888    888     888 888   d88P  888
//       "Y88b.    888    888     888 8888888P"   888
//         "888    888    888     888 888         Y8P
//   Y88b  d88P    888    Y88b. .d88P 888          "
//    "Y8888P"     888     "Y88888P"  888         888
//
//  DO NOT MODIFY THIS FILE!

namespace lqx\js;


/**
 * Should Swiper be loaded for the page being rendered?
 *
 * The Swiper bundle is ~160KB of JS and CSS and most pages never build a slider, but
 * the decision has to be made before wp_head() so the stylesheet can go in the head —
 * which means inspecting the post's blocks rather than the rendered markup.
 *
 * Deliberately coarse: a page carrying any slider-capable block counts, without
 * checking whether that block's settings actually turn the slider on, because those
 * settings live in postmeta and reading them for every block would cost more than the
 * request saves. Anything that isn't a singular post — archives, search, 404 — is
 * assumed to need it, since its blocks can't be inspected up front.
 *
 * Child themes that build Swipers in custom templates or custom scripts must say so:
 *
 *     add_filter('lqx_needs_swiper', fn($needs) => $needs || is_page_template('...'));
 *
 * @return bool
 */
function page_needs_swiper() {
	$needs = true;

	$post = is_singular() ? get_post() : null;

	// Only a block-built page can be inspected. Content with no blocks is rendered by a
	// template — a custom post type, a page template — whose markup isn't visible from
	// here, so assume it may build a slider.
	if ($post && has_blocks($post->post_content)) {
		$needs = false;

		foreach (['lqx/slider', 'lqx/cards', 'lqx/gallery', 'lqx/testimonial', 'lqx/logos', 'lqx/filters'] as $block) {
			if (has_block($block, $post)) {
				$needs = true;
				break;
			}
		}
	}

	return (bool) apply_filters('lqx_needs_swiper', $needs);
}

/**
 * Is Swiper wanted on this request?
 *
 * Site Settings: '0' never, '1' always (the default), '2' only on pages that need it.
 *
 * @return bool
 */
function swiper_enabled() {
	$mode = (string) get_theme_mod('swiperjs', '1');

	if ($mode === '0') return false;
	if ($mode === '2') return page_needs_swiper();

	return true;
}


/**
 * Enqueue JavaScript libraries and render GTM code, and custom JS based on theme settings
 *
 * @return void
*/
function enqueue_scripts() {
	// Prevent adding js libraries in wp_head()
	global $wp_scripts;
	$remove_js_libraries = explode("\n", trim(get_theme_mod('remove_js_libraries', '')));
	foreach ($wp_scripts->queue as $i => $js) {
		if (array_search(trim($js), $remove_js_libraries)) unset($wp_scripts->queue[$i]);
	}

	// Enable jQuery
	if (get_theme_mod('enable_jquery', '1')) {
		wp_enqueue_script('jquery');
	} else {
		wp_dequeue_script('jquery');
	}

	// Front-end jQuery version, jQuery 4 by default. Swapping the src of 'jquery-core'
	// rather than the 'jquery' alias keeps every dependency that declares 'jquery'
	// resolving normally. Front end only: wp-admin and the block editor run against the
	// jQuery WordPress ships, and plugins are entitled to assume that.
	$jquery_4 = get_theme_mod('jquery_version', '4') === '4' && get_theme_mod('enable_jquery', '1');

	if ($jquery_4) {
		wp_deregister_script('jquery-core');
		wp_register_script(
			'jquery-core',
			\lqx\cdn_mirror\get_url('https://cdn.jsdelivr.net/npm/jquery@4/dist/jquery.min.js'),
			[],
			'4',
			false
		);

		// Migrate has to move with it: the 'jquery' alias lists Migrate as a dependency,
		// so WordPress loads it whether or not it was asked for, and Migrate 3 throws
		// against jQuery 4.
		wp_deregister_script('jquery-migrate');
		wp_register_script(
			'jquery-migrate',
			\lqx\cdn_mirror\get_url('https://cdn.jsdelivr.net/npm/jquery-migrate@4/dist/jquery-migrate.min.js'),
			['jquery-core'],
			'4',
			false
		);
	}

	// jQuery Migrate. Defaults on under jQuery 4 and off otherwise (matching WordPress):
	// jQuery 4 removed $.trim, $.isArray, $.isFunction, $.type, $.now, $.proxy, $.unique,
	// $.parseJSON, .bind()/.unbind() and .delegate()/.undelegate(), and Migrate 4 restores
	// every one of them while logging each patch to the console. That log is the project's
	// to-do list: clear the warnings, then turn Migrate off for the extra 14KB.
	if (get_theme_mod('enable_jquery_migrate', $jquery_4 ? '1' : '0')) {
		wp_enqueue_script('jquery-migrate');
	} else {
		// Dequeuing alone does nothing here — the 'jquery' alias depends on Migrate, so
		// WordPress pulls it back in. It has to come off the alias to actually go away.
		$jquery_alias = $wp_scripts->query('jquery', 'registered');
		if ($jquery_alias) $jquery_alias->deps = array_values(array_diff($jquery_alias->deps, ['jquery-migrate']));

		wp_dequeue_script('jquery-migrate');
	}

	// Enable jQuery UI
	if (get_theme_mod('enable_jquery_ui', '0')) {
		wp_enqueue_script('jquery-ui-core');
		if (get_theme_mod('enable_jquery_ui') == 2) wp_enqueue_script('jquery-ui-sortable');
	} else {
		wp_dequeue_script('jquery-ui-core');
		wp_dequeue_script('jquery-ui-sortable');
	}

	// Array to store all scripts to be loaded
	$scripts = [];

	// Use non minified version?
	$non_min_js = get_theme_mod('non_min_js', '0');

	// Force non-minified version on local environments
	if (\lqx\util\is_local_environment()) $non_min_js = '1';

	// MobileDetect and Day.js were loaded on every page for three booleans and a date
	// parse; both are now done natively in the bundle (detect.ts, util.parseDate).
	// A project that still needs either can add it under Additional JS Libraries.

	// Swiper
	if (swiper_enabled()) {
		$scripts[] = [
			'handle' => 'swiper',
			'url' => \lqx\cdn_mirror\get_url('https://cdn.jsdelivr.net/npm/swiper@14/swiper-bundle.min.js'),
			'version' => '14'
		];

		// Swiper 11 drew its navigation arrows with a ::after icon font; Swiper 12+ drops
		// that and injects an <svg class="swiper-navigation-icon"> into each arrow button
		// instead. Every slider in this theme paints its own arrow as a background image,
		// so the injected SVG lands on top of it and you get two arrows. Turn the feature
		// off globally rather than hiding it in CSS, so the markup stays clean.
		//
		// Runs on the 'swiper' handle, so it is printed immediately after the library and
		// long before anything constructs a Swiper. Projects that do want Swiper's own
		// arrows can return true here, or pass addIcons per instance.
		$add_icons = apply_filters('lqx_swiper_add_icons', false) ? 'true' : 'false';
		$swiper_defaults = 'if (window.Swiper && Swiper.extendDefaults) Swiper.extendDefaults({ navigation: { addIcons: ' . $add_icons . ' } });';
	}

	// Additional JS Libraries
	$add_js_libraries = explode("\n", trim(get_theme_mod('add_js_libraries', '')));
	foreach ($add_js_libraries as $jsurl) {
		$jsurl = trim($jsurl);
		if ($jsurl) {
			// Check if script is local or remote
			if (parse_url($jsurl, PHP_URL_SCHEME)) {
				// Absolute URL
				$scripts[] = [
					'url' => \lqx\cdn_mirror\get_url($jsurl),
					'handle' => base_convert(crc32($jsurl), 16, 36)
				];
			} elseif (parse_url($jsurl, PHP_URL_PATH)) {
				// Relative URL
				// Add leading / if missing
				if (substr($jsurl, 0, 1) != '/') $jsurl = '/' . $jsurl;
				// Check if file exist
				if (file_exists(ABSPATH . $jsurl)) {
					$scripts[] = [
						'url' => $jsurl,
						'version' => date("YmdHis", filemtime(get_home_path() . $jsurl)),
						'handle' => base_convert(crc32($jsurl), 16, 36)
					];
				}
			}
		}
	}

	// Lyquix
	if (file_exists(get_stylesheet_directory() . '/js/lyquix' . ($non_min_js ? '' : '.min') . '.js')) {
		$scripts[] = [
			'handle' => 'lyquix',
			'url' => get_stylesheet_directory_uri() . '/js/lyquix' . ($non_min_js ? '' : '.min') . '.js',
			'version' => date("YmdHis", filemtime(get_stylesheet_directory() . '/js/lyquix' . ($non_min_js ? '' : '.min') . '.js')),
			'strategy' => 'defer'
		];
	}

	// Vue
	if (file_exists(get_stylesheet_directory() . '/js/vue' . ($non_min_js ? '' : '.min') . '.js')) {
		$scripts[] = [
			'handle' => 'vue',
			'url' => \lqx\cdn_mirror\get_url('https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global' . ($non_min_js ? '' : '.prod') . '.js')
		];
		$scripts[] = [
			'handle' => 'lyquix-vue',
			'url' => get_stylesheet_directory_uri() . '/js/vue' . ($non_min_js ? '' : '.min') . '.js',
			'version' => date("YmdHis", filemtime(get_stylesheet_directory() . '/js/vue' . ($non_min_js ? '' : '.min') . '.js'))
		];
	}

	// Scripts
	if (file_exists(get_stylesheet_directory() . '/js/scripts' . ($non_min_js ? '' : '.min') . '.js')) {
		$scripts[] = [
			'handle' => 'scripts',
			'url' => get_stylesheet_directory_uri() . '/js/scripts' . ($non_min_js ? '' : '.min') . '.js',
			'version' => date("YmdHis", filemtime(get_stylesheet_directory() . '/js/scripts' . ($non_min_js ? '' : '.min') . '.js')),
			'strategy' => 'defer'
		];
	}

	// Queue styles
	foreach ($scripts as $js_url) {
		// WP 6.3+ accepts an array with a loading strategy; older versions treat the
		// non-empty array as a truthy in_footer, so this degrades to footer loading
		$args = isset($js_url['strategy']) && version_compare(get_bloginfo('version'), '6.3', '>=')
			? ['in_footer' => true, 'strategy' => $js_url['strategy']]
			: true;
		wp_enqueue_script($js_url['handle'], $js_url['url'], [], $js_url['version'] ?? null, $args);

		if ($js_url['handle'] === 'swiper' && isset($swiper_defaults)) wp_add_inline_script('swiper', $swiper_defaults);
	}
}
add_action('wp_enqueue_scripts', '\lqx\js\enqueue_scripts', 100);


/**
 * Carry the site's CSP nonce (if any) on every script tag the theme prints.
 * See \lqx\util\csp_nonce().
 */
foreach (['wp_inline_script_attributes', 'wp_script_attributes'] as $lqx_script_attr_filter) {
	add_filter($lqx_script_attr_filter, function ($attributes) {
		$nonce = \lqx\util\csp_nonce();

		if ($nonce !== '' && !isset($attributes['nonce'])) $attributes['nonce'] = $nonce;

		return $attributes;
	});
}




/**
 * Limit the Gravity Forms reCAPTCHA scripts to pages that carry a form.
 *
 * The plugin loads them on every page on purpose: reCAPTCHA v3 scores a visitor from
 * the behaviour leading up to a submission, so the more pages it sees, the better it
 * can tell a person from a bot. That costs roughly 850KB on every page view, and on a
 * site whose forms live on a handful of pages that is most of the page weight for no
 * visible feature.
 *
 * The trade is real, so this is opt-in per project (Site Settings > reCAPTCHA scripts):
 * narrowing it means reCAPTCHA judges a submission on less history, which can push
 * genuine submissions toward bot-like scores. Turn it on where the forms are low-risk
 * or the score threshold is lenient; leave it off where spam is a live problem, and
 * watch entry scores for a few days after switching.
 *
 * Runs on wp_print_footer_scripts (before the footer scripts print at 20) rather than
 * on wp_enqueue_scripts, because forms rendered from block or template markup enqueue
 * their scripts during rendering — by the footer the answer is settled.
 *
 * @return void
 */
function scope_recaptcha_scripts() {
	if (is_admin()) return;
	if (get_theme_mod('recaptcha_scope', 'all') !== 'forms') return;

	// Gravity Forms only enqueues its own runtime when a form is on the page
	if (wp_script_is('gform_gravityforms', 'enqueued') || wp_script_is('gform_gravityforms', 'done')) return;

	// Let a project keep it, e.g. for a form injected by something we can't see here
	if (apply_filters('lqx_keep_recaptcha', false)) return;

	// The localized -extra data rides on the handle, so it goes with it
	wp_dequeue_script('gforms_recaptcha_recaptcha');
	wp_dequeue_script('gforms_recaptcha_frontend');
}
add_action('wp_print_footer_scripts', '\lqx\js\scope_recaptcha_scripts', 9);

/**
 * Renders the Lyquix options and scripts options
 * 		- lqx options
 * 		- scripts options
 *
 * @return void
 * 		Outputs the script tag with the Lyquix options and scripts options
 */
function render_lyquix_options() {
	// Set lqx options
	$lqx_options = [
		'debug' => get_theme_mod('lqx_debug', '0'),
		'siteURL' => get_site_url(),
		'tmplURL' => get_template_directory_uri()
	];

	if (get_theme_mod('ga4_account', '')) $lqx_options['analytics'] = ['measurementId' => get_theme_mod('ga4_account')];
	if (!get_theme_mod('ga_pageview', '1')) $lqx_options['analytics']['sendPageview'] = false;
	if (get_theme_mod('ga_via_gtm', '0')) $lqx_options['analytics']['usingGTM'] = true;
	// Get regions information from cache (avoids expensive ACF option queries)
	$regions_config = \lqx\regions\get_regions_config();
	$full_regions = $regions_config['full_regions'] ?? [];
	if (is_array($full_regions) && count($full_regions)) {
		$lqx_options['geolocate'] = ['regions' => $full_regions];
	}


	// Merge with options from template settings
	$theme_lqx_options = json_decode(get_theme_mod('lqx_options'), true);
	if (is_array($theme_lqx_options)) $lqx_options = array_replace_recursive($lqx_options, $theme_lqx_options);
	$lqx_options = apply_filters('lqx_options', $lqx_options);
	// lyquix.js is loaded with defer, so the bundle may not have executed yet when this
	// inline runs: listen for lqxload on both event systems (the bundle announces itself
	// with a jQuery trigger, which native addEventListener cannot hear); init is run-once
	wp_print_inline_script_tag('((lqxOptions) => {
		if (typeof lqx !== "undefined" && typeof lqx.init === "function") lqx.init(lqxOptions);
		else {
			document.addEventListener("lqxload", function () { lqx.init(lqxOptions); });
			if (window.jQuery) jQuery(document).one("lqxload", function () { lqx.init(lqxOptions); });
		}
	})(JSON.parse(new TextDecoder().decode(Uint8Array.from(atob("' . base64_encode(json_encode($lqx_options)) . '"), c => c.charCodeAt(0)))));', ['id' => 'lqx-options']);

	$scripts_options = json_decode(get_theme_mod('scripts_options'), true);
	if (!is_array($scripts_options)) $scripts_options = [];
	wp_print_inline_script_tag('(($lqxOptions) => {
		if (typeof $lqx !== "undefined" && typeof $lqx.init === "function") $lqx.init($lqxOptions);
		else {
			document.addEventListener("$lqxload", function () { $lqx.init($lqxOptions); });
			if (window.jQuery) jQuery(document).one("$lqxload", function () { $lqx.init($lqxOptions); });
		}
	})(JSON.parse(new TextDecoder().decode(Uint8Array.from(atob("' . base64_encode(json_encode($scripts_options)) . '"), c => c.charCodeAt(0)))));', ['id' => 'lqx-scripts-options']);
}

/**
 * Renders the Google Tag Manager head code.
 * It first checks if the GTM account is set in the theme settings.
 * Then, it renders the GTM head code if the account is set.
 *
 * @return void
 * 		Outputs the script tag with the GTM head code
 */
function render_gtm_head_code() {
	// Load GTM head code
	if (get_theme_mod('gtm_account', '')) {
		echo "<!-- Google Tag Manager -->\n";
		wp_print_inline_script_tag(
			"(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', '" . esc_js(get_theme_mod('gtm_account')) . "');",
			['id' => 'lqx-gtm']
		);
		echo "<!-- End Google Tag Manager -->\n";
	}
}

/**
 * Renders the Google Tag Manager body code.
 * It first checks if the GTM account is set in the theme settings.
 * Then, it renders the GTM body code if the account is set.
 *
 * @return void
 * 		Outputs the iframe tag with the GTM body code
 */
function render_gtm_body_code() {
	// Load GTM head code
	if (get_theme_mod('gtm_account', '')): ?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= esc_url(get_theme_mod('gtm_account')) ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
	<?php endif;
}

/**
 * Renders the Microsoft Clarity code.
 * It first checks if the Clarity account is set in the theme settings.
 * Then, it renders the Clarity code if the account is set.
 *
 * @return void
 * 		Outputs the script tag with the Clarity code
 */
function render_page_custom_js() {
	// Render page custom CSS and JS
	if (function_exists('get_field')) {
		$custom_js = get_field('custom_js');
		if ($custom_js) wp_print_inline_script_tag(str_ireplace('</script', '<\/script', $custom_js), ['id' => 'lqx-page-custom-js']);
	}
}
