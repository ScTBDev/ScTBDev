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
require_once(INCL_PATH.'define_bits.php');

class bt_bitmask {
	// up to 32 channels, first 32 bits = invite to channel, last 32 bits = allow invite
	private static $chans = array(
		'invite_main'			=> BIT_1,	// 1	#SceneTorrents
		'invite_pre'			=> BIT_2,	// 2	#sct.pre
		'invite_radio'			=> BIT_3,	// 3	#sct.radio
		'invite_support'		=> BIT_4,	// 4	#sct.support
		'invite_fls'			=> BIT_5,	// 5	#sct.fls
		'invite_spam'			=> BIT_6,	// 6	#sct.spam
		'invite_upload'			=> BIT_7,	// 7	#sct.upload
		'invite_upload_spam'	=> BIT_8,	// 8	#sct.upload.spam
		'invite_staff'			=> BIT_9,	// 9	#sct.staff
		'invite_log'			=> BIT_10,	// 10	#sct.log
		'invite_admins'			=> BIT_11,	// 11	#sct.admins
		'invite_hamachi'		=> BIT_12,	// 12	#sct.hamachi
		'invite_upload_auto'	=> BIT_13,	// 13	#sct.upload.auto
		'invite_fi'				=> BIT_14,	// 14	#sct.fi
		'invite_sitebots'		=> BIT_15,	// 15	#sct.sitebots
		'invite_sitebots2'		=> BIT_16,	// 16	#sct.sitebots2
		'invite_sitebots_spam'	=> BIT_17,	// 17	#sct.sitebots.spam
		'invite_swe'			=> BIT_18,	// 18	#sct.swe
		'invite_spy'			=> BIT_19,	// 19	#sct.spy
		'invite_tracers'		=> BIT_20,	// 20	#sct.tracers
		'invite_no'				=> BIT_21,	// 21	#sct.no
		'invite_ee'				=> BIT_22,	// 22	#sct.ee
		'invite_br'				=> BIT_23,	// 23	#sct.br
//		'unallocated32'			=> BIT_24,	// 24
//		'unallocated32'			=> BIT_25,	// 25
//		'unallocated32'			=> BIT_26,	// 26
//		'unallocated32'			=> BIT_27,	// 27
//		'unallocated32'			=> BIT_28,	// 28
//		'unallocated32'			=> BIT_29,	// 29
//		'unallocated32'			=> BIT_30,	// 30
//		'unallocated32'			=> BIT_31,	// 31
//		'unallocated32'			=> BIT_32,	// 32

		'allow_main'			=> BIT_33,	// 33	#SceneTorrents
		'allow_pre'				=> BIT_34,	// 34	#sct.pre
		'allow_radio'			=> BIT_35,	// 35	#sct.radio
		'allow_support'			=> BIT_36,	// 36	#sct.support
		'allow_fls'				=> BIT_37,	// 37	#sct.fls
		'allow_spam'			=> BIT_38,	// 38	#sct.spam
		'allow_upload'			=> BIT_39,	// 39	#sct.upload
		'allow_upload_spam'		=> BIT_40,	// 40	#sct.upload.spam
		'allow_staff'			=> BIT_41,	// 41	#sct.staff
		'allow_log'				=> BIT_42,	// 42	#sct.log
		'allow_admins'			=> BIT_43,	// 43	#sct.admins
		'allow_hamachi'			=> BIT_44,	// 44	#sct.hamachi
		'allow_upload_auto'		=> BIT_45,	// 45	#sct.upload.auto
		'allow_fi'				=> BIT_46,	// 46	#sct.fi
		'allow_sitebots'		=> BIT_47,	// 47	#sct.sitebots
		'allow_sitebots2'		=> BIT_48,	// 48	#sct.sitebots2
		'allow_sitebots_spam'	=> BIT_49,	// 49	#sct.sitebots.spam
		'allow_swe'				=> BIT_50,	// 50	#sct.swe
		'allow_spy'				=> BIT_51,	// 51	#sct.spy
		'allow_tracers'			=> BIT_52,	// 52	#sct.tracers
		'allow_no'				=> BIT_53,	// 53	#sct.no
		'allow_ee'				=> BIT_54,	// 54	#sct.ee
		'allow_br'				=> BIT_55,	// 55	#sct.br
//		'unallocated64'			=> BIT_56,	// 56
//		'unallocated64'			=> BIT_57,	// 57
//		'unallocated64'			=> BIT_58,	// 58
//		'unallocated64'			=> BIT_59,	// 59
//		'unallocated64'			=> BIT_60,	// 60
//		'unallocated64'			=> BIT_61,	// 61
//		'unallocated64'			=> BIT_62,	// 62
//		'unallocated64'			=> BIT_63,	// 63
//		'unallocated64'			=> BIT_64,	// 64
	);

	public static function invert($store) {
		return (int)~$store;
	}

	public static function search($str) {
		$strs = func_get_args();
		$bits = 0;
		foreach ($strs as $str) {
			if (!isset(self::$settings[$str]))
				return NULL;

			$bits |= self::$settings[$str];
		}

		return (int)$bits;
	}

	public static function chans($str) {
		$strs = func_get_args();
		$bits = 0;
		foreach ($strs as $str) {
			if (!isset(self::$chans[$str]))
				return NULL;

			$bits |= self::$chans[$str];
		}

		return (int)$bits;
	}

	public static function searchnot($str) {
		$strs = func_get_args();
		$bits = 0;
		foreach ($strs as $str) {
			if (!isset(self::$settings[$str]))
				return NULL;

			$bits |= self::$settings[$str];
		}

		return (int)~$bits;
	}

	public static function fetch($store, $str) {
		if (func_num_args() == 2) {
			if (!isset(self::$settings[$str]))
				return NULL;

			return (bool)($store & self::$settings[$str]);
		}

		$vals = array();
		$strs = func_get_args();
		array_shift($strs);
		foreach ($strs as $str) {
			if (!isset(self::$settings[$str]))
				return NULL;

			$vals[$str] = (bool)($store & self::$settings[$str]);
		}
		return $vals;
	}

	public static function set($store, $str) {
		$strs = func_get_args();
		array_shift($strs);
		foreach ($strs as $str) {
			if (!isset(self::$settings[$str]))
				return NULL;

			$store |= self::$settings[$str];
		}

		return (int)$store;
	}

	public static function clear($store, $str) {
		$strs = func_get_args();
		array_shift($strs);
		$bits = 0;
		foreach ($strs as $str) {
			if (!isset(self::$settings[$str]))
				return NULL;

			$bits |= self::$settings[$str];
		}

		return (int)($store & ~$bits);
	}

	public static function flip($store, $str) {
		$strs = func_get_args();
		array_shift($strs);
		$bits = 0;
		foreach ($strs as $str) {
			if (!isset(self::$settings[$str]))
				return NULL;

			$bits |= self::$settings[$str];
		}

		return (int)($store ^ $bits);
	}

	public static function fetch_all($store, $chans = false) {
		$vals = array();
		$settings = $chans ? self::$chans : self::$settings;
		foreach ($settings as $str => $int) {
			$vals[$str] = (bool)($store & $int);
		}
		return $vals;
	}
}
?>
