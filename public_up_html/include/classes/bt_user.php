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
require_once(CLASS_PATH.'bt_pm.php');
require_once(CLASS_PATH.'bt_sql.php');

class bt_user {
	const MAX_USERNAME_LENGTH	= 16;
	const MAX_EMAIL_LENGTH		= 128;

	private static $_users_cache = array();
	private static $_mod_comments = array();
	private static $_mod_comments_del = array();
	public static $current = NULL;

	public static $class_names = array(
		UC_USER				=> 'User',
		UC_POWER_USER		=> 'Power User',
		UC_XTREME_USER		=> 'Xtreme User',
		UC_LOVER			=> 'ScT Lover',
		UC_WHORE			=> 'ScT Whore',
		UC_SUPER_WHORE		=> 'ScT Super Whore',
		UC_SEED_WHORE		=> 'ScT Seed Whore',
		UC_OVERSEEDER		=> 'The Overseeder',
		UC_VIP				=> 'VIP',
		UC_UPLOADER			=> 'Uploader',
		UC_FORUM_MODERATOR	=> 'Forum Moderator',
		UC_MODERATOR		=> 'Global Moderator',
		UC_ADMINISTRATOR	=> 'Administrator',
		UC_LEADER			=> 'Staff Leader'
	);

	public static function prepare_user(&$user, $curuser = false) {
		if ($curuser && empty($user))
			die;

		if (isset($user['id']))
			$user['id'] = (int)$user['id'];
		if (isset($user['class']))
			$user['class'] = (int)$user['class'];
		if (isset($user['theme']))
			$user['theme'] - (int)$user['theme'];
		if (isset($user['stylesheet']))
			$user['stylesheet'] = (int)$user['stylesheet'];
		if (isset($user['added']))
			$user['added'] = (int)$user['added'];
		if (isset($user['last_login']))
			$user['last_login'] = (int)$user['last_login'];
		if (isset($user['last_access']))
			$user['last_access'] = (int)$user['last_access'];
		if (isset($user['uploaded']))
			$user['uploaded'] = (float)$user['uploaded'];
		if (isset($user['downloaded']))
			$user['downloaded'] = (float)$user['downloaded'];
		if (isset($user['payed_uploaded']))
			$user['payed_uploaded'] = (float)$user['payed_uploaded'];
		if (isset($user['seeding']))
			$user['seeding'] = (int)$user['seeding'];
		if (isset($user['leeching']))
			$user['leeching'] = (int)$user['leeching'];
		if (isset($user['country']))
			$user['country'] = (int)$user['country'];
		if (isset($user['warneduntil']))
			$user['warneduntil'] = (int)$user['warneduntil'];
		if (isset($user['torrentsperpage']))
			$user['torrentsperpage'] = (int)$user['torrentsperpage'];
		if (isset($user['topicsperpage']))
			$user['topicsperpage'] = (int)$user['topicsperpage'];
		if (isset($user['postsperpage']))
			$user['postsperpage'] = (int)$user['postsperpage'];
		if (isset($user['last_browse']))
			$user['last_browse'] = (int)$user['last_browse'];
		if (isset($user['inbox_new']))
			$user['inbox_new'] = (int)$user['inbox_new'];
		if (isset($user['inbox']))
			$user['inbox'] = (int)$user['inbox'];
		if (isset($user['sentbox']))
			$user['sentbox'] = (int)$user['sentbox'];
		if (isset($user['posts']))
			$user['posts'] = (int)$user['posts'];
		if (isset($user['last_forum_visit']))
			$user['last_forum_visit'] = (int)$user['last_forum_visit'];
		if (isset($user['invites']))
			$user['invites'] = (int)$user['invites'];
		if (isset($user['invitedby']))
			$user['invitedby'] = (int)$user['invitedby'];
		if (isset($user['donations']))
			$user['donations'] = (float)$user['donations'];
		if (isset($user['irc_time']))
			$user['irc_time'] = (int)$user['irc_time'];

		if (isset($user['ip_hex'])) {
			$user['ip'] = bt_string::hex2str($user['ip_hex']);
			unset($user['ip_hex']);
		}

		if (isset($user['realip_hex'])) {
			$user['realip'] = bt_string::hex2str($user['realip_hex']);
			unset($user['realip_hex']);
		}

		if (isset($user['flags_signed'])) {
			$user['flags'] = (int)$user['flags_signed'];
			unset($user['flags_signed']);
		}
		elseif (isset($user['flags']))
			$user['flags'] = (int)$user['flags'];

		if (isset($user['chans_signed'])) {
			$user['chans'] = (int)$user['chans_signed'];
			unset($user['chans_signed']);
		}
		elseif (isset($user['chans']))
			$user['chans'] = (int)$user['chans'];
	}

	public static function valid_class($class) {
		$class = (int)$class;
		return (bool)($class >= UC_MIN && $class <= UC_MAX);
	}

	public static function required_class($min = UC_MIN, $max = UC_MAX) {
		$minclass = (int)$min;
		$maxclass = (int)$max;
		if (empty(self::$current))
			return false;
		if (!self::valid_class($minclass) || !self::valid_class($maxclass))
			return false;
		if ($maxclass < $minclass)
			return false;

		return (bool)(self::$current['class'] >= $minclass && self::$current['class'] <= $maxclass);
	}

	public static function get_class_name($class) {
		$class = (int)$class;

		if (!self::valid_class($class))
			return '';

		if (isset(self::$class_names[$class]))
			return self::$class_names[$class];
		else
			return '';
	}

	public static function auto_demote($fromclass, $toclass, $minratio, $remove_flags = 0, $remove_chans = 0) {
		$fromclass = (int)$fromclass;
		$toclass   = (int)$toclass;
		$minratio  = (float)$minratio;

		$fromname = self::get_class_name($fromclass);
		$toname   = self::get_class_name($toclass);

		if ($fromname == '' || $toname == '' || $fromclass <= $toclass)
			return false;

		self::_cache_users();

		if (!isset(self::$_users_cache[$fromclass]))
			return false;

		$msg = 'You have been auto-demoted from [b]'.$fromname.'[/b] to [b]'.$toname.'[/b] because your share ratio '.
			'has dropped below '.$minratio;
		$title = 'Demoted to '.$toname;
		$comment = 'Auto-demoted from '.$fromname.' to '.$toname;

		foreach (self::$_users_cache[$fromclass] as $aid => $arr) {
			if ($arr['ratio'] > $minratio)
				continue;

			unset(self::$_users_cache[$fromclass][$aid]);
			self::$_users_cache[$toclass][] = $arr;

			bt_sql::query('UPDATE users SET class = '.$toclass.($remove_flags ? ', flags = (flags & ~'.$remove_flags.')' : '').
				($remove_chans ? ', chans = (chans & ~'.$remove_chans.')' : '').' WHERE id = '.$arr['id']);
			self::mod_comment($arr['id'], $comment);
			bt_pm::send(0, $arr['id'], $msg, $title);
		}

		return true;
	}

	public static function auto_promote($fromclass, $toclass, $minratio, $uplimit, $regtime, $extmsg = '', $add_flags = 0, $add_chans = 0, $downlimit = 0) {
		$fromclass	= (int)$fromclass;
		$toclass	= (int)$toclass;
		$minratio	= (float)$minratio;
		$uplimit	= 0 + $uplimit;
		$downlimit	= 0 + $downlimit;
		$regtime	= (int)$regtime;
		$extmsg		= (string)$extmsg;

		$maxdt		= time() - $regtime;
		$fromname 	= self::get_class_name($fromclass);
		$toname		= self::get_class_name($toclass);


		if ($fromname == '' || $toname == '' || $fromclass >= $toclass || $uplimit == 0)
			return false;

		self::_cache_users();

		if (!isset(self::$_users_cache[$fromclass]))
			return false;

		$msg = 'Congratulations, you have been auto-promoted to [b]'.$toname.'[/b], because you have met the necessary requirements.'."\n".
			'Thank you for sharing your files on our network.'.($extmsg != '' ? "\n\n".$extmsg : '');
		$title = 'Promoted to '.$toname;
		$comment = 'Auto-promoted from '.$fromname.' to '.$toname;

		foreach (self::$_users_cache[$fromclass] as $aid => $arr) {
			if ($arr['ratio'] < $minratio || $arr['uploaded'] < $uplimit || $arr['added'] > $maxdt || $arr['downloaded'] < $downlimit)
				continue;

			unset(self::$_users_cache[$fromclass][$aid]);
			self::$_users_cache[$toclass][] = $arr;

			bt_sql::query('UPDATE users SET class = '.$toclass.($add_flags ? ', flags = (flags | '.$add_flags.')' : '').
				($add_chans ? ', chans = (chans | '.$add_chans.')' : '').' WHERE id = '.$arr['id']);
			self::mod_comment($arr['id'], $comment);
			bt_pm::send(0, $arr['id'], $msg, $title);
		}
		unset(self::$_users_cache[$fromclass]);

		return true;
	}

	private static function _cache_users() {
		if (!empty(self::$_users_cache))
			return;

		$res = bt_sql::query('SELECT id, uploaded, payed_uploaded, downloaded, added, class FROM users WHERE (flags & '.bt_options::USER_ENABLED.')');
		while ($arr = $res->fetch_assoc()) {
			$class = (int)$arr['class'];

			if (!isset(self::$_users_cache[$class]))
				self::$_users_cache[$class] = array();

			$uploaded = 0 + ($arr['payed_uploaded'] > $arr['uploaded'] ? 1 : ($arr['uploaded'] - $arr['payed_uploaded']));
			$ratio = $arr['downloaded'] == 0 ? 1 : ($uploaded / $arr['downloaded']);

			self::$_users_cache[$class][] = array(
				'id'			=> 0 + $arr['id'],
				'uploaded'		=> $uploaded,
				'downloaded'	=> 0 + $arr['downloaded'],
				'ratio'			=> $ratio,
				'added'			=> 0 + $arr['added'],
			);
		}
		$res->free();;
	}

	public static function init_mod_comment($user, $deleted = false) {
		$user = (int)$user;
		if (!isset(self::$_mod_comments[$user]))
			self::$_mod_comments[$user] = array();
		if ($deleted)
			self::$_mod_comments_del[$user] = true;
	}

	public static function mod_comment($user, $comment) {
		$user = (int)$user;
		$comment = (string)trim($comment);

		if (isset(self::$_mod_comments[$user])) {
			self::$_mod_comments[$user][] = gmdate('Y-m-d (H:i:s)') . ' - '.$comment;
		}
		else {
			$res = bt_sql::query('SELECT modcomment FROM users WHERE id = '.$user);
			$qmc = $res->fetch_assoc();
			$res->free();

			$mc = gmdate('Y-m-d (H:i:s)') . ' - '.$comment."\n".$qmc['modcomment'];
			$res2 = bt_sql::query('UPDATE users SET modcomment = '.bt_sql::esc($mc).' WHERE id = '.$user);
			return $res2 ? true : false;
		}
	}

	public static function comit_mod_comments() {
		foreach (self::$_mod_comments as $user => $comments) {
			if (!count($comments))
				continue;

			unset(self::$_mod_comments[$user]);

			krsort($comments, SORT_NUMERIC);
			$comment = join("\n", $comments);

			$table = isset(self::$_mod_comments_del[$user]) ? 'users_deleted' : 'users';

			$res = bt_sql::query('SELECT modcomment FROM '.$table.' WHERE id = '.$user);
			$qmc = $res->fetch_assoc();
			$res->free();

			$mc = $comment."\n".$qmc['modcomment'];
			$res2 = bt_sql::query('UPDATE '.$table.' SET modcomment = '.bt_sql::esc($mc).' WHERE id = '.$user);
		}
	}

	public static function mkpasskey($length = 32) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		$passkey = '';
		for ($i = 0; $i < $length; $i++)
			$passkey .= $chars[mt_rand(0, $max)];

		return $passkey;
	}
}
?>
