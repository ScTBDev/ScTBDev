<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2010 ScTBDev.ca
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

bt_time::init();
class bt_time {
	const TIME_FORMAT = 'F d, Y H:i:s T';
	const DATE_TIME = 'Y-m-d H:i:s';

	public static $time_zones = array(
		'-12'	=> '-12:00',
		'-11'	=> '-11:00',
		'-10'	=> '-10:00',
		'-9.5'	=> '-9:30',
		'-9'	=> '-9:00',
		'-8'	=> '-8:00 (PST)',
		'-7'	=> '-7:00 (MST)',
		'-6'	=> '-6:00 (CST)',
		'-5'	=> '-5:00 (EST)',
		'-4.5'	=> '-4:30',
		'-4'	=> '-4:00 (AST)',
		'-3.5'	=> '-3:30 (NST)',
		'-3'	=> '-3:00',
		'-2'	=> '-2:00',
		'-1'	=> '-1:00',
		'0'		=> '(UTC)',
		'1'		=> '+1:00 (CET)',
		'2'		=> '+2:00 (EET)',
		'3'		=> '+3:00 (MSK)',
		'3.5'	=> '+3:30',
		'4'		=> '+4:00',
		'4.5'	=> '+4:30',
		'5'		=> '+5:00',
		'5.5'	=> '+5:30',
		'5.75'	=> '+5:45',
		'6'		=> '+6:00',
		'6.5'	=> '+6:30',
		'7'		=> '+7:00',
		'8'		=> '+8:00 (AWST)',
		'8.75'	=> '+8:45',
		'9'		=> '+9:00 (JST/KST)',
		'9.5'	=> '+9:30 (ACST)',
		'10'	=> '+10:00 (AEST)',
		'10.5'	=> '+10:30',
		'11'	=> '+11:00',
		'11.5'	=> '+11:30',
		'12'	=> '+12:00',
		'12.75'	=> '+12:45',
		'13'	=> '+13:00',
		'14'	=> '+14:00',
	);

	public static $dst_offsets = array(
		-120	=> '-2:00',
		-90		=> '-1:30',
		-60		=> '-1:00',
		-30		=> '-0:30',
		30		=> '+0:30',
		60		=> '+1:00',
		90		=> '+1:30',
		120		=> '+2:00',
	);

	public static function init() {
		date_default_timezone_set('UTC');
	}

	public static function format($timestamp = NULL) {
		if ($timestamp === NULL)
			$timestamp = time();

		$timestamp += ((bt_user::$current['timezone'] * 3600) + (bt_user::$current['settings']['dst'] ? (bt_user::$current['dst_offset'] * 60) : 0));
		return gmdate(self::DATE_TIME, $timestamp);
	}

	public static function gmdate_diff($d1, $d2, $totals = false){
		$date_diff = array();
		$d1 = (int)$d1;
		$d2 = (int)$d2;

		$lower = min($d1, $d2);
		$upper = max($d1, $d2);
		$diff_secs = $upper - $lower;

		$date1 = new DateTime;
		$date2 = new DateTime;

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
}
?>
