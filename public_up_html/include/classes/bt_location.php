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
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_security.php');

class bt_location {
	const COUNTRY_KEY = 'bt_location:::countries';
	const TIMEZONE_KEY = 'bt_location:::timezones';

	const TTL_TIME = 86400;

	private static $timezones = array();
	private static $countries = array();
	private static $tzs = false;
	private static $cns = false;

	public static function country_list($cc = '') {
		if (!self::country_by_cc($cc))
			$cc = 'O1';

		$countries = self::countries_by_cc($cc);

		$clist = self::countries();
		foreach ($clist as $cid => $country)
			$countries[$cid] = $country;

		return $countries;
	}

	public static function timezone_list($cc = '') {
		$tz = array();

		if ($cc)				
			$tz = @DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $cc);

		if (!$tz || empty($tz)) {
			if ($cc === 'EU')
				$tz = DateTimeZone::listIdentifiers(DateTimeZone::EUROPE);
			else
				$tz = array();
		}

		$tz[] = 'UTC';
		$timezones = array_flip($tz);

		$tzs = self::timezones();

		foreach ($tzs as $tz => $name)
			$timezones[$tz] = $name;

		return $timezones;
	}

	public static function countries() {
		self::cache_countries();
		return self::$countries['by_id'];
	}

	public static function country_by_id($id) {
		self::cache_countries();
		$cid = 0 + $id;

		if (!isset(self::$countries['by_id'][$cid]))
			return false;

		$country = self::$countries['by_id'][$cid];
		return $country;
	}

	public static function country_by_cc($cc) {
		self::cache_countries();
		$c2 = (string)$cc;

		if (!isset(self::$countries['by_cc'][$c2]))
			return false;

		$cid = self::$countries['by_cc'][$c2];
		$country = self::$countries['by_id'][$cid];
		unset($country['cc']);
		$country['id'] = $cid;

		return $country;
	}

	public static function countries_by_cc($cc) {
		self::cache_countries();
		$c2 = (string)$cc;

		if (!isset(self::$countries['from_ccs'][$c2]))
			return false;

		$countries = array();
		foreach (self::$countries['from_ccs'][$c2] as $cid) {
			$countries[$cid] = self::$countries['by_id'][$cid];
			unset($countries[$cid]['cc']);
		}

		return $countries;
	}

	public static function timezones() {
		self::cache_timezones();
		return self::$timezones;
	}

	private static function cache_timezones() {
		if (self::$tzs)
			return;

		bt_memcache::connect();

		$timezones = bt_memcache::get(self::TIMEZONE_KEY);
		if ($timezones === bt_memcache::NO_RESULT) {
			$timezones = array();
			$tzs = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
			foreach ($tzs as $tz)
				$timezones[$tz] = strtr($tz, '_', ' ');

			bt_memcache::add(self::TIMEZONE_KEY, $timezones, self::TTL_TIME);
		}

		self::$timezones = $timezones;
		self::$tzs = true;
	}

	private static function cache_countries() {
		if (self::$cns)
			return;

		bt_memcache::connect();
		$countries = bt_memcache::get(self::COUNTRY_KEY);
		if ($countries === bt_memcache::NO_RESULT) {
			$countries = array(
				'by_id'		=> array(),
				'by_cc'		=> array(),
				'from_ccs'	=> array(),
			);
			$cc_ccc = array();

			bt_sql::connect();
			$ct_r = bt_sql::query('SELECT id, cc, ccc, name, flagpic FROM countries ORDER BY name ASC') or bt_sql::err(__FILE__,__LINE__);

			while ($ct_a = $ct_r->fetch_assoc()) {
				$id = 0 + $ct_a['id'];
				$cc = $ct_a['cc'];
				$ccc = $ct_a['ccc'];
				$name = bt_security::html_safe($ct_a['name'], false, true, true);
				$flagpic = bt_security::html_safe($ct_a['flagpic'], false, true, true);

                $countries['by_id'][$id] = array(
					'cc'		=> $cc,
					'ccc'		=> $ccc,
					'name'		=> $name,
					'flagpic'	=> $flagpic,
				);

				if ($cc) {
					$countries['by_cc'][$cc] = $id;
					$cc_ccc[$ccc] = $cc;
				}
			}
			$ct_r->free();

			foreach ($countries['by_id'] as $id => $country) {
				if (!$country['cc'])
					$countries['by_id'][$id]['cc'] = $cc_ccc[$country['ccc']];
			}

			foreach ($countries['by_id'] as $id => $country) {
				if (!isset($countries['from_ccs'][$country['cc']]))
					$countries['from_ccs'][$country['cc']] = array();

				$countries['from_ccs'][$country['cc']][] = $id;
			}

			bt_memcache::add(self::COUNTRY_KEY, $countries, self::TTL_TIME);
		}

		self::$countries = $countries;		
		self::$cns = true;
	}
}
?>
