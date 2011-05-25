<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * HTTP Request class
 * @package Avalon
 */
class Request
{
	private static $url;
	private static $segments;
	private static $requested_with;
	
	public static function process()
	{
		static::$url = trim(static::_get_uri(), '/');
		static::$segments = explode('/', trim(static::$url, '/'));
		static::$requested_with = @$_SERVER['HTTP_X_REQUESTED_WITH'];
	}
	
	public static function base()
	{
		return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']) . (func_num_args() > 0 ? implode('/' , func_get_args()) : '');
	}
	
	public static function url()
	{
		return static::$url;
	}
	
	public static function seg($num)
	{
		return @static::$segments[$num];
	}
	
	public function is_ajax()
	{
		return strtolower(static::$requested_with) == 'xmlhttprequest';
	}
	
	private static function _get_uri()
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
		
		// I dont know what else to try, screw it..
		return '';
	}
	
	private static function _get_uri_other($prefix_slash = false)
	{
	    if (isset($_SERVER['PATH_INFO'])) {
	        $uri = $_SERVER['PATH_INFO'];
	    } elseif (isset($_SERVER['REQUEST_URI'])) {
	        $uri = $_SERVER['REQUEST_URI'];
	        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
	            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
	        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
	            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
	        }
	
	        // This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
	        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
	        if (strncmp($uri, '?/', 2) === 0) {
	            $uri = substr($uri, 2);
	        }
	        $parts = preg_split('#\?#i', $uri, 2);
	        $uri = $parts[0];
	        if (isset($parts[1])) {
	            $_SERVER['QUERY_STRING'] = $parts[1];
	            parse_str($_SERVER['QUERY_STRING'], $_GET);
	        } else {
	            $_SERVER['QUERY_STRING'] = '';
	            $_GET = array();
	        }
	        $uri = parse_url($uri, PHP_URL_PATH);
	    } else {
	        // Couldn't determine the URI, so just return false
	        return false;
	    }
	    
	    // Do some final cleaning of the URI and return it
	    return ($prefix_slash ? '/' : '').str_replace(array('//', '../'), '/', trim($uri, '/'));
	}
}