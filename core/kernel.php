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

namespace avalon\core;

use avalon\http\Router;
use avalon\http\Request;
use avalon\output\Body;
use avalon\output\View;

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
    private static $version = '0.5';
    private static $app;

    /**
     * Initializes the the kernel and routes the request.
     */
    public static function init()
    {
        session_start();

        // Route the request
        Router::route(new Request);

        // Check if the routed controller and method exists
        if (!class_exists(Router::$controller) or !method_exists(Router::$controller, 'action_' . Router::$method)) {
            Router::set404();
        }
    }

    /**
     * Executes the routed request.
     */
    public static function run()
    {
        // Start the app
        static::$app = new Router::$controller;

        // Before filters
        $filters = array_merge(
            isset(static::$app->_before['*']) ? static::$app->_before['*'] : array(),
            isset(static::$app->_before[Router::$method]) ? static::$app->_before[Router::$method] : array()
        );
        foreach ($filters as $filter) {
            static::$app->{$filter}(Router::$method);
        }
        unset($filters, $filter);

        // Call the method
        if (static::$app->_render['action']) {
            $output = call_user_func_array(array(static::$app, 'action_' . Router::$method), Router::$vars);
        }

        // After filters
        $filters = array_merge(
            isset(static::$app->_after['*']) ? static::$app->_after['*'] : array(),
            isset(static::$app->_after[Router::$method]) ? static::$app->_after[Router::$method] : array()
        );
        foreach ($filters as $filter) {
            static::$app->{$filter}(Router::$method);
        }
        unset($filters, $filter);

        // Check if the action returned content
        if (isset($output) and $output !== null) {
            static::$app->_render['view'] = false;
            Body::append($output);

            // Get the content, clear the body
            // and append content to a clean slate.
            $content = Body::body();
            Body::clear();
            Body::append($content);
        }

        static::$app->__shutdown();
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
    public static function version() {
        return static::$version;
    }
}
