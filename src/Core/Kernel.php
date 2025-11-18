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

namespace Avalon\Core;

use Avalon\Http\Router;
use Avalon\Http\Request;
use Avalon\Http\Response;
use Avalon\Output\Body;
use Exception;
use ReflectionAttribute;

/**
 * Avalon's Kernel.
 *
 * @since 0.1
 * @package Avalon
 * @subpackage Core
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Kernel
{
    private static $version = '0.9';
    private static Controller $app;

    /**
     * Initializes the the kernel and routes the request.
     */
    public static function init()
    {
        session_start();

        // Route the request
        Router::route(new Request);

        // Check if the routed controller and method exists
        if (
            !class_exists(Router::$controller) ||
            (Router::$legacyRoute && !method_exists(Router::$controller, 'action_' . Router::$method)) ||
            (!Router::$legacyRoute && !method_exists(Router::$controller, Router::$method))
        ) {
            Router::set404();
        }
    }

    /**
     * Runs the before or after filters.
     */
    private static function runFilters(string $type): void
    {
        $filters = array_merge(
            isset(static::$app->{$type}['*']) ? static::$app->{$type}['*'] : [],
            isset(static::$app->{$type}[Router::$method]) ? static::$app->{$type}[Router::$method] : []
        );
        foreach ($filters as $filter) {
            static::$app->{$filter}(Router::$method);
        }
        unset($filters, $filter);
    }

    /**
     * Executes the routed request.
     */
    public static function run(): void
    {
        $pipeline = static::buildPipeline(Router::$middleware, function () {
            // Start the app
            static::$app = new Router::$controller;

            // Before filters
            static::runFilters('before');

            // Call the method
            $output = null;
            if (static::$app->render['action']) {
                if (Router::$legacyRoute) {
                    $output = call_user_func_array(array(static::$app, 'action_' . Router::$method), Router::$vars);
                } else {
                    $output = [static::$app, Router::$method](...Router::$params);
                }
            }

            // After filters
            static::runFilters('after');

            return $output;
        });

        $output = $pipeline();

        if ($output instanceof Response) {
            $output->send();
        } elseif (is_array($output)) {
            Body::clear();
            header('Content-Type: application/json; charset=utf-8');
            Body::append(json_encode($output));

            static::$app->render['layout'] = false;
            static::$app->render['view'] = false;
            static::$app->__shutdown();
        } else {
            // If an object is returned, use the `response` variable if it's set.
            if (is_object($output)) {
                $output = isset($output->response) ? $output->response : null;
            }

            // Check if we have any content
            if (static::$app->render['action'] && $output !== null) {
                static::$app->render['view'] = false;
                Body::append($output);

                // Get the content, clear the body
                // and append content to a clean slate.
                $content = Body::content();
                Body::clear();
                Body::append($content);
            }

            static::$app->__shutdown();
        }
    }

    /**
     * Builds the middleware pipeline.
     */
    protected static function buildPipeline(array $middleware, callable $finalHandler): callable
    {
        return array_reduce(
            array_reverse($middleware),
            function (callable $next, ReflectionAttribute $currentMiddleware) {
                return function () use ($currentMiddleware, $next) {
                    $middleware = $currentMiddleware->newInstance();
                    return $middleware->run($next);
                };
            },
            $finalHandler
        );
    }

    /**
     * Returns the app object.
     *
     * @return object
     */
    public static function app()
    {
        return static::$app;
    }

    /**
     * Returns the version of Avalon.
     *
     * @return string
     */
    public static function version()
    {
        return static::$version;
    }
}
