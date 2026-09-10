<?php

/**
 * rest.php - Base controller and rate limiting for the theme's REST routes
 *
 * @package     wp_theme_lyquix
 * @author      Lyquix
 * @copyright   Copyright (C) 2015 - 2026 Lyquix
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

namespace lqx\rest;

/**
 * Requests allowed per window, per client, for a route that doesn't set its own.
 * A single page view can call several public routes (alerts, popup, modal, geolocation)
 * and a whole office can share one address, so this leaves generous room for people
 * while still stopping a script hammering an endpoint.
 */
const DEFAULT_LIMIT = 300;

// Length of the rate limit window, in seconds
const DEFAULT_WINDOW = 60;

// Object cache group; ignored when the site has no persistent object cache
const CACHE_GROUP = 'lqx_rest_rate';

/**
 * The address a rate limit is keyed on.
 *
 * Only the header the site has configured for ip2geo is trusted (REMOTE_ADDR unless the
 * site sits behind a proxy that sets another one). Walking the usual forwarding headers
 * instead would let any client take a fresh identity per request by sending its own
 * X-Forwarded-For.
 *
 * @return string
 */
function client_key() {
	$header = (string) get_theme_mod('ip2geo_ip_address_header', 'REMOTE_ADDR');
	$value = $_SERVER[$header] ?? ($_SERVER['REMOTE_ADDR'] ?? '');

	// Proxy headers can carry a list; the first entry is the original client
	$ip = trim(explode(',', (string) $value)[0]);

	if (!filter_var($ip, FILTER_VALIDATE_IP)) $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

	return $ip;
}

/**
 * Count one request against a client's budget and report whether they're over it.
 *
 * Fixed windows: the counter key includes the current window number, so every client
 * starts again at the top of each window however steadily they browse. A single key
 * given a fresh expiry on every hit never resets for an active visitor.
 *
 * Each bucket is counted once per request. WordPress calls a route's permission callback
 * again while building the Allow header on rest_post_dispatch, so without this every
 * request would count twice.
 *
 * Counts in APCu when it is available: its increment is atomic, so concurrent requests
 * can't lose counts. Otherwise a persistent object cache is used as a best effort (W3 Total
 * Cache implements increment as read-then-write, so parallel requests can slip through).
 * With neither, the limit isn't applied: counting in the database would mean writes on
 * every public request, lost increments under concurrent load, and a leftover row per
 * visitor per minute, which costs more than it protects. Fails open for the same reason
 * if the store misbehaves.
 *
 * @param string $bucket - identifier for the route being limited
 * @param int $limit - requests allowed per window
 * @param int $window - window length in seconds
 *
 * @return bool - true when the client has exceeded the limit
 */
function is_rate_limited($bucket, $limit = DEFAULT_LIMIT, $window = DEFAULT_WINDOW) {
	static $decided = [];

	if (array_key_exists($bucket, $decided)) return $decided[$bucket];

	$limit = (int) apply_filters('lqx_rest_rate_limit', $limit, $bucket);
	$window = (int) apply_filters('lqx_rest_rate_window', $window, $bucket);

	// A limit of zero or less disables limiting for this route
	if ($limit <= 0 || $window <= 0) return $decided[$bucket] = false;

	// Logged-in users with a reason to make many calls aren't the threat model here
	if (is_user_logged_in() && current_user_can('edit_posts')) return $decided[$bucket] = false;

	$slot = (int) floor(time() / $window);
	// The site is part of the key: APCu is shared by every site on the server
	$key = 'lqx_rl_' . md5($bucket . '|' . home_url('/') . '|' . client_key() . '|' . $slot);

	if (function_exists('apcu_enabled') && apcu_enabled()) {
		apcu_add($key, 0, $window * 2);
		$count = apcu_inc($key);
	} elseif (function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache()) {
		wp_cache_add($key, 0, CACHE_GROUP, $window * 2);
		$count = wp_cache_incr($key, 1, CACHE_GROUP);
	} else {
		$count = false;
	}

	if ($count === false) return $decided[$bucket] = false;

	return $decided[$bucket] = (int) $count > $limit;
}

/**
 * The error returned once a client is over the limit.
 *
 * WP_REST_Server only reads 'status' out of a WP_Error's data, so Retry-After has to
 * be attached to the response itself once it has been built.
 *
 * @param int $window - window length in seconds, sent as Retry-After
 *
 * @return \WP_Error
 */
function rate_limited_error($window = DEFAULT_WINDOW) {
	add_filter('rest_post_dispatch', function ($response) use ($window) {
		if ($response instanceof \WP_REST_Response && $response->get_status() === 429) {
			$response->header('Retry-After', (int) $window);
		}
		return $response;
	});

	return new \WP_Error(
		'lqx_rate_limited',
		__('Too many requests. Please try again shortly.', 'lyquix'),
		['status' => 429]
	);
}

/**
 * Base controller for the theme's REST routes.
 *
 * Subclasses set $rest_base and implement get_routes(). Everything the routes have
 * in common — namespace, rate limiting, the permission callback, a response schema —
 * lives here so a new endpoint can't quietly ship without them.
 */
abstract class Controller extends \WP_REST_Controller {
	// REST namespace shared by the theme's endpoints
	protected $namespace = 'lyquix/v3';

	// Route segment, set by the subclass
	protected $rest_base = '';

	// Requests allowed per window for this route; null uses DEFAULT_LIMIT
	protected $rate_limit = null;

	// Window length in seconds for this route
	protected $rate_window = DEFAULT_WINDOW;

	/**
	 * Route definitions, in the shape register_rest_route() expects.
	 * Omit permission_callback and the public callback below is used.
	 *
	 * @return array
	 */
	abstract protected function get_routes();

	/**
	 * Register this controller's routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		$routes = $this->get_routes();

		foreach ($routes as $i => $route) {
			if (!isset($route['permission_callback'])) {
				$routes[$i]['permission_callback'] = [$this, 'public_permissions_check'];
			}
		}

		register_rest_route($this->namespace, '/' . $this->rest_base, $routes);
	}

	/**
	 * Permission callback for routes that are open to anyone: no capability check,
	 * but a per-client rate limit so an open endpoint can't be hammered.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return true|\WP_Error
	 */
	public function public_permissions_check($request) {
		$limit = $this->rate_limit === null ? DEFAULT_LIMIT : $this->rate_limit;

		if (is_rate_limited($this->namespace . '/' . $this->rest_base, $limit, $this->rate_window)) {
			return rate_limited_error($this->rate_window);
		}

		return true;
	}
}

/**
 * Adapter that turns an existing route definition into a Controller, so routes can
 * be moved onto the base class without being rewritten as individual classes.
 */
class Simple_Controller extends Controller {
	// Route definitions supplied at construction
	protected $routes = [];

	/**
	 * @param string $rest_base - route segment, without slashes
	 * @param array $routes - one route definition, or a list of them
	 * @param array $options - 'namespace', 'rate_limit', 'rate_window'
	 */
	public function __construct($rest_base, $routes, $options = []) {
		$this->rest_base = $rest_base;
		$this->routes = isset($routes['methods']) ? [$routes] : $routes;

		if (isset($options['namespace'])) $this->namespace = $options['namespace'];
		if (array_key_exists('rate_limit', $options)) $this->rate_limit = $options['rate_limit'];
		if (isset($options['rate_window'])) $this->rate_window = $options['rate_window'];
	}

	/**
	 * @return array
	 */
	protected function get_routes() {
		return $this->routes;
	}
}

/**
 * Register a public route through the base controller.
 *
 * @param string $rest_base - route segment, without slashes
 * @param array $routes - one route definition, or a list of them
 * @param array $options - 'namespace', 'rate_limit', 'rate_window'
 *
 * @return Simple_Controller
 */
function register_public_route($rest_base, $routes, $options = []) {
	$controller = new Simple_Controller($rest_base, $routes, $options);
	$controller->register_routes();
	return $controller;
}
