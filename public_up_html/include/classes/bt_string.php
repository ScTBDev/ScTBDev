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

bt_string::init();
class bt_string {
	public static $chr = array();
	public static $ord = array();
	public static $b2c = array();
	public static $c2b = array();

	private static $glob_search = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]';
	private static $glob_replace = 'abcdefghijklmnopqrstuvwxyz{|}';
	private static $glob_regexp = array(
		'\\?'	=> '.',
		'\\*'	=> '.*?',
	);

	public static function init() {
		bt_memcache::connect();
		$key = 'ord_chars_bin';
		$chars = bt_memcache::get($key);

		if (!is_array($chars)) {
			$chrs = $ords = array();
			for ($i = 0; $i <= 255; $i++) {
				$char = chr($i);
				$bin = sprintf('%08b', $i);
				$chrs[$i] = $char;
				$ords[$char] = $i;
				$b2cs[$bin] = $char;
				$c2bs[$char] = $bin;
			}

			$chars = array($chrs, $ords, $b2cs, $c2bs);
			bt_memcache::set($key, $chars, 86400, false);
		}

		self::$chr = $chars[0];
		self::$ord = $chars[1];
		self::$b2c = $chars[2];
		self::$c2b = $chars[3];
	}

	public static function str2bin($string) {
		return strtr($string, self::$c2b);
	}

	public static function bin2str($bin) {
		$len = strlen($bin);
		if ($len % 8) {
			trigger_error('Input string length must be a multiple of 8 in '.__METHOD__, E_USER_WARNING);
			return '';
		}

		$slen = $len / 8;
		$string = strtr($bin, self::$b2c);

		if (strlen($string) != $slen) {
			trigger_error('Input string must contain only binary digits in '.__METHOD__, E_USER_WARNING);
			return '';
		}

		return $string;
	}

	public static function str2hex($string) {
		return bin2hex($string);
	}

	public static function hex2str($hex) {
		return pack('H*', $hex);
	}

	public static function xor_string($string, $xor_with) {
		for ($i = 0, $strlen = strlen($string), $xorlen = strlen($xor_with); $i < $strlen; $i++) {
			// get the xor position
			$p = $i % $xorlen;

			// xor here
			$r = (self::$ord[$string[$i]] ^ self::$ord[$xor_with[$p]]) & 0xff;

			// add data to new string
			$string[$i] = self::$chr[$r];
		}
		return $string;
	}

	public static function glob_match($string, $mask, $case_sensitive = false) {
		if (!is_string($string) || !is_string($mask))
			return false;

		if (!$case_sensitive) {
			$string = strtr($string, self::$glob_search, self::$glob_replace);
			$mask = strtr($mask, self::$glob_search, self::$glob_replace);
		}

		$mask  = preg_quote($mask, '#');
		$mask = '#^'.strtr($mask, self::$glob_regexp).'$#sDU';
		return (bool)(preg_match($mask, $string));
	}

	public static function bincmp($str1, $str2, $bits) {
		if (!is_int($bits) || !is_string($str1) || !is_string($str2) || $bits < 1)
			return false;

		$bin1 = self::str2bin($str1);
		$bin2 = self::str2bin($str2);

		return strncmp($bin1, $bin2, $bits);
	}

	public static function is_hex($str) {
		return ctype_xdigit($str);
	}

	public static function random($length = 50) {
		$rand = '';
		for ($i = 0; $i < $length; $i++)
			$rand .= chr(mt_rand() & 0xff);
		return $rand;
	}

	public static function cut_word($txt, $max_length = 24, $pad_with = '&#8203;') {
		if (empty($txt))
			return false;
		for ($c = 0, $a = 0, $g = 0; $c < strlen($txt); $c++) {
			$d[($c + $g)] = $txt[$c];
			if ($txt[$c] != ' ')
				$a++;
			elseif ($txt[$c] == ' ')
				$a = 0;
			if ($a > $max_length) {
				$g++;
				$d[($c + $g)] = $pad_with;
				$a = 0;
			}
		}
		return implode('', $d);
	}

	public static function shorten_string($str, $max_length, $mid_cut = false) {
		if (!is_scalar($str))
			return false;

		if (!is_int($max_length))
			return false;

		$length = strlen($str);
		if ($length <= $max_length)
			return $str;
		elseif ($mid_cut) {
			$mid = (int)ceil($max_length / 2);
			$string = substr($str, 0, $mid).'...'.substr($str, $mid);
		}
		else
			return substr($str, 0, $max_length).'...';
	}
};
?>
