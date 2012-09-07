<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * This file is part of Avalon.
 *
 * Avalon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Avalon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Avalon. If not, see <http://www.gnu.org/licenses/>.
 */

namespace avalon\http;

/**
 * The router.
 *
 * @author Jack P.
 * @package Avalon
 */
class Router
{
	public static $namespace;
	public static $controller;
	public static $method;
	public static $params = array();
	public static $args = array();
	public static $extension;
	public static $error404;
	private static $routes = array();

	// Router stuff
	public static $extensions = array('.json', '.xml', '.rss');
	
	/**
	 * Matche the request to a route and get the controller, method and arguments.
	 * @param string $request The request.
	 * @return boolean
	 */
	public static function process($request)
	{
		// Are we on the front page?
		if ($request == '/') {
			static::set_request(static::$routes['root']);
			return true;
		}
		
		// Check if we have an exact match
		if (isset(static::$routes[$request])) {
			static::set_request(static::$routes[$request]);
			return true;
		}
		
		// Loop through routes and find a regex match
		foreach (static::$routes as $route => $args) {
			$route = '#^' . $route . '(?<extension>' . implode('|', static::$extensions) . ')?$#';
			
			if (preg_match($route, $request, $params)) {
				unset($params[0]);
				$args['params'] = array_merge($args['params'], $params);
				$args['value'] = preg_replace($route, $args['value'], $request);
				$args['extension'] = isset($params['extension']) ? $params['extension'] : null;
				static::set_request($args);
				return true;
			}
		}
		
		// No match, error controller, make it so.
		static::set_request(array('value' => 'Error::404', 'params' => array()));
		return false;
	}

	/**
	 * Add a route.
	 * @param string $route The route to match to.
	 * @param string $value The controller and method to route to.
	 * @param array $params Parameters to be passed to the controller method.
	 */
	public static function add($route, $value, $params = array())
	{
		if (!isset(static::$routes[$route])) {
			static::$routes[$route] = array(
				'template' => $route,
				'value' => $value,
				'params' => $params
			);
		}
	}
	
	/**
	 * Private function to set the routed controller, method, parameters and method arguments.
	 * @param array $route The route array.
	 */
	private static function set_request($route)
	{
		// Seperate the namespace, controller, method and arguments
		$bits = explode('::', $route['value']);
		$method_bits = explode('/', end($bits));

		static::$namespace = ($ns = implode('::', array_slice($bits, 0, -2)) and !empty($ns)) ? $ns : null;
		static::$controller = $bits[count($bits) - 2];
		static::$method = $method_bits[0];
		static::$args = (isset($method_bits[1])) ? explode(',', $method_bits[1]) : array();
		static::$extension = isset($route['extension']) ? $route['extension'] : null;
		static::$params = $route['params'];

		unset($bits, $method_bits, $ns);
	}

	/**
	 * Returns the namespace in the form of a directory path.
	 *
	 * @return string
	 */
	public static function namespace_path()
	{
		return str_replace('::', '/', static::$namespace) . '/';
	}
}