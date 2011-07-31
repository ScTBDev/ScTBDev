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
require_once(CLASS_PATH.'bt_session.php');
require_once(CLASS_PATH.'bt_hash.php');
require_once(CLASS_PATH.'bt_security.php');
require_once(CLASS_PATH.'bt_dns.php');

bt_loginout::db_connect(false);

$username = trim($_POST['username']);
$password = $_POST['password'];
$form_hash = $_POST['hash'];
$use_ssl = (bool)0 + $_POST['ssl'];

$returnto = isset($_POST['returnto']) ? trim($_POST['returnto']) : '';;
$returnto = stripos($returnto, 'login.php') === false ? $returnto : '';

if (!$username || !$password)
	die();

function add_failure($inc = 1) {
	$hip = bt_ip::ip2hex(bt_vars::$realip);
	$key = 'failed_login:'.$hip;
	
	$num = (int)bt_memcache::get($key);
	$num += $inc;

	bt_memcache::set($key, $num, 21600);

	if ($num >= 5)
		die('Too many failed login attempts, you must now wait 6 hours before attempting to login again.');
}

function bark($text = 'Username or password incorrect') {
	bt_theme::error('Login failed!', $text);
}

if (!$form_hash) {
	add_failure(3);
	die();
}

$session = new bt_session(false, 300);
if (!$session->check($form_hash, 'login')) {
	add_failure(3);
	die('possible automated login attempt, if this is in error, try logging in from an HTTPS connection');
}

$ip = bt_vars::$ip;
$rip = bt_vars::$realip;


$res = bt_sql::query('SELECT id, password, CAST(flags AS SIGNED) AS flags FROM users WHERE username = '. bt_sql::esc($username).
                   ' AND (flags & '.bt_options::USER_CONFIRMED.')') or bt_sql::err(__FILE__,__LINE__);
$row = $res->fetch_assoc();
$res->free();

if (!$row)
	bark();

$row['flags'] = (int)$row['flags'];

$userid = (int)$row['id'];

if (!($row['flags'] & bt_options::USER_BYPASS_BANS)) {
	if (bt_bans::dnsbl_check($rip, $matches))
		$proxy = true;
	elseif ($ip != $rip) {
		if (bt_bans::dnsbl_check($ip, $matches))
			$proxy = true;
	}

	if ($proxy)
		bark('Sorry, you are not allowed to login from this location ('.$rip.($ip != $rip ? ' - '.$ip : '').') ['.
			bt_security::html_safe(implode(' - ', $matches)).']');
}

if (!bt_hash::verify_hash($password, $row['password'], $SECRETS['salt1'], $SECRETS['salt2'])) {
	add_failure(1);

	$uname	= strtolower($username);
	$key	= sha1($uname.':'.$password);
	$key	= 'takelogin::logins:::'.$key;

	$login = bt_memcache::get($key);

	if ($login === bt_memcache::NO_RESULT) {
		$pass_key	= 'bad_logins:passwords';
		$passes = bt_memcache::get($pass_key);
		if ($passes === bt_memcache::NO_RESULT {
			$passes = igbinary_unserialize(file_get_contents('badpasses.bin'));
			bt_memcache::set($pass_key, $passes, 86400);
		}

		if (isset($passes[$uname])) {
			$pwds = $passes[$uname];
			if (in_array($password, $pwds, true)) {
				bt_memcache::add($key, 0, 2592000);
				add_failure(20);
				die();
			}
		}

		bt_memcache::add($key, 1, 2592000);
	}
	elseif (!$login)
		die();

	$realdom = bt_dns::verify_rdns($rip);
	$realaddr = $rip.($realdom ? ' ('.$realdom.')' : '');
	if ($ip != $rip) {
		$dom = bt_dns::verify_rdns($ip);
		$addr = $ip.($dom ? ' ('.$dom.')' : '');
	}

	$msg = '[b]Important[/b]'."\n".
		'Someone (hopefully you) was unsuccessful in trying to login to your account:'."\n".
		($addr ? '[b]IP:[/b] '.$addr."\n" : '').
		'[b]Real IP:[/b] '.$realaddr."\n\n".
		'If this was not you, please do not forward this message to staff unless it persists.'."\n".
		'Best Regards,'."\n".
		'SceneTorrents Staff';

	bt_pm::send(0, $userid, $msg, 'Failed login attempt', bt_pm::PM_INBOX);

	bark();
}
else {
	add_failure(0);
	if (!bt_hash::secure_hash($row['password'])) {
		$hash_types = bt_hash::pick_hash();
		$new_hash = bt_hash::hash($password, $hash_types[0], $hash_types[1], bt_hash::MAX_SALT_LEN, $SECRETS['salt1'], $SECRETS['salt2']);
		if ($new_hash) {
			$ehash = bt_sql::esc($new_hash);
			bt_sql::query('UPDATE `users` SET `password` = '.$ehash.' WHERE `id` = '.$userid);
		}
	}
}

if (!($row['flags'] & bt_options::USER_ENABLED))
	bark('This account has been disabled.');

$ssl_site = (bool)($row['flags'] & bt_options::USER_SSL_SITE);
logincookie($userid, $ssl_site);

$redirectbase = bt_security::redirect_base(($use_ssl || $ssl_site));

if (!empty($returnto) && !$new_hash)
	header('Location: '.$redirectbase.$returnto);
else
	header('Location: '.$redirectbase.'/my.php'.($new_hash ? '?ircch=1' : ''));
?>
