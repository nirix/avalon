<?php
/*!
 * Avalon
 * Copyright (C) 2011-2025 Jack Polgar
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

declare(strict_types=1);

namespace Avalon\Output;

use Exception;

/**
 * View class.
 *
 * @author Jack P.
 * @package Avalon
 */
class View
{
    /**
     * Current view.
     */
    protected static string $current;

    /**
     * Global variables for all views.
     */
    protected static array $globals = [];

    /**
     * Directories to search for views in.
     */
    protected static array $directories = [];

    /**
     * Parent views that other views have extended from.
     */
    protected static array $parents = [];

    /**
     * View sections stack.
     */
    protected static array $sectionStack = [];

    /**
     * Rendered view sections.
     */
    protected static array $sections = [];

    /**
     * Search directories for the specified file.
     *
     * @param string $name File name
     *
     * @return string|false
     */
    public static function find(string $name): string|false
    {
        foreach (static::$directories as $directory) {
            $path = $directory . '/' . $name;

            if (file_exists($path)) {
                return $path;
            }
        }

        return false;
    }

    /**
     * Renders the view with the given variables.
     *
     * @param string $name View name.
     * @param array  $data Variables for the view.
     *
     * @return string
     */
    public static function render(string $name, array $data = []): string
    {
        $path = static::find($name);

        if (!$path) {
            throw new Exception(sprintf('Unable to find view "%s" in directories [%s]', $name, join(', ', static::$directories)));
        }

        static::$current = $name;
        static::$parents[$name] = null;

        $content = static::sandboxView($path, $data);

        if (static::$parents[$name]) {
            $content = static::render(static::$parents[$name], [...$data, 'content' => $content]);
        }

        return $content;
    }

    protected static function sandboxView(string $_viewPath, array $data = []): string|false
    {
        $data = static::$globals + $data;
        extract($data, \EXTR_SKIP);

        ob_start();
        require $_viewPath;

        return ob_get_clean();
    }

    /**
     * Add directory to search for views in.
     *
     * @param string $directory Directory path.
     */
    public static function addDirectory(string $directory): void
    {
        static::$directories[] = $directory;
    }

    /**
     * Set global variable or variables for every view.
     *
     * @param string|array $name  String for single variable or array of variables by name => value.
     * @param mixed|null   $value Value of variable or null if mass setting by first parameter.
     */
    public static function set(string|array $name, mixed $value = null): void
    {
        if (is_array($name)) {
            foreach ($name as $name => $value) {
                static::$globals[$name] = $value;
            }

            return;
        }

        static::$globals[$name] = $value;
    }

    public static function extends(string $name): void
    {
        static::$parents[static::$current] = $name;
    }

    public static function startSection(string $name): void
    {
        static::$sectionStack[] = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        $section = array_pop(static::$sectionStack);
        static::$sections[$section] = ob_get_clean();
    }

    public static function hasSection(string $name): bool
    {
        return isset(static::$sections[$name]);
    }

    public static function getSection(string $name, string $fallback = ''): string
    {
        return static::$sections[$name] ?? $fallback;
    }
}
