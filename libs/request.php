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
	private static $entry_file;
	private static $path_info;
	private static $base_url;
	private static $url;
	private static $segments;
	private static $requested_with;
	public static $request;
	public static $method;
	public static $post;
	
	public static function process()
	{
		static::$url = trim(static::_get_path_info(), '/');
		static::$segments = explode('/', trim(static::$url, '/'));
		static::$requested_with = @$_SERVER['HTTP_X_REQUESTED_WITH'];
		static::$request = $_REQUEST;
		static::$method = strtolower($_SERVER['REQUEST_METHOD']);
		static::$post = $_POST;
	}
	
	public static function base()
	{
		return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']) . (func_num_args() > 0 ? implode('/' , func_get_args()) : '');
	}
	
	public static function redirect($url)
	{
		header("Location: " . $url);
	}
	
	public static function url()
	{
		return static::$url;
	}
	
	public static function matches($uri)
	{
		return trim($uri, '/') == trim(implode('/', self::$segments), '/');
	}
	
	public static function seg($num)
	{
		return @static::$segments[$num];
	}
	
	public static function is_ajax()
	{
		return strtolower(static::$requested_with) == 'xmlhttprequest';
	}
	
	private static function _get_base_url()
	{
		static::$base_url = rtrim(dirname(static::_get_script_url()), '\\/');
		return static::$base_url;
	}
	
	private static function _get_path_info()
	{
		if (static::$path_info === null) {
			$path_info = static::_get_request_url();

			if (($pos = strpos($path_info, '?')) !== false) {
			   $path_info = substr($path_info, 0, $pos);
			}
			$path_info = urldecode($path_info);

			$script_url = static::_get_script_url();
			$base_url = static::_get_base_url();
			if (strpos($path_info, $script_url) === 0) {
				$path_info = substr($path_info, strlen($script_url));
			}
			elseif ($base_url === '' || strpos($path_info, $base_url) === 0) {
				$path_info = substr($path_info, strlen($base_url));
			}
			elseif (strpos($_SERVER['PHP_SELF'],$script_url) === 0) {
				$path_info = substr($_SERVER['PHP_SELF'], strlen($script_url));
			} else {
				throw new Exception("Unable to determin path info.");
			}
			
			static::$path_info = trim($path_info, '/');
		}
		
		return static::$path_info;
	}
	
	private static function _get_script_url()
	{
		if (static::$entry_file === null) {
			$file_name = basename($_SERVER['SCRIPT_FILENAME']);
			
			// script_name
			if (basename($_SERVER['SCRIPT_NAME']) === $file_name) {
				static::$entry_file = $_SERVER['SCRIPT_NAME'];
			}
			// php_self
			elseif (basename($_SERVER['PHP_SELF']) === $file_name) {
				static::$entry_file = $_SERVER['PHP_SELF'];
			}
			// orig_script_name
			elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $file_name)
			{
				static::$entry_file = $_SERVER['ORIG_SCRIPT_NAME'];
			}
			// more php_self
			elseif (($pos=strpos($_SERVER['PHP_SELF'], '/' . $file_name))!==false)
			{
				static::$entry_file = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $file_name;
			}
			// document_root
			elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
			{
				static::$entry_file = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
			}
			// /wrists
			else {
				throw new Exception("Unable to determin entry file.");
			}
		}
		
		return static::$entry_file;
	}
	
	private static function _get_request_url()
	{
		if (static::$url === null) {
			// IIS
			if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
				static::$url = $_SERVER['HTTP_X_REWRITE_URL'];
			} elseif (isset($_SERVER['REQUEST_URI'])) {
				static::$url = $_SERVER['REQUEST_URI'];
				if(isset($_SERVER['HTTP_HOST'])) {
					if (strpos(static::$url, $_SERVER['HTTP_HOST']) !== false) {
						static::$url = preg_replace('/^\w+:\/\/[^\/]+/', '', static::$url);
					}
				} else {
					static::$url = preg_replace('/^(http|https):\/\/[^\/]+/i', '', static::$url);
				}
			}
			// IIS 5.0 CGI
			elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
				static::$url = $_SERVER['ORIG_PATH_INFO'];
				if(!empty($_SERVER['QUERY_STRING'])) {
					static::$url .= '?' . $_SERVER['QUERY_STRING'];
				}
			} else {
				throw new Exception("Unable to determin URI.");
			}
		}
		
		return static::$url;
	}
}