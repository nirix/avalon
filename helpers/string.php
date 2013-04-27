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
 * Shortens a string to the set length
 * and appends '...' to the end.
 *
 * @param string  $string
 * @param integer $length
 * @param string  $append
 *
 * @return string
 */
function strshorten($string, $length = 20, $append = '...')
{
    // Check if it's longer than the length
    if (isset($string[$length-1])) {
        return trim(mb_substr($string, 0, $length)) . '...';
    } else {
        return $string;
    }
}
