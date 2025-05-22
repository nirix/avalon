<?php
/*!
 * Avalon
 * Copyright (C) 2011-2025 Jack Polgar
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

declare(strict_types=1);

namespace Avalon\Http;

use Avalon\Http\Middleware\MiddlewareInterface;
use \Exception;
use ReflectionClass;
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
    /**
     * Registered routes array.
     *
     * Indexed by name
     */
    protected static array $routes = [];

    protected static array $routesByMethod = [
        'get' => [],
        'post' => [],
        'put' => [],
        'patch' => [],
        'delete' => [],
    ];

    /**
     * Controller class.
     */
    public static string $controller;

    /**
     * Controller method.
     */
    public static string $method;

    /**
     * Method parameters.
     */
    public static array $params = [];

    /**
     * Route path attributes.
     */
    public static array $attributes = [];

    /**
     * Route/path file extension.
     *
     * @example `.json`
     */
    public static ?string $extension;

    /**
     * Accepted extensions.
     */
    public static array $extensions = ['.json', '.xml', '.atom'];

    public static array $middleware;

    /**
     * @deprecated
     */
    public static array $vars = [];
    public static bool $legacyRoute = false;

    public static function register(
        string $name,
        string $path,
        array $controller,
        array $params = [],
        array $methods = ['GET'],
    ) {
        if (isset(static::$routes[$name])) {
            throw new UnexpectedValueException(sprintf('Route "%s" already registered', $name));
        }

        $methods = array_map(fn ($method) => strtoupper($method), $methods);

        static::$routes[$name] = [
            'name' => $name,
            'route' => $path,
            'controller' => $controller,
            'params' => $params,
            'methods' => $methods,
        ];

        foreach ($methods as $method) {
            static::$routesByMethod[$method][$path] = $name;
        }
    }

    /**
     * Routes the request to the controller.
     *
     * @param Request $request
     */
    public static function handle(Request $request)
    {
        $uri = "/" . trim($request->getUri(), '/');
        $routes = static::$routesByMethod[Request::method()] ?? [];

        // The fun begins
        if (isset($routes[$uri])) {
            return static::processRoute(static::$routes[$routes[$uri]]);
        } else {
            foreach ($routes as $route) {
                $route = static::$routes[$route];
                $pattern = "#^{$route['route']}" . '(?<extension>' . implode('|', static::$extensions) . ")?$#";

                if (preg_match($pattern, $uri, $params)) {
                    unset($params[0]);
                    return static::processRoute($route, $params);
                }
            }
        }

        // 404
        return Router::set404();
    }

    /**
     * Sets the route info to that of the 404 route.
     */
    public static function set404()
    {
        if (!isset(static::$routes['404'])) {
            throw new Exception("There is no 404 route set.");
        }

        return static::processRoute(static::$routes['404']);
    }

    protected static function processRoute(
        array $route,
        array $matches = []
    ) {
        list($controller, $action) = $route['controller'];

        $reflect = new ReflectionMethod($controller, $action);

        $params = [];
        foreach ($reflect->getParameters() as $parameter) {
            $params[$parameter->getName()] = $matches[$parameter->getName()] ?? $route['params'][$parameter];
        }

        // Get middleware attributes from the controller and method
        $middleware = [];
        $controllerReflection = new ReflectionClass($controller);
        $attributes = $controllerReflection->getAttributes() + $reflect->getAttributes();

        foreach ($attributes as $attribute) {
            if (is_subclass_of($attribute->getName(), MiddlewareInterface::class)) {
                $middleware[] = $attribute->newInstance();
            }
        }

        static::$controller = $controller;
        static::$method = $action;
        static::$params = array_merge($route['params'], $params);
        static::$attributes = $matches;
        static::$extension = (isset($matches['extension']) ? $matches['extension'] : null);
        static::$middleware = $middleware;

        unset($reflect, $controller, $action, $parameter, $params, $route, $matches, $middleware, $controllerReflection, $attributes);
    }
}
