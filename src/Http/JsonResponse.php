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

namespace Avalon\Http;

use Avalon\Core\Kernel;

/**
 * @since 0.8
 */
class JsonResponse extends Response
{
    protected string $content;

    public function __construct(
        array $content,
        protected int $statusCode = Response::HTTP_OK
    ) {
        parent::__construct(json_encode($content), $statusCode);
    }

    #[\Override]
    public function send()
    {
        header("Content-Type: application/json; charset=utf-8");

        parent::send();
    }
}
