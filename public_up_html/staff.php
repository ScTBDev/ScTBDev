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

$countries = bt_location::countries();
$tsettings = bt_theme::$settings['staff'];
$online_time = time() - 300;
bt_theme::head('Staff');


// Search User Database for Staff and display in alphabetical order
$reqflags = bt_options::USER_CONFIRMED | bt_options::USER_ENABLED;
$res = bt_sql::query('SELECT u.id, u.username, u.class, u.last_access, u.country, f.lang, f.helpwith '.
	'FROM users AS u LEFT JOIN firstline AS f ON (f.id = u.id) WHERE u.class >= '.UC_STAFF.' AND '.
	'(u.flags & '.$reqflags.') = '.$reqflags.' ORDER BY u.class DESC, u.username ASC') or bt_sql::err(__FILE__,__LINE__);

$staffs = array();
if ($res->num_rows) {
	while ($row = $res->fetch_assoc()) {
		$id = 0 + $row['id'];
		$class = 0 + $row['class'];
		$username = bt_security::html_safe($row['username']);
		$country = $countries[$row['country']];
		$status = ($row['last_access'] > $online_time ? 'on' : 'off').'line';
		$last_seen = get_elapsed_time($row['last_access']);
		$lang = bt_security::html_safe($row['lang']);
		$help_with = bt_security::html_safe($row['helpwith']);

		if (!isset($staffs[$class]))
			$staffs[$class] = array();

		$uservars = array(
			'STATUS'	=> $status,
			'USER_NAME'	=> $username,
			'FLAG'		=> $country['flagpic'],
			'COUNTRY'	=> $country['name'],
			'LAST_SEEN'	=> $last_seen,
			'ID'		=> $id,
			'LANG'		=> $lang,
			'HELP_WITH'	=> $help_with,
		);
		$staffs[$class][] = bt_theme_engine::load_tpl('staff_list_row', $uservars);
	}
	$res->free();
}

$staff_lists = array();
foreach ($staffs as $class => $staff) {
	$classc = bt_theme::$settings['classes']['colors'][$class];
	$name = bt_user::get_class_name($class);
	$staff_list = implode($tsettings['staff_join'], $staff);

	$staff_listvars = array(
		'CLASS'			=> $classc,
		'NAME'			=> $name,
		'STAFF_LIST'	=> $staff_list,
	);
	$staff_lists[] = bt_theme_engine::load_tpl('staff_list', $staff_listvars);
}

$staff_list = implode($tsettings['staff_list_join'], $staff_lists);


$res = bt_sql::query('SELECT f.id, f.lang, f.helpwith, u.last_access, u.username, u.class, u.country '.
	'FROM firstline AS f JOIN users AS u ON (u.id = f.id) WHERE u.class < '.UC_STAFF.' AND '.
	'(u.flags & '.$reqflags.') = '.$reqflags.' ORDER BY u.username ASC') or bt_sql::err(__FILE__,__LINE__);

$flss = array();
if ($res->num_rows) {
	while ($row = $res->fetch_assoc()) {
		$id = 0 + $row['id'];
		$class = 0 + $row['class'];
		$user_link = bt_forums::user_link($id, $row['username'], $class);
		$country = $countries[$row['country']];
		$status = ($row['last_access'] > $online_time ? 'on' : 'off').'line';
		$last_seen = get_elapsed_time($row['last_access']);
		$lang = bt_security::html_safe($row['lang']);
		$help_with = bt_security::html_safe($row['helpwith']);

		$uservars = array(
			'STATUS'	=> $status,
			'USER_LINK'	=> $user_link,
			'FLAG'		=> $country['flagpic'],
			'COUNTRY'	=> $country['name'],
			'LAST_SEEN'	=> $last_seen,
			'ID'		=> $id,
			'LANG'		=> $lang,
			'HELP_WITH'	=> $help_with,
		);
		$flss[] = bt_theme_engine::load_tpl('staff_fls_row', $uservars);
	}
	$res->free();
}
$fls_list = implode($tsettings['fls_join'], $flss);

$admin_tools = bt_user::required_class(UC_ADMINISTRATOR) ? bt_theme_engine::load_tpl('staff_admin') : '';
$mod_tools = bt_user::required_class(UC_MODERATOR) ? bt_theme_engine::load_tpl('staff_mod') : '';
$staff_tools = bt_user::required_class(UC_STAFF) ? bt_theme_engine::load_tpl('staff_tools') : '';

$staffvars = array(
	'STAFF_LISTS'	=> $staff_list,
	'FLS_LIST'		=> $fls_list,
	'ADMIN_TOOLS'	=> $admin_tools,
	'MOD_TOOLS'		=> $mod_tools,
	'STAFF_TOOLS'	=> $staff_tools,
);

echo bt_theme_engine::load_tpl('staff', $staffvars);

bt_theme::foot();
?>
