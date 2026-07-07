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

/**
 * @since 0.10
 */
class Logger
{
    public static ?string $path = null;
    public static int $level = self::LEVEL_INFO;

    public const LEVEL_DEBUG = 1;
    public const LEVEL_INFO = 2;
    public const LEVEL_WARNING = 3;
    public const LEVEL_ERROR = 4;

    public static function log(string $message, int $level = self::LEVEL_INFO): void
    {
        if (!static::$path || $level < self::$level) {
            return;
        }

        $levelName = match ($level) {
            self::LEVEL_DEBUG => 'DEBUG',
            self::LEVEL_INFO => 'INFO',
            self::LEVEL_WARNING => 'WARNING',
            self::LEVEL_ERROR => 'ERROR',
        };

        // Get the file that called this method
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $file = $trace[1]['file'];
        $line = $trace[1]['line'];

        $message = date('Y-m-d H:i:s') . ' [' . $levelName . '] [' . $file . ':' . $line . '] ' . $message;
        file_put_contents(self::$path, $message . PHP_EOL, FILE_APPEND);
    }

    public static function debug(string $message): void
    {
        self::log($message, self::LEVEL_DEBUG);
    }

    public static function info(string $message): void
    {
        self::log($message, self::LEVEL_INFO);
    }

    public static function warning(string $message): void
    {
        self::log($message, self::LEVEL_WARNING);
    }

    public static function error(string $message): void
    {
        self::log($message, self::LEVEL_ERROR);
    }
}
