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

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'defines.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_security.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_vars.php');

$num = isset($_GET['num']) ? (0 + $_GET['num']) : 10;
$strip = isset($_GET['strip']) ? 1 : 0;
$stripun = isset($_GET['stripun']) ? 1 : 0;
$download = isset($_GET['type']) ? $_GET['type'] === 'dl' : false;

if ($num > 100)
	$num = 100;
elseif ($num < 10)
	$num = 10;

$passkey = isset($_GET['passkey']) ? $_GET['passkey'] : '';
if (!$passkey)
	die;

bt_memcache::connect();
$user = bt_mem_caching::get_user_from_passkey($passkey);

if (!$user)
	die;

$last_torrents = bt_mem_caching::get_last_torrents();
$categories = bt_mem_caching::get_cat_list();
$valid_cats = array_keys($categories);
$whereq = array();
if (isset($_GET['c']) && is_array($_GET['c'])) {
	$cats = array();
	foreach ($_GET['c'] as $cat)
		$cats[] = (int)$cat;

	$cats = array_unique($cats);
	$cats = array_intersect($cats, $valid_cats);
	sort($cats, SORT_NUMERIC);

	if (count($cats))
		$whereq[] = 'category IN ('.implode(', ', $cats).')';
	else
		$cats = $valid_cats;
}
else
	$cats = $valid_cats;

// Create a $last_torrent array containing only info about the currently selected categories
$last_torrent = array();
foreach ($cats as $cat)
	$last_torrent[$cat] = $last_torrents[$cat];
ksort($last_torrent, SORT_NUMERIC);


$where = count($whereq) ? ' WHERE '.implode(' AND ', $whereq) : '';

$key = 'rss::cache:::'.hash('ripemd160', $where);
$torrents = bt_memcache::get($key, $cas);

if ($torrents === bt_memcache::NO_RESULT || $torrents['last'] != $last_torrent) {
	$torrents = array();
	$torrents['last'] = $last_torrent;
	$torrents['list'] = array();

	bt_sql::connect();
	$torrentsq = bt_sql::query('SELECT id, name, descr, category, filename, added FROM torrents'.$where.' ORDER BY id DESC LIMIT 100') or bt_sql::err(__FILE__, __LINE__);

	while ($torrent = $torrentsq->fetch_assoc()) {
		$torrent['id'] = (int)$torrent['id'];
		$torrent['added'] = (int)$torrent['added'];
		$torrent['name_strip'] = bt_security::html_safe(strtr($torrent['name'], '._', '  '), true);
		$torrent['name_stripun'] = bt_security::html_safe(strtr($torrent['name'], '_', ' '), true);
		$torrent['name'] = bt_security::html_safe($torrent['name'], true);
		$torrent['filename'] = bt_security::html_safe($torrent['filename'], true);
		$torrent['catname'] = isset($categories[$torrent['category']]) ? $categories[$torrent['category']]['ename'] : 'None';
		$torrent['descr'] = bt_security::html_safe($torrent['descr'], true);
		$torrents['list'][] = $torrent;
	}
	$torrentsq->free();

	bt_memcache::cas($key, $torrents, 10800, $cas);
}


header('Content-Type: application/xml; charset=UTF-8');
if ($download)
	echo <<<HEAD
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="0.91">
	<channel>
		<title>ScT</title>
		<description>ScT RSS DL Feed</description>
		<link>{bt_vars::$base_url}/</link>
		<language>en-US</language>

HEAD;
else
	echo <<<HEAD
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
	<channel>
		<title>ScT</title>
		<description>ScT RSS Feed</description>
		<link>{bt_vars::$base_url}/</link>
		<language>en-US</language>

HEAD;

for ($i = 0; $i < $num; $i++) {
	if (!isset($torrents['list'][$i]))
		break;

	$row = $torrents['list'][$i];
	if ($strip)
		$title = $row['name_strip'];
	elseif ($stripun)
		$title = $row['name_stripun'];
	else
		$title = $row['name'];

	$date = gmdate('D, d M Y H:i:s', $row['added']);

	echo <<<STARTITEM
		<item>
			<title>{$title}</title>
			<pubDate>{$date} +0000</pubDate>

STARTITEM;

	if ($download)
		echo <<<ITEM
			<description>{$row['catname']}</description>
			<link>{bt_vars::$base_url}/download.php/{$row['id']}/{$row['filename']}?passkey={$passkey}</link>

ITEM;
	else
		echo <<<ITEM
			<category>{$row['catname']}</category>
			<description>{$row['descr']}</description>
			<link>{bt_vars::$base_url}/details.php?id={$row['id']}&amp;hit=1</link>

ITEM;

	echo <<<ENDITEM
		</item>

ENDITEM;
}

echo <<<FOOT
	</channel>
</rss>
FOOT;
?>
