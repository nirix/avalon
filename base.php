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

require SYSPATH . '/core/error.php';
require SYSPATH . '/core/load.php';
require SYSPATH . '/core/controller.php';
require SYSPATH . '/core/avalon.php';
require SYSPATH . '/core/database.php';
require SYSPATH . '/database/driver.php';

Load::lib('request');
Load::lib('router');
Load::lib('output');
Load::lib('view');
Load::helper('time');