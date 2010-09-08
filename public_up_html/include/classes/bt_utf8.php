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
require_once(CLASS_PATH.'bt_string.php');

bt_utf8::init();
class bt_utf8 {
	const TRANSKEY		= 'bt_utf8:::utf8_win_iso::translation_table';
	const NBSP			= "\xC2\xA0";	// &nbsp; # Non-Breaking Space

	//	Trim Char List:		Control Codes + Space Characters
	//						0 - 20,			80 - A0, 			2000 - 200B,		202F,		2060,		3000,		FEFF
	const TRIM_CHARLIST	= "\x00..\x20\xC2\x80..\xC2\xA0\xE2\x80\x80..\xE2\x80\x8B\xE2\x80\xAF\xE2\x81\xA0\xE3\x80\x80\xEF\xBB\xBF";

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
		return (bool)preg_match('##Dsu', $string);
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

	public static function unicode_to_utf8($unicodepoint, $allow_invalid = false) {
		if (is_array($unicodepoint)) {
			$chars = '';
			foreach ($unicodepoint as $codepoint) {
				$char = self::unicode_to_utf8($codepoint, $allow_invalid);
				if ($char === false)
					return false;
				$chars .= $char;
			}
			return $chars;
		}
		if (!is_int($unicodepoint))
			return false;

		$unicodepoint = (int)$unicodepoint;
		$unicode = $unicodepoint & 0x7FFFFFFF;
		if ($unicode != $unicodepoint)
			return false; // Not within the 31 bit limit of UTF-8

		$char = '';
		if ($unicode < 0x80)
			$char = bt_string::$chr[$unicode];
		elseif ($unicode < 0x800) {
			$char = bt_string::$chr[(0xC0 | ($unicode >> 6))];
			$char .= bt_string::$chr[(0x80 | ($unicode & 0x3F))];
		}
		elseif ($unicode < 0x10000) {
			if (($unicode >= 0xD800 && $unicode <= 0xDFFF) && !$allow_invalid)
				return false; // Surrogate Pairs

			$char = bt_string::$chr[(0xE0 | ($unicode >> 12))];
			$char .= bt_string::$chr[(0x80 | (($unicode >> 6) & 0x3F))];
			$char .= bt_string::$chr[(0x80 | ($unicode & 0x3F))];
		}
		elseif ($unicode < 0x200000) {
			if ($unicode > 0x10FFFF && !$allow_invalid)
				return false; // Above max Unicode Code Points

			$char = bt_string::$chr[(0xF0 | ($unicode >> 18))];
			$char .= bt_string::$chr[(0x80 | (($unicode >> 12) & 0x3F))];
			$char .= bt_string::$chr[(0x80 | (($unicode >> 6) & 0x3F))];
			$char .= bt_string::$chr[(0x80 | ($unicode & 0x3F))];
		}
		elseif ($allow_invalid) {
			if ($unicode < 0x4000000) {
				$char = bt_string::$chr[(0xF8 | ($unicode >> 24))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 18) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 12) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 6) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | ($unicode & 0x3F))];
			}
			else {
				$char = bt_string::$chr[(0xFC | ($unicode >> 30))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 24) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 18) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 12) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | (($unicode >> 6) & 0x3F))];
				$char .= bt_string::$chr[(0x80 | ($unicode & 0x3F))];
			}
		}

		return $char;
	}
}
?>
