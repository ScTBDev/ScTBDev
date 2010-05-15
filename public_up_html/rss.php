<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2010 ScTBDev.ca
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

if ($num > 50)
	$num = 50;
elseif ($num < 5)
	$num = 5;

$passkey = isset($_GET['passkey']) ? $_GET['passkey'] : '';
if (!$passkey)
	die;

$user = bt_mem_caching::get_user_from_passkey($passkey);
if (!$user)
	die;

header('Content-Type: application/xml; charset=UTF-8');
echo <<<HEAD
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
	<channel>
		<title>ScT</title>
		<description>ScT RSS Feed</description>
		<link>{bt_vars::$base_url}/</link>
		<language>en-US</language>

HEAD;

bt_memcache::connect();
$last_torrents = bt_memcache::get('last_torrents');
if (!$last_torrents) {
    $last_torrents = array();
    bt_sql::connect();
    $ltorrentsq = bt_sql::query('SELECT category, MAX(id) AS id FROM torrents GROUP BY category ORDER BY category ASC') or bt_sql::err(__FILE__, __LINE__);
    while ($lt = $ltorrentq->fetch_row())
        $last_torrents[$lt[0]] = (int)$lt[1];

    $ltorrentsq->free();
    bt_memcache::add('last_torrents', $last_torrents, 10800);
}
$last_torrent = max($last_torrents);


$whereq = array();
if (isset($_GET['c']) && is_array($_GET['c'])) {
	$cats = array();
	foreach ($_GET['c'] as $cat)
		$cats[] = (int)$cat;

	$cats = array_unique($cats);
	sort($cats, SORT_NUMERIC);
	$whereq[] = 'category IN ('.implode(', ', $cats).')';
}

$where = count($whereq) ? ' WHERE '.implode(' AND ', $whereq) : '';

$key = 'rss::norm:::'.sha1($where);
$torrents = bt_memcache::get($key);

if ($torrents === false || $torrents['last'] != $last_torrent) {
	$torrents = array();
	$torrents['last'] = $last_torrent;
	$torrents['list'] = array();

	bt_sql::connect();

	$torrentsq = bt_sql::query('SELECT t.id, t.name, t.descr, t.added, c.name AS catname FROM torrents AS t '.
		'LEFT JOIN categories AS c ON (c.id = t.category)'.$where.' ORDER BY t.id DESC LIMIT 50') or bt_sql::err(__FILE__, __LINE__);

	while ($torrent = $torrentsq->fetch_assoc()) {
		$torrent['id'] = 0 + $torrent['id'];
		$torrent['added'] = 0 + $torrent['added'];
		$torrent['name_strip'] = bt_security::html_safe(strtr($torrent['name'], '._', '  '), true);
		$torrent['name_stripun'] = bt_security::html_safe(strtr($torrent['name'], '_', ' '), true);
		$torrent['name'] = bt_security::html_safe($torrent['name'], true);
		$torrent['catname'] = $torrent['catname'] ? bt_security::html_safe($torrent['catname'], true) : 'None';
		$torrent['descr'] = bt_security::html_safe($torrent['descr'], true);
		$torrents['list'][] = $torrent;
	}
	$torrentsq->free();

	bt_memcache::set($key, $torrents, 10800, true);
}


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
	
	echo "\t\t".'<item>'."\n\t\t\t".'<title>'.$title.'</title>'."\n\t\t\t".'<pubDate>'.gmdate('D, d M Y H:i:s', $row['added']).
		' +0000</pubDate>'."\n\t\t\t".'<category>'.$row['catname'].'</category>'."\n\t\t\t".'<link>'.bt_vars::$base_url.
		'/details.php?id='.$row['id'].'&amp;hit=1</link>'."\n\t\t\t".'<description>'.$row['descr'].'</description>'."\n\t\t".
		'</item>'."\n";
}

echo "\t".'</channel>'."\n".'</rss>';
?>
