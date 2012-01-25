<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * JavaScript Helper
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Helpers
 */
class JS
{
	/**
	 * Escapes the specified content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function escape($content)
	{
		$replace = array(
			"\r" => '',
			"\n" => ''
		);
		return addslashes(str_replace(array_keys($replace), array_values($replace), $content));
	}
}