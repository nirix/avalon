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

namespace Avalon\Core;

use Avalon\Http\Controller;
use Avalon\Http\JsonResponse;
use Avalon\Http\Router;
use Avalon\Http\Request;
use Avalon\Http\Response;
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
    private static string $version = '1.0.0.alpha.0';
    private static Controller $app;

    /**
     * Initializes the the kernel and routes the request.
     */
    public static function init()
    {
        session_start();

        // Route the request
        Router::handle(new Request);

        // Check if the routed controller and method exists
        if (
            !class_exists(Router::$controller) ||
            !method_exists(Router::$controller, Router::$method)
        ) {
            Router::set404();
        }
    }

    /**
     * Executes the routed request.
     */
    public static function run(): void
    {
        $pipeline = static::buildPipeline(Router::$middleware, function () {
            // Start the app
            static::$app = new Router::$controller;

            // Call the method
            $output = null;
            $output = [static::$app, Router::$method](...Router::$params);

            return $output;
        });

        $output = $pipeline();

        if ($output instanceof Response) {
            $output->send();
        } elseif (is_array($output)) {
            (new JsonResponse($output))->send();
        } else {
            try {
                (new Response($output))->send();
            } catch (Exception $e) {
                (new Response('An error occurred processing the request.'))->setStatusCode(500)->send();
            }
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
