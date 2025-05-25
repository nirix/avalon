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

namespace Avalon\Database;

use Avalon\Database;
use Avalon\Database\Drivers\PDO\Statement;
use InvalidArgumentException;
use PDO;

/**
 * Database Model class
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Model
{
    // Static information
    protected static $table; // Table name
    protected static $primaryKey = 'id'; // Primary key
    protected static $connection = 'default'; // Name of the connection to use

    // Instance information
    protected $data = [];
    protected $isNew = true;

    protected $errors = [];

    public function __construct(array $data = [], $isNew = true)
    {
        $this->data = $data;
        $this->isNew = $isNew;
    }

    public function __call(string $name, array $args = [])
    {
        $action = substr($name, 0, 3);
        $property = strtolower(
            preg_replace('/(?<!^)[A-Z]/', '_$0', substr($name, 3))
        );

        if ($action === 'get') {
            return $this->data[$property] ?? (count($args) ? $args[0] : null);
        } elseif ($action === 'set') {
            $this->data[$property] = count($args) ? $args[0] : $args;
            return $this;
        }

        throw new InvalidArgumentException(sprintf('No method with name "%s" exists on class %s', $name, static::class));
    }

    public function data(): array
    {
        return $this->data;
    }

    public function properties(): array
    {
        return array_keys($this->data);
    }

    public function delete(): void
    {
        $query = static::prepare(sprintf('DELETE FROM %s WHERE %s = :value', static::$table, static::$primaryKey));
        $query->bindValue('value', $this->data[static::$primaryKey]);
        $query->execute();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasError(string $property): bool
    {
        return isset($this->errors[$property]);
    }

    public function getError(string $property): string
    {
        return $this->errors[$property];
    }

    public static function findBy(string $property, mixed $value): static|false
    {
        $query = static::prepareSelect("WHERE {$property} = :value LIMIT 1");
        $query->bindColumn('property', $property);
        $query->bindValue('value', $value);
        $query->execute();

        return $query->fetch() ?? false;
    }

    public static function __callStatic($name, $arguments)
    {
        if (str_starts_with($name, 'getBy')) {
            $property = strtolower(
                preg_replace('/(?<!^)[A-Z]/', '_$0', substr($name, 5))
            );

            $value = $arguments[0] ?? null;

            return static::findBy($property, $value);
        }

        throw new InvalidArgumentException(sprintf('No static method with name "%s" exists on class %s', $name, static::class));
    }

    public static function prepareSelect(string $query, array $options = []): Statement
    {
        return static::db()->prepare(sprintf('SELECT * FROM %s %s', static::$table, $query), $options)->withModel(static::class);
    }

    public static function prepare(string $query, array $options = []): Statement
    {
        return static::db()->prepare($query, $options)->withModel(static::class);
    }

    /**
     * Private function to get the database connection.
     *
     * @return object
     */
    protected static function db(): PDO
    {
        return Database::connection(static::$connection);
    }
}
