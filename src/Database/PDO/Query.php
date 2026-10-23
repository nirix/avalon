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

namespace Avalon\Database\PDO;

use Avalon\Database;
use Avalon\Database\PDO;

/**
 * PDO Database wrapper query builder
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Query
{
    private $connection_name;
    private $type;
    private $cols;
    private $table;
    private $group_by = array();
    private $where = array();
    private $limit;
    private $order_by = array();
    private $custom_sql = array();
    private $set;
    private $_model;
    private $prefix;
    private $data;

    /**
     * PDO Query builder constructor.
     *
     * @param string $type
     * @param mixed $data
     *
     * @return object
     */
    public function __construct($type, $data = null, $connection_name = 'main')
    {
        $this->connection_name = $connection_name;

        if ($type == 'SELECT') {
            $this->cols = (is_array($data) ? $data : array('*'));
        } else if ($type == 'INSERT INTO') {
            $this->data = $data;
        } else if ($type == 'UPDATE') {
            $this->table = $data;
        }

        $this->type = $type;
        $this->prefix = $this->_conn()->prefix;
    }

    /**
     * Enable use of the model object for table rows.
     *
     * @param string $model The model class.
     *
     * @return object
     */
    public function _model($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Append DISTINCT to query type.
     *
     * @return object
     */
    public function distinct(): static
    {
        $this->type = $this->type . ' DISTINCT';
        return $this;
    }

    /**
     * Set the table to select/delete from.
     *
     * @param string $table
     *
     * @return object
     */
    public function from($table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the table to insert data into.
     *
     * @param string $table
     *
     * @return object
     */
    public function into($table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Sets the column => value data.
     *
     * @param array $data
     *
     * @return object
     */
    public function set(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Orders the query rows.
     *
     * @param string $col Column
     * @param string $dir Direction
     */
    public function orderBy(string $col, string $dir = 'ASC'): static
    {
        $this->order_by = array($col, $dir);
        return $this;
    }

    public function order_by(string $col, string $dir = 'ASC'): static
    {
        return $this->orderBy($col, $dir);
    }

    /**
     * Insert custom SQL into the query.
     *
     * @param string $sql
     *
     * @return object
     */
    public function custom_sql($sql)
    {
        $this->custom_sql[] = $sql;
        return $this;
    }

    /**
     * Easily add a "table = something" to the query.
     *
     * @example
     *    where("count", 5, ">=")
     *    or
     *    where(array(array('count', 5, '>=')));
     *
     * @param string $columm Column
     * @param mixed $value Column value
     * @param string $cond Condintional (=, !=, >=, <=, !=, etc)
     *
     * @return object
     */
    public function where($columm, $value = null, $cond = '='): static
    {
        // Check if this is a mass add
        if (is_array($columm)) {
            // Loop though the columns and add them
            foreach ($columm as $where) {
                $this->where($where[0], $where[1], (isset($where[2]) ? $where[2] : '='));
            }
        }
        // Just one, add it.
        else {
            static::assertIdentifier($columm);

            $cond = strtoupper(trim($cond));
            if (!in_array($cond, static::ALLOWED_OPERATORS, true)) {
                throw new \InvalidArgumentException("Invalid where operator '{$cond}'");
            }

            $this->where[] = array($columm, $cond, $value);
        }

        return $this;
    }

    /**
     * Limits the query rows.
     *
     * @param integer $from
     * @param integer $to
     *
     * @return object
     */
    public function limit($from, $to = null): static
    {
        $this->limit = implode(',', func_get_args());
        return $this;
    }

    /**
     * Executes the query and return the statement.
     *
     * @return object
     */
    public function exec()
    {
        $result = $this->_conn()->prepare($this->_assemble());

        // Where values, placeholders match buildWhereQuery()
        foreach ($this->where as $i => $where) {
            // IS NULL / IS NOT NULL have no placeholder
            if ($where[2] === null && in_array($where[1], ['=', '!=', '<>'], true)) {
                continue;
            }

            static::bindValue($result, ":w{$i}", $where[2]);
        }

        // Handle data for INSERT INTO and UPDATE queries, placeholders match
        // buildInsertQuery() and buildUpdateQuery()
        if (in_array($this->type, ['INSERT INTO', 'UPDATE'])) {
            $this->bindValues($result, $this->data);
        }

        return $result->_model($this->_model)->exec();
    }

    /**
     * Binds values to the statement.
     *
     * @param \PDOStatement $statement
     * @param array $values
     *
     * @return void
     */
    public function bindValues($statement, array $values): void
    {
        $i = 0;
        foreach ($values as $value) {
            static::bindValue($statement, ":v{$i}", $value);
            $i++;
        }
    }

    /**
     * Operators accepted by where().
     */
    private const ALLOWED_OPERATORS = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'];

    /**
     * Asserts that the name is a valid identifier.
     *
     * @param mixed $name
     *
     * @return void
     */
    private static function assertIdentifier(mixed $name): void
    {
        if (!is_string($name) || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new \InvalidArgumentException('Invalid column name');
        }
    }

    /**
     * Binds a value, translating null to SQL NULL and the exact string "NOW()".
     *
     * @param \PDOStatement $statement
     * @param string $param
     * @param mixed $value
     *
     * @return void
     */
    private static function bindValue($statement, string $param, mixed $value): void
    {
        if ($value === null) {
            $statement->bindValue($param, null, \PDO::PARAM_NULL);
        } elseif ($value === 'NOW()') {
            $statement->bindValue($param, gmdate('Y-m-d H:i:s'));
        } else {
            $statement->bindValue($param, $value);
        }
    }

    /**
     * Private method used to compile the query into a string.
     *
     * @return string
     */
    public function _assemble()
    {
        $query = [];
        $query[] = $this->type;

        if (in_array($this->type, array("SELECT", "SELECT DISTINCT"))) {
            $cols = array();
            foreach ($this->cols as $col => $as) {
                // Check for `table.*` or `table.column`
                if (strpos($as, '.')) {
                    $col = explode('.', $as);
                    $cols[] = "`{$this->prefix}{$col[0]}`." . ($col[1] == '*' ? '*' : "`{$col[1]}`");
                }
                // Check if we're fetching all columns
                else if ($as == '*') {
                    $cols[] = '*';
                }
                // Check if we're fetching a column as an "alias"
                else if (!is_numeric($col)) {
                    $cols[] = "`{$col}` AS `{$as}`";
                }
                // Normal column
                else {
                    $cols[] = "`{$as}`";
                }
            }
            $query[] = implode(', ', $cols);
        }

        // Select or Delete query
        if (in_array($this->type, array("SELECT", "SELECT DISTINCT", "DELETE"))) {
            $query[] = "FROM `{$this->prefix}{$this->table}`";

            // Where
            $query = array_merge($query, $this->buildWhereQuery());

            // Group by
            if (count($this->group_by) > 0) {
                $query[] = "GROUP BY " . implode(', ', $this->group_by);
            }

            // Custom SQL
            if (count($this->custom_sql)) {
                $query[] = implode(" ", $this->custom_sql);
            }

            // Order by
            if (count($this->order_by) > 0) {
                $query[] = "ORDER BY `{$this->prefix}{$this->table}`.`{$this->order_by[0]}` {$this->order_by[1]}";
            }

            // Limit
            if ($this->limit != null) {
                $query[] = "LIMIT {$this->limit}";
            }
        }
        // Insert query
        else if ($this->type == "INSERT INTO") {
            $query[] = "`{$this->prefix}{$this->table}`";
            $query[] = $this->buildInsertQuery();
        }
        // Update query
        else if ($this->type == "UPDATE") {
            $query[] = "`{$this->prefix}{$this->table}`";
            $query[] = $this->buildUpdateQuery();

            // Where
            $query = array_merge($query, $this->buildWhereQuery());
        }

        return implode(" ", $query);
    }

    /**
     * Builds the where query.
     *
     * @return string
     */
    private function buildWhereQuery()
    {
        $query = array();

        // Where
        if (count($this->where)) {
            $where = array();

            foreach ($this->where as $i => $param) {
                if ($param[2] === null && in_array($param[1], ['=', '!=', '<>'], true)) {
                    $where[] = "`{$param[0]}` IS " . ($param[1] === '=' ? '' : 'NOT ') . 'NULL';
                } else {
                    $where[] = "`{$param[0]}` {$param[1]} :w{$i}";
                }
            }

            $query[] = "WHERE " . implode(' AND ', $where);
            unset($where);
        }

        return $query;
    }

    /**
     * Builds the insert query.
     *
     * @return string
     */
    private function buildInsertQuery()
    {
        $columns = [];
        $placeholders = [];

        foreach (array_keys($this->data) as $i => $column) {
            static::assertIdentifier($column);
            $columns[] = "`{$column}`";
            $placeholders[] = ":v{$i}";
        }

        return '(' . implode(', ', $columns) . ') VALUES(' . implode(', ', $placeholders) . ')';
    }

    /**
     * Builds the update query.
     *
     * @return string
     */
    private function buildUpdateQuery()
    {
        $set = [];

        foreach (array_keys($this->data) as $i => $column) {
            static::assertIdentifier($column);
            $set[] = "`{$column}` = :v{$i}";
        }

        return 'SET ' . implode(', ', $set);
    }

    /**
     * Private function to return the database connection.
     *
     * @return object
     */
    private function _conn()
    {
        return Database::connection($this->connection_name);
    }

    /**
     * Magic method that converts the query to a string.
     * And some PHP team members have said PHP is not a magic language,
     * this is why Ruby is better.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_assemble();
    }
}
