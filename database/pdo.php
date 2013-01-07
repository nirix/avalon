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

namespace avalon\database;

use avalon\core\Error;
use avalon\database\pdo\Query;
use avalon\database\pdo\Statement;

/**
 * PDO Database wrapper
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class PDO extends Driver
{
    private $connection;
    private $connection_name;
    private $query_count = 0;
    protected $last_query;

    public $prefix;

    /**
     * PDO wrapper constructor.
     *
     * @param array $config Database config array
     */
    public function __construct($config, $name)
    {
        if (!is_array($config)) {
            Error::halt('PDO Error', 'Database config must be an array.');
        }

        // Lowercase the database type
        $config['type'] = strtolower($config['type']);

        try {
            $this->connection_name = $name;
            $this->prefix = isset($config['prefix']) ? $config['prefix'] : '';

            // Check if a DSN is already specified
            if (isset($config['dsn'])) {
                $dsn = $config['dsn'];
            }
            // SQLite
            elseif ($config['type'] == 'sqlite') {
                $dsn = strtolower("sqlite:" . $config['path']);
            }
            // Something else...
            else {
                $dsn = strtolower($config['type']) . ':dbname=' . $config['database'] . ';host=' . $config['host'];
                if (isset($config['port'])) {
                    $dsn = "{$dsn};port={$config['port']}";
                }
            }

            $this->connection = new \PDO(
                $dsn,
                isset($config['username']) ? $config['username'] : null,
                isset($config['password']) ? $config['password'] : null,
                isset($config['options']) ? $config['options'] : array()
            );

            unset($dsn);
        } catch (\PDOException $e) {
            $this->halt($e->getMessage());
        }
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string $string
     * @param int $type Paramater type
     */
    public function quote($string, $type = \PDO::PARAM_STR)
    {
        return $this->connection->quote($string, $type);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query($query)
    {
        $this->query_count++;
        $this->last_query = $query;

        $rows = $this->connection->query($query);
        return $rows;
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $query
     * @param array $options Driver options (not used)
     *
     * @return object
     */
    public function prepare($query, array $options = array())
    {
        $this->last_query = $query;
        return new Statement($this->connection->prepare($query, $options), $this->connection_name);
    }

    /**
     * Returns a select query builder object.
     *
     * @param array $cols Columns to select
     *
     * @return object
     */
    public function select($cols = array('*'))
    {
        if (!is_array($cols)) {
            $cols = func_get_args();
        }
        return new Query("SELECT", $cols, $this->connection_name);
    }

    /**
     * Returns an update query builder object.
     *
     * @param string $table Table name
     *
     * @return object
     */
    public function update($table)
    {
        return new Query("UPDATE", $table, $this->connection_name);
    }

    /**
     * Returns a delete query builder object.
     *
     * @return object
     */
    public function delete()
    {
        return new Query("DELETE", null, $this->connection_name);
    }

    /**
     * Returns an insert query builder object.
     *
     * @param array $data Data to insert
     *
     * @return object
     */
    public function insert(array $data)
    {
        return new Query("INSERT INTO", $data, $this->connection_name);
    }

    /**
     * Returns the ID of the last inserted row.
     *
     * @return integer
     */
    public function last_insert_id()
    {
        return $this->connection->lastInsertId();
    }
}
