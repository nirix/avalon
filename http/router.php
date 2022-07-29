<?php
/*!
 * Avalon
 * Copyright (C) 2011-2022 Jack Polgar
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

use \Exception;
use ReflectionMethod;
use UnexpectedValueException;

/**
 * Avalon's Router.
 *
 * @since 0.1
 * @package Avalon
 * @subpackage HTTP
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Router
{
    private static $routes = array();

    // Routed values
    public static $controller;
    public static $method;
    public static $params = array();
    public static $vars = array();
    public static $extension;
    public static $legacyRoute = false;
    public static $extensions = array('.json', '.xml', '.atom');

    public static function get(
        string $name,
        string $path,
        array $controller,
        array $params = []
    ) {
        if (isset(static::$routes[$path])) {
            throw new UnexpectedValueException(sprintf('Route "%s" already registered', $path));
        }

        static::register($name, $path, $controller, $params, ['GET']);
    }

    public static function register(
        string $name,
        string $path,
        array $controller,
        array $params = [],
        array $methods = ['GET']
    ) {
        if (isset(static::$routes[$path])) {
            throw new UnexpectedValueException(sprintf('Route "%s" already registered', $path));
        }

        static::$routes[$path] = [
            'name' => $name,
            'route' => $path,
            'controller' => $controller,
            'params' => $params,
            'methods' => $methods
        ];
    }

    /**
     * Adds a route to be routed.
     *
     * @param string $route  URI to match
     * @param string $value  Controller/method to route to
     * @param array  $params Default params to pass to the method
     */
    public static function add($route, $value, array $params = array())
    {
        // Don't overwrite the route
        if (!isset(static::$routes[$route])) {
            static::$routes[$route] = array(
                'route'  => $route,
                'value'  => $value,
                'params' => $params
            );
        }
    }

    /**
     * Routes the request to the controller.
     *
     * @param Request $request
     */
    public static function route(Request $request)
    {
        $uri = "/" . trim($request->getUri(), '/');

        // Is this the root route?
        if ($uri === '/' && isset(static::$routes['root'])) {
            return static::setRoute(static::$routes['root']);
        }

        // Do we have an exact match?
        if (isset(static::$routes[$uri])) {
            if (isset(static::$routes[$uri]['methods'])) {
                if (in_array(strtoupper($request->method()), static::$routes[$uri]['methods'])) {
                    return static::processRoute(static::$routes[$uri]);
                } else {
                    return Router::set404();
                }
            } else {
                return static::setRoute(static::$routes[$uri]);
            }
        }

        // The fun begins
        foreach (static::$routes as $route) {
            // Does the route match the request?
            $pattern = "#^{$route['route']}" . '(?<extension>' . implode('|', static::$extensions) . ")?$#";

            if (preg_match($pattern, $uri, $params)) {
                unset($params[0]);

                if (isset($route['controller'])) {
                    if (in_array(strtoupper($request->method()), $route['methods'])) {
                        return static::processRoute($route, $params);
                    }
                } else {
                    $route['params'] = array_merge($route['params'], $params);
                    $route['value'] = preg_replace($pattern, $route['value'], $uri);

                    return static::setRoute($route);
                }
            }
        }

        // No matches, try 404 route
        if (isset(static::$routes['404'])) {
            return static::setRoute(static::$routes['404']);
        }
        // No 404 route, Exception time! FUN :D
        else {
            throw new Exception("No routes found for '{$uri}'");
        }
    }

    /**
     * Sets the route info to that of the 404 route.
     */
    public static function set404()
    {
        if (!isset(static::$routes['404'])) {
            throw new Exception("There is no 404 route set.");
        }
        return static::setRoute(static::$routes['404']);
    }

    private static function setRoute($route)
    {
        if (isset($route['controller'])) {
            return static::processRoute($route);
        }

        $value = explode('.', $route['value']);
        $method = explode('/', implode('.', array_slice($value, 1)));
        $vars = isset($method[1]) ? explode(',', $method[1]) : array();

        static::$controller = str_replace('::', '\\', '\\' . $value[0]);
        static::$method = $method[0];
        static::$vars = $vars;
        static::$params = $route['params'];
        static::$extension = (isset($route['params']['extension']) ? $route['params']['extension'] : null);
        static::$legacyRoute = true;
    }

    protected static function processRoute(
        array $route,
        array $matches = []
    ) {
        list($controller, $action) = $route['controller'];

        $reflect = new ReflectionMethod($controller, $action);

        $params = [];
        foreach ($reflect->getParameters() as $parameter) {
            $params[$parameter->getName()] = $matches[$parameter->getName()] ?? null;
        }

        static::$controller = $controller;
        static::$method = $action;
        static::$params = array_merge($route['params'], $params);
        static::$extension = (isset($matches['extension']) ? $matches['extension'] : null);

        unset($reflect, $controller, $action, $parameter, $params, $route, $matches);
    }
}
