<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2011 ScTBDev.ca
 *
 *	This file is part of ScTBDev.
 *
 *	ScTBDev is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	ScTBDev is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_location.php');

bt_time::init();
class bt_time {
	const TIME_FORMAT = 'F d, Y H:i:s T';
	const DATE_TIME = 'Y-m-d H:i:s';

	private static $utc_tz = NULL;
	private static $cur_tz = NULL;

	public static function init() {
		date_default_timezone_set('UTC');
		self::$utc_tz = new DateTimeZone('UTC');
	}

	public static function set_timezone() {
		if (self::$cur_tz)
			return;


		$timezone = bt_user::$current ? (self::valid_timezone(bt_user::$current['timezone']) ? bt_user::$current['timezone'] : 'UTC') : 'UTC';
		date_default_timezone_set($timezone);
		self::$cur_tz = new DateTimeZone($timezone);
	}

	public static function format($timestamp = NULL) {
		if (!self::$cur_tz)
			self::set_timezone();

		$date = new DateTime('now', self::$cur_tz);

		if ($timestamp !== NULL)
			$date->setTimestamp($timestamp);

		return $date->format(self::DATE_TIME);
	}

	public static function gmdate_diff($d1, $d2, $totals = false){
		$date_diff = array();
		$d1 = (int)$d1;
		$d2 = (int)$d2;

		$lower = min($d1, $d2);
		$upper = max($d1, $d2);
		$diff_secs = $upper - $lower;

		$date1 = new DateTime('now', self::$utc_tz);
		$date2 = new DateTime('now', self::$utc_tz);

		$date1->setTimestamp($d1);
		$date2->setTimestamp($d2);
		

		$diff = $date1->diff($date2, true);

		if ($diff->y)
			$date_diff['years']		= $diff->y;
		if ($diff->m)
			$date_diff['months']	= $diff->m;
		if ($diff->d)
			$date_diff['days']		= $diff->d;
		if ($diff->h)
			$date_diff['hours']		= $diff->h;
		if ($diff->i)
			$date_diff['minutes']	= $diff->i;
		if ($diff->s)
			$date_diff['seconds']	= $diff->s;


		if ($totals) {
			$date_diff['months_total']	= ($diff->y * 12) + $diff->m;
			$date_diff['days_total']	= (int)floor($diff_secs / 86400);
			$date_diff['hours_total']	= (int)floor($diff_secs / 3600);
			$date_diff['minutes_total']	= (int)floor($diff_secs / 60);
			$date_diff['seconds_total']	= $diff_secs;
		}

		return $date_diff;
	}

	public static function format_elapsed_time($d1, $d2, $short = false) {
		$date_diff = self::gmdate_diff($d1, $d2, false);
		$diff = array();

		if (isset($date_diff['years']))
			$diff[] = $date_diff['years'].' '.($short ? 'y' : 'year'.(($date_diff['years'] != 1) ? 's' : ''));
		if (isset($date_diff['months']))
			$diff[] = $date_diff['months'].' '.($short ? 'M' : 'month'.(($date_diff['months'] != 1) ? 's' : ''));
		if (isset($date_diff['days']))
			$diff[] = $date_diff['days'].' '.($short ? 'd' : 'day'.(($date_diff['days'] != 1) ? 's' : ''));
		if (isset($date_diff['hours']))
			$diff[] = $date_diff['hours'].' '.($short ? 'h' : 'hour'.(($date_diff['hours'] != 1) ? 's' : ''));
		if (isset($date_diff['minutes']))
			$diff[] = $date_diff['minutes'].' '.($short ? 'm' : 'min'.(($date_diff['minutes'] != 1) ? 's' : ''));
		if (isset($date_diff['seconds']))
			$diff[] = $date_diff['seconds'].' '.($short ? 's' : 'sec'.(($date_diff['seconds'] != 1) ? 's' : ''));

		if ($short)
			return implode(' ', $diff);
		else
			return implode(', ', $diff);
	}

	public static function ago_time($time) {
		$date_diff = self::gmdate_diff($time, time(), false);

		if (isset($date_diff['years']))
			return $date_diff['years'].' year'.($date_diff['years'] != 1 ? 's' : '');
		elseif (isset($date_diff['months']))
			return $date_diff['months'].' month'.($date_diff['months'] != 1 ? 's' : '');
		elseif (isset($date_diff['days']))
			return $date_diff['days'].' day'.($date_diff['days'] != 1 ? 's' : '');
		elseif (isset($date_diff['hours']))
			return $date_diff['hours'].' hour'.($date_diff['hours'] != 1 ? 's' : '');
		elseif (isset($date_diff['minutes']))
			return $date_diff['minutes'].' min'.($date_diff['minutes'] != 1 ? 's' : '');
		else
			return '&lt; 1 min';
	}

	public static function valid_timezone($name) {
		$timezones = bt_location::timezones();
		return isset($timezones[$name]);
	}
}
?>
