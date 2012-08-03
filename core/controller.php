<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * This file is part of Avalon.
 *
 * Avalon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Avalon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Avalon. If not, see <http://www.gnu.org/licenses/>.
 */

namespace avalon\core;

use avalon\core\Kernel;
use avalon\Database;
use avalon\http\Router;
use avalon\output\View;
use avalon\output\Body;

/**
 * Base controller class.
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Core
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
			$this->db = Database::connection();
		}

		// Set the view path
		$this->_render['view'] = strtolower((Router::$namespace !== null ? Router::namespace_path() : '') . Router::$controller . '/' . Router::$method);

		// Check if the route has an extension
		if (Router::$extension !== null)
		{
			$this->_render['view'] = $this->_render['view'] . Router::$extension;
			
			// Lets make sure the view for the extension exists
			if (View::exists($this->_render['view']) === false)
			{
				$this->show_404();
			}
			else
			{
				$this->_render['layout'] = 'plain';
			}
		}
		
		// Allow the views to access the app,
		// even though its not good practice...
		View::set('app', $this);
	}

	/**
	 * Used to display the 404 page.
	 */
	public function show_404()
	{
		// Send the request to the view and
		// change the view file to error/404.php
		// and disable the calling of the routed
		// controller method.
		View::set('request', Request::url());
		$this->_render['view'] = 'error/404';
		$this->_render['action'] = false;
	}

	public function __shutdown()
	{
		if (!$this->_render['view']) {
			return;
		}
		
		// Render the view, get the content and clear the output
		View::render($this->_render['view']);
		$output = Body::body();
		Body::clear();
		
		// Set the X-Powered-By header and render the layout with the content
		header("X-Powered-By: Avalon/" . Kernel::version());
		View::render("layouts/{$this->_render['layout']}", array('output' => $output));
		echo Body::body();
	}
}