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
 * Public routes are called once or twice per page view, so this is far above what
 * a person browsing can produce and well below what a script can.
 */
const DEFAULT_LIMIT = 120;

// Length of the rate limit window, in seconds
const DEFAULT_WINDOW = 60;

// Object cache group; ignored when the site has no persistent object cache
const CACHE_GROUP = 'lqx_rest_rate';

/**
 * Count one request against a client's budget and report whether they're over it.
 *
 * Uses the object cache when the site has a persistent one (Redis, Memcached, W3TC),
 * and falls back to transients otherwise. Without a persistent object cache the
 * counters live in the options table, which is durable but writes on every request —
 * acceptable for the request volumes these routes see, and the reason the default
 * window is a whole minute rather than a few seconds.
 *
 * Fails open: if the counter can't be read or written the request is allowed. A
 * broken cache should not take the site's public endpoints down with it.
 *
 * @param string $bucket - identifier for the route being limited
 * @param int $limit - requests allowed per window
 * @param int $window - window length in seconds
 *
 * @return bool - true when the client has exceeded the limit
 */
function is_rate_limited($bucket, $limit = DEFAULT_LIMIT, $window = DEFAULT_WINDOW) {
	$limit = (int) apply_filters('lqx_rest_rate_limit', $limit, $bucket);
	$window = (int) apply_filters('lqx_rest_rate_window', $window, $bucket);

	// A limit of zero or less disables limiting for this route
	if ($limit <= 0 || $window <= 0) return false;

	// Logged-in users with a reason to make many calls aren't the threat model here
	if (is_user_logged_in() && current_user_can('edit_posts')) return false;

	$key = 'lqx_rl_' . md5($bucket . '|' . \lqx\util\get_client_ip());
	$persistent = function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache();

	if ($persistent) {
		$count = wp_cache_get($key, CACHE_GROUP);
		if ($count === false) {
			wp_cache_set($key, 1, CACHE_GROUP, $window);
			return false;
		}
		wp_cache_set($key, (int) $count + 1, CACHE_GROUP, $window);
		return (int) $count + 1 > $limit;
	}

	$count = get_transient($key);
	if ($count === false) {
		set_transient($key, 1, $window);
		return false;
	}
	set_transient($key, (int) $count + 1, $window);
	return (int) $count + 1 > $limit;
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
