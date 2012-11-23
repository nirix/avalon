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
    public static function halt($title, $message = '')
    {
        @ob_end_clean();

        $body = array();
        $body[] = "<blockquote style=\"font-family:'Helvetica Neue', Arial, Helvetica, sans-serif;background:#fbe3e4;color:#8a1f11;padding:0.8em;margin-bottom:1em;border:2px solid #fbc2c4;\">";

        if (!$title !== null) {
            $body[] = "  <h1 style=\"margin: 0;\">{$title}</h1>";
        }

        $body[] = "  {$message}";
        $body[] = "  <div style=\"margin-top:8px;\"><small>Powered by Avalon</small></div>";
        $body[] = "</blockquote>";

        echo implode(PHP_EOL, $body);
        exit;
    }
}
