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
    public static array $searchPaths = [];
    private static array $globalVars = [];
    protected static string $current;
    protected static array $parents = [];
    protected static array $sections = [];
    protected static array $sectionStack = [];

    /**
     * Renders the specified file.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     *
     * @return string
     *
     * @throws Exception
     */
    public static function render($file, array $vars = []): string
    {
        $path = static::find($file);

        if ($path === false) {
            throw new Exception(sprintf('Unable to find view "%s" in directories [%s]', $file, join(', ', static::$searchPaths)));
        }

        static::$current = $path;
        static::$parents[$path] = null;

        // Get the view content
        $content = static::sandboxView($path, $vars);

        if (static::$parents[$path] !== null) {
            $content = static::render(
                static::$parents[$path],
                [
                    ...$vars,
                    'content' => $content
                ]
            );
        }

        return $content;
    }

    public static function find(string $file): string|false
    {
        foreach (static::$searchPaths as $path) {
            $path = rtrim($path, '/') . '/' . $file;

            // Check for .phtml, .php and the name itself
            if (file_exists($path)) {
                return $path;
            } elseif (file_exists($path . '.phtml')) {
                return $path . '.phtml';
            } elseif (file_exists($path . '.php')) {
                return $path . '.php';
            }

            // Legacy support for camelCase to snake_case
            $path = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_' . '\\1', $path));
            if (file_exists($path)) {
                Logger::warning(sprintf('Automatically converting camelCase to snake_case for view file is deprecated.', $path));
                return $path;
            } elseif (file_exists($path . '.phtml')) {
                Logger::warning(sprintf('Automatically converting camelCase to snake_case for view file is deprecated.', $path));
                return $path . '.phtml';
            } elseif (file_exists($path . '.php')) {
                Logger::warning(sprintf('Automatically converting camelCase to snake_case for view file is deprecated.', $path));
                return $path . '.php';
            }
        }

        return false;
    }

    /**
     * Private function to handle the rendering of files.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     *
     * @return string
     */
    protected static function sandboxView(string $path, array $vars = []): string
    {
        $vars = array_merge(self::$globalVars, $vars);
        extract($vars, EXTR_SKIP);

        ob_start();
        require $path;

        return ob_get_clean();
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
            self::$globalVars[$var] = $val;
        }
    }

    /**
     * Returns the variables array.
     *
     * @return array
     */
    public static function vars()
    {
        return self::$globalVars;
    }

    public static function extend(string $file): void
    {
        static::$parents[static::$current] = $file;
    }

    public static function startSection(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            static::$sections[$name] = $content;
            return;
        }

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
