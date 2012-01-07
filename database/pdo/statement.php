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
	private $statement;
	private $_model;
	
	public function __construct($statement)
	{
		$this->statement = $statement;
		return $this;
	}
	
	public function _model($model)
	{
		$this->_model = $model;
		return $this;
	}
	
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
	
	public function bind_param($param, &$value, $type = PDO::PARAM_STR, $length = 0, $options = array())
	{
		$this->statement->bindParam($param, $value, $type, $length, $options);
		return $this;
	}
	
	public function bind_value($param, $value, $type = PDO::PARAM_STR)
	{
		$this->statement->bindValue($param, $value, $type);
		return $this;
	}
	
	public function exec()
	{
		$result = $this->statement->execute();
		
		if ($result)
		{
			return $this;
		}
		else
		{
			Database::driver()->halt($this->statement->errorInfo());
		}
	}
	
	public function row_count()
	{
		return $this->statement->rowCount();
	}
}