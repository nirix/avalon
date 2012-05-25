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
 * Form Helper
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Helpers
 */
class Form
{
	/**
	 * Creates a text input field.
	 *
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function text($name, $attributes = array())
	{
		return self::input('text', $name, $attributes);
	}
	
	/**
	 * Creates a password input field.
	 *
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function password($name, $attributes = array())
	{
		return self::input('password', $name, $attributes);
	}
	
	/**
	 * Creates a hidden field.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return string
	 */
	public static function hidden($name, $value)
	{
		return self::input('hidden', $name, array('value' => $value));
	}
	
	/**
	 * Creates a form submit button.
	 *
	 * @param string $text
	 * @param string $name
	 * @param string $attributes
	 *
	 * @return string
	 */
	public static function submit($text, $name = 'submit', $attributes = array())
	{
		return self::input('submit', $name, array_merge(array('value' => $text), $attributes));
	}
	
	/**
	 * Creates a textarea field.
	 *
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function textarea($name, $attributes = array())
	{
		return self::input('textarea', $name, $attributes);
	}
	
	/**
	 * Creates a checkbox field.
	 *
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function checkbox($name, $value, $attributes = array())
	{
		$attributes['value'] = $value;
		return self::input('checkbox', $name, $attributes);
	}
	
	/**
	 * Creates a select field.
	 *
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function select($name, $options, $attributes = array())
	{
		// Extract the value
		$value = isset($attributes['value']) ? $attributes['value'] : null;
		unset($attributes['value']);
		
		$attributes['name'] = $name;

		// Opening tag
		$select = "<select " . HTML::build_attributes($attributes) . ">";
		
		// Options
		foreach ($options as $option)
		{
			$select .= '<option value="' . $option['value'] . '"' . ($value == $option['value'] ? ' selected="selected"' :'') . '>' . $option['label'] . '</option>';
		}
		
		// Closing tags
		$select .= '</select>';
		
		return $select;
	}
	
	/**
	 * Creates a form field.
	 *
	 * @param string $type
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function input($type, $name, $attributes)
	{
		// Check if the value is set in the
		// attributes array
		if (isset($attributes['value']))
		{
			$value = $attributes['value'];
		}
		// Check if its in the _POST array
		elseif (isset($_POST[$name]))
		{
			$value = $_POST[$name];
		}
		// It's nowhere...
		else
		{
			$value = '';
		}
		
		// Merge default attributes with
		// the specified attributes.
		$attributes = array_merge(array('type' => $type, 'name' => $name), $attributes);

		// Textareas
		if ($type == 'textarea')
		{
			return "<textarea " . HTML::build_attributes($attributes) . ">{$value}</textarea>";
		}
		// Everything else
		else
		{
			// Don't pass the checked attribute if its false.
			if (isset($attributes['checked']) and !$attributes['checked'])
			{
				unset($attributes['checked']);
			}
			return "<input " . HTML::build_attributes($attributes) . ">";
		}
	}
}