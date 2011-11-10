<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

class MySQLi_Statement
{
	private $_model;
	private $_callback;
	
	public function __construct($result)
	{
		$this->result = $result;
	}
	
	public function _model($model)
	{
		$this->_model = $model;
		return $this;
	}
	
	public function _callback($callback)
	{
		$this->_callback = $callback;
		return $this;
	}

	public function fetchArray()
	{
		return mysqli_fetch_array($this->result);
	}

	public function fetchAssoc()
	{
		$row = mysqli_fetch_assoc($this->result);
		if ($this->_model !== null) {
			$model = $this->_model;
			return new $model($row);
		} else {
			return $row;
		}
	}

	public function fetchAll()
	{
		$rows = array();
		while($row = mysqli_fetch_assoc($this->result)) {
			if ($this->_model !== null) {
				$model = $this->_model;
				$rows[] = new $model($row);
			} else {
				$rows[] = $row;
			}
		}
		return $rows;
	}

	public function numRows()
	{
		return mysqli_num_rows($this->result);
	}
	
	public function _exec_callback($data)
	{
		if (is_array($this->_callback)) {
			return call_user_func($this->_callback, $data);
		} else if ($this->_callback !== null) {
			$method = $this->_callback;
			return $method($data);
		} else {
			return $data;
		}
	}
}