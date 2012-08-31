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

namespace avalon;

/**
 * Avalon's Autoloader
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Core
 */
class Autoloader
{
	private static $vendor_path;
	private static $registered_namespaces = array();
	private static $classes = array();

	/**
	 * Registers the class as the autoloader.
	 */
	public static function register()
	{
		spl_autoload_register('avalon\Autoloader::load', true, true);
	}

	/**
	 * Registers multiple namespaces.
	 *
	 * @param array $namespaces
	 */
	public static function register_namespaces($namespaces = array())
	{
		foreach ($namespaces as $namespace => $path) {
			static::register_namespace($namespace, $path);
		}
	}

	/**
	 * Registers a namespace.
	 *
	 * @param string $namespace
	 * @param string $path
	 */
	public static function register_namespace($namespace, $path)
	{
		static::$registered_namespaces[$namespace] = $path;
	}

	/**
	 * Alias multiple classes at once.
	 *
	 * @param array $classes
	 */
	public static function alias_classes($classes)
	{
		foreach ($classes as $original => $alias) {
			static::alias_class($original, $alias);
		}
	}

	/**
	 * Alias a class from a complete namespace to just it's name.
	 *
	 * @param string $original
	 * @param string $alias
	 */
	public static function alias_class($original, $alias)
	{
		static::$classes[$alias] = ltrim($original, '\\');
	}

	/**
	 * Sets the vendor directory path.
	 *
	 * @param string $path
	 */
	public static function vendor_path($path)
	{
		static::$vendor_path = $path;
	}

	/**
	 * Loads a class
	 *
	 * @param string $class The class
	 *
	 * @return bool
	 */
	public static function load($class)
	{
		$class = ltrim($class, '\\');
		$namespaces = explode('\\', $class);
		$vendor_namespace = $namespaces[0];

		// Aliased classes
		if (array_key_exists($class, static::$classes)) {
			if (!class_exists($class)) {
				static::load(static::$classes[$class]);
			}

			if (class_exists(static::$classes[$class])) {
				class_alias(static::$classes[$class], $class);
			}
		}
		// Registered namespace
		elseif(isset(static::$registered_namespaces[$vendor_namespace])) {
			$file = static::file_path(str_replace("{$vendor_namespace}\\", '', $class), static::$registered_namespaces[$vendor_namespace]);
			if (!class_exists($class) and file_exists($file)) {
				require $file;
			}
		}
		// Everything else
		else {
			$file = static::file_path($class);
			if (file_exists($file) and !class_exists($class)) {
				require $file;
			}
		}
	}

	/**
	 * Converts the class into the file path.
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	public static function file_path($class, $vendor_path = null)
	{
		return (($vendor_path === null) ? static::$vendor_path : $vendor_path) . static::lowercase(str_replace(array('\\', '_'), '/', "/{$class}.php"));
	}

	/**
	 * Lowercases the string to camcel_case.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private static function lowercase($string) {
		$string = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_' . '\\1', $string));

		// There are certain things we don't want under_scored
		// such as mysql.
		$undo = array(
			'my_sql' => 'mysql'
		);

		return str_replace(array_keys($undo), array_values($undo), $string);
	}
}
