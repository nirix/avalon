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
 * Avalon's HTTP request class.
 *
 * @since 0.1
 * @package Avalon
 * @subpackage HTTP
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Request
{
    private static $uri;
    private static $segments = array();
    private static $method;
    private static $requested_with;
    public static $request = array();
    public static $post = array();

    public function __construct()
    {
        // Get the request path
        static::$uri = ($uri = static::requestPath() and $uri != '') ? $uri : '/';

        // Request segments
        static::$segments = explode('/', trim(static::$uri, '/'));

        // Set the request method
        static::$method = strtolower($_SERVER['REQUEST_METHOD']);

        // Requested with
        static::$requested_with = @$_SERVER['HTTP_X_REQUESTED_WITH'];

        // _REQUEST
        static::$request = $_REQUEST;

        // _POST
        static::$post = $_POST;
    }

    /**
     * Returns the relative requested URI.
     *
     * @return string
     */
    public function getUri()
    {
        return static::$uri;
    }

    /**
     * Static method for returning relative the URI.
     *
     * @return string
     */
    public static function uri()
    {
        return static::$uri;
    }

    /**
     * Returns the request method if nothing
     * is passed, otherwise returns true/false
     * if the passed string matches the method.
     *
     * @param string $matches
     *
     * @return string
     */
    public static function method($matches = false)
    {
        // Return the request method
        if (!$matches) {
            return static::$method;
        }
        // Match the request method
        else {
            return static::$method == $matches;
        }
    }

    /**
     * Returns the full requested URI.
     *
     * @return string
     */
    public static function requestUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Gets the URI segment.
     *
     * @param integer $segment Segment index
     *
     * @return mixed
     */
    public static function seg($segment)
    {
        return (isset(static::$segments[$segment]) ? static::$segments[$segment] : false);
    }

    /**
     * Redirects to the specified URL.
     *
     * @param string $url
     */
    public static function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Redirects to the specified path relative to the
     * entry file.
     *
     * @param string $path
     */
    public static function redirectTo($path = '')
    {
        static::redirect(static::base($path));
    }

    /**
     * Checks if the request was made via Ajax.
     *
     * @return bool
     */
    public static function isAjax()
    {
        return strtolower(static::$requested_with) == 'xmlhttprequest';
    }

    /**
     * Gets the base URL
     *
     * @return string
     */
    public static function base($path = '')
    {
        return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']) . trim($path, '/');
    }

    private function requestPath()
    {
        // Check if there is a PATH_INFO variable
        // Note: some servers seem to have trouble with getenv()
        $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
        if (trim($path, '/') != '' && $path != "/index.php") {
            return $path;
        }

        // Check if ORIG_PATH_INFO exists
        $path = str_replace($_SERVER['SCRIPT_NAME'], '', (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO'));
        if (trim($path, '/') != '' && $path != "/index.php") {
            return $path;
        }

        // Check for ?uri=x/y/z
        if (isset($_REQUEST['url'])) {
            return $_REQUEST['url'];
        }

        // Check the _GET variable
        if (is_array($_GET) && count($_GET) == 1 && trim(key($_GET), '/') != '') {
            return key($_GET);
        }

        // Check for QUERY_STRING
        $path = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : @getenv('QUERY_STRING');
        if (trim($path, '/') != '') {
            return $path;
        }

        // Check for REQUEST_URI
        $path = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
        if (trim($path, '/') != '' && $path != "/index.php") {
            return str_replace(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '', $path);
        }

        // I dont know what else to try, screw it..
        return '';
    }
}
