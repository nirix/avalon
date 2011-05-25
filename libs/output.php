<?php

class Output
{
	private static $body = '';
	
	public static function body()
	{
		return static::$body;
	}
	
	public static function append($content)
	{
		static::$body .= $content;
	}
	
	public static function clear()
	{
		static::$body = '';
	}
}