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

/**
 * Converts the given data to an array.
 *
 * @param mixed $data
 *
 * @return array
 */
function to_array($data)
{
	/*
	// Check if it's a boolean
	if (is_bool($data)
	or is_double($data)   // or a double
	or is_float($data)    // or a float
	or is_int($data)      // or an integer
	or is_long($data)     // or a long integer
	or is_null($data)     // or if its null
	or is_numeric($data)  // or some kind of numeric thing
	or is_string($data))  // or even a regular string
	{
		// If so, return it
		return $data;
	}
	// Is it an array itself?
	// lets try to convert its values...
	elseif (in_array($data))
	{
		// Loop over the data
		foreach ($data as $key => $val)
		{
			// and turn it into an array
			$data[$key] = to_array($val);
		}
		return $data;
	}
	*/
	// Is it an object with a __toArray() method?
	if (is_object($data) and method_exists($data, '__toArray'))
	{
		// Hell yeah, we don't need to do anything.
		return $data->__toArray();
	}
	// Just an object, take its variables!
	elseif (is_object($data))
	{
		// Create an array
		$array = array();

		// Loop over the classes variables
		foreach (get_class_vars($data) as $var => $val)
		{
			// And steal them! MY PRECIOUS!
			$array[$var] = $val;
		}

		// And return the array.
		return $array;
	}

	// I have no idea what it is...
	// just return it...
	return $data;
}