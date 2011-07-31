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

const SALT_NEEDED = true;
require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_hash.php');

bt_loginout::db_connect(true);

function check_ban($ip) {
	$sip = ip2long($ip);
	$resip = bt_sql::query('SELECT COUNT(*) FROM `signupbans` WHERE `first` <= '.$sip.' AND `last` >= '.$sip) or bt_sql::err(__FILE__, __LINE__);
	$n = $resip->fetch_row();
	$resip->free();
	if ($n[0] > 0)
		return true;
	else
		return false;
}

$id = 0 + $_POST['id'];
$invid = trim($_POST['invite']);

if ($id && $invid) {
	if ($id < 1 || strlen($invid) != 40 || !bt_string::is_hex($invid))
		bt_theme::error('Sorry','Invalid invite');

	$inv = bt_sql::query('SELECT i.inviteid, i.userid, u.class '.
		'FROM invites AS i '.
		'JOIN users AS u ON (u.id = i.userid) '.
		'WHERE i.id = '.$id.' AND (u.flags & '.bt_options::USER_ENABLED.')') or bt_sql::err(__FILE__,__LINE__);

	if ($inv->num_rows) {
		$invite = $inv->fetch_assoc();
		$invhash = sha1(bt_string::hex2str($invite['inviteid']));
		if ($invhash != $invid)
			bt_theme::error('Sorry','Invalid invite');
		$invitedby = 0 + $invite['userid'];
	}
    else
		bt_theme::error('Sorry','Invalid invite or invite expired');
}
elseif (!bt_config::$conf['allow_signups'])
	bt_theme::error('Sorry', 'Sorry, signups are closed');


$ip = bt_ip::get_ip();
$rip = bt_vars::$realip;

if ((check_ban($ip) || bt_bans::check($ip)) || ($ip != $rip && (check_ban($rip) || bt_bans::check($rip))))
	bt_theme::error('Sorry', 'Sorry, signups are closed!');

if (bt_bans::dnsbl_check($rip, $matches) || ($ip != $rip && bt_bans::dnsbl_check($ip, $matches)))
	bark('Sorry, you are not allowed to signup from this location ('.$rip.($ip != $rip ? ' - '.$ip : '').') ['.
		bt_security::html_safe(implode(' - ', $matches)).']');


$res = bt_sql::query('SELECT COUNT(*) FROM `users`') or sqlerr(__FILE__, __LINE__);
$arr = $res->fetch_row();
$res->free();

if ($arr[0] >= bt_config::$conf['maxusers'] && !($invite && $invite['class'] >= UC_STAFF))
	bt_theme::error('Sorry', 'The current user account limit ('.number_format(bt_config::$conf['maxusers']).') has been reached. '.
		'Inactive accounts are pruned all the time, please check back again later...');


$wantusername = trim($_POST['wantusername']);
$wantpassword = $_POST['wantpassword'];
$passagain = $_POST['passagain'];
$email = trim($_POST['email']);

function bark($msg) {
	bt_theme::error('Signup failed!', $msg, true);
}

function validusername($username) {
	if ($username == '')
		return false;

	// The following characters are allowed in user names
	$allowedchars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	for ($i = 0; $i < strlen($username); ++$i)
		if (bt_utf8::strpos($allowedchars, $username[$i]) === false)
			return false;

	return true;
}

$country = 0 + $_POST['country'];

if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($country))
	bark('Don\'t leave any fields blank.');

// Dupe IP check
$ips = $ip === $rip ? '= '.bt_sql::esc($ip) : 'IN ('.bt_sql::esc($ip).', '.bt_sql::esc($rip).')';
$ares = bt_sql::query('SELECT COUNT(*) FROM `users` WHERE `ip` '.$ips.' OR `realip` '.$ips) or bt_sql::err(__FILE__, __LINE__);
$a = $ares->fetch_row();
$ares->free();
if ($a[0] != 0)
	bark('The ip is already in use.');

if (bt_utf8::strlen($wantusername) > 12)
	bark('Sorry, username is too long (max is 12 chars)');

if ($wantpassword != $passagain)
	bark('The passwords didn\'t match! Must\'ve typoed. Try again.');

if (bt_utf8::strlen($wantpassword) < 10)
	bark('Sorry, password is too short (min is 10 chars)');

if (bt_utf8::strlen($wantpassword) > 40)
	bark('Sorry, password is too long (max is 40 chars)');

if ($wantpassword == $wantusername || $wantpassword == strrev($wantusername))
	bark('Sorry, password cannot be same as user name.');

if (!validemail($email))
	bark('That doesn\'t look like a valid email address.');

if (!validusername($wantusername))
	bark('Invalid username.');

// make sure user agrees to everything...
if (!((bool)0 + $_POST['rulesverify']) || !((bool)0 + $_POST['faqverify']) || !((bool)0 + $_POST['ageverify']))
	bt_theme::error('Signup failed', 'Sorry, you\'re not qualified to become a member of this site.');

// check if email addy is already in use
$ares = bt_sql::query('SELECT COUNT(*) FROM `users` WHERE `email` = '.bt_sql::esc($email)) or bt_sql::err(__FILE__, __LINE__);
$a = $ares->fetch_row();
$ares->free();
if ($a[0] != 0)
  bark("The e-mail address $email is already in use.");


$hash_types = bt_hash::pick_hash();
$wantpasshash = bt_hash::hash($wantpassword, $hash_types[0], $hash_types[1], bt_hash::MAX_SALT_LEN, $SECRETS['salt1'], $SECRETS['salt2']);
$editsecret = mksecret();

$passkey = md5($wantusername . time() . $wantpasshash);

$usercheck = bt_sql::query('SELECT COUNT(*) FROM users WHERE username = '.bt_sql::esc($wantusername)) or bt_sql::err(__FILE__,__LINE__);
$check = $usercheck->fetch_row();
$usercheck->free();

if ($check[0])
	bark('Username already exists!');

$ret = bt_sql::query('INSERT INTO `users` (`username`, `password`, `editsecret`, `email`, `country`, `added`, `last_access`, `passkey`'.
	($id ? ', `invitedby`' : '').') VALUES ('.bt_sql::esc($wantusername).', '.bt_sql::esc($wantpasshash).', '.bt_sql::esc(bt_string::str2hex($editsecret)).
	', '.bt_sql::esc($email).', '.$country.', '.time().', '.time().', '.bt_sql::esc($passkey).($id ? ', '.$invitedby : '').')');

if (!$ret) {
	if (bt_sql::$errno == 1062)
		bark('Username already exists!');
	bt_sql::err(__FILE__,__LINE__);
}

$uid = bt_sql::$insert_id;

bt_sql::query('DELETE FROM `invites` WHERE `id` = '.$id) or bt_sql::err(__FILE__,__LINE__);
bt_sql::query('UPDATE `users` SET `uploaded` = (`uploaded` + 262144000) WHERE `id` = '.$invitedby) or bt_sql::err(__FILE__,__LINE__);

$psecret = sha1($editsecret);

$body = 'You have requested a new user account on '.bt_config::$conf['site_name'].' and you have
specified this address ('.$email.') as user contact.

If you did not do this, please ignore this email. The person who entered your
email address had the IP address '.$_SERVER['REMOTE_ADDR'].' Please do not reply.

To confirm your user registration, you have to follow this link:

'.bt_vars::$base_url.'/confirm.php/'.$uid.'/'.$psecret.'

After you do this, you will be able to use your new account. If you fail to
do this, you account will be deleted within a few days. We urge you to read
the RULES and FAQ before you start using '.bt_config::$conf['site_name'];

@mail($email, bt_config::$conf['site_name'].' user registration confirmation', $body, 'From: '.bt_config::$conf['site_email']);
header('Refresh: 0; url=ok.php?type=signup&email='.rawurlencode($email));
die();
?>
