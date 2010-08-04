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

const SALT_NEEDED = true;
require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_session.php');
require_once(CLASS_PATH.'allowed_staff.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_pm.php');
require_once(CLASS_PATH.'bt_chans.php');
require_once(CLASS_PATH.'bt_hash.php');

dbconn();
loggedinorreturn();

if (!bt_user::required_class(bt_user::UC_STAFF))
	bt_theme::error('Error','Permission Denied');

$form_hash = trim($_POST['hash']);

$as = new allowed_staff;
if (!$as->check('edituser'))
	die('h4x');

$pv = new bt_session(true, 1800);
if (!$pv->check($form_hash, 'edituser'))
	die('h4x');


if (bt_user::required_class(bt_user::UC_LEADER))
	$maxeditclass = bt_user::$current['class'];
elseif (bt_user::required_class(bt_user::UC_FORUM_MODERATOR, bt_user::UC_MODERATOR))
	$maxeditclass = bt_user::UC_UPLOADER;
else
	$maxeditclass = bt_user::$current['class'] - 1;

function stat_size($type) {
	$stats_sizes = array(
		'T'	=> 1099511627776,
		'G'	=> 1073741824,
		'M'	=> 1048576,
	);

	return isset($stats_sizes[$type]) ? $stats_sizes[$type] : 0;
}

bt_memcache::connect();

$setflags = 0;
$clrflags = 0;

$userid				= 0 + $_POST['userid'];

$username			= trim($_POST['username']);
$title				= trim($_POST['title']);
$email				= trim($_POST['email']);
$avatar				= trim($_POST['avatar']);
$enabled			= (bool) (0 + $_POST['enabled']);
$donor				= (bool) (0 + $_POST['donor']);
$modcomm			= trim($_POST['modcomment']);
$info				= trim($_POST['info']);
$protect			= (bool) (0 + $_POST['protect']);
//$log				= (bool) (0 + $_POST['log']);
//$upld				= (bool) (0 + $_POST['upld']);
$fls				= (bool) (0 + $_POST['fls']);
$avatar_po			= (bool) (0 + $_POST['avatar_po']);
$warnlength			= 0 + $_POST['warnlength'];
$warned				= ((bool) (0 + $_POST['warned'])) || $warnlength;
$uploaded			= 0 + $_POST['uploaded'];
$downloaded			= 0 + $_POST['downloaded'];
$class				= 0 + $_POST['class'];
$anon				= (bool) (0 + $_POST['anon']);
$reset_pk			= (bool) (0 + $_POST['reset_pk']);
$reset_sl			= (bool) (0 + $_POST['reset_sl']);
$reset_pw			= (bool) (0 + $_POST['reset_pw']);
$status				= (bool) (0 + $_POST['status']);
$post_en			= (bool) (0 + $_POST['post_en']);
$irc_en				= (bool) (0 + $_POST['irc_en']);
$hide_stats			= (bool) (0 + $_POST['hide_stats']);
$bypass_ban			= (bool) (0 + $_POST['bypass_ban']);
$disable_invites	= (bool) (0 + $_POST['disable_invites']);
$invites			= 0 + $_POST['invites'];
$reason				= trim($_POST['reason']);
$ip_access			= trim($_POST['ip_access']);
$ban				= (bool) (0 + $_POST['ban']);
$add_comment		= trim($_POST['add_comment']);

$upl_add			= 0 + $_POST['upl_add'];
$upl_add_size		= stat_size($_POST['upl_add_size']);
$upl_rem			= 0 + $_POST['upl_rem'];
$upl_rem_size		= stat_size($_POST['upl_rem_size']);

$dnl_add			= 0 + $_POST['dnl_add'];
$dnl_add_size		= stat_size($_POST['dnl_add_size']);
$dnl_rem			= 0 + $_POST['dnl_rem'];
$dnl_rem_size		= stat_size($_POST['dnl_rem_size']);

$add_upl			= floor($upl_add * $upl_add_size);
$rem_upl			= floor($upl_rem * $upl_rem_size);
$add_dnl			= floor($dnl_add * $dnl_add_size);
$rem_dnl			= floor($dnl_rem * $dnl_rem_size);

if ($fls) {
	$flshelpwith	= trim($_POST['flshw']);
	$flslang		= trim($_POST['flsl']);
}


$stafflog = true;

if ($userid < 1 || !bt_user::valid_class($class))
	bt_theme::error('Error', 'Bad user ID or class ID.');

// check target user class
$res = bt_sql::query('SELECT *, CAST(flags AS SIGNED) AS flags_signed FROM users WHERE id = '.$userid) or bt_sql::err(__FILE__, __LINE__);
$arr = $res->fetch_assoc() or puke();
$res->free();
$arr['settings'] = bt_bitmask::fetch_all($arr['flags']);

$curpass		= $arr['password'];
$curusername	= trim($arr['username']);
$curpasskey		= trim($arr['passkey']);
$curtitle		= trim($arr['title']);
$curavatar		= trim($arr['avatar']);
$curenabled		= $arr['enabled'] == 'yes';
$curdonor		= $arr['settings']['donor'];
$curclass		= 0 + $arr['class'];
$curwarned		= $arr['settings']['warned'];
$curprotect		= $arr['settings']['protect'];
//$curlog			= $arr['settings']['log'];
//$curupld		= $arr['settings']['uploader'];
$curfls			= $arr['settings']['fls'];
$curuploaded	= 0 + $arr['uploaded'];
$curdownloaded	= 0 + $arr['downloaded'];
$curemail		= trim($arr['email']);
$curavatar_po	= $arr['settings']['avatar_po'];
$curanon		= $arr['settings']['privacy'];
$curpost_en		= $arr['settings']['post_enable'];
$curirc_en		= $arr['settings']['irc_enable'];
$curstatus		= $arr['settings']['status'];
$curinvites		= 0 + $arr['invites'];
$curinfo		= trim($arr['info']);
$curip_access	= trim($arr['ip_access']);
$curban			= false;
$curip			= (int)$arr['ip'];
$currealip		= (int)$arr['realip'];
$curmodcomment	= trim($arr['modcomment']);
$curchannels	= bt_bitmask::fetch_all($arr['chans'], true);
$curhide_stats	= $arr['settings']['hide_stats'];
$curbypass_ban	= $arr['settings']['bypass_ban'];
$curdisable_invites = $arr['settings']['disable_invites'];

if ($curfls) {
	$flsq = bt_sql::query('SELECT `lang`, `helpwith` FROM `firstline` WHERE `id` = '.$userid);
	$flsrow = $flsq->fetch_assoc();
	$flsq->free();
	$curflshelpwith = trim($flsrow['helpwith']);
	$curflslang = trim($flsrow['lang']);
}

// Ban IP Stuff
if ($currealip) {
	$lips = array();
	$lips[] = $currealip;
	if ($curip && $currealip != $curip)
		$lips[] = $curip;

	foreach ($lips as $lip) {
		$ipcheck = bt_sql::query('SELECT `comment` FROM `bans` WHERE (`first` <= '.$lip.' AND `last` >= '.$lip.')');
		if ($ipcheck->num_rows) {
			$ipcheck->free();
			$curban = true;
			break;
		}
		$ipcheck->free();
	}
}

$modcomment = bt_user::required_class(bt_user::UC_ADMINISTRATOR) ? $modcomm : $curmodcomment;
$username = bt_user::required_class(bt_user::UC_MODERATOR) ? $username : $curusername;

bt_user::init_mod_comment($userid);

// User may not edit someone with same or higher class than himself!
if ($curclass > $maxeditclass)
	bt_theme::error('Error','Permission Denied');

if (bt_user::$current['class'] === bt_user::UC_MODERATOR)
	$maxclass = bt_user::UC_VIP;
elseif (bt_user::$current['class'] === bt_user::UC_LEADER)
	$maxclass = bt_user::$current['class'];
else
	$maxclass = bt_user::$current['class'] - 1;


if (bt_user::required_class(bt_user::UC_MODERATOR) && $curclass != $class) {
	if ($class <= $maxclass  && $curclass <= $maxclass) {
		// Notify user
		$what = ($class > $curclass ? 'promoted' : 'demoted');
		$msg = 'You have been '.$what.' to "' . bt_user::get_class_name($class) . '" by '.$CURUSER['username'];

		if ($stafflog)
			write_staff_log('User '.$userid.' ('.$username.') '.$what.' to '.get_user_class_name($class).' by '.$CURUSER['username'],'INFO');

		bt_pm::send(0, $userid, $msg, 'You have been '.$what, bt_pm::PM_INBOX);

		$what = ($class > $curclass ? 'Promoted' : 'Demoted');
		$updateset[] = '`class` = '.$class;
		bt_user::mod_comment($userid, $what.' to "' . get_user_class_name($class) . '" by '.$CURUSER['username']);
	}
}

if (bt_user::required_class(bt_user::UC_FORUM_MODERATOR)) {
	if ($curwarned != $warned) {
		if (!$warned) {
			$clrflags |= bt_bitmask::search('warned');
			$updateset[] = '`warneduntil` = 0';
			bt_user::mod_comment($userid, 'Warning removed by ' . $CURUSER['username']);
			$msg = 'Your warning has been removed by '.$CURUSER['username'].'.';
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') unwarned by '.$CURUSER['username'],'UNBAN');
		
		bt_pm::send(0, $userid, $msg, 'Warning removed', bt_pm::PM_INBOX);
		}
		elseif ($warnlength) {
			if ($reason == '')
				bt_theme::error('Error', 'You must enter a reason before giving a warning');

			$setflags |= bt_bitmask::search('warned');
			if ($warnlength == 255) {
				bt_user::mod_comment($userid, 'Warned by '.$CURUSER['username'].'.'."\n".'Reason: '.$reason);
				$msg = 'You have received a [url='.$DEFAULTBASEURL.'/rules.php#warning]warning[/url] from '.$CURUSER['username'].
					"\n\n".'Reason: '.$reason;
				$updateset[] = '`warneduntil` = 0';
				if ($stafflog)
					write_staff_log('User '.$userid.' ('.$username.') warned indefinetly by '.$CURUSER['username'].' ('.$reason.')','BAN');
			}
			else {
				$warneduntil = time() + $warnlength * 604800;
				$dur = $warnlength . ' week' . ($warnlength > 1 ? 's' : '');
				$msg = 'You have received a '.$dur.' [url='.$DEFAULTBASEURL.'/rules.php#warning]warning[/url] from '.$CURUSER['username'].
					"\n\n".'Reason: '.$reason;

				bt_user::mod_comment($userid, 'Warned for '.$dur.' by '.$CURUSER['username'].'.'."\n".'Reason: '.$reason);

				if ($stafflog)
					write_staff_log('User '.$userid.' ('.$username.') warned for '.$dur.' by '.$CURUSER['username'].
						' ('.$reason.')','BAN');
				$updateset[] = '`warneduntil` = '.$warneduntil;
			}

			bt_pm::send(0, $userid, $msg, 'Warning received', bt_pm::PM_INBOX);
		}
	}

	if ($post_en != $curpost_en) {
		if ($post_en) {
			$setflags |= bt_bitmask::search('post_enable');
			bt_user::mod_comment($userid, 'Posting rights allowed by '.$CURUSER['username']);
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') got posting rights allowed by '.$CURUSER['username'],'UBAN');
		}
		else {
			if ($reason == '')
				bt_theme::error('Error', 'You must enter a reason before revoking posting rights');
			$clrflags |= bt_bitmask::search('post_enable');
			bt_user::mod_comment($userid, 'Posting rights revoked by ' . $CURUSER['username'].' ('.$reason.')');
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') got posting rights revoked by '.$CURUSER['username'].' ('.$reason.')','BAN');
		}
	}

	if ($irc_en != $curirc_en) {
		if ($irc_en) {
			$setflags |= bt_bitmask::search('irc_enable');
			bt_user::mod_comment($userid, 'IRC channel access allowed by '.$CURUSER['username']);
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') got IRC access allowed by '.$CURUSER['username'],'UBAN');
		}
		else {
			if ($reason == '')
				bt_theme::error('Error', 'You must enter a reason before revoking IRC access');
			$clrflags |= bt_bitmask::search('irc_enable');
			bt_user::mod_comment($userid, 'IRC channel access revoked by '.$CURUSER['username'].' ('.$reason.')');
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') got IRC access revoked by '.$CURUSER['username'].' ('.$reason.')','BAN');
		}
	}
}

if (bt_user::required_class(bt_user::UC_MODERATOR)) {
	if ($enabled != $curenabled) {
		if ($enabled) {
			$updateset[] = '`enabled` = "yes"';
			bt_mem_caching::remove_passkey($curpasskey, true);
			bt_user::mod_comment($userid, 'Enabled by '.$CURUSER['username']);
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') enabled by '.$CURUSER['username'],'UBAN');
		}
		else {
			if ($reason == '')
				bt_theme::error('Error', 'You must enter a reason before disabling');

			$updateset[] = '`enabled` = "no"';
			bt_mem_caching::remove_passkey($curpasskey);
			bt_user::mod_comment($userid, 'Disabled by '.$CURUSER['username'].' ('.$reason.')');
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') disabled by '.$CURUSER['username'].' ('.$reason.')','BAN');
		}
	}

	if ($username && $username != $curusername) {
		$ucheck = bt_sql::query('SELECT `id` FROM `users` WHERE `username` = '.bt_sql::esc($username));
		if ($ucheck->num_rows) {
			$checkedu = $ucheck->fetch_assoc();
			bt_theme::error('Error', 'Username already exists. Click <a href="/userdetails.php?id='.$checkedu['id'].'">'.
				'here</a> for profile.');
		}
		$ucheck->free();
		$updateset[] = '`username` = '.bt_sql::esc($username);
		bt_user::mod_comment($userid, 'Changed nick from "'.$curusername.'" to "'.$username.'" by '.$CURUSER['username']);
	}

	if ($email != $curemail) {
		if (!bt_security::valid_email($email))
			bt_theme::error('Error','Please specify a valid email address');
		$updateset[] = 'email = ' . bt_sql::esc($email);
		bt_user::mod_comment($userid, 'Changed email from "'.$curemail.'" to "'.$email.'" by ' . $CURUSER['username']);
	}

	if ($title != $curtitle) {
		$updateset[] = '`title` = ' . bt_sql::esc($title);
		bt_user::mod_comment($userid, 'Changed title to "'.$title.'" by '.$CURUSER['username']);
	}
}


if (bt_user::required_class(bt_user::UC_FORUM_MODERATOR)) {
	if ($avatar != $curavatar) {
		$updateset[] = '`avatar` = ' . bt_sql::esc($avatar);
		bt_user::mod_comment($userid, 'Changed avatar to "'.$avatar.'" by '.$CURUSER['username']);
	}

	if ($avatar_po != $curavatar_po) {
		if ($avatar_po) {
			$setflags |= bt_bitmask::search('avatar_po');
			bt_user::mod_comment($userid, 'Avatar tagged as potentially offensive by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('avatar_po');
			bt_user::mod_comment($userid, 'Avatar untagged as potentially offensive by '.$CURUSER['username']);
		}
	}
}


if (bt_user::required_class(bt_user::UC_MODERATOR)) {
	if ($protect != $curprotect) {
		if ($protect) {
			$setflags |= bt_bitmask::search('protect');
			$updateset[] = '`ip` = 0, `realip` = 0';
			bt_user::mod_comment($userid, 'IP Protection enabled by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('protect');
			bt_user::mod_comment($userid, 'IP Protection disabled by '.$CURUSER['username']);
		}
	}

	if ($ban != $curban) {
		if ($ban) {
			if ($reason == '')
				bt_theme::error('Error', 'You must enter a reason before banning');

			foreach ($lips as $lip)
				bt_sql::query('INSERT INTO `bans` (`added`, `addedby`, `comment`, `first`, `last`) '.
					'VALUES('.time().', '.$CURUSER['id'].', '.bt_sql::esc($reason).', '.$lip.', '.$lip.')');

			bt_user::mod_comment($userid, 'IP banned by '.$CURUSER['username'].' ('.$reason.')');
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') ip banned by '.$CURUSER['username'].
					' ('.$reason.')','BAN');
		}
		else {
			foreach ($lips as $lip)
				bt_sql::query('DELETE FROM `bans` WHERE (`first` <= '.$lip.' AND `last` >= '.$lip.')');

			bt_user::mod_comment($userid, 'IP unbanned by '.$CURUSER['username']);
			if ($stafflog)
				write_staff_log('User '.$userid.' ('.$username.') ip unbanned by '.$CURUSER['username'],'BAN');
		}
	}

	if ($bypass_ban != $curbypass_ban) {
		if ($bypass_ban) {
			$setflags |= bt_bitmask::search('bypass_ban');
			bt_user::mod_comment($userid, 'IP Ban Bypass enabled by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('bypass_ban');
			bt_user::mod_comment($userid, 'IP Ban Bypass disabled by '.$CURUSER['username']);
		}
	}

	if ($reset_sl) {
		bt_sql::query('DELETE FROM `peers` WHERE `userid` = '.$userid);
		$updateset[] = '`seeding` = 0, `leeching` = 0';
		bt_user::mod_comment($userid, 'Seeds/Leechs cleared by '.$CURUSER['username']);
	}

	if ($reset_pk) {
		$passkey = md5($curusername.time().$curpass);
		$updateset[] = '`passkey` = '.bt_sql::esc($passkey);
		bt_user::mod_comment($userid, 'Passkey reset by '.$CURUSER['username']);
		bt_mem_caching::remove_passkey($curpasskey, true);
		bt_mem_caching::remove_passkey($passkey);
		$curpasskey = $passkey;
	}

	if ($invites != $curinvites) {
		$updateset[] = 'invites = '.$invites;
		$what = ($invites > $curinvites ? 'gave' : 'took away');
		$num = abs($curinvites - $invites);
		bt_user::mod_comment($userid, $CURUSER['username'].' '.$what.' '.$num.' invite'.($num != 1 ? 's' : ''));
	}

/*	if ($log != $curlog) {
		if ($log) {
			$setflags |= bt_bitmask::search('log');
			bt_user::mod_comment($userid, 'Account loging enabled by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('log');
			bt_user::mod_comment($userid, 'Account loging disabled by '.$CURUSER['username']);
		}
	}*/

	if ($fls != $curfls) {
		if ($fls) {
			$setflags |= bt_bitmask::search('fls');
			$helpwith = bt_sql::esc($flshelpwith);
			$lang = bt_sql::esc($flslang);
			bt_user::mod_comment($userid, 'User added to first line support by '.$CURUSER['username']);
			bt_sql::query('INSERT INTO `firstline` (`id`, `lang`, `helpwith`) VALUES ('.$userid.', '.$lang.', '.$helpwith.')');
		}
	    else {
			$clrflags |= bt_bitmask::search('fls');
			bt_user::mod_comment($userid, 'User removed form first line support by '.$CURUSER['username']);
			bt_sql::query('DELETE FROM `firstline` WHERE `id` = '.$userid);
		}
	}
	elseif ($fls) {
		if ($flshelpwith != $curflshelpwith || $flslang != $curflslang) {
			$helpwith = bt_sql::esc($flshelpwith);
			$lang = bt_sql::esc($flslang);
			bt_user::mod_comment($userid, 'First Line Support info changed by '.$CURUSER['username']);
			bt_sql::query('UPDATE `firstline` SET `lang` = '.$lang.', `helpwith` = '.$helpwith.' WHERE `id` = '.$userid);
		}
	}

	if ($reset_pw) {
		$passwd_reset = true;
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		$newpassword = '';
		for ($i = 0; $i < 14; $i++)
			$newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];

		$hash_types = bt_hash::pick_hash();
		$newpasshash = bt_hash::hash($newpassword, $hash_types[0], $hash_types[1], bt_hash::MAX_SALT_LEN, $SECRETS['salt1'], $SECRETS['salt2']);

		$updateset[] = '`editsecret` = ""';
		$updateset[] = '`password` = '.bt_sql::esc($newpasshash);

		bt_sql::query('DELETE FROM `sessions` WHERE `uid` = '.$userid);
		bt_user::mod_comment($userid, 'Password reset by '.$CURUSER['username']);
	}

	if ($info != $curinfo) {
		$updateset[] = '`info` = ' . bt_sql::esc($info);
		bt_user::mod_comment($userid, 'Profile info changed by '.$CURUSER['username']);
	}

	// IP Access Stuff
	$ips = explode("\n",$ip_access);
	$ipa = array();
	foreach ($ips as $ip)
		$ipa[] = trim($ip);

	if (trim($ip_access) != '') {
		foreach($ipa as $ipt)
			if (!preg_match('/^[0-9.\/\?\*]+$/',$ipt))
				bt_theme::error('Error','Ip Access format incorrect');
	}

	$ipacc = join(';', $ipa);
	if ($curip_access != $ipacc) {
		$updateset[] = '`ip_access` = '.bt_sql::esc($ipacc);
		bt_user::mod_comment($userid, 'IP access changed by '.$CURUSER['username']);
	}
}


if (bt_user::required_class(bt_user::UC_ADMINISTRATOR)) {
	if ($donor != $curdonor) {
		if ($donor) {
			$setflags |= bt_bitmask::search('donor');
			bt_user::mod_comment($userid, 'Given donor star by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('donor');
			bt_user::mod_comment($userid, 'Taken donor star by '.$CURUSER['username']);
		}
	}

	if ($disable_invites != $curdisable_invites) {
		if ($disable_invites) {
			$setflags |= bt_bitmask::search('disable_invites');
			bt_user::mod_comment($userid, 'Invite buying disabled by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('disable_invites');
			bt_user::mod_comment($userid, 'Invite buying enabled by '.$CURUSER['username']);
		}
	}

	if ($anon != $curanon) {
		if ($anon) {
			$setflags |= bt_bitmask::search('privacy');
			bt_user::mod_comment($userid, 'Set to appear as Anonymous by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('privacy');
			bt_user::mod_comment($userid, 'Set to appear as '.$username.' by '.$CURUSER['username']);
		}
	}

	if ($hide_stats != $curhide_stats) {
		if ($hide_stats) {
			$setflags |= bt_bitmask::search('hide_stats');
			bt_user::mod_comment($userid, 'Hidden Stats enabled by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('hide_stats');
			bt_user::mod_comment($userid, 'Hidden Stats disabled by '.$CURUSER['username']);
		}
	}

/*	if ($upld != $curupld) {
		if ($upld) {
			$setflags |= bt_bitmask::search('uploader');
			bt_user::mod_comment($userid, 'User added to uploader list by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('uploader');
			bt_user::mod_comment($userid, 'User removed from uploader list by '.$CURUSER['username']);
		}
	}*/

	if ($add_upl > 0)
		$uploaded += $add_upl;

	if ($rem_upl > 0)
		$uploaded -= $rem_upl;

	$uploaded = $uploaded >= 0 ? $uploaded : 0;

	if ($curuploaded != $uploaded) {
		$updateset[] = '`uploaded` = '.$uploaded;
		bt_user::mod_comment($userid, 'Uploaded amount changed from '.$curuploaded.' to '.$uploaded.' by '.$CURUSER['username']);
		if ($stafflog)
			write_staff_log('User '.$userid.' ('.$username.') upload stats changed from '.bt_theme::mksize($curuploaded).
				' to '.bt_theme::mksize($uploaded).' by '.$CURUSER['username'],'EDIT');
	}

	if ($add_dnl > 0)
		$downloaded += $add_dnl;

    if ($rem_dnl > 0)
        $downloaded -= $rem_dnl;

    $downloaded = $downloaded >= 0 ? $downloaded : 0;

	if ($curdownloaded != $downloaded) {
		$updateset[] = '`downloaded` = '.$downloaded;
		bt_user::mod_comment($userid, 'Downloaded amount changed from '.$curdownloaded.' to '.$downloaded.' by '.$CURUSER['username']);
		if ($stafflog)
			write_staff_log('User '.$userid.' ('.$username.') download stats changed from '.bt_theme::mksize($curdownloaded).
				' to '.bt_theme::mksize($downloaded).' by '.$CURUSER['username'],'EDIT');
	}

	if ($curstatus != $status) {
		if ($status) {
			$setflags |= bt_bitmask::search('status');
			bt_user::mod_comment($userid, 'User confirmed manually by '.$CURUSER['username']);
		}
		else {
			$clrflags |= bt_bitmask::search('status');
			bt_user::mod_comment($userid, 'User unconfirmed by '.$CURUSER['username']);
		}
	}

	$add_chans = 0;
	$rem_chans = 0;
	$updatedchan = false;

	foreach (bt_chans::$channels as $chid => $chan) {
		$channel = (bool)(0 + $_POST['chan_'.$chid]);
		if ($channel != $curchannels['allow_'.$chid]) {
			$updatedchan = true;
			if ($channel)
				$add_chans |= bt_bitmask::chans('allow_'.$chid);
			else
				$rem_chans |= bt_bitmask::chans('allow_'.$chid);
		}
	}

	if ($updatedchan) {
		bt_user::mod_comment($userid, 'IRC channel access changed by '.$CURUSER['username']);
		if ($add_chans)
			$updateset[] = '`chans` = (`chans` | '.$add_chans.')';
		if ($rem_chans)
			$updateset[] = '`chans` = (`chans` & ~'.$rem_chans.')';
	}
}

if ($add_comment != '')
	bt_user::mod_comment($userid, $CURUSER['username'].' - '.$add_comment);


if ($modcomment != $curmodcomment)
	$updateset[] = '`modcomment` = ' . bt_sql::esc($modcomment);

//// Do NOT EDIT THESE LINES
if ($setflags > 0)
	$updateset[] = '`flags` = (`flags` | '.$setflags.')';
if ($clrflags > 0)
	$updateset[] = '`flags` = (`flags` & '.bt_bitmask::invert($clrflags).')';

if ($setflags || $clrflags) {
	bt_memcache::connect();
	bt_memcache::del('good_user:'.$curpasskey);
}
////////////////////////////
if (count($updateset))
	bt_sql::query('UPDATE `users` SET '.implode(', ', $updateset).' WHERE `id` = '.$userid) or bt_sql::err(__FILE__, __LINE__);
bt_user::comit_mod_comments();
if ($stafflog)
	write_staff_log('User '.$userid.' ('.$username.') edited by '.$CURUSER['username'],'EDIT');

if ($passwd_reset) {
	$body = 'An administraor has reset the password for your account.

Here is the new information for your account:

    User name: '.$username.'
    Password: '.$newpassword.'

You may login at '.$BASEURL.'/login.php

--
'.$SITENAME;

	@mail($email, $SITENAME.' account details', $body, 'From: '.$SITEEMAIL)
		or bt_theme::error('Error', 'Unable to send mail.');
}
;
header('Location: '.$BASEURL.'/edituser.php?id='.$userid);
die;
?>
