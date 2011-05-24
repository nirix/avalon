<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * The base controller class
 * @package Avalon
 */
class Controller
{
	public $db;
	
	public function __construct()
	{
		$this->db = Avalon::db();
		
		View::set('app', $this);
	}
}