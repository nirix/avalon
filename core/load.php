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

namespace avalon\core;

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
    public static $search_paths = array();

    /**
     * Loads the specified controller.
     *
     * @param string $controller
     *
     * @return string
     */
    public static function controller($controller)
    {
        $controller = strtolower($controller);

        // Add the apps controller directory
        $dirs = array();
        $dirs[] = APPPATH . '/controllers';

        // Add the registered paths
        foreach (static::$search_paths as $path) {
            if (is_dir($path . '/controllers')) {
                $dirs[] = $path . '/controllers';
            }
        }

        // Search for the controller
        foreach ($dirs as $dir) {
            if (file_exists("{$dir}/{$controller}_controller.php")) {
                return "{$dir}/{$controller}_controller.php";
            }
        }

        // No controller found...
        return APPPATH . '/controllers/error_controller.php';
    }

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
        // If it already loaded?
        if (isset(static::$libs[$class])) {
            return static::$libs[$class];
        }

        // Set the class and file name
        $class_name = ucfirst($class);
        $file_name = static::lowercase($class);

        // App library
        if (file_exists(APPPATH . '/libs/' . $file_name . '.php')) {
            require APPPATH . '/libs/' . $file_name . '.php';
        }
        // Avalon library
        elseif (file_exists(SYSPATH . '/libs/' . $file_name . '.php')) {
            require SYSPATH . '/libs/' . $file_name . '.php';
        }
        // Not found
        else {
            Error::halt("Loader Error", "Unable to load library '{$class}'");
            return false;
        }

        // Initiate the class?
        if ($init) {
            static::$libs[$class] = new $class_name();
        }
        // No, just load it
        else {
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
        // In case we're loading multiple helpers
        $class = func_num_args() > 1 ? func_get_args() : func_get_arg(0);

        // Multiple helpers
        if (is_array($class)) {
            foreach ($class as $helper) {
                static::helper($helper);
            }
            return;
        }

        // Is it already loaded?
        if (in_array($class, static::$helpers)) {
            return true;
        }

        // Lowercase the file name
        $file_name = static::lowercase($class);

        // App helper
        if (file_exists(APPPATH . '/helpers/' . $file_name . '.php')) {
            require APPPATH . '/helpers/' . $file_name . '.php';
        }
        // Avalon helper
        elseif (file_exists(SYSPATH . '/helpers/' . $file_name . '.php')) {
            require SYSPATH . '/helpers/' . $file_name . '.php';
        }
        // Not found
        else {
            Error::halt("Loader Error", "Unable to load helper '{$class}'");
            return false;
        }

        static::$helpers[] = $class;
        return true;
    }

    /**
     * Adds a path to be searched when loading controllers and views.
     *
     * @param string $path
     */
    public static function register_path($path)
    {
        static::$search_paths[] = $path;
    }

    /**
     * Lower cases the specified string.
     */
    private static function lowercase($string) {
        $string = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_' . '\\1', $string));
        return str_replace(array_keys(static::$undo), array_values(static::$undo), $string);
    }
}
