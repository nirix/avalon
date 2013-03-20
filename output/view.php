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

namespace avalon\output;

use avalon\core\Load;
use avalon\core\Error;

/**
 * View class.
 *
 * @author Jack P.
 * @package Avalon
 */
class View
{
    private static $ob_level;
    public static $theme;
    public static $inherit_from;
    private static $vars = array();

    /**
     * Renders the specified file.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     */
    public static function render($file, array $vars = array())
    {
        // Get the view content
        return static::_get_view($file, $vars);
    }

    /**
     * Renders and returns the specified file.
     *
     * @deprecated Deprecated since 0.6
     */
    public static function get($file, array $vars = array())
    {
        return static::render($file, $vars);
    }

    /**
     * Private function to handle the rendering of files.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     *
     * @return string
     */
    private static function _get_view($_file, array $vars = array())
    {
        // Get the file name/path
        $_file = self::_view_file_path($_file);

        // Make sure the ob_level is set
        if (self::$ob_level === null) {
            self::$ob_level = ob_get_level();
        }

        // Make the set variables accessible
        foreach (self::$vars as $_var => $_val) {
            $$_var = $_val;
        }

        // Make the vars for this view accessible
        if (count($vars)) {
            foreach($vars as $_var => $_val)
                $$_var = $_val;
        }

        // Load up the view and get the contents
        ob_start();
        include($_file);
        return ob_get_clean();
    }

    /**
     * Determines the path of the view file.
     *
     * @param string $file File name.
     *
     * @return string
     */
    private static function _view_file_path($file)
    {
        $path = static::exists($file);

        // Check if the theme has this view
        if (!$path) {
            Error::halt("View Error", "Unable to load view '{$file}'", 'HALT');
        }

        unset($file);
        return $path;
    }

    public static function exists($name)
    {
        $dirs = array();

        // Add the theme path, if a theme is set
        if (static::$theme !== null) {
            $dirs[] = APPPATH . '/views/' . static::$theme . '/';
        }

        // Registered search paths
        foreach (Load::$search_paths as $path) {
            if (is_dir($path . '/views')) {
                $dirs[] = $path . '/views/';
            }
        }

        // Add the inheritance path, if there is one
        if (static::$inherit_from !== null) {
            $dirs[] = static::$inherit_from . '/';
        }

        // Add the regular path
        $dirs[] = APPPATH . '/views/';

        // Loop over and find the view
        foreach ($dirs as $dir) {
            $path = $dir . strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_' . '\\1', $name));
            if (file_exists($path . '.phtml')) {
                return $path . '.phtml';
            } elseif (file_exists($path . '.php')) {
                return $path . '.php';
            }
        }

        // Damn it Jim, I'm a doctor not a view path.
        return false;
    }

    /**
     * Sends the variable to the view.
     *
     * @param string $var The variable name.
     * @param mixed $val The variables value.
     */
    public static function set($var, $val = null)
    {
        // Mass set
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                static::set($k, $v);
            }
        } else {
            self::$vars[$var] = $val;
        }
    }

    /**
     * Returns the variables array.
     *
     * @return array
     */
    public static function vars()
    {
        return self::$vars;
    }
}
