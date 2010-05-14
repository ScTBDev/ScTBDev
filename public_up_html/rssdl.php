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

header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="iso-8859-1" ?>'.
	"\n".'<rss version="0.91">'."\n\t".'<channel>'."\n\t\t".'<title>ScT</title>'."\n\t\t".
	'<description>ScT RSS DL Feed</description>'."\n\t\t".'<link>'.bt_vars::$base_url.'/</link>'."\n\t\t".'<language>en-US</language>'."\n";

bt_memcache::connect();
$last_torrent = bt_memcache::get('last_torrent');
if (!$last_torrent) {
	bt_sql::connect();
	$ltorrentq = bt_sql::query('SELECT id FROM torrents ORDER BY id DESC LIMIT 1') or bt_sql::err(__FILE__, __LINE__);
	$lt = $ltorrentq->fetch_row();
	$ltorrentq->free();
	$last_torrent = 0 + $lt[0];
	bt_memcache::add('last_torrent', $last_torrent, 10800);
}

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

$key = 'rss::dl:::'.sha1($where);
$torrents = bt_memcache::get($key);

if (!$torrents || $torrents['last'] != $last_torrent) {
	$torrents = array();
	$torrents['last'] = $last_torrent;
	$torrents['list'] = array();

	bt_sql::connect();
	$torrentsq = bt_sql::query('SELECT t.id, t.name, t.filename, t.added, c.name AS catname FROM torrents AS t '.
		'LEFT JOIN categories AS c ON (c.id = t.category)'.$where.' ORDER BY t.id DESC LIMIT 50') or bt_sql::err(__FILE__, __LINE__);

	while ($torrent = $torrentsq->fetch_assoc()) {
		$torrent['id'] = 0 + $torrent['id'];
		$torrent['added'] = 0 + $torrent['added'];
		$torrent['name_strip'] = bt_security::html_safe(strtr($torrent['name'], '._', '  '), true);
		$torrent['name_stripun'] = bt_security::html_safe(strtr($torrent['name'], '_', ' '), true);
		$torrent['name'] = bt_security::html_safe($torrent['name'], true);
		$torrent['filename'] = bt_security::html_safe($torrent['filename'], true);
		$torrent['catname'] = $torrent['catname'] ? bt_security::html_safe($torrent['catname'], true) : 'None';
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

	echo "\t\t".'<item>'."\n\t\t\t".'<title>'.$title.'</title>'."\n\t\t\t".
		'<pubDate>'.gmdate('D, d M Y H:i:s', $row['added']).' +0000</pubDate>'."\n\t\t\t".'<description>'.$row['catname'].
		'</description>'."\n\t\t\t".'<link>'.bt_vars::$base_url.'/download.php/'.$row['id'].'/'.$row['filename'].'?passkey='.$passkey.
		'</link>'."\n\t\t".'</item>'."\n";
}

echo "\t".'</channel>'."\n".'</rss>';
?>
