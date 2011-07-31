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

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_chans.php');
require_once(CLASS_PATH.'bt_session.php');
require_once(CLASS_PATH.'allowed_staff.php');

bt_loginout::db_connect(true);

if (!bt_user::required_class(UC_STAFF))
  bt_theme::error('Error','Permission Denied');


$as = new allowed_staff;
if (!$as->check('edituser'))
  die();

$pv = new bt_session(true, 1800);
$form_hash = $pv->create('edituser');

$tsettings = bt_theme::$settings['edituser'];
$check_on = $tsettings['check_on'];
$radio_on = $tsettings['radio_on'];
$list_on = $tsettings['list_on'];

if (bt_user::required_class(UC_LEADER))
	$maxeditclass = bt_user::$current['class'];
elseif (bt_user::required_class(UC_FORUM_MODERATOR, UC_MODERATOR))
	$maxeditclass = UC_UPLOADER;
else
	$maxeditclass = bt_user::$current['class'] - 1;


$id = 0 + $_GET['id'];
if ($id < 1)
	die();

$r = bt_sql::query('SELECT *, CAST(flags AS SIGNED) AS flags_signed, CAST(chans AS SIGNED) AS chans_signed FROM users WHERE id = '.$id);
if ($r->num_rows === 1)
	$user = $r->fetch_assoc();
else
	bt_theme::error('Error','Unknown User');
bt_user::prepare_user($user);

if ($user['class'] > $maxeditclass)
	bt_theme::error('Error','Permission Denied');

$modcomment = trim($user['modcomment']);
$username = $user['username'];
$user_link = bt_forums::user_link($id, $username, $user['class']);
///////////////////////////////////////////////////////////////////////////////

bt_theme::head('Edit User "'.$user['username'].'"');

$euvars = array(
	'USER_LINK'		=> $user_link,
	'ID'			=> $id,
	'HASH'			=> $form_hash,
	'MOD_COMMENTS'	=> bt_security::html_safe($modcomment),
);

if (bt_user::required_class(UC_FORUM_MODERATOR)) {
	$template = 'fmod';

	$avatar = trim($user['avatar']);
	$avatar_po = (bool)($user['flags'] & bt_options::USER_AVATAR_PO);
	$offensive_on = $avatar_po ? $radio_on : '';
	$offensive_off = !$avatar_po ? $radio_on : '';

	$warn = (bool)($user['flags'] & bt_options::USER_WARNED);
	$warneduntil = 0 + $user['warneduntil'];
	$warned = $warn ? sprintf($tsettings['warned_yes'], ($warneduntil ? mkprettytime($warneduntil - time()).' to go' : 'arbitrary duration')) :
		$tsettings['warned_no'];

	$post_en = (bool)($user['flags'] & bt_options::USER_POST_ENABLE);
	$post_on = $post_en ? $radio_on : '';
	$post_off = !$post_en ? $radio_on : '';

	$irc_en  = (bool)($user['flags'] & bt_options::USER_IRC_ENABLE);
	$irc_on = $irc_en ? $radio_on : '';
	$irc_off = !$irc_en ? $radio_on : '';

	$euvars['AVATAR']			= bt_security::html_safe($avatar);
	$euvars['OFFENSIVE_ON']		= $offensive_on;
	$euvars['OFFENSIVE_OFF']	= $offensive_off;
	$euvars['WARNED']			= $warned;
	$euvars['POST_ON']			= $post_on;
	$euvars['POST_OFF']			= $post_off;
	$euvars['IRC_ON']			= $irc_on;
	$euvars['IRC_OFF']			= $irc_off;
}

if (bt_user::required_class(UC_MODERATOR)) {
	$template = 'mod';

	$username = $user['username'];
	$title = trim($user['title']);
	$email = trim($user['email']);
	$enabled = (bool)($user['flags'] & bt_options::USER_ENABLED);
	$enabled_on = $enabled ? $radio_on : '';
	$enabled_off = !$enabled ? $radio_on : '';

	if (bt_user::$current['class'] === UC_MODERATOR)
		$maxclass = UC_VIP;
	elseif (bt_user::$current['class'] === UC_LEADER)
		$maxclass = bt_user::$current['class'];
	else
		$maxclass = bt_user::$current['class'] - 1;

    if ($user['class'] <= $maxclass) {
		$classes = array();

		for ($i = 0; $i <= $maxclass; ++$i) {
			$selected = $i == $user['class'] ? $list_on : '';
			$classes[] = sprintf($tsettings['class_list']['entry'], $i, $selected, bt_user::get_class_name($i));
		}

		$class = implode($tsettings['class_list']['join'], $classes);
		$class_list = sprintf($tsettings['class_list']['list'], $class);
	}
	else
		$class_list = sprintf($tsettings['class_list']['no_list'], bt_user::get_class_name($user['class']));

	$ip_access = implode("\n", explode(';', trim($user['ip_access'])));

	$fls = (bool)($user['flags'] & bt_options::USER_FIRST_LINE_SUPPORT);
	if ($fls) {
		$fq = bt_sql::query('SELECT * FROM `firstline` WHERE `id` = '.$user['id']);
		$firstline = $fq->fetch_assoc();
	}
	$fls_on = $fls ? $radio_on : '';
	$fls_off = !$fls ? $radio_on : '';
	$fls_lang = $fls ? trim($firstline['lang']) : '';
	$fls_help = $fls ? trim($firstline['helpwith']) : '';

	$banned = false;
	$curip = (int)$user['ip'];
	$currealip = (int)$user['realip'];
	if ($currealip) {
		$lips = array();
		$lips[] = $currealip;
		if ($curip != '' && $currealip != $curip)
			$lips[] = $curip;

		foreach ($lips as $lip) {
			$ipcheck = bt_sql::query('SELECT `comment` FROM `bans` WHERE (`first` <= '.$lip.' AND `last` >= '.$lip.')');
			if ($ipcheck->num_rows) {
				$banned = true;
				$ipcheck->free();
				break;
			}
			$ipcheck->free();
		}
	}
	$ban_on = $banned ? $radio_on : '';
	$ban_off = !$banned ? $radio_on : '';

    $protect = (bool)($user['flags'] & bt_options::USER_PROTECT);
	$protect_on = $protect ? $radio_on : '';
	$protect_off = !$protect ? $radio_on : '';

	$bypass_ban = (bool)($user['flags'] & bt_options::USER_BYPASS_BANS);
	$bypass_on = $bypass_ban ? $radio_on : '';
	$bypass_off = !$bypass_ban ? $radio_on : '';

	$invites = 0 + $user['invites'];
	$info = trim($user['info']);


	$euvars['USERNAME']			= bt_security::html_safe($username);
	$euvars['TITLE']			= bt_security::html_safe($title);
	$euvars['EMAIL']			= bt_security::html_safe($email);
	$euvars['ENABLED_ON']		= $enabled_on;
	$euvars['ENABLED_OFF']		= $enabled_off;
	$euvars['CLASS_LIST']		= $class_list;
	$euvars['IP_ACCESS']		= bt_security::html_safe($ip_access);
	$euvars['FLS_ON']			= $fls_on;
	$euvars['FLS_OFF']			= $fls_off;
	$euvars['FLS_LANG']			= bt_security::html_safe($fls_lang);
	$euvars['FLS_HELP']			= bt_security::html_safe($fls_help);
	$euvars['BAN_ON']			= $ban_on;
	$euvars['BAN_OFF']			= $ban_off;
	$euvars['PROTECT_ON']		= $protect_on;
	$euvars['PROTECT_OFF']		= $protect_off;
	$euvars['BYPASS_ON']		= $bypass_on;
	$euvars['BYPASS_OFF']		= $bypass_off;
	$euvars['INVITES']			= $invites;
	$euvars['INFO']				= bt_security::html_safe($info);
}

if (bt_user::required_class(UC_ADMINISTRATOR)) {
	$template = 'admin';

	$uploaded = 0 + $user['uploaded'];
	$downloaded = 0 + $user['downloaded'];

	$anon = (bool)($user['flags'] & bt_options::USER_ANON);
	$anon_on = $anon ? $radio_on : '';
	$anon_off = !$anon ? $radio_on : '';
	$hide_stats = (bool)($user['flags'] & bt_options::USER_HIDE_STATS);
	$hide_on = $hide_stats ? $radio_on : '';
	$hide_off = !$hide_stats ? $radio_on : '';
	$confirmed = (bool)($user['flags'] & bt_options::USER_CONFIRMED);
	$status_on = $confirmed ? $radio_on : '';
	$status_off = !$confirmed ? $radio_on : '';
	$donor = (bool)($user['flags'] & bt_options::USER_DONOR);
	$donor_on = $donor ? $radio_on : '';
	$donor_off = !$donor ? $radio_on : '';
	$disable_invites = (bool)($user['flags'] & bt_options::USER_DISABLE_INVITE_BUY) ? $check_on : '';

	$channels = bt_bitmask::fetch_all($user['chans'], true);
	$chans = $chanrows = array();
	$nchans = count(bt_chans::$channels);
	$i = 0;
	foreach (bt_chans::$channels as $chid => $chan) {
		$checked = $channels['allow_'.$chid] ? $check_on : '';
		$chans[] = sprintf($tsettings['irc_list']['entry'], $chid, $checked, bt_security::html_safe($chan));
		$i++;
		$chansleft = $i % $tsettings['irc_list']['per_row'];
		if ($chansleft == 0 || $i == $nchans) {
			$chanrows[] = implode($tsettings['irc_list']['join'], $chans);
			$chans = array();
		}
    }

	$irc_list = implode($tsettings['irc_list']['join_row'], $chanrows);


	$euvars['UPLOADED']			= $uploaded;
	$euvars['DOWNLOADED']		= $downloaded;
	$euvars['ANON_ON']			= $anon_on;
	$euvars['ANON_OFF']			= $anon_off;
	$euvars['HIDE_ON']			= $hide_on;
	$euvars['HIDE_OFF']			= $hide_off;
	$euvars['STATUS_ON']		= $status_on;
	$euvars['STATUS_OFF']		= $status_off;
	$euvars['DONOR_ON']			= $donor_on;
	$euvars['DONOR_OFF']		= $donor_off;
	$euvars['DISABLE_INVITES']	= $disable_invites;
	$euvars['IRC_LIST']			= $irc_list;
}

echo bt_theme_engine::load_tpl('edituser_'.$template, $euvars);

bt_theme::foot();
?>
