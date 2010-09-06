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
require_once(CLASS_PATH.'bt_memcache.php');

bt_utf8::init();
class bt_utf8 {
	const TRANSKEY		= 'bt_utf8:::utf8_win_iso::translation_table';
	const NBSP			= "\xC2\xA0";
	const TRIM_CHARLIST	= "\x00..\x20\xC2\xA0";

	private static $trans_table = array();

	public static function init() {
		mb_internal_encoding('UTF-8');
		mb_language('uni');
		mb_regex_encoding('UTF-8');
		mb_detect_order(array('UTF-8', 'ISO-8859-1'));
		mb_substitute_character(0xFFFD);

		bt_memcache::connect();
		$trans = bt_memcache::get(self::TRANSKEY);
		if ($trans === bt_memcache::NO_RESULT) {
			$win		= "\x80".implode('', range("\x82", "\x8c"))."\x8e".implode('', range("\x91", "\x9c")).implode('', range("\x9e", "\xff"));
			$win_iso	= "\x81\x8d\x8f\x90\x9d";
			$iso		= implode('', range("\x80", "\xff"));
			$winlen		= strlen($win);
			$winisolen	= strlen($win_iso);
			$isolen		= strlen($iso);

			$trans = array(
				'iso_to_utf8'	=> array(),
				'win_to_utf8'	=> array(),
				'from_utf8'		=> array(),
			);

			for ($i = 0; $i < $isolen; $i++) {
				$utf8 = mb_convert_encoding($iso[$i], 'UTF-8', 'ISO-8859-1');
				$trans['iso_to_utf8'][$iso[$i]] = $utf8;
				$trans['from_utf8'][$utf8] = $iso[$i];
			}

			for ($i = 0; $i < $winlen; $i++) {
				$utf8 = mb_convert_encoding($win[$i], 'UTF-8', 'Windows-1252');
				$trans['win_to_utf8'][$win[$i]] = $utf8;
				$trans['from_utf8'][$utf8] = $win[$i];
			}

			for ($i = 0; $i < $winisolen; $i++) {
				$utf8 = mb_convert_encoding($win_iso[$i], 'UTF-8', 'ISO-8859-1');
				$trans['win_to_utf8'][$win_iso[$i]] = $utf8;
			}

			bt_memcache::add(self::TRANSKEY, $trans, 86400);
		}

		self::$trans_table = $trans;
	}

	public static function is_utf8($string) {
		return (bool)preg_match('#^.*$#Dsu', $string);
	}

	public static function bin2utf8($string, $win1252 = true) {
		return strtr($string, ($win1252 ? self::$trans_table['win_to_utf8'] : self::$trans_table['iso_to_utf8']));
	}

	public static function utf82bin($string) {
		return strtr($string, self::$trans_table['from_utf8']);
	}

	public static function to_utf8($data) {
		if (is_string($data)) {
			if (!self::is_utf8($data))
				$data = self::bin2utf8($data); // Windows-1252 - ISO-8859-1 hybrid, lossless

			return $data;
		}
		elseif (is_array($data)) {
			$newdata = array();
			foreach ($data as $key => $value) {
				$key = self::to_utf8($key);
				$newdata[$key] = self::to_utf8($value);
			}
			return $newdata;
		}
		else
			return $data;
	}

	public static function strlen($str) {
		return mb_strlen($str, 'UTF-8');
	}

	public static function substr($str, $start, $length = -1) {
		return mb_substr($str, $start, $length, 'UTF-8');
	}

	public static function strpos($haystack, $needle, $offset = 0) {
		return mb_strpos($haystack, $needle, $offset, 'UTF-8');
	}

	public static function stripos($haystack, $needle, $offset = 0) {
		return mb_stripos($haystack, $needle, $offset, 'UTF-8');
	}

	public static function strrpos($haystack, $needle, $offset = 0) {
		return mb_strrpos($haystack, $needle, $offset, 'UTF-8');
	}

	public static function strripos($haystack, $needle, $offset = 0) {
		return mb_strripos($haystack, $needle, $offset, 'UTF-8');
	}

	public static function substr_count($haystack, $needle) {
		return mb_substr_count($haystack, $needle, 'UTF-8');
	}

	public static function strtoupper($str) {
		return mb_strtoupper($str, 'UTF-8');
	}

	public static function strtolower($str) {
		return mb_strtolower($str, 'UTF-8');
	}

	public static function ltrim($str, $charlist = self::TRIM_CHARLIST) {
		if (!is_string($str) || !is_string($charlist))
			return false;

		$charlist = preg_quote($charlist, '#');
		$charlist = strtr($charlist, array('\\.\\.' => '-'));

		return preg_replace('#^['.$charlist.']+#Dsu', '', $str);
	}

	public static function rtrim($str, $charlist = self::TRIM_CHARLIST) {
		if (!is_string($str) || !is_string($charlist))
			return false;

		$charlist = preg_quote($charlist, '#');
		$charlist = strtr($charlist, array('\\.\\.' => '-'));

		return preg_replace('#['.$charlist.']+$#Dsu', '', $str);
	}

	public static function trim($str, $charlist = self::TRIM_CHARLIST) {
		return self::ltrim(self::rtrim($str, $charlist), $charlist);
	}
}
?>
