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

namespace avalon\database\pdo;

use avalon\Database;
use avalon\database\PDO;

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
        return $this;
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
    public function distinct()
    {
        $this->type = $this->type.' DISTINCT';
        return $this;
    }

    /**
     * Set the table to select/delete from.
     *
     * @param string $table
     *
     * @return object
     */
    public function from($table)
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
    public function into($table)
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
    public function set(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Orders the query rows.
     *
     * @param string $col Column
     * @param string $dir Direction
     *
     * @return object
     */
    public function order_by($col, $dir = 'ASC')
    {
        $this->order_by = array($col, $dir);
        return $this;
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
    public function where($columm, $value = null, $cond = '=')
    {
        // Check if this is a mass add
        if (is_array($columm)) {
            // Loop though the columns and add them
            foreach($columm as $where) {
                $this->where($where[0], $where[1], (isset($where[2]) ? $where[2] : '='));
            }
        }
        // Just one, add it.
        else {
            $this->where[] = array($columm, $cond, $value === null ? 'NULL' : $value);
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
    public function limit($from, $to = null)
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

        if ($this->type != 'INSERT') {
            foreach ($this->where as $where) {
                $result->bind_value(':'. $where[0], $where[2]);
            }
        }

        return $result->_model($this->_model)->exec();
    }

    /**
     * Private method used to compile the query into a string.
     *
     * @return string
     */
    public function _assemble()
    {
        $query = array();
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
            $query = array_merge($query, $this->_build_where());

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
        else if($this->type == "INSERT INTO") {
            $query[] = "`{$this->prefix}{$this->table}`";

            $keys = array();
            $values = array();

            foreach($this->data as $key => $value) {
                $keys[] = "`{$key}`";
                $values[] = $this->_process_value($value);
            }

            $query[] = '(' . implode(', ', $keys) . ')';
            $query[] = 'VALUES(' . implode(', ', $values) . ')';
        }
        // Update query
        else if($this->type == "UPDATE") {
            $query[] = "`{$this->prefix}{$this->table}`";

            $query[] = "SET";
            $set = array();
            foreach ($this->data as $column => $value) {
                $value = $this->_process_value($value);
                $set[] = "`{$column}` = {$value}";
            }
            $query[] = implode(', ', $set);

            // Where
            $query = array_merge($query, $this->_build_where());
        }

        return implode(" ", $query);
    }

    private function _build_where()
    {
        $query = array();

        // Where
        if (count($this->where)) {
            $where = array();

            foreach ($this->where as $param) {
                $where[] = "`{$param[0]}` {$param[1]} :{$param[0]}";
            }

            $query[] = "WHERE " . implode(' AND ', $where);
            unset($where);
        }

        return $query;
    }

    /**
     * Processes the value.
     *
     * @return mixed
     */
    private function _process_value($value)
    {
        if ($value === "NOW()") {
            return $this->_conn()->quote(gmdate("Y-m-d H:i:s"));
        } elseif ($value === "NULL") {
            return 'NULL';
        } else {
            return $this->_conn()->quote($value);
        }
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
