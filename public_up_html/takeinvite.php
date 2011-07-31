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

bt_loginout::db_connect(true);

function bark($msg) {
	bt_theme::error('Invite failed!', $msg);
}

if (bt_user::$current['invites'] == 0 && get_user_class() < UC_STAFF)
	bark('Sorry, but you have no invites left');

$email = $_POST['email'];

if (empty($email))
  bark('Don\'t leave any fields blank.');
if (!validemail($email))
  bark('That doesn\'t look like a valid email address.');

$secret = mksecret();
$invid = bt_string::str2hex($secret);
$inviteid = sha1($secret);
mysql_query('INSERT INTO invites (added, userid, inviteid, email) '.
            'VALUES ('.time().', '.bt_user::$current['id'].', "'.$invid.'", '.sqlesc($email).')') or sqlerr(__FILE__,__LINE__);

$id = mysql_insert_id();
if (bt_user::$current['invites'] > 0)
	mysql_query('UPDATE users SET invites = (invites - 1) WHERE id = '.bt_user::$current['id']);

$username = bt_user::$current['username'];
$base_url = bt_vars::$base_url;
$site_name = bt_config::$conf['site_name'];
$body = <<<EOD
You have been invited to get a new user account on $site_name by the user {$username}.

If you do not want this invite, please ignore this email. Please do not reply.

To start your user registration, you have to follow this link:

$base_url/signup.php?id=$id&invite=$inviteid

After you do this, you will be able setup your new account. If you fail to
do this, your invite will expire within a few days, and the invite returned
to the inviting user.
EOD;
mail($email, $site_name.' Invite from '.bt_user::$current['username'], $body, 'From: '.bt_config::$conf['site_email']);

header('Refresh: 0; url=/ok.php?type=invite&email='.urlencode($email));
?>
