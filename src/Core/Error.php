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

namespace Avalon\Core;

/**
 * Error class
 *
 * @since 0.1
 * @package Avalon
 * @subpackage Core
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Error
{
    public static function halt($title, $message = '', $exception = '')
    {
        @ob_end_clean();

        require dirname(__DIR__) . '/Views/error.phtml';
        error_log("{$title} - {$message} - {$exception}");

        exit;
    }
}
