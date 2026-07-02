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

namespace Avalon\Http;

use Avalon\Output\View;

/**
 * Controller
 *
 * @since 0.3
 * @package Avalon
 * @subpackage Http
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Controller
{
    public function __construct() {}

    /**
     * Set global variable or variables for every view.
     *
     * @param string|array $name  String for single variable or array of variables by name => value.
     * @param mixed|null   $value Value of variable or null if mass setting by first parameter.
     */
    public function set(string|array $name, mixed $value = null): void
    {
        View::set($name, $value);
    }
}
