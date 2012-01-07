<?php
/*
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * @author Jack P. <nrx@nirix.net>
 * @copyright Jack P.
 * @license New BSD License
 */

require __DIR__ . '/pdo/query.php';
require __DIR__ . '/pdo/statement.php';

/**
 * PDO Database wrapper
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class DB_PDO extends Driver
{
	private $connection;
	private $query_count = 0;
	protected $last_query;
	
	public $prefix;
	
	public function __construct($config)
	{
		try
		{
			$this->prefix = isset($config['prefix']) ? $config['prefix'] : '';
			
			$dsn = strtolower($config['type']) . ':dbname=' . $config['database'] . ';host=' . $config['host'];
			$this->connection = new PDO($dsn, $config['username'], $config['password'], isset($config['options']) ? $config['options'] : array());
			unset($dsn);
		}
		catch (PDOException $e)
		{
			$this->halt($e->getMessage());
		}
	}
	
	public function quote($string, $type = PDO::PARAM_STR)
	{
		return $this->connection->quote($string, $type);
	}
	
	public function query($query)
	{
		$this->query_count++;
		$this->last_query = $query;
		
		$rows = $this->connection->query($query);
		return $rows;
	}
	
	public function prepare($query, array $options = array())
	{
		$this->last_query = $query;
		return new PDO_Statement($this->connection->prepare($query, $options));
	}
	
	public function select($cols = array('*'))
	{
		if (!is_array($cols)) {
			$cols = func_get_args();
		}
		return new PDO_Query("SELECT", $cols);
	}
	
	public function update($table)
	{
		return new PDO_Query('UPDATE', $table);
	}
	
	public function delete()
	{
		return new PDO_Query("DELETE");
	}

	public function insert(array $data)
	{
		return new PDO_Query("INSERT INTO", $data);
	}
	
	public function last_insert_id()
	{
		return $this->connection->lastInsertId();
	}
}
