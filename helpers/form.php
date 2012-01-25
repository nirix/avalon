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
	 * Creates a text input field.
	 *
	 * @param string $name
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function select($name, $values, $attributes = array())
	{
		$select = '<select name="' . $name . '"' . (isset($attributes['id']) ? ' id="' . $attributes['id'] . '"' :'') . '>';
		
		foreach ($values as $value) {
			$select .= '<option value="' . $value['value'] . '"' . (@$attributes['value'] == $value['value'] ? ' selected="selected"' :'') . '>' . $value['text'] . '</option>';
		}
		
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
		if (isset($attributes['value'])) {
			$value = $attributes['value'];
		} elseif(isset($_POST[$name])) {
			$value = $_POST[$name];
		} else {
			$value = '';
		}
		
		if ($type == 'text'
		or  $type == 'password'
		or  $type == 'hidden') {
			return '<input type="' . $type . '" name="' . $name . '" value="' . $value . '"' . (isset($attributes['id']) ? ' id="' . $attributes['id'] . '"' :'') . ' class="' . $type . '">';
		} elseif($type == 'textarea') {
			return '<textarea name="' . $name . '"'.(isset($attributes['id']) ? ' id="'.$attributes['id'] . '"' :'') . (isset($attributes['class']) ? ' class="' . $attributes['class'] . '"' :'') . (isset($attributes['cols']) ? ' cols="' . $attributes['cols'] . '"' :'') . (isset($attributes['rows']) ? ' rows="' . $attributes['rows'] . '"' :'') . '>' . $value . '</textarea>';
		} elseif($type == 'submit') {
			return '<input type="' . $type . '" value="' . $value . '" class="' . $type . '" />';
		} elseif($type == 'checkbox') {
			return '<input type="' . $type . '" name="' . $name . '" value="' . $value . '" class="' . $type . '" ' . (isset($attributes['checked']) && $attributes['checked'] ? 'checked ' :'') . (isset($attributes['id']) ? ' id="' . $attributes['id'] . '"' :'') . '>';
		}
	}
}