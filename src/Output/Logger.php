<?php

namespace Avalon\Output;

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
