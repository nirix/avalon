<?php
/*!
 * Avalon
 * Copyright (C) 2011-2026 Jack Polgar
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

namespace Avalon\Database\Traits\MariaDB;

use Avalon\Database\Drivers\PDO\Statement;

trait CRUD
{
    public static function prepareSelect(string $query, array $options = []): Statement
    {
        return static::db()->prepare(sprintf('SELECT * FROM %s %s', static::$table, $query), $options)->withModel(static::class);
    }

    public static function findBy(string $property, mixed $value): static|false
    {
        $query = static::prepareSelect("WHERE {$property} = :value LIMIT 1");
        $query->bindColumn('property', $property);
        $query->bindValue('value', $value);
        $query->execute();

        return $query->fetch() ?? false;
    }

    public function insert(): bool
    {
        $query = static::prepare(sprintf('INSERT INTO %s (%s) VALUES (%s)', static::$table, implode(', ', array_keys(static::$properties)), implode(', ', array_map(fn($property) => ':' . $property, array_keys(static::$properties)))));

        foreach (array_keys(static::$properties) as $property) {
            $query->bindValue($property, $this->data[$property]);
        }

        return $query->execute();
    }

    public function update(): bool
    {
        $query = static::prepare(sprintf('UPDATE %s SET %s WHERE %s = :value', static::$table, implode(', ', array_map(fn($property) => $property . ' = :' . $property, array_keys(static::$properties))), static::$primaryKey));

        foreach (array_keys(static::$properties) as $property) {
            $query->bindValue($property, $this->data[$property]);
        }

        $query->bindValue('value', $this->data[static::$primaryKey]);

        return $query->execute();
    }

    public function delete(): void
    {
        $query = static::prepare(sprintf('DELETE FROM %s WHERE %s = :value', static::$table, static::$primaryKey));
        $query->bindValue('value', $this->data[static::$primaryKey]);
        $query->execute();
    }
}
