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
 * Avalon's Autoloader.
 *
 * @since 0.2
 * @package Avalon
 * @subpackage Core
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Autoloader
{
    private static $vendorLocation;
    private static $classes = array();
    private static $namespaces = array();

    /**
     * Registers the class as the autoloader.
     */
    public static function register()
    {
        spl_autoload_register('avalon\Autoloader::load', true, true);
    }

    /**
     * Alias multiple classes at once.
     *
     * @param array $classes
     */
    public static function aliasClasses($classes)
    {
        foreach ($classes as $original => $alias) {
            static::aliasClass($original, $alias);
        }
    }

    /**
     * Alias a class from a complete namespace to just it's name.
     *
     * @param string $original
     * @param string $alias
     */
    public static function aliasClass($original, $alias)
    {
        static::$classes[$alias] = ltrim($original, '\\');
    }

    /**
     * Register multiple namespaces at once.
     *
     * @param array $namespaces
     */
    public static function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $vendor => $location) {
            static::registerNamespace($vendor, $location);
        }
    }

    /**
     * Registers a namespace location.
     *
     * @param string $vendor
     * @param string $location
     */
    public static function registerNamespace($vendor, $location)
    {
        static::$namespaces[$vendor] = $location;
    }

    /**
     * Sets the vendor location.
     *
     * @param string $location
     */
    public static function vendorLocation($location)
    {
        static::$vendorLocation = $location;
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
        $vendor = explode('\\', $class);
        $vendor = $vendor[0];

        // Aliased classes
        if (isset(static::$classes[$class])) {
            $file = static::filePath(static::$classes[$class]);

            if (file_exists($file) and !class_exists(static::$classes[$class])) {
                require $file;
            }

            if (class_exists(static::$classes[$class])) {
                class_alias(static::$classes[$class], $class);
            }
        }
        // Registered namespace
        elseif (isset(static::$namespaces[$vendor])) {
            $namespace = explode('\\', $class);
            unset($namespace[0]);
            $namespace = implode('\\', $namespace);

            require static::$namespaces[$vendor] . static::filePath($namespace, false);
        }
        // Everything else
        else {
            $file = static::filePath($class);
            if (file_exists($file)) {
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
    public static function filePath($class, $prependVendor = true)
    {
        return ($prependVendor ? static::$vendorLocation :'') . static::lowercase(str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, "/{$class}.php"));
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
