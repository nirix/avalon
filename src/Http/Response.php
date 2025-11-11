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
 * @since 0.7
 */
class Response
{
    public const HTTP_OK = 200;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    public const HTTP_GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const HTTP_VARIANT_ALSO_NEGOTIATES = 506;
    public const HTTP_INSUFFICIENT_STORAGE = 507;
    public const HTTP_LOOP_DETECTED = 508;
    public const HTTP_NOT_EXTENDED = 510;
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    public const STATUS_TEXT = [
        Response::HTTP_OK => 'OK',
        Response::HTTP_NOT_FOUND => 'Not Found',
        Response::HTTP_MOVED_PERMANENTLY => 'Moved Permanently',
        Response::HTTP_FOUND => 'Found',
        Response::HTTP_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
        Response::HTTP_BAD_REQUEST => 'Bad Request',
        Response::HTTP_UNAUTHORIZED => 'Unauthorized',
        Response::HTTP_FORBIDDEN => 'Forbidden',
        Response::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        Response::HTTP_NOT_IMPLEMENTED => 'Not Implemented',
        Response::HTTP_BAD_GATEWAY => 'Bad Gateway',
        Response::HTTP_SERVICE_UNAVAILABLE => 'Service Unavailable',
        Response::HTTP_GATEWAY_TIMEOUT => 'Gateway Timeout',
        Response::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        Response::HTTP_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        Response::HTTP_INSUFFICIENT_STORAGE => 'Insufficient Storage',
        Response::HTTP_LOOP_DETECTED => 'Loop Detected',
        Response::HTTP_NOT_EXTENDED => 'Not Extended',
        Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    public function __construct(
        protected string $content,
        protected int $statusCode = Response::HTTP_OK,
        protected array $headers = []
    ) {}

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function addHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
    }

    public function removeHeader(string $name)
    {
        unset($this->headers[$name]);
    }

    public function send()
    {
        header(sprintf("HTTP/1.1 %d %s", $this->statusCode, static::STATUS_TEXT[$this->statusCode]));

        foreach ($this->headers as $name => $value) {
            header(sprintf("%s: %s", $name, $value));
        }

        header("X-Powered-By: Avalon/" . Kernel::version());

        echo $this->content;
    }
}
