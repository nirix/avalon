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
 * The base controller class
 * @package Avalon
 */
class Controller
{
	public $db;
	public $_render = array('action' => true, 'layout' => 'default', 'view' => null);
	public $_before = array();
	
	public function __construct()
	{
		// Get the database for easy access
		if (Database::initiated()) {
			$this->db = Database::driver();
		}
		
		// Allow the views to access the app,
		// even though its not good practice...
		View::set('app', $this);
	}
	
	public function __shutdown()
	{
		if (!$this->_render['view']) {
			return;
		}
		
		// Render the view, get the content and clear the output
		View::render($this->_render['view']);
		$output = Output::body();
		Output::clear();
		
		// Set the X-Powered-By header and render the layout with the content
		header("X-Powered-By: Avalon/" . Avalon::version());
		View::render("layouts/{$this->_render['layout']}", array('output' => $output));
		echo Output::body();
	}
}