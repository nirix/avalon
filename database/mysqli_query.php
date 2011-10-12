<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

class MySQLi_Query
{
	private $type;
	private $cols;
	private $table;
	private $group_by = array();
	private $where = array();
	private $limit;
	private $order_by = array();
	private $custom_sql = array();
	private $callback;
	private $model;
	private $set;
	
	public function __construct($type, $data = null)
	{
		if ($type == 'SELECT') {
			$this->cols = (is_array($data) ? $data : array('*'));
		} else if ($type == 'INSERT INTO') {
			$this->data = $data;
		} else if ($type == 'UPDATE') {
			$this->table = $data;
		}
		$this->type = $type;
		$this->prefix = Avalon_MySQLi::get_instance()->prefix;
		return $this;
	}
	
	/**
	 * Enable use of the model object for table rows.
	 * @param string $model The model class.
	 */
	public function _model($model)
	{
		$this->model = $model;
		return $this;
	}

	public function distinct()
	{
		$this->type = $this->type.' DISTINCT';
		return $this;
	}

	public function from($table)
	{
		$this->table = $table;
		return $this;
	}

	public function into($table)
	{
		$this->table = $table;
		return $this;
	}
	
	public function set($data)
	{
		$this->data = $data;
		return $this;
	}
	
	public function orderBy($col, $dir = 'ASC')
	{
		$this->order_by = array($col, $dir);
		return $this;
	}
	
	public function customSql($sql)
	{
		$this->custom_sql[] = $sql;
		return $this;
	}
	
	/**
	 * Easily add a "table = something" to the query.
	 * @example where("user_id = '?'", 1)
	 * @param string $sql The SQL bit.
	 * @param string|integer $val The value to add to the SQL.
	 */
	public function where($sql, $val = '')
	{
		$this->where[] = "`{$this->prefix}{$this->table}`." . str_replace('?', Avalon_MySQLi::get_instance()->real_escape_string($val), $sql);
		return $this;
	}
	
	public function limit($from, $to = null)
	{
		$this->limit = implode(',', func_get_args());
		return $this;
	}
	
	public function callback($callback)
	{
		$this->callback = $callback;
		return $this;
	}
  
	public function exec()
	{
		$result = Avalon_MySQLi::get_instance()->query($this->_assemble())->_model($this->model);
		
		if ($this->callback !== null) {
			$method = $this->callback;
			return $method($result);
		} else {
			return $result;
		}
	}
	
	private function _assemble()
	{
		$query = array();
		$query[] = $this->type;
		
		if (in_array($this->type, array("SELECT", "SELECT DISTINCT"))) {
			$cols = array();
			foreach ($this->cols as $col => $as) {
				if (!is_numeric($col)) {
					$cols[] = "{$col} AS {$as}";
				} else {
					$cols[] = "{$as}";
				}
			}
			$query[] = implode(', ', $cols);
		}
		
		if (in_array($this->type, array("SELECT", "SELECT DISTINCT", "DELETE")))
		{
			$query[] = "FROM `{$this->prefix}{$this->table}`";
			
			if (count($this->group_by) > 0)
			{
				$query[] = "GROUP BY " . implode(', ', $this->groupby);
			}
			
			if (count($this->where)) {
				$query[] = "WHERE " . implode(' AND ', $this->where);
			}
			
			if (count($this->custom_sql)) {
				$query[] = implode(" ", $this->custom_sql);
			}
			
			if (count($this->order_by) > 0) {
				$query[] = "ORDER BY `{$this->table}`.`{$this->order_by[0]}` {$this->order_by[1]}";
			}
			
			if ($this->limit != null) {
				$query[] = "LIMIT {$this->limit}";
			}
		} else if($this->type == "INSERT INTO") {
			$query[] = "`{$this->prefix}{$this->table}`";
			
			$keys = array();
			$values = array();
			
			foreach($this->data as $key => $value) {
				$keys[] = "`{$key}`";
				$values[] = $this->_process_value($value);
			}
			
			$query[] = ' (' . implode(', ', $keys) . ')';
			$query[] = ' VALUES(' . implode(', ', $values) . ')';
		} else if($this->type == "UPDATE") {
			$query[] = "`{$this->prefix}{$this->table}`";
			
			$query[] = "SET";
			$set = array();
			foreach ($this->data as $column => $value) {
				$value = $this->_process_value($value);
				$set[] = "`{$column}` = {$value}";
			}
			$query[] = implode(', ', $set);
			
			if (count($this->where)) {
				$query[] = "WHERE " . implode(' AND ', $this->where);
			}
		}
		
		return implode(" ", $query);
	}
	
	private function _process_value($value)
	{
		// PHP bullshit thinks "0" is in the god damn array when its clearly NOT
		// so lets make sure the value is NOT a numeric value.
		// PHP really pisses me off sometimes.
		if (!is_numeric($value) and in_array($value, array("NOW()", "GMTTIME()"))) {
			return "'" . Time::gmt() . "'";
		} else {
			return "'" . Avalon_MySQLi::get_instance()->real_escape_string($value) . "'";
		}
	}
	
	public function __toString()
	{
		return $this->_assemble();
	}
}