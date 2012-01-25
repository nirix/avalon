<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Shortcut to the HTML::link method.
 *
 * @param string $url The URL.
 * @param string $label The label.
 * @param array $attributes Options for the URL code (class, title, etc).
 *
 * @return string
 */
function link_to($label, $uri, array $attributes = array())
{
	return HTML::link($label, $uri, $attributes);
}

/**
 * HTML Helper
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Helpers
 */
class HTML
{
	/**
	 * Returns the code to include a CSS file.
	 *
	 * @param string $file The path to the CSS file.
	 *
	 * @return string
	 */
	public static function css_link($path, $media = 'screen')
	{
		return '<link rel="stylesheet" href="'.$path.'" media="'.$media.'" />'.PHP_EOL;
	}

	/**
	 * Returns the code to include a JavaScript file.
	 *
	 * @param string $file The path to the JavaScript file.
	 *
	 * @return string
	 */
	public static function js_inc($path)
	{
		return '<script src="'.$path.'" type="text/javascript"></script>'.PHP_EOL;
	}

	/**
	 * Returns the code for a link.
	 *
	 * @param string $url The URL.
	 * @param string $label The label.
	 * @param array $options Options for the URL code (class, title, etc).
	 *
	 * @return string
	 */
	public static function link($label, $url = null, array $attributes = array())
	{
		if ($label === null) {
			$label = $url;
		}
		
		$url = Request::base(ltrim($url, '/'));
		
		$attributes['href'] = $url;
		
		$options = static::build_attributes($attributes);
		
		return "<a {$options}>{$label}</a>";
	}
	
	/**
	 * Builds the attributes for HTML elements.
	 *
	 * @param array $attributes An array of attributes and their values.
	 *
	 * @return string
	 */
	public static function build_attributes($attributes)
	{
		$options = array();
		foreach ($attributes as $attr => $val) {
			$options[] = "{$attr}=\"{$val}\"";
		}
		return implode(' ', $options);
	}
}