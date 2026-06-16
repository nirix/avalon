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

namespace Avalon\Http;

/**
 * @since 0.7
 */
class RedirectResponse extends Response
{
    public function __construct(
        protected string $url,
        protected int $statusCode = Response::HTTP_FOUND
    ) {}

    public function send(): void
    {
        header(\sprintf('Location: %s', $this->url), true, $this->statusCode);
        exit;
    }
}
