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

define('SALT_NEEDED', true);
require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_session.php');
require_once(CLASS_PATH.'bt_chans.php');
require_once(CLASS_PATH.'bt_forums.php');
require_once(CLASS_PATH.'bt_hash.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_location.php');
require_once(CLASS_PATH.'bt_bitmask.php');

function bark($msg)
  {
   genbark($msg, 'Update failed!');
  }

bt_loginout::db_connect(true);

if (!mkglobal('email:oldpassword:chpassword:passagain'))
  bark('missing form data');

$form_hash = trim($_POST['hash']);

$pv = new bt_session(true, 1800);
if (!$pv->check($form_hash, 'profile'))
  die('h4x');


$updateset = array();
$changedemail = 0;
bt_user::init_mod_comment(bt_user::$current['id']);

$setflags = 0;
$clrflags = 0;

$ip = bt_vars::$ip;
$rip = bt_vars::$realip;

if ($chpassword != '') {
   if (bt_utf8::strlen($chpassword) > 40)
		bark('Sorry, password is too long (max is 40 chars)');

	if (!bt_hash::verify_hash($oldpassword, bt_user::$current['password'], $SECRETS['salt1'], $SECRETS['salt2']))
		bark('The old password was not correct. Try again.');

	if ($chpassword != $passagain)
		bark('The passwords didn\'t match. Try again.');

	$hash_types = bt_hash::pick_hash();
	$password = bt_hash::hash($chpassword, $hash_types[0], $hash_types[1], bt_hash::MAX_SALT_LEN, $SECRETS['salt1'], $SECRETS['salt2']);

	$updateset[] = '`password` = '.bt_sql::esc($password);

	mysql_query('DELETE FROM `sessions` WHERE `uid` = "'.bt_user::$current['id'].'"');
	logincookie(bt_user::$current['id'], $passhash);
	bt_user::mod_comment(bt_user::$current['id'], 'User changed password from '.$ip.($rip != $ip ? ' ('.$rip.')' : ''));
}

if ($email != bt_user::$current['email']) {
        if (!validemail($email))
                bark('That doesn\'t look like a valid email address.');
  $r = mysql_query('SELECT `id` FROM `users` WHERE `email` = '.sqlesc($email)) or sqlerr();
        if (mysql_num_rows($r) > 0)
                bark('That e-mail address is already in use.');
        $changedemail = 1;
}
$statbar = 0 + $_POST['statbar'];
$deletepms = 0 + $_POST['deletepms'];
$savepms = 0 + $_POST['savepms'];
$pmnotif = 0 + $_POST['pmnotif'];

$ip_access = str_replace(array("\r\n","\n","\r"),';',trim($_POST['ip_access']));

$cats = bt_mem_caching::get_cat_list();
$browsecats = array();
if (is_array($_POST['c'])) {
	foreach ($_POST['c'] as $c)
		$browsecats[] = (int)$c;
}

$notifs = '';
foreach ($cats as $catid => $cat) {
	if (in_array($catid, $browsecats, true))
		$notifs .= '[cat'.$catid.']';
}

$avatar = trim($_POST['avatar']);
$avat = 0 + $_POST['avatars'];
$apms = 0 + $_POST['acceptpms'];

if ($avat == 2) {
	$avatars = 1;
	$avatars_po = 1;
}
elseif ($avat == 1) {
	$avatars = 1;
	$avatars_po = 0;
}
else {
	$avatars = 0;
	$avatars_po = 0;
}

if ($apms == 2) {
	$acceptpms = 1;
	$acceptfriendpms = 1;
}
elseif ($apms == 1) {
	$acceptpms = 0;
	$acceptfriendpms = 1;
}
else {
	$acceptpms = 0;
	$acceptfriendpms = 0;
}

$avatar_po = 0 + $_POST['avatar_po'];
/*
$timezone = (float)0 + $_POST['timezone'];
if ($timezone != bt_user::$current['timezone']) {
	if (!isset(bt_time::$time_zones["$timezone"]))
		bt_theme::error('Error','Invalid Timezone');

	$updateset[] = '`timezone` = '.$timezone;
}
*/

if (bt_user::required_class(UC_STAFF) && $_POST['resetpasskey']) {
	$passkey = md5(bt_user::$current['username'].time().bt_user::$current['password']);
	$updateset[] = 'passkey = '.sqlesc($passkey);
	bt_mem_caching::remove_passkey(bt_user::$current['passkey'], true);
	bt_mem_caching::remove_passkey($passkey);

	mysql_query('INSERT INTO passkeylog (added, userid, oldkey, newkey) '.
		'VALUES ('.time().', '.bt_user::$current['id'].', '.bt_sql::esc(bt_user::$current['passkey']).
		', '.bt_sql::esc($passkey).')') or sqlerr(__FILE__,__LINE__);
}



$info = trim($_POST['info']);
$theme = 0 + $_POST['theme'];
$country = 0 + $_POST['country'];
$proxy = 0 + $_POST['proxy'];
$ssl_tracker = 0 + $_POST['ssl_tracker'];
$ssl_site = 0 + $_POST['ssl_site'];
$privacy = 0 + $_POST['privacy'];
$fb = 0 + $_POST['forum_buttons'];
$hide_stats = (bool)0 + $_POST['hide_stats'];

if (bt_user::required_class(UC_WHORE)) {
	if ($privacy != ((bool)(bt_user::$current['flags'] & bt_options::USER_ANON))) {
		if ($privacy)
			$setflags |= bt_options::USER_ANON;
		else
			$clrflags |= bt_options::USER_ANON;
	}
	if ($hide_stats != ((bool)(bt_user::$current['flags'] & bt_options::USER_HIDE_STATS))) {
		if ($hide_stats)
			$setflags |= bt_options::USER_HIDE_STATS;
		else
			$clrflags |= bt_options::USER_HIDE_STATS;
	}
}

if (bt_user::required_class(UC_STAFF)) {
	if (trim($ip_access) != '') {
		$ipas = explode(';',$ip_access);
		foreach($ipas as $ipa)
			if (!preg_match('/^[0-9\.\/\?\*]+$/',$ipa))
				bt_theme::error('Error','Ip Access format incorrect');
	}
	if ($ip_access != trim(bt_user::$current['ip_access']))
		$updateset[] = 'ip_access = '.sqlesc($ip_access);
}

if (bt_user::required_class(UC_WHORE) || (bt_user::$current['flags'] & bt_options::USER_DONOR)) {
	$title = sqlesc((trim($_POST['title']) != '') ? trim($_POST['title']) : '');
	$updateset[] = 'title = '.$title;
}

if ($acceptpms != ((bool)(bt_user::$current['flags'] & bt_options::USER_ACCEPT_PMS))) {
	if ($acceptpms)
		$setflags |= bt_options::USER_ACCEPT_PMS;
	else
		$clrflags |= bt_options::USER_ACCEPT_PMS;
}

if ($acceptfriendpms != ((bool)(bt_user::$current['flags'] & bt_options::USER_ACCEPT_FRIEND_PMS))) {
	if ($acceptfriendpms)
		$setflags |= bt_options::USER_ACCEPT_FRIEND_PMS;
	else
		$clrflags |= bt_options::USER_ACCEPT_FRIEND_PMS;
}

if ($pmnotif != ((bool)(bt_user::$current['flags'] & bt_options::USER_PM_NOTIFICATION))) {
	if ($pmnotif)
		$setflags |= bt_options::USER_PM_NOTIFICATION;
	else
		$clrflags |= bt_options::USER_PM_NOTIFICATION;
}

if ($statbar != ((bool)(bt_user::$current['flags'] & bt_options::USER_STATBAR))) {
	if ($statbar)
		$setflags |= bt_options::USER_STATBAR;
	else
		$clrflags |= bt_options::USER_STATBAR;
}

if ($proxy != ((bool)(bt_user::$current['flags'] & bt_options::USER_PROXY_TRACKER))) {
	if ($proxy)
		$setflags |= bt_options::USER_PROXY_TRACKER;
	else
		$clrflags |= bt_options::USER_PROXY_TRACKER;
}

if ($ssl_tracker != ((bool)(bt_user::$current['flags'] & bt_options::USER_SSL_TRACKER))) {
	if ($ssl_tracker)
		$setflags |= bt_options::USER_SSL_TRACKER;
	else
		$clrflags |= bt_options::USER_SSL_TRACKER;
}

if ($ssl_site != ((bool)(bt_user::$current['flags'] & bt_options::USER_SSL_SITE))) {
	if ($ssl_site)
		$setflags |= bt_options::USER_SSL_SITE;
	else
		$clrflags |= bt_options::USER_SSL_SITE;
}

if ($deletepms != ((bool)(bt_user::$current['flags'] & bt_options::USER_DELETE_PMS))) {
	if ($deletepms)
		$setflags |= bt_options::USER_DELETE_PMS;
	else
		$clrflags |= bt_options::USER_DELETE_PMS;
}

$cursavepms = (bool)(bt_user::$current['flags'] & bt_options::USER_SAVE_PMS);
if ($savepms != $cursavepms) {
	if ($savepms)
		$setflags |= bt_options::USER_SAVE_PMS;
	else
		$clrflags |= bt_options::USER_SAVE_PMS;
}

$curavatar_po = (bool)(bt_user::$current['flags'] & bt_options::USER_AVATAR_PO);
if ($avatar_po != $curavatar_po) {
	if ($avatar_po)
		$setflags |= bt_options::USER_AVATAR_PO;
	else
		$clrflags |= bt_options::USER_AVATAR_PO;
}

if ($avatars != ((bool)(bt_user::$current['flags'] & bt_options::USER_SHOW_AVATARS))) {
	if ($avatars)
		$setflags |= bt_options::USER_SHOW_AVATARS;
	else
		$clrflags |= bt_options::USER_SHOW_AVATARS;
}

if ($avatars_po != ((bool)(bt_user::$current['flags'] & bt_options::USER_SHOW_PO_AVATARS))) {
	if ($avatars_po)
		$setflags |= bt_options::USER_SHOW_PO_AVATARS;
	else
		$clrflags |= bt_options::USER_SHOW_PO_AVATARS;
}
$cur_fb = bt_forums::settings_to_forum_theme(bt_user::$current['flags']);
if ($fb != $cur_fb) {
	if (!isset(bt_forums::$buttons[$fb]))
		$fb = 0;

	$forum_1 = (bool)($fb & BIT_1);
	$forum_2 = (bool)($fb & BIT_2);
	$forum_3 = (bool)($fb & BIT_3);
	$forum_4 = (bool)($fb & BIT_4);

	if ($forum_1 != ((bool)(bt_user::$current['flags'] & bt_options::USER_FORUM_ICONS_1))) {
		if ($forum_1)
			$setflags |= bt_options::USER_FORUM_ICONS_1;
		else
			$clrflags |= bt_options::USER_FORUM_ICONS_1;
	}
	if ($forum_2 != ((bool)(bt_user::$current['flags'] & bt_options::USER_FORUM_ICONS_2))) {
		if ($forum_2)
			$setflags |= bt_options::USER_FORUM_ICONS_2;
		else
			$clrflags |= bt_options::USER_FORUM_ICONS_2;
	}
	if ($forum_3 != ((bool)(bt_user::$current['flags'] & bt_options::USER_FORUM_ICONS_3))) {
		if ($forum_3)
			$setflags |= bt_options::USER_FORUM_ICONS_3;
		else
			$clrflags |= bt_options::USER_FORUM_ICONS_3;
	}
	if ($forum_4 != ((bool)(bt_user::$current['flags'] & bt_options::USER_FORUM_ICONS_4))) {
		if ($forum_4)
			$setflags |= bt_options::USER_FORUM_ICONS_4;
		else
			$clrflags |= bt_options::USER_FORUM_ICONS_4;
	}
}

$curchannels = bt_bitmask::fetch_all(bt_user::$current['chans'], true);
$add_chans = 0;
$rem_chans = 0;
foreach (bt_chans::$channels as $chid => $chan) {
    if ($curchannels['allow_'.$chid]) {
		$channel = (bool)(0 + $_POST['chan_'.$chid]);
		if ($channel != $curchannels['invite_'.$chid]) {
			if ($channel)
				$add_chans |= bt_bitmask::chans('invite_'.$chid);
			else
				$rem_chans |= bt_bitmask::chans('invite_'.$chid);
		}
	}
}
if ($avatar != bt_user::$current['avatar'])
  {
   if ($avatar == '')
     $updateset[] = '`avatar` = ""';
   else
     {
      if (preg_match('#^((https?://|www\\.)[a-z0-9:.-]+)?/([a-z0-9%&_.=~/?()+-]+)\\.(jpe?g|png|gif|php|=[a-z0-9/-]+)$#i',$avatar))
        $updateset[] = '`avatar` = '.sqlesc($avatar);
      else
        bt_theme::error('Error','Avatar URL Not Valid');
     }
  }


$updateset[] = "torrentsperpage = " . min(100, 0 + $_POST["torrentsperpage"]);
$updateset[] = "topicsperpage = " . min(100, 0 + $_POST["topicsperpage"]);
$updateset[] = "postsperpage = " . min(100, 0 + $_POST["postsperpage"]);

if ($theme >= 0)
  $updateset[] = 'theme = '.$theme;

if ($country != bt_user::$current['country']) {
	if (bt_location::country_by_id($country))
		$updateset[] = '`country` = '.$country;
}


$updateset[] = "info = " . sqlesc($info);
$updateset[] = "notifs = '$notifs'";
/* ****** */

$urladd = '';

if ($changedemail)
  {
   $sec = mksecret();
   $hash = sha1($sec.strtolower($email).$sec);
   $thishost = $_SERVER['HTTP_HOST'];
   $thisdomain = preg_replace('/^www\./is', '', $thishost);
   $body = 'You have requested that your user profile (username '.bt_user::$current['username'].')
on '.$thisdomain.' should be updated with this email address ('.$email.') as
user contact.

The person who entered your email address had the IP address '.$ip.($rip != $ip ? ' ('.$rip.')' : '').'
If you did not do this, please report this incident to a staff member, including the
IP address of the person who initiated this email change. Please do not reply to this email.

To to continue with the update of your user profile, please follow this link:

http://'.$thishost.'/changeemail.php?userid='.bt_user::$current['id'].'&code='.$hash.'

Your new email address will need to be verified after you do this. Otherwise
your profile will remain unchanged.';

   $em = mysql_query('INSERT INTO `email_changes` (`code`, `userid`, `time`, `newemail`, `ip`, `realip`) '.
               'VALUES ('.sqlesc(bt_string::str2hex($sec)).', "'.bt_user::$current['id'].'", "'.time().'", '.sqlesc($email).', '.
               sqlesc($ip).', '.sqlesc($rip).')');

	if (!$em) {
		if (mysql_errno() == 1062)
			bt_theme::error('Error','You already have a email change in progress, please verify the original email before continuing');

		sqlerr(__FILE__,__LINE__);
	}


   mail(bt_user::$current['email'], $thisdomain.' email change confirmation', $body, 'From: '.bt_config::$conf['site_email']);
   $urladd .= '&mailsent=1';
   bt_user::mod_comment(bt_user::$current['id'], 'Email change process initiated from '.$ip.($rip != $ip ? ' ('.$rip.')' : ''));
  }

//// Do NOT EDIT THESE LINES
if ($setflags)
	$updateset[] = 'flags = (flags | '.$setflags.')';
if ($clrflags)
	$updateset[] = 'flags = (flags & ~'.$clrflags.')';

if ($add_chans)
	$updateset[] = 'chans = (chans | '.$add_chans.')';
if ($rem_chans)
	$updateset[] = 'chans = (chans & ~'.$rem_chans.')';

if ($setflags || $clrflags)
	bt_mem_caching::remove_passkey(bt_user::$current['passkey']);

////////////////////////////

mysql_query('UPDATE users SET '.implode(',', $updateset) . ' WHERE id = '.bt_user::$current['id']) or sqlerr(__FILE__,__LINE__);
bt_user::comit_mod_comments();

header('Location: '.bt_vars::$base_url.'/my.php?edited=1'.$urladd);
?>
