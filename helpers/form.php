<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
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
	public static function select($name, $values, $attributes = array())
	{
		// Extract the value
		$value = isset($attributes['value']) ? $attributes['value'] : null;
		unset($attributes['value']);
		
		// Opening tag
		$select = "<select " . HTML::build_attributes($attributes) . ">";
		
		// Options
		foreach ($values as $value)
		{
			$select .= '<option value="' . $value['value'] . '"' . ($value == $value['value'] ? ' selected="selected"' :'') . '>' . $value['label'] . '</option>';
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
		
		// Text, password and hidden fields
		if ($type == 'text'
		or $type == 'password'
		or $type == 'hidden')
		{
			$attributes['value'] = $value;
			return "<input " . HTML::build_attributes($attributes) . ">";
		}
		// Text area
		elseif ($type == 'textarea')
		{
			return '<textarea name="'.$name.'"'.(isset($args['id']) ? ' id="'.$args['id'].'"' :'').(isset($args['class']) ? ' class="'.$args['class'].'"' :'').(isset($args['cols']) ? ' cols="'.$args['cols'].'"' :'').(isset($args['rows']) ? ' rows="'.$args['rows'].'"' :'').'>'.$value.'</textarea>';
		}
		// Submit button
		elseif ($type == 'submit')
		{
			return '<input type="'.$type.'" value="'.$value.'" class="'.$type.'" />';
		}
		// Checkbox
		elseif ($type == 'checkbox')
		{
			return '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" class="'.$type.'" '.(isset($args['checked']) && $args['checked'] ? 'checked ' :'').(isset($args['id']) ? ' id="'.$args['id'].'"' :'').'/>';
		}
	}
}