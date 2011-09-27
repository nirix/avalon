<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Base model class
 * @author Jack Polgar
 * @since 0.1
 * @package Avalon
 * @subpackage Database
 */
class Model
{
	public static $db;
	protected static $_name; // Table name
	protected static $_primary_key = 'id'; // Primary key
	protected static $_has_many; // Has many relationship array
	protected static $_belongs_to; // Belongs to relationship array
	protected static $_class_name; // Class name if different to table name
	protected $_columns = array(); // Table columns
	protected $_primary_key_value; // Priamry key value
	
	/**
	 * Used to build to assign the row data to the class as variables.
	 * @param array $data The row data
	 * @author Jack Polgar
	 * @since 0.1
	 */
	public function __construct($data = null)
	{
		// Loop through the data and make it accessible
		// via $model->column_name
		$this->_data = $data;
		if ($data !== null) {
			foreach ($data as $column => $value) {
				$this->$column = $value;
				$this->_columns[] = $column;
			}
		}
		
		// Set the primary key value
		if (isset($data[static::$_primary_key])) {
			$this->_primary_key_value = $data[static::$_primary_key];
		}
	}
	
	public function save()
	{
		$primary_key = static::$_primary_key;
		// Save
		if (isset($this->$primary_key)) {
			if ($this->is_valid()) {
				$data = array();
				foreach ($this->_columns as $column) {
					$data[$column] = $this->$column;
				}
				unset($data[static::$_primary_key]);
				
				Database::link()->update(static::$_name)->set($data)->where(static::$_primary_key . " = '?'", $this->_primary_key_value)->exec();
			}
		}
		// Create
		else {
			if ($this->is_valid()) {
				$data = array();
				foreach ($this->_columns as $column) {
					$data[$column] = $this->$column;
				}
				unset($data[static::$_primary_key]);
				
				Database::link()->insert($data)->into(static::$_name)->exec();
			}
		}
	}
	
	public function delete()
	{
		if ($this->_primary_key_value !== null) {
			return Database::link()->delete()->from('users')->where(static::$_primary_key . " = '?'", $this->_primary_key_value)->exec();
		}
	}
	
	/**
	 * Sets the value of the column(s) to the value(s).
	 * @param mixed $col Either the column or an array to update multiple columns.
	 * @param mixed $val The value of the column if only updating one column.
	 * @since 0.1
	 * @example $model->set(array('col1'=>'val1', 'col2'=>'val2'));
	 *          $model->set('col1', 'val1');
	 */
	public function set($col, $val = null)
	{
		if (is_array($col)) {
			foreach ($col as $var => $val) {
				$this->$var = $val;
			}
		} else {
			$this->$col = $val;
		}
	}
	
	/**
	 * Shortcut of the select() function for the database.
	 * @param mixed $cols The columns to select.
	 * @since 0.1
	 */
	public static function select($cols = '*')
	{
		return Database::link()->select($cols)->from(static::$_name)->_model(static::_class_name());
	}
	
	/**
	 * Aliases the database's update() method for the current row.
	 * @author Jack Polgar
	 * @since 0.1
	 */
	public function update()
	{
		return Database::link()->update(static::$_name)->where(static::$_primary_key . " = '?'", $this->data[static::$_primary_key]);
	}
	
	/**
	 * Find the first matching row and returns it.
	 * @param string $find Either the value of the primary key, or the field name.
	 * @param value $value The value of the field to find if the $find param is the field name.
	 * @return Object
	 * @author Jack Polgar
	 * @since 0.1
	 */
	public static function find($find, $value = null)
	{
		$select = Database::link()->select()->from(static::$_name);
		if ($value == null) {
			$select = $select->where(static::$_primary_key . " = '?'", $find)->limit(1)->exec()->fetchAssoc();
		} else {
			$select = $select->where($find . " = '?'", $value)->limit(1)->exec()->fetchAssoc();
		}
		return new static($select);
	}
	
	/**
	 * Fetches all the rows for the table.
	 * @return array
	 */
	public static function fetchAll()
	{
		$rows = array();
		$fetched = Database::link()->select('*')->from(static::$_name)->exec()->fetchAll();
		
		foreach ($fetched as $row) {
			$rows[] = new static($row);
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
		// Has many
		if (is_array(static::$_has_many) and (in_array($var, static::$_has_many) or isset(static::$_has_many[$var]))) {
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
			$this->$var = $model::select()->where($has_many['foreign_key'] . " = '?'", $this->$column);
		}
		// Belongs to
		elseif (is_array(static::$_belongs_to) and (in_array($var, static::$_belongs_to) or isset(static::$_belongs_to[$var]))) {
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
			$this->$var = $model::find($belongs_to['foreign_key'], $this->$belongs_to['column']);
		}
		return $this->$var;
	}
	
	/**
	 * Private function to be used by the model class to get the class name.
	 * @access private
	 */
	private static function _class_name()
	{
		if (isset(static::$_class_name)) {
			return static::$_class_name;
		} elseif (substr(static::$_name, -1) == 's') {
			return substr(static::$_name, 0, -1);
		} else {
			throw new Exception('Unable to determin class name for the ' . static::$_name . ' model');
		}
	}
}