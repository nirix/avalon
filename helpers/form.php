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
     * Creates a label.
     *
     * @param string $text
     * @param string $for
     * @param array $attributes
     *
     * @return string
     */
    public static function label($text, $for = null, $attributes = array())
    {
        if ($for !== null) {
            $attributes['for'] = $for;
        }
        return "<label ". HTML::build_attributes($attributes) .">{$text}</label>";
    }

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
     * @param mixed $value
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
     * Creates a radio field.
     *
     * @param string $name
     * @param mixed $value
     * @param array $attributes
     *
     * @return string
     */
    public static function radio($name, $value, $attributes = array())
    {
        $attributes['value'] = $value;
        return self::input('radio', $name, $attributes);
    }

    /**
     * Creates a select field.
     *
     * @param string $name
     * @param array  $options
     * @param array  $attributes
     *
     * @return string
     */
    public static function select($name, $options, $attributes = array())
    {
        // Extract the value
        $value = isset($attributes['value']) ? $attributes['value'] : null;
        unset($attributes['value']);

        // Set the name
        $attributes['name'] = $name;

        // Set the id to the name if one
        // is not already set.
        if (!isset($attributes['id'])) {
            $attributes['id'] = $name;
        }

        // Opening tag
        $select = array();
        $select[] = "<select " . HTML::build_attributes($attributes) . ">";

        // Options
        foreach ($options as $index => $option) {
            if (!is_numeric($index)) {
                $select[] = '<optgroup label="' . $index . '">';
                foreach ($option as $opt) {
                    $select[] = static::select_option($opt, ($opt['value'] == $value));
                }
                $select[] = '</optgroup>';
            } else {
                $select[] = static::select_option($option, ($option['value'] == $value));
            }
        }

        // Closing tags
        $select[] = '</select>';

        return implode(PHP_EOL, $select);
    }

    /**
     * Creates a multi-select field.
     *
     * @param string $name
     * @param array  $options
     * @param array  $values
     * @param array  $attributes
     *
     * @return string
     */
    public static function multiselect($name, $options, $values, $attributes = array()) {
        // Set attributes
        $attributes = array_merge(array('class' => 'multiselect', 'name' => $name), $attributes);

        // Set ID if not already set
        if (!isset($attributes['id'])) {
            $attributes['id'] = $name;
        }

        // Opening tag
        $select = array();
        $select[] = "<select multiple " . HTML::build_attributes($attributes) . ">";

        // Options
        foreach ($options as $index => $option) {
            // Option group
            if (!is_numeric($index)) {
                $select[] = '<optgroup label="' . $index . '">';
                foreach ($option as $opt) {
                    $select[] = static::select_option($opt, in_array($opt['value'], $values));
                }
                $select[] = '</optgroup>';
            }
            // Regular option
            else {
                $select[] = static::select_option($option, in_array($option['value'], $values));
            }
        }

        // Closing tag
        $select[] = "</select>";

        return implode(PHP_EOL, $select);
    }

    /**
     * Return the HTML for a select option.
     *
     * @param array $option
     *
     * @return string
     */
    private static function select_option($option, $selected)
    {
        // Set value
        $attributes = array('value' => $option['value']);

        // Return option
        return "<option " . HTML::build_attributes($attributes) . ($selected ? ' selected' :'') . ">{$option['label']}</option>";
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
        // Set id attribute to be same as the name
        // if one has not been set
        if (!isset($attributes['id'])) {
            $attributes['id'] =  $name;
        }

        // Check if the value is set in the
        // attributes array
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
        }
        // Check if its in the _POST array
        elseif (isset($_POST[$name])) {
            $value = htmlspecialchars($_POST[$name]);
        }
        // It's nowhere...
        else {
            $value = '';
        }

        // Make the value "safe"
        $attributes['value'] = $value;

        // Add selected or checked attribute?
        foreach (array('selected', 'checked') as $attr) {
            if (isset($attributes[$attr]) and !$attributes[$attr]) {
                unset($attributes[$attr]);
            } elseif (isset($attributes[$attr])) {
                $attributes[$attr] = $attr;
            }
        }

        // Add name and type (if not textarea)
        $attributes['name'] = $name;
        if ($type != 'textarea') {
            $attributes['type'] = $type;
        }

        // Textareas
        if ($type == 'textarea') {
            return "<textarea " . HTML::build_attributes($attributes) . ">{$value}</textarea>";
        }
        // Everything else
        else {
            // Don't pass the checked attribute if its false.
            if (isset($attributes['checked']) and !$attributes['checked']) {
                unset($attributes['checked']);
            }
            return "<input " . HTML::build_attributes($attributes) . ">";
        }
    }
}
