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

if (isset($_GET['ok']))
	bt_theme::error('Thanks','Thank you for applying for uploader status. If you are eligible/accepted for uploading, you should be contacted shortly.');

$tsettings = bt_theme::$settings['upapp'];
$user = bt_user::$current;


bt_theme::head('Uploader Application');

$ratio = bt_theme::ratio($user['uploaded'], $user['downloaded']);
$uped = bt_theme::mksize($user['uploaded']);
$upday = bt_theme::mksize($user['uploaded'] / ((time() - $user['added']) / 86400));


$cats = bt_mem_caching::get_cat_list();
$catrows = $catentrys = array();
$ncats = count($cats);
$i = 0;

foreach ($cats as $catid => $cat) {
	$catentrys[] = sprintf($tsettings['catlist']['entry'], $catid, $cat['ename']);
	$i++;
	$catsleft = $i % $tsettings['catlist']['per_row'];
	if ($catsleft == 0 || $i == $ncats) {
		$catrows[] = implode($tsettings['catlist']['join'], $catentrys);
		$catentrys = array();
	}
}
$cat_table = implode($tsettings['catlist']['row_join'], $catrows);

$upappvars = array(
	'RATIO'		=> $ratio,
	'UPLD'		=> $uped,
	'DAY'		=> $upday,
	'CATS'		=> $cat_table,
);

echo bt_theme_engine::load_tpl('upapp', $upappvars);

bt_theme::foot();
?>
