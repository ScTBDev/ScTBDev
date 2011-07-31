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

bt_loginout::db_connect(false);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $email = strtolower(trim($_POST['email']));
   if (!bt_security::valid_email($email))
     bt_theme::error('Error', 'You must enter a valid email address');
   $res = mysql_query('SELECT * FROM `users` WHERE `email` = '.sqlesc($email)) or sqlerr();
   $arr = mysql_fetch_assoc($res) or bt_theme::error('Error', 'That email address was not found in the database.'."\n");

   $sec = mksecret();

   mysql_query('UPDATE `users` SET `editsecret` = '.sqlesc(bt_string::str2hex($sec)).' WHERE `id` = "'.$arr['id'].'"') or sqlerr();
   if (!mysql_affected_rows())
     bt_theme::error('Error', 'Database error. Please contact an administrator about this.');

   $hash = sha1($sec . $email . $arr['password'] . $sec);

   $body = 'Someone, hopefully you, requested that the password for the account
associated with this email address ('.$email.') be reset.

The request originated from '.$_SERVER['REMOTE_ADDR'].'

If you did not do this ignore this email. Please do not reply.


Should you wish to confirm this request, please follow this link:

'.bt_vars::$base_url.'/recover.php/'.$arr['id'].'/'.$hash.'


After you do this, your password will be reset and emailed back
to you.

--
'.bt_config::$conf['site_name'];

   @mail($arr['email'], bt_config::$conf['site_name'].' password reset confirmation', $body, 'From: '.bt_config::$conf['site_email'])
     or bt_theme::error('Error', 'Unable to send mail. Please contact an administrator about this error.');
   bt_theme::error('Success', 'A confirmation email has been mailed to <b>'.bt_security::html_safe($email).'</b>.'."\n" .
     'Please allow a few minutes for the mail to arrive.');
  }
elseif($_SERVER['PATH_INFO'])
  {
   if (!preg_match('/^\/([0-9]+)\/([0-9a-f]{40})$/', $_SERVER['PATH_INFO'], $matches))
     httperr();

   $id = 0 + $matches[1];
   $sha1 = trim($matches[2]);

   if (!$id)
     bt_theme::error('Error', 'User ID Invalid');

   $res = mysql_query('SELECT `username`, `email`, `password`, `editsecret` FROM `users` WHERE id = "'.$id.'"');
   $arr = mysql_fetch_assoc($res) or bt_theme::error('Error', 'User not found');
   mysql_query('DELETE FROM `sessions` WHERE `uid` = "'.$id.'"');
   $email = strtolower(trim($arr['email']));

   $sec = bt_string::hex2str($arr['editsecret']);
   if (trim($sec) == '')
     bt_theme::error('Error', 'This user has not requested a password reset.');
   if ($sha1 != sha1($sec . $email . $arr['password'] . $sec))
     httperr();

   // generate new password;
   $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

   $newpassword = '';
   for ($i = 0; $i < 14; $i++)
     $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];

	$hash_types = bt_hash::pick_hash();
	$newpasshash = bt_hash::hash($newpassword, $hash_types[0], $hash_types[1], bt_hash::MAX_SALT_LEN, $SECRETS['salt1'], $SECRETS['salt2']);

	mysql_query('UPDATE `users` SET `editsecret` = "", `password` = '.bt_sql::esc($newpasshash).' WHERE `id` = '.$id.' AND `editsecret` = '.sqlesc($arr['editsecret']));

   if (!mysql_affected_rows())
     bt_theme::error('Error', 'Unable to update user data. Please contact an administrator about this error.');

   $body = 'As per your request we have generated a new password for your account.

Here is the information we now have on file for this account:

    User name: '.$arr['username'].'
    Password: '.$newpassword.'

You may login at '.bt_vars::$base_url.'/login.php

--
'.bt_config::$conf['site_name'];

   @mail($email, bt_config::$conf['site_name'].' account details', $body, 'From: '.bt_config::$conf['site_email'])
     or bt_theme::error('Error', 'Unable to send mail. Please contact an administrator about this error.');
   bt_theme::error('Success', 'The new account details have been mailed to <b>'.bt_security::html_safe($email).'</b>'."\n".
     'Please allow a few minutes for the mail to arrive.');
  }
else {
	bt_theme::head();
	$recover = bt_theme_engine::load_tpl('recover');
	echo $recover;
	bt_theme::foot();
}
?>
