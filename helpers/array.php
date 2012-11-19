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
        if (is_array($value) && isset($merged [$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive2($merged [$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
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
    // Is it an object with a __toArray() method?
    if (is_object($data) and method_exists($data, '__toArray')) {
        // Hell yeah, we don't need to do anything.
        return $data->__toArray();
    }
    // Just an object, take its variables!
    elseif (is_object($data)) {
        // Create an array
        $array = array();

        // Loop over the classes variables
        foreach (get_class_vars($data) as $var => $val) {
            // And steal them! MY PRECIOUS!
            $array[$var] = $val;
        }

        // And return the array.
        return $array;
    }
    // Array containing other things?
    elseif (is_array($data)) {
        foreach ($data as $k => $v) {
            $data[$k] = to_array($v);
        }
    }

    return $data;
}
