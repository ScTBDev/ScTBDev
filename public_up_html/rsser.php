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

bt_theme::head('RSS Feed Configurator');
$tsettings = bt_theme::$settings['rsser'];
$radio_on = $tsettings['radio_on'];
$check_on = $tsettings['check_on'];


$cats = bt_mem_caching::get_cat_list();

$urls = array();
$urls[] = 'passkey='.bt_user::$current['passkey'];

$selcats = array();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['num']))
		$num = (int)$_POST['num'];
		

	if (isset($_POST['c']) && is_array($_POST['c'])) {
		foreach ($_POST['c'] as $c)
			$selcats[((int)$c)] = true;
	}
}
else {
	foreach ($cats as $catid => $cat) {
		if (strpos(bt_user::$current['notifs'], '[cat'.$catid.']') !== false)
			$selcats[$cat['id']] = true;
	}
	reset($cats);
}
$num = $num ? ($num < 10 ? 10 : ($num > 100 ? 100 : $num)) : 10;
if ($num != 10)
	$urls[] = 'num='.$num;
$feed = $_SERVER['REQUEST_METHOD'] == 'POST' ? (int)$_POST['feed'] : 1;
$urls[] = 'type='.($feed == 2 ? 'dt' : 'dl');
$feed_1 = $feed != 2 ? $radio_on : '';
$feed_2 = $feed == 2 ? $radio_on : '';

$ssl = $_SERVER['REQUEST_METHOD'] == 'POST' ? ((bool)0 + $_POST['ssl']) : ((bt_user::$current['flags'] & bt_options::USER_SSL_SITE) || bt_vars::$ssl);
$ssl_on = $ssl ? $check_on : '';

$catrows = $catentrys = array();
$ncats = count($cats);
$i = 0;

foreach ($cats as $catid => $cat) {
	if (isset($selcats[$catid])) {
		$checked = $check_on;
		$urls[] = 'c[]='.$catid;
	}
	else
		$checked = '';
	$catentrys[] = sprintf($tsettings['entry'], $cat['id'], $checked,  $cat['ename']);
	$i++;
	$catsleft = $i % $tsettings['per_row'];
	if ($catsleft == 0 || $i == $ncats) {
		$catrows[] = implode($tsettings['join'], $catentrys);
		$catentrys = array();
	}
}
$cat_table = implode($tsettings['join_row'], $catrows);

$feed_url = ($ssl ? bt_config::$conf['default_ssl_url'] : bt_config::$conf['default_plain_url']).'/rss.php?'.implode('&amp;', $urls);

$rsservars = array(
	'FEED_URL'	=> $feed_url,
	'CAT_TABLE'	=> $cat_table,
	'FEED_1'	=> $feed_1,
	'FEED_2'	=> $feed_2,
	'SSL_ON'	=> $ssl_on,
	'NUM'		=> $num,
);

echo bt_theme_engine::load_tpl('rsser', $rsservars);

bt_theme::foot();
?>
