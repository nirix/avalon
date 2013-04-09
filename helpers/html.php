<?php
/*!
 * Avalon
 * Copyright (C) 2011-2013 Jack Polgar
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
 * Shortcut to the HTML::link method.
 *
 * @param string $url The URL.
 * @param string $label The label.
 * @param array $attributes Options for the URL code (class, title, etc).
 *
 * @return string
 */
function link_to($label, $uri, array $attributes = array())
{
    return HTML::link($label, $uri, $attributes);
}

/**
 * HTML Helper
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Helpers
 */
class HTML
{
    /**
     * Returns the code to include a CSS file.
     *
     * @param string $file The path to the CSS file.
     *
     * @return string
     */
    public static function css_link($path, $media = 'screen')
    {
        return '<link rel="stylesheet" href="'.str_replace('&', '&amp;', $path).'" media="'.$media.'" />'.PHP_EOL;
    }

    /**
     * Returns the code to include a JavaScript file.
     *
     * @param string $file The path to the JavaScript file.
     *
     * @return string
     */
    public static function js_inc($path)
    {
        return '<script src="'.str_replace('&', '&amp;', $path).'" type="text/javascript"></script>'.PHP_EOL;
    }

    /**
     * Returns the code for a link.
     *
     * @param string $url The URL.
     * @param string $label The label.
     * @param array $options Options for the URL code (class, title, etc).
     *
     * @return string
     */
    public static function link($label, $url = null, array $attributes = array())
    {
        if ($label === null) {
            $label = $url;
        }

        $url = Request::base(ltrim($url, '/'));
        $attributes['href'] = str_replace('&', '&amp;', $url);
        $options = static::build_attributes($attributes);

        return "<a {$options}>{$label}</a>";
    }

    /**
     * Builds the attributes for HTML elements.
     *
     * @param array $attributes An array of attributes and their values.
     *
     * @return string
     */
    public static function build_attributes($attributes)
    {
        $options = array();
        foreach ($attributes as $attr => $val) {
            if (in_array($attr, array('id', 'checked', 'disabled')) and $val === false) {
                continue;
            }
            $options[] = "{$attr}=\"{$val}\"";
        }
        return implode(' ', $options);
    }
}
