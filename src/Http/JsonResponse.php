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

use Avalon\Core\Kernel;

/**
 * @since 1.0
 */
class JsonResponse extends Response
{
    public function __construct(
        protected $jsonContent,
        protected int $statusCode = Response::HTTP_OK
    ) {
    }

    public function send()
    {
        header(sprintf("HTTP/1.1 %d %s", $this->statusCode, static::STATUS_TEXT[$this->statusCode]));
        header("X-Powered-By: Avalon/" . Kernel::version());
        header("Content-Type: application/json", true);
        echo json_encode($this->jsonContent);
    }
}
