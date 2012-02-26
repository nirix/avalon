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
 * PDO Database wrapper statement class
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class PDO_Statement
{
	private $connection_name;
	private $statement;
	private $_model;
	
	/**
	 * PDO Statement constructor.
	 *
	 * @param $statement
	 *
	 * @return object
	 */
	public function __construct($statement, $connection_name = 'main')
	{
		$this->statement = $statement;
		$this->connection_name = $connection_name;
		return $this;
	}
	
	/**
	 * Sets the model for the rows to use.
	 *
	 * @param string $model
	 *
	 * @return object
	 */
	public function _model($model)
	{
		$this->_model = $model;
		return $this;
	}
	
	/**
	 * Fetches all the rows.
	 *
	 * @return array
	 */
	public function fetch_all()
	{
		$result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
		$rows = array();
		
		if ($this->_model !== null)
		{
			foreach ($result as $row)
			{
				$model = $this->_model;
				$rows[] = new $model($row, false);
			}
		}
		else
		{
			foreach ($result as $row)
			{
				$rows[] = $row;
			}
		}
		
		return $rows;
	}
	
	/**
	 * Fetches the next row from a result set.
	 *
	 * @param integer $style Fetch style
	 * @param integer $orientation Cursor orientation
	 * @param integer $offset Cursor offset
	 *
	 * @return object
	 */
	public function fetch($style = PDO::FETCH_ASSOC, $orientation = PDO::FETCH_ORI_NEXT, $offset = 0)
	{
		$result = $this->statement->fetch($style, $orientation, $offset);
		
		if ($this->_model !== null)
		{
			$model = $this->_model;
			return new $model($result, false);
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * Binds a parameter to the specified variable name.
	 *
	 * @param mixed $param Parameter
	 * @param mixed &$value Variable
	 * @param integer $type Data type
	 * @param integer $length Length
	 * @param mixed $options Driver options
	 *
	 * @return object
	 */
	public function bind_param($param, &$value, $type = PDO::PARAM_STR, $length = 0, $options = array())
	{
		$this->statement->bindParam($param, $value, $type, $length, $options);
		return $this;
	}
	
	/**
	 * Binds a value to a parameter.
	 *
	 * @param mixed $param Parameter
	 * @param mixed $value Value
	 * @param integer $type Data type
	 *
	 * @return object
	 */
	public function bind_value($param, $value, $type = PDO::PARAM_STR)
	{
		$this->statement->bindValue($param, $value, $type);
		return $this;
	}
	
	/**
	 * Executes a prepared statement.
	 *
	 * @return object
	 */
	public function exec()
	{
		$result = $this->statement->execute();
		
		if ($result)
		{
			return $this;
		}
		else
		{
			Database::connection($this->connection_name)->halt($this->statement->errorInfo());
		}
	}
	
	/**
	 * Returns the number of rows affected by the last SQL statement.
	 *
	 * @return integer
	 */
	public function row_count()
	{
		return $this->statement->rowCount();
	}
}