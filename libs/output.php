<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Content output class.
 *
 * @author Jack P.
 * @package Avalon
 */
class Output
{
	private static $body = '';
	
	/**
	 * Returns the contents of the body.
	 *
	 * @return string
	 */
	public static function body()
	{
		return static::$body;
	}
	
	/**
	 * Appends the content to the body.
	 *
	 * @param string $content
	 */
	public static function append($content)
	{
		static::$body .= $content;
	}
	
	/**
	 * Clears the body.
	 */
	public static function clear()
	{
		static::$body = '';
	}
}