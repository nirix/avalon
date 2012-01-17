<?php
/**
 * Avalon
 * Copyright (C) 2011 Jack Polgar
 * 
 * @license http://opensource.org/licenses/BSD-3-Clause BSD License
 */

/**
 * Time Helper
 * @package Avalon
 * @subpackage Helpers
 */
class Time
{
	public function date($format = "Y-m-d H:i:s", $time = null)
	{
		$time = ($time !== null ? $time : static::time());
		
		if (!is_numeric($time)) {
			$time = static::to_unix($time);
		}
		
		return date($format, $time);
	}
	
	public static function gmt()
	{
		return date("Y-m-d H:i:s", time() - date("Z", time()));
	}

	public static function gmt_to_local($datetime)
	{
		$stamp = strtotime($datetime);
		return date("Y-m-d H:i:s", $stamp + date("Z", $stamp));
	}
	
	/**
	 * Converts a MySQL datetime timestamp into a unix timestamp.
	 * @param datetime $original
	 * @return mixed
	 */
	public static function to_unix($original)
	{
		//return strtotime($original);
		// YYYY-MM-DD HH:MM:SS
		if (preg_match("#(?P<year>\d+)-(?P<month>\d+)-(?P<day>\d+) (?P<hour>\d+):(?P<minute>\d+):(?P<second>\d+)#siU", $original, $match)) {
			return mktime($match['hour'], $match['minute'], $match['second'], $match['month'], $match['day'], $match['year']);
		}
		// YYYY-MM-DD
		elseif (preg_match("#(?P<year>\d+)-(?P<month>\d+)-(?P<day>\d+)#siU", $original, $match)) {
			return mktime(0, 0, 0, $match['month'], $match['day'], $match['year']);
		}
		// Fail
		else {
			return strtotime($original);
		}
	}
	
	public static function ago_in_words($original, $detailed = true)
	{
		// Check what kind of format we're dealing with, timestamp or datetime
		// and convert it to a timestamp if it is in datetime form.
		if (!is_numeric($original)) {
			$original = static::to_unix($original);
		}
		
		$now = time(); // Get the time right now...

		// Time chunks...
		$chunks = array(
			array(60 * 60 * 24 * 365, 'year', 'years'),
			array(60 * 60 * 24 * 30, 'month', 'months'),
			array(60 * 60 * 24 * 7, 'week', 'weeks'),
			array(60 * 60 * 24, 'day', 'days'),
			array(60 * 60, 'hour', 'hours'),
			array(60, 'minute', 'minutes'),
			array(1, 'second', 'seconds'),
		);

		// Get the difference
		$difference = ($now - $original);

		// Loop around, get the time from
		for ($i = 0, $c = count($chunks); $i < $c; $i++) {
			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];
			$names = $chunks[$i][2];
			if(0 != $count = floor($difference / $seconds)) break;
		}

		// Format the time from
		$from = $count . " " . (1 == $count ? $name : $names);

		// Get the detailed time from if the detaile variable is true
		if ($detailed && $i + 1 < $c) {
			$seconds2 = $chunks[$i + 1][0];
			$name2 = $chunks[$i + 1][1];
			$names2 = $chunks[$i + 1][2];
			if (0 != $count2 = floor(($difference - $seconds * $count) / $seconds2)) {
				$from = $from . " and " . $count2 . " " . (1 == $count2 ? $name2 : $names2);
			}
		}

		// Return the time from
		return $from;
	}
}