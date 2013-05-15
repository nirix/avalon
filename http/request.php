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
    private static $request_uri;
    private static $uri;
    private static $base;
    private static $segments = array();
    private static $method;
    private static $requested_with;
    public static $query;
    public static $request = array();
    public static $post = array();
    public static $scheme;
    public static $host;

    /**
     * Initialize the class to get request
     * information statically.
     */
    public static function init()
    {
        return new static;
    }

    public function __construct()
    {
        // Because some hosts are complete
        // idiotic pieces of shit, let's
        // strip slashes from input.
        if (get_magic_quotes_gpc()) {
            $php_is_the_worst_language_ever_because_of_this = function (&$value) {
                $value = stripslashes($value);
            };
            array_walk_recursive($_GET, $php_is_the_worst_language_ever_because_of_this);
            array_walk_recursive($_POST, $php_is_the_worst_language_ever_because_of_this);
            array_walk_recursive($_COOKIE, $php_is_the_worst_language_ever_because_of_this);
            array_walk_recursive($_REQUEST, $php_is_the_worst_language_ever_because_of_this);
        }

        // Set query string
        static::$query = (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null);

        // Set request scheme
        static::$scheme = static::isSecure() ? 'https' : 'http';

        // Set host
        static::$host = strtolower(preg_replace('/:\d+$/', '', trim($_SERVER['SERVER_NAME'])));

        // Set base url
        static::$base = static::baseUrl();

        // Set the request path
        static::$request_uri = static::requestPath();

        // Set relative uri without query string
        $uri = explode('?', str_replace(static::$base, '', static::$request_uri));
        static::$uri = $uri[0];

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
        return static::$request_uri;
    }

    /**
     * Returns the value of the key from the POST array,
     * if it's not set, returns null by default.
     *
     * @param string $key     Key to get from POST array
     * @param mixed  $not_set Value to return if not set
     *
     * @return mixed
     */
    public static function post($key, $not_set = null)
    {
        return isset(static::$post[$key]) ? static::$post[$key] : $not_set;
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
        return static::$base . '/' . trim($path, '/');
    }

    /**
     * Determines if the request is secure.
     *
     * @return boolean
     */
    public static function isSecure()
    {
        if (!isset($_SERV['HTTPS']) or empty($_SERVER['HTTPS'])) {
            return false;
        }

        return $_SERVER['HTTPS'] == 'on' or $_SERVER['HTTPS'] == 1;
    }

    private function baseUrl()
    {
        $filename = basename($_SERVER['SCRIPT_FILENAME']);

        if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME'];
        }

        $baseUrl = rtrim(str_replace($filename, '', $baseUrl), '/');

        return $baseUrl;
    }

    private function requestPath()
    {
        $requestPath = '';

        if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
            $requestPath = $_SERVER['HTTP_X_ORIGINAL_URL'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $requestPath = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['IIS_WasUrlRewritten'])
                  and $_SERVER['IIS_WasUrlRewritten'] = 1
                  and isset($_SERVER['UNENCODED_URL'])
                  and $_SERVER['UNENCODED_URL'] != '')
        {
            $requestPath = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestPath = $_SERVER['REQUEST_URI'];

            $schemeAndHost = static::$scheme . '://' . static::$host;
            if (strpos($requestPath, $schemeAndHost)) {
                $requestPath = substr($requestPath, strlen($schemeAndHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $requestPath = $_SERVER['ORIG_PATH_INFO'];
        }

        return $requestPath;
    }
}
