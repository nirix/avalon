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
	private static $connections = array();
	private static $initiated = array();
	
	/**
	 * Connects to the database.
	 *
	 * @return object
	 */
	public static function init()
	{
		require APPPATH . '/config/database.php';
		require SYSPATH . '/database/model.php';
		
		// Define the DB_PREFIX constant
		define("DB_PREFIX", isset($db['prefix']) ? $db['prefix'] : '');
		
		static::factory($db, 'main');

		// Load the models
		foreach(scandir(APPPATH . '/models') as $file)
		{
			// Make sure it's not a directory and is a php file
			if(!is_dir($file) and substr($file, -3) == 'php')
			{
				require(APPPATH . '/models/' . $file);
			}
		}

		return static::$connections['main'];
	}

	/**
	 * Create a new database connection based off the passed
	 * config array and the specified name.
	 *
	 * @param array $config
	 * @param string $name
	 *
	 * @return object
	 */
	public static function factory(array $config, $name)
	{
		// Make sure the connection name is available
		if (isset(static::$connections[$name]))
		{
			throw new Exception("Database connection name '{$name}' already initiated");
		}

		// Prepend 'DB_' to the driver name
		$class_name = 'DB_' . $config['driver'];

		// Load the driver class
		if (!class_exists($class_name))
		{
			require SYSPATH . '/database/' . strtolower($config['driver']) . '.php';
		}

		// Create the connection and mark it as initiated.
		static::$connections[$name] = new $class_name($config, $name);
		static::$initiated[$name] = true;

		return static::$connections[$name];
	}
	
	/**
	 * Returns the database instance object.
	 *
	 * @param string $name Connection name
	 *
	 * @return object
	 */
	public static function connection($name = 'main')
	{
		return isset(static::$connections[$name]) ? static::$connections[$name] : false;
	}
	
	/**
	 * Returns true if the database has been initiated, false if not.
	 *
	 * @param string $name Connection name
	 *
	 * @return bool
	 */
	public static function initiated($name = 'main')
	{
		return isset(static::$initiated[$name]) ? static::$initiated[$name] : false;
	}
}
