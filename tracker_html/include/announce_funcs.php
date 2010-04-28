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

require_once(__DIR__.DIRECTORY_SEPARATOR.'tdefines.php');

header('Cache-Control: no-cache, no-store, no-transform, private');
if (!defined('NEED_SECRETS'))
	define('NEED_SECRETS', true);

require_once(CLASS_PATH.'bt_config.php');

require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_bans.php');
require_once(CLASS_PATH.'bt_ip.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_vars.php');
require_once(CLASS_PATH.'bt_tracker.php');

function portblacklisted($port) {
	$blackports = array (
		array(135, 139),	// Winodws
		array(445, 445),	// Netbios
		array(411, 413),	// DC++
		array(6881,6889),	// Bittorrent
		array(1214,1214),	// Kazaa
		array(6346,6347),	// Gnutella
		array(4662,4662),	// eMule
		array(6699,6699),	// WinMX
	);

	foreach ($blackports as $b) {
		if ($port >= $b[0] && $port <= $b[1])
			return true;
	}
	return false;
}

function getclient($httpagent, $peer_id) {
	if(preg_match('/^-U([TM])([0-9]{3})([0-9B])-(..)/s', $peer_id, $matches)) {
		$ver		= (int) $matches[2];
		$vere		= $matches[3];
		$beta		= $vere === 'B';
		$buildnum	= $matches[4];
		$buildvar	= unpack('v*', $buildnum);
		$buildv		= $buildvar[1];

		if ($matches[1] === 'M' || $ver > 180)
			$build = $buildv;
		elseif ($ver < 180)
			$build = $buildv & 16383;
		else {
			if ($beta && $buildv & 49152)
				$build = $buildv & 16383;
			else
				$build = $buildv;
		}

		if ($matches[1] === 'M')
			return "\xB5".'TorrentMac/'.$matches[2]{0}.'.'.$matches[2]{1}.'.'.$matches[2]{2}.' ('.$build.')';
		else
			return "\xB5".'Torrent/'.$matches[2]{0}.'.'.$matches[2]{1}.'.'.$matches[2]{2}.' ('.$build.')';
	}

	if (preg_match('/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)/', $httpagent, $matches))
		return 'Azureus/'.$matches[1];
	if (preg_match('/BitTorrent\\/S-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'Shadows/'.$matches[1];
	if (preg_match('/BitTorrent\\/ABC-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'ABC/'.$matches[1];
	if (preg_match('/ABC-([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'ABC/'.$matches[1];
	if (preg_match('/Rufus\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'Rufus/'.$matches[1];
	if (preg_match('/BitTorrent\\/U-([0-9]+\\.[0-9]+\\.[0-9]+)/', $httpagent, $matches))
		return 'UPnP/'.$matches[1];
	if (preg_match('/^BitTorrent\\/T-(.+)$/', $httpagent, $matches))
		return 'BitTornado/'.$matches[1];
	if (preg_match('/^BitTornado\\/T-(.+)$/', $httpagent, $matches))
		return 'BitTornado/'.$matches[1];
	if (preg_match('/^BitTorrent\\/brst(.+)/', $httpagent, $matches))
		return 'Burst/'.$matches[1];
	if (preg_match('/^RAZA (.+)$/', $httpagent, $matches))
		return 'Shareaza/'.$matches[1];

	// Shareaza 2.2.1.0
	if (preg_match('/^Shareaza ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/', $httpagent, $matches))
		return 'Shareaza/'.$matches[1];
	if (substr($httpagent, 0, 8) == 'MLdonkey')
		return 'MLDonkey/' . substr($httpagent, 9);

	if (preg_match('/^rtorrent\/([0-9]+\.[0-9]+\.[0-9]+)/', $httpagent, $matches))
		return 'rTorrent/'.$matches[1];
	if (preg_match('/^Transmission\/([0-9]+\.[0-9]+)/', $httpagent, $matches))
		return 'Transmission/'.$matches[1];
	if (preg_match('/^Deluge ((?:[0-9](?:\.[0-9]){1,3}))(?:-.+)?$/', $httpagent, $matches))
		return 'Deluge/'.$matches[1];

	
	//Try to figure it out by peer id
	$short_id = substr($peer_id, 1, 2);
	if ($peer_id[0] == 'T')
		return 'BitTornado/' . substr($peer_id, 1, 1) . '.' . substr($peer_id, 2, 1) . '.' . substr($peer_id, 3, 1);
	if (substr($peer_id, 0, 4) == 'exbc' && substr($peer_id, 6, 4) == 'LORD')
		return 'BitLord/' . ord(substr($peer_id, 4, 1)) . '.' . ord(substr($peer_id, 5, 1));
	if ($short_id == 'BC')
		return 'BitComet/' . (0 + substr($peer_id, 3, 2)) . '.' . (0 + substr($peer_id, 5, 2));
	if (substr($peer_id, 0, 4) == 'exbc')
		return 'BitComet/' . ord(substr($peer_id, 4, 1)) . '.' . ord(substr($peer_id, 5, 1));
	if (substr($peer_id, 1, 3) == 'UTB')
		return 'BitComet/' . ord(substr($peer_id, 4, 1)) . '.' . ord(substr($peer_id, 5, 1));
	if (substr($peer_id, 0, 5) == 'Mbrst')
		return 'Burst/' . substr($peer_id, 5, 1) . '.' . substr($peer_id, 7, 1) . '.' . substr($peer_id, 9, 1);
	if (substr($peer_id, 2, 2) == 'BS')
		return 'BitSpirit/' . ord(substr($peer_id, 1, 1)) . '.' . ord(substr($peer_id, 0, 1));
	if (preg_match('/^M([0-9])\-([0-9])\-([0-9])/', $peer_id, $matches))
		return 'Mainline/'.$matches[1].'.'.$matches[2].'.'.$matches[3];
	if ($short_id == 'G3')
		return 'G3 Torrent';
	if ($short_id == 'AR')
		return 'Arctic Torrent';
	if ($short_id == 'KT')
		return 'KTorrent';
	if (substr($peer_id, 1, 3) == 'BOW')
		return 'Bits on Wheels';
	if (substr($peer_id, 0, 3) == 'XBT')
		return 'XBT/'.substr($peer_id, 3, 1).'.'.substr($peer_id, 4, 1).'.'.substr($peer_id, 5, 1);

	//Regular Old Bittorrent
	if (preg_match('/libtorrent/i', $httpagent, $matches))
		return 'LibTorrent';
	if (substr($httpagent, 0, 13) == 'Python-urllib')
		return 'BitTorrent/' . substr($httpagent, 14);
	if (preg_match('/^BitTorrent\\/([0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'BitTorrent/'.$matches[1];
	if (preg_match('/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'BitTorrent/'.$matches[1];
	if (preg_match('/^Python-urllib\\/.+?, BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/', $httpagent, $matches))
		return 'BitTorrent/'.$matches[1];

	return preg_replace('/[^a-zA-z0-9._-]/', '-', $peer_id);
}

function probe_port($ip, $port, $timeout = 5) {
	if (!bt_ip::type($ip, $type))
		return false;

	if (isset(bt_config::$conf['probe_ip'])) {
		$conopts = array(
			'socket'	=> array(
				'bindto'	=> ($type === bt_ip::IP6 ? '['.bt_config::$conf['probe_ip6'].']' : bt_config::$conf['probe_ip']).':0',
			),
		);
	}
	else
		$conopts = array();

	$ip = ($type === bt_ip::IP6 ? '['.$ip.']' : $ip);
	
	$context = @stream_context_create($conopts);
	$res = @stream_socket_client('tcp://'.$ip.':'.$port, $errstr, $errno, $timeout, STREAM_CLIENT_CONNECT, $context);

	if (!$res)
		return false;
	else {
		@fclose($res);
		return true;
	}
}

function verify_keys_and_order($expected, $given, &$unexpected) {
	$keyed_given = array_flip($given);
	$cur_num = 0;
	$unexpected = array();
	$num_given = count($given);
	foreach ($expected as $key => $required) {
		if (!isset($given[$cur_num])) {
			if ($required)
				return false;
			else {
				continue;
			}
		}
		while (!isset($expected[$given[$cur_num]]) && $cur_num < ($num_given - 1)) {
			$unexpected[] = $given[$cur_num];
			$cur_num++;
		}
		$cur_key = $given[$cur_num];
		if ($cur_key === $key)
			$cur_num++;
		elseif ($required)
			return false;
		elseif (isset($keyed_given[$key]))
			return false;
	}

	while ($cur_num < $num_given) {
		$unexpected[] = $given[$cur_num];
		$cur_num++;
	}

	return true;
}
?>
