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

$lowchars	= '1234567890abcdefghijklmnopqrstuvwxyz';
$upchars	= strtoupper($lowchars);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$class = isset($_GET['class']) ? ((int)0 + $_GET['class']) : -1;
$country = isset($_GET['country']) ? ((int)0 + $_GET['country']) : 0;

if ($class < 0 || !is_valid_user_class($class))
  $class = -1;

if ($search != '' || $class >= 0 || $country > 0) {
	$query = 'username LIKE '.sqlesc('%'.$search.'%').(!bt_user::required_class(UC_STAFF) ? ' AND (flags & '.bt_options::USER_CONFIRMED.')' : '');
	if ($search)
		$q = 'search='.htmlentities($search);
}
else {
	$letter = isset($_GET['letter']) ? trim($_GET['letter']) : '';
	if (strlen($letter) > 1)
		die;

	if ($letter == '' || strpos($lowchars, $letter) === false)
		$letter = '1';
	$query = 'username LIKE "'.$letter.'%"'.(!bt_user::required_class(UC_STAFF) ? ' AND (flags & '.bt_options::USER_CONFIRMED.')' : '');
	$q = 'letter='.$letter;
}

if (bt_user::required_class(UC_STAFF))
	$maxclass = UC_MAX;
elseif (bt_user::required_class(UC_WHORE))
	$maxclass = UC_OVERSEEDER;
else
	$maxclass = UC_WHORE;


if ($class >= 0 && $class <= $maxclass) {
	$query .= ' AND class = '.$class;
	$q .= ($q ? '&amp;' : '').'class='.$class;
}
else
	$query .= ' AND class <= '.$maxclass;
if ($country > 0) {
	$query .= ' AND country = '.$country;
	$q .= ($q ? '&amp;' : '').'country='.$country;
}

bt_theme::head('Users');

$classes = array();
$i = 0;
while ($i <= $maxclass && $c = get_user_class_name($i)) {
	$selected = $class == $i ? bt_theme::$settings['users']['class_list']['selected'] : '';
	$classes[] = sprintf(bt_theme::$settings['users']['class_list']['option'], $i, $selected, $c);
	$i++;
}
$class_list = implode(bt_theme::$settings['users']['class_list']['join'], $classes);

$countries = bt_location::countries();
$countrys = array();
foreach ($countries as $cid => $carr) {
	$selected = $country == $cid ? bt_theme::$settings['users']['country_list']['selected'] : '';
	$countrys[] = sprintf(bt_theme::$settings['users']['country_list']['option'], $cid, $selected, $carr['name']);
}
$country_list = implode(bt_theme::$settings['users']['country_list']['join'], $countrys);

$links = array();
for ($i = 0, $ln = strlen($lowchars); $i < $ln; $i++) {
	$l = $lowchars{$i};
	$L = $upchars{$i};
	if ($l == $letter)
		$links[] = sprintf(bt_theme::$settings['users']['page_links']['no_link'], $L);
	else
		$links[] = sprintf(bt_theme::$settings['users']['page_links']['link'], $l, $L);
}
$page_links = implode(bt_theme::$settings['users']['page_links']['join'], $links);

$perpage = 100;
$res = bt_sql::query('SELECT COUNT(*) FROM users WHERE '.$query) or bt_sql::err(__FILE__,__LINE__);
$arr = $res->fetch_row();
$res->free();
$count = $arr[0];

if ($count) {
	list($pager, $limit) = bt_theme::pager($perpage, $count, '?'.($q ? $q.'&amp;' : ''), bt_theme::PAGER_SHOW_PAGES);

	$res = bt_sql::query('SELECT id, username, added, last_access, country, CAST(flags AS SIGNED) AS flags, class FROM users '.
		'WHERE '.$query.' ORDER BY username '.$limit) or bt_sql::err(__FILE__,__LINE__);
	$num = $res->num_rows;

	if ($num) {
		$users = array();
		while ($arr = $res->fetch_assoc()) {
			$arr['flags'] = (int)$arr['flags'];
			if ($arr['country'] && isset($countries[$arr['country']])) {
				$flagpic = bt_config::$conf['pic_base_url'].'flag/'.$countries[$arr['country']]['flagpic'];
				$flagname = $countries[$arr['country']]['name'];
				$flag = sprintf(bt_theme::$settings['users']['users_list']['flag'], $flagpic, $flagname);
			}
			else
				$flag = '---';

			$user_link = bt_forums::user_link($arr['id'], $arr['username'], $arr['class']);
			$user_stars = bt_forums::user_stars($arr['flags']);

			list($date, $time) = explode(' ', format_time($arr['added']));
			list($ldate, $ltime) = explode(' ', format_time($arr['last_access']));
			$lago = get_elapsed_time($arr['last_access']);

			$classname = get_user_class_name($arr['class']);

			$users[] = sprintf(bt_theme::$settings['users']['users_list']['row'], $user_link, $user_stars, $date, $time, $ldate, $ltime, $lago,
				$classname, $flag);
		}
		$res->free();
	
		$user_rows = implode(bt_theme::$settings['users']['users_list']['join'], $users);
		$users_list = sprintf(bt_theme::$settings['users']['users_list']['table'], $user_rows);
	}
	else {
		$pager = '';
		$users_list = bt_theme::message('Error', 'An error occured', false, true, true);
	}
}
else {
	$pager = '';
	$users_list = bt_theme::message('Error', 'No results', false, true, true);
}

$usersvars = array(
	'CLASS_LIST'	=> $class_list,
	'COUNTRY_LIST'	=> $country_list,
	'PAGE_LINKS'	=> $page_links,
	'PAGER'			=> $pager,
	'USERS_LIST'	=> $users_list,
);

echo bt_theme_engine::load_tpl('users', $usersvars);
bt_theme::foot();
?>
