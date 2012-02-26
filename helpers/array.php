<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Removes the specified keys from the array.
 *
 * @param array $array
 * @param array $keys Keys to remove
 *
 * @return array
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Helpers
 */
function array_remove_keys($array, $keys)
{
	// Loop over the array
	foreach ($array as $key => $value)
	{
		// Check if we want to remove it...
		if (!is_numeric($key) and in_array($key, $keys))
		{
			unset($array[$key]);
			continue;
		}

		// Filter the value if it's an array also
		$array[$key] = is_array($value) ? array_remove_keys($value, $keys) : $value;
	}

	return $array;
}