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
 * Request class.
 *
 * @author Jack P.
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
	
	/**
	 * Processes the request and gets the URL,
	 * request method, request type, and so on.
	 */
	public static function process()
	{
		static::$url = trim(static::_get_path_info(), '/');
		static::$segments = explode('/', trim(static::$url, '/'));
		static::$requested_with = @$_SERVER['HTTP_X_REQUESTED_WITH'];
		static::$request = $_REQUEST;
		static::$method = strtolower($_SERVER['REQUEST_METHOD']);
		static::$post = $_POST;
	}
	
	/**
	 * Determines the base URL of the app.
	 *
	 * @return string
	 */
	public static function base()
	{
		return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']) . (func_num_args() > 0 ? trim(implode('/' , func_get_args()), '/') : '');
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
	 * Returns the requested URL.
	 *
	 * @return string
	 */
	public static function url()
	{
		return '/' . static::$url;
	}
	
	/**
	 * Returns the full URI of the request.
	 *
	 * @return string
	 */
	public static function full_uri()
	{
		return static::base(trim(static::url(), '/'));
	}
	
	/**
	 * Checks of the URI matches the specified URI.
	 *
	 * @param string $uri
	 *
	 * @return bool
	 */
	public static function matches($uri)
	{
		return trim($uri, '/') == trim(implode('/', self::$segments), '/');
	}
	
	/**
	 * Returns the segment at the specified index.
	 *
	 * @param integer $num
	 *
	 * @return string
	 */
	public static function seg($num)
	{
		return @static::$segments[$num];
	}
	
	/**
	 * Checks if the request was made via Ajax.
	 *
	 * @return bool
	 */
	public static function is_ajax()
	{
		return strtolower(static::$requested_with) == 'xmlhttprequest';
	}
	
	/**
	 * Gets the base URL
	 *
	 * @return string
	 */
	private static function _get_base_url()
	{
		static::$base_url = rtrim(dirname(static::_get_script_url()), '\\/');
		return static::$base_url;
	}
	
	/**
	 * Determines the path info.
	 *
	 * @return string
	 */
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
	
	/**
	 * Determines the script URL.
	 *
	 * @return string
	 */
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
	
	/**
	 * Determines the requested URL.
	 *
	 * @return string
	 */
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