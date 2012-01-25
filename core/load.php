<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Avalons loader class.
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Core
 */
class Load
{
	private static $undo = array('my_sql' => 'mysql', 'java_script' => 'javascript');
	private static $libs = array();
	private static $helpers = array();
	
	/**
	 * Library loader.
	 *
	 * @param string $class The class name
	 * @param boolean $init Initialize the class or not
	 *
	 * @return object
	 */
	public static function lib($class, $init = true)
	{
		if (isset(static::$libs[$class])) {
			return static::$libs[$class];
		}
		
		$class_name = ucfirst($class);
		$file_name = static::lowercase($class);
		
		if (file_exists(APPPATH . '/libs/' . $file_name . '.php')) {
			require APPPATH . '/libs/' . $file_name . '.php';
		} elseif (file_exists(SYSPATH . '/libs/' . $file_name . '.php')) {
			require SYSPATH . '/libs/' . $file_name . '.php';
		} else {
			Error::halt("Loader Error", "Unable to load library '{$class}'");
			return false;
		}
		
		if ($init) {
			static::$libs[$class] = new $class_name();
		} else {
			static::$libs[$class] = $class_name;
		}
		
		return static::$libs[$class];
	}
	
	/**
	 * Helper loader.
	 *
	 * @param mixed $helper
	 *
	 * @return bool
	 */
	public static function helper()
	{
		$class = func_num_args() > 1 ? func_get_args() : func_get_arg(0);
		
		if (is_array($class)) {
			foreach ($class as $helper) {
				static::helper($helper);
			}
			return;
		}
		
		if (in_array($class, static::$helpers)) {
			return true;
		}
		
		$file_name = static::lowercase($class);
		
		if (file_exists(APPPATH . '/helpers/' . $file_name . '.php')) {
			require APPPATH . '/helpers/' . $file_name . '.php';
		} elseif (file_exists(SYSPATH . '/helpers/' . $file_name . '.php')) {
			require SYSPATH . '/helpers/' . $file_name . '.php';
		} else {
			Error::halt("Loader Error", "Unable to load helper '{$class}'");
			return false;
		}
		
		static::$helpers[] = $class;
		return true;
	}
	
	/**
	 * Lower cases the specified string.
	 */
	private static function lowercase($string) {
		$string = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_' . '\\1', $string));
		
		return str_replace(array_keys(static::$undo), array_values(static::$undo), $string);
	}
}