<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Database class.
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Core
 */
class Database
{
	private static $driver;
	private static $initiated = false;
	
	/**
	 * Connects to the database.
	 *
	 * @return object
	 */
	public static function init()
	{
		require APPPATH . '/config/database.php';
		require SYSPATH . '/database/model.php';
		require SYSPATH . '/database/' . strtolower($db['driver']) . '.php';
		
		// Define the DB_PREFIX constant
		define("DB_PREFIX", isset($db['prefix']) ? $db['prefix'] : '');
		
		// Build the class with DB_ prefix, to be safe.
		// it to the $driver variable.
		$class_name = 'DB_' . $db['driver'];
		static::$driver = new $class_name($db);
		
		Model::$db =& static::$driver;
		
		foreach(scandir(APPPATH . '/models') as $file)
		{
			if(!is_dir($file))
			{
				require(APPPATH . '/models/' . $file);
			}
		}
		
		static::$initiated = true;
		return static::$driver;
	}
	
	/**
	 * Returns the database instance object.
	 *
	 * @return object
	 */
	public static function driver()
	{
		return static::$driver;
	}
	
	/**
	 * Returns true if the database has been initiated, false if not.
	 *
	 * @return bool
	 */
	public static function initiated()
	{
		return static::$initiated;
	}
}
