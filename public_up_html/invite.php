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
require_once(CLASS_PATH.'bt_location.php');

bt_loginout::db_connect(true);

$cancel = 0 + $_GET['cancel'];
if (is_valid_id($cancel)) {
	bt_sql::query('DELETE FROM `invites` WHERE `id` = '.$cancel.' AND `userid` = '.bt_user::$current['id']) or bt_sql::err(__FILE__,__LINE__);
	if (!bt_sql::$affected_rows)
		bt_theme::error('Error', 'Invite not found');

	bt_sql::query('UPDATE `users` SET `invites` = (`invites` + 1) WHERE `id` = '.bt_user::$current['id']) or bt_sql::err(__FILE__,__LINE__);

	header('Location: '.bt_vars::$base_url.'/invite.php');
	die;
}


$invites = bt_user::$current['invites'];
bt_memcache::connect();
$row = bt_memcache::get('stats:index');
$users = $row['rusers'];

bt_theme::head('Invite');

if ($invites || bt_user::required_class(UC_STAFF)) {
	$box = '';

	if ($users >= (bt_config::$conf['maxusers'] - 25) && !bt_user::required_class(UC_MODERATOR))
		$box .= bt_theme::message('Warning', 'Please note that your invite may not work because the user limit has been '.
			'(or is close to being) reached ('.$users.'/'.bt_config::$conf['maxusers'].').', false, true);

	$boxvars = array(
		'NUM_INVITES'	=> $invites,
	);

	$box .= bt_theme_engine::load_tpl('invite_box', $boxvars);
}
else
	$box = bt_theme::message('Sorry', 'Sorry, but you have no invites left', false, true);


$res = bt_sql::query('SELECT * FROM `invites` WHERE `userid` = '.bt_user::$current['id']) or bt_sql::err(__FILE__,__LINE__);
if ($res->num_rows) {
	$invites = array();
	while ($pend = $res->fetch_assoc()) {
		$id = 0 + $pend['id'];
		$email = bt_security::html_safe($pend['email']);
		list($date, $time) = explode(' ', format_time($pend['added']));
		$invites[] = sprintf(bt_theme::$settings['invite']['pending_row'], $email, $date, $time, $id, bt_theme_engine::$theme_pic_dir);
	}
	$pending_rows = implode("\n", $invites);
	$pendingvars = array(
		'PENDING_ROWS'	=> $pending_rows,
	);

	$pending = bt_theme_engine::load_tpl('invite_pending', $pendingvars);
}
else
	$pending = '';

$res = bt_sql::query('SELECT `id`, `username`, `class`, `added`, `uploaded`, `downloaded`, `comments`, `posts`, `last_access`, `country` '.
	'FROM `users` WHERE `invitedby` = '.bt_user::$current['id'].' ORDER BY `username` ASC') or bt_sql::err(__FILE__,__LINE__);

if ($res->num_rows) {
	$invitees = array();
	while ($conf = $res->fetch_assoc()) {
		$id = 0 + $conf['id'];
		$name = bt_forums::user_link($id, $conf['username'], $conf['class']);
		list($date, $time) = explode(' ', format_time($conf['added']));
		if ($conf['downloaded'] > 0) {
			$ratio = $conf['uploaded'] / $conf['downloaded'];
			$ratio = '<span style="color: '.bt_theme::ratio_color($ratio).'">'.number_format($ratio, 3).'</span>';
		}
		elseif ($conf['uploaded'] > 0)
			$ratio = '&infin;';
		else
			$ratio = '---';

		$country = bt_location::country_by_id($conf['country']);

		$rowvars = array(
			'NAME'		=> $name,
			'UP'		=> bt_theme::mksize($conf['uploaded']),
			'DOWN'		=> bt_theme::mksize($conf['downloaded']),
			'RATIO'		=> $ratio,
			'COMMENTS'	=> number_format($conf['comments']),
			'POSTS'		=> number_format($conf['posts']),
			'DATE'		=> $date,
			'TIME'		=> $time,
			'AGO'		=> get_elapsed_time($conf['added']),
			'SEEN'		=> get_elapsed_time($conf['last_access']),
			'FLAG'		=> $country['flagpic'],
			'COUNTRY'	=> $country['name'],
		);

		$invitees[] = bt_theme_engine::load_tpl('invite_confirmed_row', $rowvars);
	}

	$confirmed_rows = implode("\n", $invitees);
	$confirmedvars = array(
		'CONFIRMED_ROWS'  => $confirmed_rows,
	);

	$confirmed = bt_theme_engine::load_tpl('invite_confirmed', $confirmedvars);
}
else
	$confirmed = '';

$invitevars = array(
	'INVITE_BOX'	=> $box,
	'PENDING'		=> $pending,
	'CONFIRMED'		=> $confirmed,
);
$invite_box = bt_theme_engine::load_tpl('invite', $invitevars);
echo $invite_box;
bt_theme::foot();
?>
