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
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_config.php');

bt_hash::init();
class bt_hash {
	public static $all_algos = array();
	public static $algos = array();
	public static $ignore_algos = array('adler32','crc32','crc32b','md2','md4','md5','ripemd128','tiger128,3','tiger128,4','haval128,3','haval128,4','haval128,5');

	private static $_last_hash = false;
	private static $_last_read_hash = false;

	const MIN_SALT_LEN = 16;
	const MAX_SALT_LEN = 64;
	const DEF_SALT_LEN = 32;
	const BEGND = '$';
	const SEP = '#';
	const HSEP = '@';

	public static function init() {
		self::$all_algos = hash_algos();
		self::$algos = array_diff(self::$all_algos, self::$ignore_algos);
	}

	public static function hash($string, $hash1, $hash2, $saltlen = self::DEF_SALT_LEN, $hidden_salt1 = '', $hidden_salt2 = '') {
		$saltlen = (int)$saltlen;
		$saltlen = ($saltlen < self::MIN_SALT_LEN ? self::MIN_SALT_LEN : ($saltlen > self::MAX_SALT_LEN ? self::MAX_SALT_LEN : $saltlen));

		$hashes = array((string)strtolower($hash1), (string)strtolower($hash2));
		$hashes = array_unique(array_intersect($hashes, self::$algos));
		
		if (count($hashes) !== 2)
			return false;

		$hidden_salt1 = (string)$hidden_salt1;
		$hidden_salt2 = (string)$hidden_salt2;

		$salt = bt_string::random($saltlen);

		return self::create_hash($string, $hashes[0], $hashes[1], $salt, $hidden_salt1, $hidden_salt2);
	}

	private static function create_hash($string, $hash1, $hash2, $salt, $hidden_salt1, $hidden_salt2) {
		$data1 = hash($hash1, $hidden_salt1.hash($hash2, $string, true).$salt, true);
		$data2 = hash($hash2, $salt.hash($hash1, $string, true).$hidden_salt2, true);

		return self::BEGND.bt_string::b64_encode($salt).self::SEP.$hash1.self::HSEP.bt_string::b64_encode($data1).
			self::SEP.$hash2.self::HSEP.bt_string::b64_encode($data2).self::BEGND;
	}

	private static function read_hash($hash) {
		if (self::$_last_hash !== $hash) {
			$split = explode(self::BEGND, $hash);
			if (count($split) !== 3 || !empty($split[0]) || !empty($split[2]) || empty($split[1]))
				return false;

			$split = explode(self::SEP, $split[1]);
			if (count($split) !== 3 || empty($split[0]) || empty($split[1]) || empty($split[2]))
				return false;

			$salt = bt_string::b64_decode($split[0]);
			if ($salt === false)
				return false;

			$hash1 = explode(self::HSEP, $split[1]);
			$hash2 = explode(self::HSEP, $split[2]);

			if (count($hash1) !== 2 || count($hash2) !== 2 || empty($hash1[0]) || empty($hash1[1]) || empty($hash2[0]) || empty($hash2[1]))
				return false;

			// Use the $all_algos array here for comparison in case an insecure algorithum is added to the ignore list later, still must be able to verify it
			$hashes = array(strtolower($hash1[0]), strtolower($hash2[0]));
			$hashes = array_unique(array_intersect($hashes, self::$all_algos));
			if (count($hashes) !== 2)
				return false;

			self::$_last_hash = $hash;
			self::$_last_read_hash = array($salt, $hashes[0], $hashes[1]);
		}
		return self::$_last_read_hash;
	}

	public static function verify_hash($string, $hash, $hidden_salt1 = '', $hidden_salt2 = '') {
		$hashes = self::read_hash($hash);
		if (!$hashes)
			return false;

		list($salt, $hash1, $hash2) = $hashes;
		$hidden_salt1 = (string)$hidden_salt1;
		$hidden_salt2 = (string)$hidden_salt2;

		$correct_hash = self::create_hash($string, $hash1, $hash2, $salt, $hidden_salt1, $hidden_salt2);
		return $correct_hash === $hash;
	}

	public static function secure_hash($hash) {
		$hashes = self::read_hash($hash);
		if (!$hashes)
			return false;

		list($salt, $hash1, $hash2) = $hashes;

		$saltlen = strlen($salt);
		if ($saltlen < self::MIN_SALT_LEN || $saltlen > self::MAX_SALT_LEN)
			return false;

		$hash_list = array($hash1, $hash2);
		$secure_list = array_intersect($hash_list, self::$algos);
		return count($secure_list) === 2;
	}

	public static function pick_hash() {
		$hash_types = bt_config::$conf['hash_types'];
		shuffle($hash_types);
		$rands = array_rand($hash_types, 2);
		return array($hash_types[$rands[0]], $hash_types[$rands[1]]);
	}
};
?>
