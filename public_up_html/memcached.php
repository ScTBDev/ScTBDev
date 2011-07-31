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
require_once(CLASS_PATH.'bt_time.php');

bt_loginout::db_connect(true);

header('Content-Type: text/plain');

if (!bt_user::required_class(UC_STAFF))
	die;

$timezone = new DateTimeZone('UTC');
$time = new DateTime('now', $timezone);

bt_memcache::connect();
$stats = bt_memcache::stats();
foreach ($stats as $server => $stat) {
	$time->setTimestamp($stat['time']);
	$usertime = 0 + ''.$stat['rusage_user_seconds'].'.'.$stat['rusage_user_microseconds'];
	$systime = 0 + ''.$stat['rusage_system_seconds'].'.'.$stat['rusage_system_microseconds'];

	echo '-----------------------------------------------------------------'."\n";
	echo 'Server: '.$server.' ('.$stat['version'].' '.$stat['pointer_size'].' bit) PID: '.$stat['pid'].' Threads: '.$stat['threads']."\n";
	echo 'Time: '.$time->format(bt_time::TIME_FORMAT)."\n";
	echo 'Uptime: '.bt_time::format_elapsed_time(bt_vars::$timestamp, bt_vars::$timestamp - $stat['uptime'])."\n";
	echo 'CPU Time: '.round($usertime, 4).'s User / '.round($systime, 4).'s System / '.round($systime + $usertime, 4).'s Total'."\n";
	echo 'Network I/O: '.bt_theme::mksize($stat['bytes_read']).' / '.bt_theme::mksize($stat['bytes_written'])."\n";
	echo 'Network Connections: '.number_format($stat['curr_connections']).' / '.number_format($stat['total_connections'])."\n";
	echo 'Items: '.number_format($stat['curr_items']).' / '.number_format($stat['total_items'])."\n";
	echo 'Size: '.bt_theme::mksize($stat['bytes']).' / '.bt_theme::mksize($stat['limit_maxbytes'])."\n";
	echo 'Gets: '.number_format($stat['cmd_get'])."\n";
	echo 'Sets: '.number_format($stat['cmd_set'])."\n";
	echo 'Hits: '.number_format($stat['get_hits'])."\n";
	echo 'Misses: '.number_format($stat['get_misses'])."\n";
	if ($stat['evictions'])
		echo 'Evictions: '.number_format($stat['evictions'])."\n";
	echo 'Hit Rate: '.round(($stat['get_hits'] / $stat['cmd_get']) * 100, 2).'%'."\n";
}
?>
