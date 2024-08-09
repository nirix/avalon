<?php
/*!
 * Avalon
 * Copyright (C) 2011-2024 Jack Polgar
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

namespace Avalon\Output;

/**
 * Content output class.
 *
 * @author Jack P.
 * @package Avalon
 */
class Body
{
    private static $body = '';

    /**
     * Returns the contents of the body.
     *
     * @return string
     */
    public static function content()
    {
        return static::$body;
    }

    /**
     * Appends the content to the body.
     *
     * @param string $content
     */
    public static function append($content)
    {
        static::$body .= $content;
    }

    /**
     * Clears the body.
     */
    public static function clear()
    {
        static::$body = '';
    }
}
