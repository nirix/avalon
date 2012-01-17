<?php
/*
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * @author Jack P. <nrx@nirix.net>
 * @copyright Jack P.
 * @license New BSD License
 */

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
	public static $db;
	protected static $_name; // Table name
	protected static $_primary_key = 'id'; // Primary key
	protected static $_has_many; // Has many relationship array
	protected static $_belongs_to; // Belongs to relationship array
	protected static $_filters_before = array(); // Before filters
	protected static $_filters_after = array(); // After filters
	protected static $_properties = array(); // Table columns
	protected $_changed_properties = array(); // Properties that have been changed
	protected $_data = array();
	protected $_is_new = true; // Used to determine if this is a new row or not.
	
	/**
	 * Used to build to assign the row data to the class as variables.
	 *
	 * @param array $data The row data
	 */
	public function __construct($data = null, $is_new = true)
	{
		$this->_data = $data;
		$this->_is_new = $is_new;
		
		if (!isset(static::$_filters_after['construct'])) {
			static::$_filters_after['construct'] = array();
		}
		if (!in_array('_date_time_convert', static::$_filters_after['construct'])) {
			static::$_filters_after['construct'][] = '_date_time_convert';
		}
			
		if (isset(static::$_filters_after['construct'])) {
			$filters = (is_array(static::$_filters_after['construct']) ? static::$_filters_after['construct'] : array(static::$_filters_after['construct']));
			foreach ($filters as $filter) {
				$this->$filter();
			}
		}
	}
	
	/**
	 * Find the first matching row and returns it.
	 *
	 * @param string $find Either the value of the primary key, or the field name.
	 * @param value $value The value of the field to find if the $find param is the field name.
	 *
	 * @return Object
	 */
	public static function find($find, $value = null)
	{
		$select = Database::driver()->select()->from(static::$_name);
		if ($value == null) {
			$select = $select->where(static::$_primary_key, $find)->limit(1)->exec();
		} else {
			$select = $select->where($find, $value)->limit(1)->exec();
		}
		
		if ($select->row_count() == 0)
		{
			return false;
		}
		
		return new static($select->fetch(), false);
	}
	
	/**
	 * Creates a new row or saves the changed properties.
	 */
	public function save()
	{
		$primary_key = static::$_primary_key;
		
		// Make sure the data is valid..
		if (!$this->is_valid())
		{
			return false;
		}
		
		// Save
		if ($this->_is_new() === false)
		{
			// Loop over the properties
			$data = array();
			foreach (static::$_properties as $column) {
				// Check if column is updated, if so, save.
				if (in_array($column, $this->_changed_properties)) {
					$data[$column] = $this->_data[$column];
				}
			}
			unset($data[static::$_primary_key]);
			
			// Save the row..
			Database::driver()->update(static::$_name)->set($data)->where(static::$_primary_key, $this->_data[static::$_primary_key])->exec();
		}
		// Create
		else
		{
			// Before create filters
			if (isset(static::$_filters_before['create']) and is_array(static::$_filters_before['create']))
			{
				foreach (static::$_filters_before['create'] as $filter)
				{
					$this->$filter();
				}
			}
			
			// Loop over the properties
			$data = array();
			foreach (static::$_properties as $column) {
				$data[$column] = $this->_data[$column];
			}
			unset($data[static::$_primary_key]);
			
			// Insert the row..
			Database::driver()->insert($data)->into(static::$_name)->exec();
			
			// Set the primary key
			$this->$primary_key = Database::driver()->last_insert_id();
		}
	}
	
	/**
	 * Deletes the row.
	 */
	public function delete()
	{
		if ($this->_is_new() === false) {
			return Database::driver()->delete()->from('users')->where(static::$_primary_key, $this->_data[static::$_primary_key])->exec();
		}
	}
	
	/**
	 * Checks if the row is new or not.
	 *
	 * @return bool
	 */
	public function _is_new($is_new = null)
	{
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
	public function set($col, $val = null)
	{
		if (is_array($col)) {
			foreach ($col as $var => $val) {
				$this->set($var, $val);
			}
		} else {
			$this->_data[$col] = $val;
			$this->_set_changed($col);
			
			if (!isset(static::$_properties[$val]))
			{
				static::$_properties[] = $val;
			}
		}
	}
	
	private function _set_changed($property)
	{
		if (in_array($property, static::$_properties) and !in_array($property, $this->_changed_properties))
		{
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
	public static function select($cols = null)
	{
		return Database::driver()->select($cols === null ? static::$_properties : $cols)->from(static::$_name)->_model(static::_class());
	}
	
	/**
	 * Aliases the database's update() method for the current row.
	 */
	public function update()
	{
		return Database::driver()->update(static::$_name)->where(static::$_primary_key, $this->data[static::$_primary_key]);
	}
		
	/**
	 * Fetches all the rows for the table.
	 *
	 * @return array
	 */
	public static function fetch_all()
	{
		$rows = array();
		$fetched = Database::driver()->select(static::$_properties)->from(static::$_name)->exec()->fetch_all();
		
		foreach ($fetched as $row) {
			$rows[] = new static($row);
			unset($row);
		}
		
		return $rows;
	}
	
	public function is_valid()
	{
		// Until the validation stuff is done we will return false,
		// to work around this each model will have to create its own
		// is_valid method.
		return false;
	}
	
	/**
	 * Magical function to load the relationships.
	 */
	public function __get($var)
	{
		if (in_array($var, static::$_properties))
		{
			return $this->_data[$var];
		}
		// Has many
		if (is_array(static::$_has_many) and (in_array($var, static::$_has_many) or isset(static::$_has_many[$var])))
		{
			$has_many = array();
			if (isset(static::$_has_many[$var])) {
				$has_many = static::$_has_many[$var];
			}
			// Model
			if (!isset($has_many['model'])) {
				$has_many['model'] = ucfirst((substr($var, -1) == 's' ? substr($var, 0, -1) : $var));
			}
			// Different foreign key?
			if (!isset($has_many['foreign_key'])) {
				$has_many['foreign_key'] = substr(static::$_name, 0, -1) . '_id';
			}
			// Different column?
			if (!isset($has_many['column'])) {
				$has_many['column'] = 'id';
			}
			
			$model = $has_many['model'];
			$column = $has_many['column'];
			return $this->$var = $model::select()->where($has_many['foreign_key'] . " = '?'", $this->$column);
		}
		// Belongs to
		else if (is_array(static::$_belongs_to) and (in_array($var, static::$_belongs_to) or isset(static::$_belongs_to[$var])))
		{
			$belongs_to = array();
			if (isset(static::$_belongs_to[$var])) {
				$belongs_to = static::$_belongs_to[$var];
			}
			// Model
			if (!isset($belongs_to['model'])) {
				$belongs_to['model'] = ucfirst($var);
			}
			// Different foreign key?
			if (!isset($belongs_to['foreign_key'])) {
				$belongs_to['foreign_key'] = 'id';
			}
			// Different column?
			if (!isset($belongs_to['column'])) {
				$belongs_to['column'] = $var . '_id';
			}
			$model = $belongs_to['model'];
			return $this->$var = $model::find($belongs_to['foreign_key'], $this->$belongs_to['column']);
		}
		else
		{
			return $this->$var;
		}
	}
	
	/**
	 * Magical set function to check if the property exists or not.
	 */
	public function __set($var, $val)
	{
		if (in_array($var, static::$_properties))
		{
			$this->_data[$var] = $val;
			$this->_set_changed($var);
		}
		else
		{
			$this->$var = $val;
		}
	}
	
	/**
	 * Returns the real name of this model class, not the top-most parent.
	 *
	 * @return string
	 */
	public static function _class()
	{
		return get_class(new static());
	}
	
	/**
	 * Converts the created_at, updated_at and published_at properties
	 * to local time from gmt time.
	 */
	public function _date_time_convert()
	{
		foreach (array('created_at', 'updated_at', 'published_at') as $var) {
			if (isset($this->_data[$var])) {
				$this->_data[$var] = Time::gmt_to_local($this->_data[$var]);
			}
		}
	}
}