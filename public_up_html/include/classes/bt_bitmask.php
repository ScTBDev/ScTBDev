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

class bt_bitmask {
/*
 * Please note that on 32 bit systems, bit #32 will be allocated as a float
 * but since all functions in this class cast to int or bool when returning,
 * everything should still work just fine. The same applies for bit #64 on
 * 64 bit systems
 */
	private static $settings = array(					// Bit		Coded	Description
// ------------------------------------------------------------------------------------------------------------------
		'status'			=> 0x1,						// 1		yes		confirmed account ?
		'privacy'			=> 0x2,						// 2		yes		hide seeding/leeching torrents (anonymous)
		'acceptpms'			=> 0x4,						// 3		yes		allow pms from everybody
		'acceptfriendpms'	=> 0x8,						// 4		yes		allow pms from friends
		'avatar_po'			=> 0x10,					// 5		yes		my avatar is potentially offensive
		'dst'				=> 0x20,					// 6		yes		daylight savings
		'pmnotif'			=> 0x40,					// 7		yes		email notification of pms
		'enabled'			=> 0x80,					// 8				enabled account?
		'avatars'			=> 0x100,					// 9		yes		show avatars
		'avatars_po'		=> 0x200,					// 10		yes		show potentially offensive avatars
		'donor'				=> 0x400,					// 11		yes		user has donated
		'warned'			=> 0x800,					// 12		yes		user is warned
		'deletepms'			=> 0x1000,					// 13		yes		default to deleting pms from inbox on reply
		'savepms'			=> 0x2000,					// 14		yes		default to saving sent pms to sentbox
		'ssl_site'			=> 0x4000,					// 15		yes		force use of ssl, redirect from http to https
		'protect'			=> 0x8000,					// 16		yes		protect users ip, don't store it in the database
		'hide_stats'		=> 0x10000,					// 17		yes		hide upload/download/class/ratio
		'log'				=> 0x20000,					// 18		yes		log everything the user does (really messy atm)
		'uploader'			=> 0x40000,					// 19		yes		user is an uploader on the upload list
		'fls'				=> 0x80000,					// 20		yes		user is first line support
		'statbar'			=> 0x100000,				// 21		yes		show statbar
		'post_enable'		=> 0x200000,				// 22		yes		enable forum posting
		'irc_enable'		=> 0x400000,				// 23		yes		allow in irc channels
		'proxy'				=> 0x800000,				// 24		yes		enable proxy option for tracker
		'ssl_tracker'		=> 0x1000000,				// 25		yes		enable ssl tracker
		'bypass_ban'		=> 0x2000000,				// 26		yes		allows a user to bypass bans
		'hide_last_seen'	=> 0x4000000,				// 27				hide last seen time from other users
		'disable_invites'	=> 0x8000000,				// 28		yes		disable buying of invites
//		'unallocated29'		=> 0x10000000,				// 29
//		'unallocated30'		=> 0x20000000,				// 30
//		'unallocated31'		=> 0x40000000,				// 31
//		'unallocated32'		=> 0x80000000,				// 32		64bit

//////////////////////////////////////////////////////////////////////
// DO NOT USE THE FOLLOWING SETTINGS UNLESS YOUR SERVERS ARE 64 BIT //
//////////////////////////////////////////////////////////////////////

														// Bit		Coded
//		'unallocated64'		=> 0x100000000,				// 33	
//		'unallocated64'		=> 0x200000000,				// 34
//		'unallocated64'		=> 0x400000000,				// 35
//		'unallocated64'		=> 0x800000000,				// 36
//		'unallocated64'		=> 0x1000000000,			// 37
//		'unallocated64'		=> 0x2000000000,			// 38
//		'unallocated64'		=> 0x4000000000,			// 39
//		'unallocated64'		=> 0x8000000000,			// 40
//		'unallocated64'		=> 0x10000000000,			// 41
//		'unallocated64'		=> 0x20000000000,			// 42
//		'unallocated64'		=> 0x40000000000,			// 43
//		'unallocated64'		=> 0x80000000000,			// 44
//		'unallocated64'		=> 0x100000000000,			// 45
//		'unallocated64'		=> 0x200000000000,			// 46
//		'unallocated64'		=> 0x400000000000,			// 47
//		'unallocated64'		=> 0x800000000000,			// 48
//		'unallocated64'		=> 0x1000000000000,			// 49
//		'unallocated64'		=> 0x2000000000000,			// 50
//		'unallocated64'		=> 0x4000000000000,			// 51
//		'unallocated64'		=> 0x8000000000000,			// 52
		'forum_1'			=> 0x10000000000000,		// 53		yes		forum_1-4 used for button selection in forums
		'forum_2'			=> 0x20000000000000,		// 54		yes		see bit 53
		'forum_3'			=> 0x40000000000000,		// 55		yes		see bit 53
//		'unallocated64'		=> 0x80000000000000,		// 56
//		'unallocated64'		=> 0x100000000000000,		// 57
//		'unallocated64'		=> 0x200000000000000,		// 58
//		'unallocated64'		=> 0x400000000000000,		// 59
//		'unallocated64'		=> 0x800000000000000,		// 60
//		'unallocated64'		=> 0x1000000000000000,		// 61
//		'unallocated64'		=> 0x2000000000000000,		// 62
//		'unallocated64'		=> 0x4000000000000000,		// 63
//		'unallocated64'		=> '-9223372036854775808',	// 64	do not use bit 64
	);

	// up to 32 channels, first 32 bits = invite to channel, last 32 bits = allow invite
	private static $chans = array(
		'invite_main'			=> 0x1,					// 1	#SceneTorrents
		'invite_pre'			=> 0x2,					// 2	#sct.pre
		'invite_radio'			=> 0x4,					// 3	#sct.radio
		'invite_support'		=> 0x8,					// 4	#sct.support
		'invite_fls'			=> 0x10,				// 5	#sct.fls
		'invite_spam'			=> 0x20,				// 6	#sct.spam
		'invite_upload'			=> 0x40,				// 7	#sct.upload
		'invite_upload_spam'	=> 0x80,				// 8	#sct.upload.spam
		'invite_staff'			=> 0x100,				// 9	#sct.staff
		'invite_log'			=> 0x200,				// 10	#sct.log
        'invite_admins'			=> 0x400,				// 11	#sct.admins
		'invite_hamachi'		=> 0x800,				// 12	#sct.hamachi
		'invite_upload_auto'	=> 0x1000,				// 13	#sct.upload.auto
		'invite_fi'				=> 0x2000,				// 14	#sct.fi
		'invite_sitebots'		=> 0x4000,				// 15	#sct.sitebots
		'invite_sitebots2'		=> 0x8000,				// 16	#sct.sitebots2
		'invite_sitebots_spam'	=> 0x10000,				// 17	#sct.sitebots.spam
		'invite_swe'			=> 0x20000,				// 18	#sct.swe
		'invite_spy'			=> 0x40000,				// 19	#sct.spy
		'invite_tracers'		=> 0x80000,				// 20	#sct.tracers
		'invite_no'				=> 0x100000,			// 21	#sct.no
		'invite_ee'				=> 0x200000,			// 22	#sct.ee
		'invite_br'				=> 0x400000,			// 23	#sct.br
//		'unallocated32'			=> 0x800000,			// 24
//		'unallocated32'			=> 0x1000000,			// 25
//		'unallocated32'			=> 0x2000000,			// 26
//		'unallocated32'			=> 0x4000000,			// 27
//		'unallocated32'			=> 0x8000000,			// 28
//		'unallocated32'			=> 0x10000000,			// 29
//		'unallocated32'			=> 0x20000000,			// 30
//		'unallocated32'			=> 0x40000000,			// 31
//		'unallocated32'			=> 0x80000000,			// 32

		'allow_main'			=> 0x100000000,			// 33	#SceneTorrents
		'allow_pre'				=> 0x200000000,			// 34	#sct.pre
		'allow_radio'			=> 0x400000000,			// 35	#sct.radio
		'allow_support'			=> 0x800000000,			// 36	#sct.support
		'allow_fls'				=> 0x1000000000,		// 37	#sct.fls
		'allow_spam'			=> 0x2000000000,		// 38	#sct.spam
		'allow_upload'			=> 0x4000000000,		// 39	#sct.upload
		'allow_upload_spam'		=> 0x8000000000,		// 40	#sct.upload.spam
		'allow_staff'			=> 0x10000000000,		// 41	#sct.staff
		'allow_log'				=> 0x20000000000,		// 42	#sct.log
		'allow_admins'			=> 0x40000000000,		// 43	#sct.admins
		'allow_hamachi'			=> 0x80000000000,		// 44	#sct.hamachi
		'allow_upload_auto'		=> 0x100000000000,		// 45	#sct.upload.auto
		'allow_fi'				=> 0x200000000000,		// 46	#sct.fi
		'allow_sitebots'		=> 0x400000000000,		// 47	#sct.sitebots
		'allow_sitebots2'		=> 0x800000000000,		// 48	#sct.sitebots2
		'allow_sitebots_spam'	=> 0x1000000000000,		// 49	#sct.sitebots.spam
		'allow_swe'				=> 0x2000000000000,		// 50	#sct.swe
		'allow_spy'				=> 0x4000000000000,		// 51	#sct.spy
		'allow_tracers'			=> 0x8000000000000,		// 52	#sct.tracers
		'allow_no'				=> 0x10000000000000,	// 53	#sct.no
		'allow_ee'				=> 0x20000000000000,	// 54	#sct.ee
		'allow_br'				=> 0x40000000000000,	// 55	#sct.br
//		'unallocated64'			=> 0x80000000000000,	// 56
//		'unallocated64'			=> 0x100000000000000,	// 57
//		'unallocated64'			=> 0x200000000000000,	// 58
//		'unallocated64'			=> 0x400000000000000,	// 59
//		'unallocated64'			=> 0x800000000000000,	// 60
//		'unallocated64'			=> 0x1000000000000000,	// 61
//		'unallocated64'			=> 0x2000000000000000,	// 62
//		'unallocated64'			=> 0x4000000000000000,	// 63
//		'unallocated64'			=> 0x8000000000000000,	// 64
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
