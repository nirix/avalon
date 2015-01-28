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

use avalon\Database;
use avalon\helpers\Time;
use \FishHook;

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
    protected static $_name; // Table name
    protected static $_primary_key = 'id'; // Primary key
    protected static $_has_many; // Has many relationship array
    protected static $_properties = array('*'); // Table columns
    protected static $_belongs_to; // Belongs to relationship array
    protected static $_filters_before = array(); // Before filters
    protected static $_filters_after = array(); // After filters
    protected static $_connection_name = 'main'; // Name of the connection to use
    protected static $_escape = array(); // Fields to escape when reading from database

    // Information different per table row
    protected $_changed_properties = array(); // Properties that have been changed
    protected $_data = array();
    protected $_is_new = true; // Used to determine if this is a new row or not.
    protected $errors = array();

    /**
     * Used to build to assign the row data to the class as variables.
     *
     * @param array $data The row data
     */
    public function __construct($data = null, $is_new = true) {
        $this->_data = $data;
        $this->_is_new = $is_new;

        // Is there any data?
        if (is_array($data)) {
            // If so get the columns and add them to
            // the properties array
            foreach (array_keys($data) as $column) {
                if (!in_array($column, static::$_properties)) {
                    static::$_properties[] = $column;
                }

                if (in_array($column, static::$_escape)) {
                    $this->_data[$column] = htmlspecialchars($this->_data[$column]);
                }
            }
        }

        // Create filter arrays if they aren't already
        foreach (array('construct', 'create', 'save') as $filter) {
            // Before filters
            if (!isset(static::$_filters_before[$filter])) {
                static::$_filters_before[$filter] = array();
            }

            // After filters
            if (!isset(static::$_filters_after[$filter])) {
                static::$_filters_after[$filter] = array();
            }
        }

        if (!in_array('_date_time_convert', static::$_filters_after['construct'])) {
            static::$_filters_after['construct'][] = '_date_time_convert';
        }

        if (!in_array('_timestamps', static::$_filters_before['create'])) {
            static::$_filters_before['create'][] = '_timestamps';
        }

        if (!in_array('_timestamps', static::$_filters_before['save'])) {
            static::$_filters_before['save'][] = '_timestamps';
        }

        // And run the after construct filter array...
        if (isset(static::$_filters_after['construct'])) {
            foreach (static::$_filters_after['construct'] as $filter) {
                $this->$filter();
            }
        }

        // Plugin hook
        FishHook::run('model::__construct', array(get_called_class(), $this, &static::$_properties, &static::$_escape));
    }

    /**
     * Find the first matching row and returns it.
     *
     * @param string $find Either the value of the primary key, or the field name.
     * @param value $value The value of the field to find if the $find param is the field name.
     *
     * @return Object
     */
    public static function find($find, $value = null) {
        $select = static::db()->select()->from(static::$_name);
        if ($value == null) {
            $select = $select->where(static::$_primary_key, $find)->limit(1)->exec();
        } else {
            $select = $select->where($find, $value)->limit(1)->exec();
        }

        if ($select->row_count() == 0) {
            return false;
        }

        // Plugin hook
        FishHook::run('model::find', array(get_called_class(), $find, $value));

        return new static($select->fetch(), false);
    }

    /**
     * Creates a new row or saves the changed properties.
     */
    public function save() {
        $primary_key = static::$_primary_key;

        // Make sure the data is valid..
        if (!$this->is_valid()) {
            return false;
        }

        // Save
        if ($this->_is_new() === false) {
            // Before save filters
            if (isset(static::$_filters_before['save']) and is_array(static::$_filters_before['save'])) {
                foreach (static::$_filters_before['save'] as $filter) {
                    $this->$filter();
                }
            }

            // Loop over the properties
            $data = array();
            foreach (static::$_properties as $column) {
                // Check if column is updated, if so, save.
                if (in_array($column, $this->_changed_properties)) {
                    $data[$column] = (in_array($column, static::$_escape)) ? htmlspecialchars_decode($this->_data[$column]) : $this->_data[$column];
                }
            }
            unset($data[static::$_primary_key]);

            FishHook::run('model::save/save', array(get_called_class(), &$data));

            // Save the row..
            static::db()->update(static::$_name)->set($data)->where(static::$_primary_key, $this->_data[static::$_primary_key])->exec();

            return true;
        }
        // Create
        else {
            // Before create filters
            if (isset(static::$_filters_before['create']) and is_array(static::$_filters_before['create'])) {
                foreach (static::$_filters_before['create'] as $filter) {
                    $this->$filter();
                }
            }

            // Loop over the properties
            $data = array();
            foreach (static::$_properties as $column) {
                // Hack to fix http://bugs.traq.io/traq/tickets/358
                if (!is_array($column) and !is_object($column) and isset($this->_data[$column])) {
                    $data[$column] = $this->_data[$column];
                }
            }
            //unset($data[static::$_primary_key]);

            FishHook::run('model::save/create', array(get_called_class(), &$data));

            // Insert the row..
            static::db()->insert($data)->into(static::$_name)->exec();

            // Set the primary key
            $this->_data[$primary_key] = static::db()->last_insert_id();

            return true;
        }
    }

    /**
     * Deletes the row.
     */
    public function delete() {
        if ($this->_is_new() === false) {
            // Before delete filters
            if (isset(static::$_filters_before['delete']) and is_array(static::$_filters_before['delet'])) {
                foreach (static::$_filters_before['delete'] as $filter) {
                    $this->$filter();
                }
            }
            return static::db()->delete()->from(static::$_name)->where(static::$_primary_key, $this->_data[static::$_primary_key])->exec();
        }
        return false;
    }

    /**
     * Checks if the row is new or not.
     *
     * @return bool
     */
    public function _is_new($is_new = null) {
        if ($is_new !== null) {
            $this->_is_new =  $is_new;
        }
        return $this->_is_new;
    }

    /**
     * Sets the value of the column(s) to the value(s).
     *
     * @param mixed $col Either the column or an array to update multiple columns.
     * @param mixed $val The value of the column if only updating one column.
     *
     * @example $model->set(array('col1'=>'val1', 'col2'=>'val2'));
     *          $model->set('col1', 'val1');
     */
    public function set($col, $val = null) {
        if (is_array($col)) {
            foreach ($col as $var => $val) {
                $this->set($var, $val);
            }
        } else {
            $this->_data[$col] = $val;
            $this->_set_changed($col);

            if (!in_array($val, static::$_properties)) {
                static::$_properties[] = $val;
            }

            // Plugin hook
            FishHook::run('model::set', array(get_called_class(), $col, $val));
        }
    }

    /**
     * Adds the property to the changed properties array.
     *
     * @param string $property
     */
    protected function _set_changed($property) {
        if (in_array($property, static::$_properties) and !in_array($property, $this->_changed_properties)) {
            $this->_changed_properties[] = $property;
        }
    }

    /**
     * Shortcut of the select() function for the database.
     *
     * @param mixed $cols The columns to select.
     *
     * @return object
     */
    public static function select($cols = null) {
        return static::db()->select($cols === null ? static::$_properties : $cols)->from(static::$_name)->_model(static::_class());
    }

    /**
     * Aliases the database's update() method for the current row.
     */
    public function update() {
        return static::db()->update(static::$_name)->where(static::$_primary_key, $this->data[static::$_primary_key]);
    }

    /**
     * Fetches all the rows for the table.
     *
     * @return array
     */
    public static function fetch_all() {
        $rows = array();
        $fetched = static::db()->select(static::$_properties)->from(static::$_name)->exec()->fetch_all();

        foreach ($fetched as $row) {
            $rows[] = new static($row, false);
            unset($row);
        }

        return $rows;
    }

    public function is_valid() {
        // Until the validation stuff is done we will return false,
        // to work around this each model will have to create its own
        // is_valid method.
        return false;
    }

    /**
     * Magical function to load the relationships.
     */
    public function __get($var) {
        // Model data
        if (in_array($var, static::$_properties)) {
            $val = isset($this->_data[$var]) ? $this->_data[$var] : '';

            // Plugin hook
            FishHook::run('model::__get', array(get_called_class(), $var, $this->_data, &$val));

            return $val;
        }
        // Has many
        elseif (is_array(static::$_has_many) and (in_array($var, static::$_has_many) or isset(static::$_has_many[$var]))) {
            $has_many = array();
            if (isset(static::$_has_many[$var])) {
                $has_many = static::$_has_many[$var];
            }
            // Model
            if (!isset($has_many['model'])) {
                $namespace = explode('\\', get_called_class());
                unset($namespace[count($namespace) - 1]);

                $model = ucfirst((substr($var, -1) == 's' ? substr($var, 0, -1) : $var));
                $class = '\\' . implode('\\', $namespace) . '\\' . $model;

                $has_many['model'] = $class;
            } else {
                $model = explode('\\', $var);

                if (count($model) == 1) {
                    $namespace = explode('\\', get_called_class());
                    $namespace[count($namespace) -1] = ucfirst($has_many['model']);
                    $has_many['model'] = implode('\\', $namespace);
                }
            }
            // Different foreign key?
            if (!isset($has_many['foreign_key'])) {
                $has_many['foreign_key'] = substr(static::$_name, 0, -1) . '_id';
            }
            // Different column?
            if (!isset($has_many['column'])) {
                $has_many['column'] = static::$_primary_key;
            }

            $model = $has_many['model'];
            $column = $has_many['column'];
            return $this->$var = $model::select()->where($has_many['foreign_key'], $this->$column);
        }
        // Belongs to
        else if (is_array(static::$_belongs_to) and (in_array($var, static::$_belongs_to) or isset(static::$_belongs_to[$var]))) {
            $belongs_to = array();
            if (isset(static::$_belongs_to[$var])) {
                $belongs_to = static::$_belongs_to[$var];
            }
            // Model
            if (!isset($belongs_to['model'])) {
                $namespace = explode('\\', get_called_class());
                unset($namespace[count($namespace) - 1]);

                $model = ucfirst($var);
                $class = '\\' . implode('\\', $namespace) . '\\' . $model;

                $belongs_to['model'] = $class;
            } else {
                $model = explode('\\', $belongs_to['model']);

                if (count($model) == 1) {
                    $namespace = explode('\\', get_called_class());
                    $namespace[count($namespace) -1] = ucfirst($belongs_to['model']);
                    $belongs_to['model'] = implode('\\', $namespace);
                }
            }
            // Different foreign key?
            if (!isset($belongs_to['foreign_key'])) {
                $belongs_to['foreign_key'] = $belongs_to['model']::$_primary_key;
            }
            // Different column?
            if (!isset($belongs_to['column'])) {
                $belongs_to['column'] = $var . '_id';
            }
            $model = $belongs_to['model'];
            return $this->$var = $model::find($belongs_to['foreign_key'], $this->{$belongs_to['column']});
        } else {
            $val = $this->$var;

            // Plugin hook
            FishHook::run('model::__get', array(get_called_class(), $var, $this->_data, &$val));

            return $val;
        }
    }

    /**
     * Magical set function to check if the property exists or not.
     */
    public function __set($var, $val) {
        if (in_array($var, static::$_properties)) {
            FishHook::run('model::__set', array(get_called_class(), $var, &$val));
            $this->_data[$var] = $val;
            $this->_set_changed($var);
        } else {
            $this->$var = $val;
        }
    }

    /**
     * Returns the models data as an array.
     *
     * @return array
     */
    public function __toArray($fields = null) {
        // Returns the models data for all fields
        if ($fields == null) {
            return $this->_data;
        }
        // Return only the fields specified
        else
        {
            $data = array();
            foreach($fields as $field) {
                $data[$field] = $this->_data[$field];
            }
            unset($fields, $field);
            return $data;
        }
    }

    /**
     * Used to add errors to the models error array.
     *
     * @param string $field
     * @param string $message
     */
    public function _add_error($field, $message) {
        $this->errors[$field] = $message;
    }

    /**
     * Adds a data property to the model.
     *
     * @param string $name
     */
    public static function _add_property($name)
    {
        if (!in_array($name, static::$_properties)) {
            static::$_properties[] = $name;
        }
    }

    /**
     * Returns the real name of this model class, not the top-most parent.
     *
     * @return string
     */
    public static function _class() {
        return get_class(new static());
    }

    /**
     * Sets the created_at and updated_at fields when saving.
     */
    private function _timestamps() {
        // Created at field
        if ($this->_is_new() and in_array('created_at', static::$_properties) and !isset($this->_data['created_at'])) {
            $this->_data['created_at'] = "NOW()";
        }

        // Updated at field
        if (!$this->_is_new() and in_array('updated_at', static::$_properties)) {
            $this->updated_at = "NOW()";
        }
    }

    /**
     * Converts the created_at, updated_at and published_at properties
     * to local time from gmt time.
     */
    private function _date_time_convert() {
        foreach (array('created_at', 'updated_at', 'published_at') as $var) {
            if (!$this->_is_new() and isset($this->_data[$var])) {
                $this->_data[$var] = Time::gmt_to_local($this->_data[$var]);
            }
        }
    }

    /**
     * Private function to get the database connection.
     *
     * @return object
     */
    protected static function db() {
        return Database::connection(static::$_connection_name);
    }
}
