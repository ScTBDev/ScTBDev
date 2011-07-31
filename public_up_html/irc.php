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

$code = sha1(bt_user::$current['password'].bt_user::$current['passkey']); 

if (!isset($_POST['nickserv'])) {
	bt_theme::head('IRC');
	$ircvars = array(
		'USER_NAME'	=> bt_security::html_safe(bt_user::$current['username']),
		'IRC_KEY'	=> $code,
	);

	echo bt_theme_engine::load_tpl('irc', $ircvars);
	bt_theme::foot();
}
else {
	header('Content-Type: text/plain');
	$scriptvars = array(
		'IRC_PASSWORD'	=> trim($_POST['nickserv']),
		'USER_NAME'		=> bt_user::$current['username'],
		'IRC_KEY'		=> $code,
	);

	$script = bt_theme_engine::load_tpl('irc_script', $scriptvars);
	echo $script;
}
?>
