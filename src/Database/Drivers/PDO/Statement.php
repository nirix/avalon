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

namespace Avalon\Database\Drivers\PDO;

use PDO;
use PDOStatement;

class Statement extends PDOStatement
{
    protected string $modelClass;

    public function withModel(string $class): static
    {
        $this->modelClass = $class;

        return $this;
    }

    #[\Override]
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        if ($this->modelClass) {
            $this->setFetchMode($mode|\PDO::FETCH_FUNC, function ($row) use ($args) {
                return new $this->modelClass($row, false);
            });
        }

        return parent::fetchAll($mode, ...$args);
    }

    #[\Override]
    public function fetch(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): mixed
    {
        $data = parent::fetch($mode, ...$args);

        if ($data && $this->modelClass) {
            return new $this->modelClass($data, false);
        }

        return $data;
    }
}
