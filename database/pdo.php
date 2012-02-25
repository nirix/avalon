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
	
	/**
	 * PDO wrapper constructor.
	 *
	 * @param array $config Database config array
	 */
	public function __construct($config)
	{
		try
		{
			$this->prefix = isset($config['prefix']) ? $config['prefix'] : '';
			
			// Check if a DSN is already specified
			if (isset($config['dsn']))
			{
				$dsn = $config['dsn'];
			}
			// SQLite
			elseif ($config['type'] == 'sqlite')
			{
				$dsn = strtolower("sqlite:" . $config['path']);
			}
			// Something else...
			else
			{
				$dsn = strtolower($config['type']) . ':dbname=' . $config['database'] . ';host=' . $config['host'];
			}

			$this->connection = new PDO(
				$dsn,
				isset($config['username']) ? $config['username'] : null,
				isset($config['password']) ? $config['password'] : null,
				isset($config['options']) ? $config['options'] : array()
			);

			unset($dsn);
		}
		catch (PDOException $e)
		{
			$this->halt($e->getMessage());
		}
	}
	
	/**
	 * Quotes a string for use in a query.
	 *
	 * @param string $string
	 * @param int $type Paramater type
	 */
	public function quote($string, $type = PDO::PARAM_STR)
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
		return new PDO_Statement($this, $this->connection->prepare($query, $options));
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
		return new PDO_Query($this, "SELECT", $cols);
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
		return new PDO_Query($this, "UPDATE", $table);
	}
	
	/**
	 * Returns a delete query builder object.
	 *
	 * @return object
	 */
	public function delete()
	{
		return new PDO_Query($this, "DELETE");
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
		return new PDO_Query($this, "INSERT INTO", $data);
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
