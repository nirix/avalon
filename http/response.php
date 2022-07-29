<?php
/*!
 * Avalon
 * Copyright (C) 2011-2022 Jack Polgar
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

namespace avalon\http;

use avalon\core\Kernel;

class Response
{
    public const HTTP_OK = 200;
    public const HTTP_NOT_FOUND = 404;

    public const STATUS_TEXT = [
        Response::HTTP_OK => 'OK',
        Response::HTTP_NOT_FOUND => 'Not Found',
    ];

    public function __construct(
        protected string $content,
        protected int $statusCode = Response::HTTP_OK
    ) {
    }

    public function send()
    {
        header(sprintf("HTTP/1.1 %d %s", $this->statusCode, static::STATUS_TEXT[$this->statusCode]));
        header("X-Powered-By: Avalon/" . Kernel::version());
        echo $this->content;
    }
}
