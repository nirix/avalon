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
    foreach ($array as $key => $value) {
        // Check if we want to remove it...
        if (!is_numeric($key) and in_array($key, $keys)) {
            unset($array[$key]);
            continue;
        }

        // Filter the value if it's an array also
        $array[$key] = is_array($value) ? array_remove_keys($value, $keys) : $value;
    }

    return $array;
}

/**
 * Merges two arrays recursively.
 * Unlike the standard array_merge_recursive which converts values with duplicate keys to arrays
 * this one overwrites them.
 *
 * @param array $first
 * @param array $second
 *
 * @return array
 */
function array_merge_recursive2(&$first, &$second)
{
    $merged = $first;

    foreach ($second as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive2($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

/**
 * Converts the given data to an array.
 *
 * Recursively converts objects and nested arrays to plain arrays.
 * Objects with a __toArray() method will use that for conversion.
 *
 * @param mixed $data Data to convert
 *
 * @return mixed Array if data is object/array, otherwise returns data unchanged
 */
function to_array($data)
{
    // Handle objects with custom __toArray() method
    if (is_object($data) && method_exists($data, '__toArray')) {
        return $data->__toArray();
    } elseif (is_object($data) && method_exists($data, 'toArray')) {
        return $data->toArray();
    }

    // Handle regular objects - convert to array of properties
    if (is_object($data)) {
        $array = [];

        // Get all instance properties (public, protected, private)
        foreach (get_object_vars($data) as $property => $value) {
            $array[$property] = to_array($value);
        }

        return $array;
    }

    // Handle arrays - recursively convert nested values
    if (is_array($data)) {
        $result = [];

        foreach ($data as $key => $value) {
            $result[$key] = to_array($value);
        }

        return $result;
    }

    // Return scalar values unchanged
    return $data;
}
