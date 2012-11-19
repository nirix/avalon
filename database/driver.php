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

namespace avalon\database;
use avalon\core\Error;

/**
 * Database driver base.
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Driver
{
    /**
     * Shortcut to the Error::halt method.
     *
     * @param string $error DB error message
     */
    public function halt($error = 'Unknown error')
    {
        if (is_array($error) and isset($error[2]) and !empty($error[2])) {
            $error = $error[2];
        }
        else if (!is_array($error)) {
            $error = $error;
        }
        else {
            $error = 'Unknown error. ' . implode('/', $error);
        }

        Error::halt("Database Error", $error . '<br />' . $this->last_query);
    }
}