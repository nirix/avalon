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
 * Database driver base.
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Driver
{
	/**
	 * Shortcut to the Error::halt method.
	 *
	 * @param string $error DB error message
	 */
	public function halt($error = 'Unknown error')
	{
		if (is_array($error) and isset($error[2]) and !empty($error[2]))
		{
			$error = $error[2];
		}
		else if (!is_array($error))
		{
			$error = $error;
		}
		else
		{
			$error = 'Unknown error. ' . implode('/', $error);
		}
		
		Error::halt("Database Error", $error . '<br />' . $this->last_query);
	}
}