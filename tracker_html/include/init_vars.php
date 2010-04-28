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

$splitihpk = true;
if (isset($_GET['passkey']) && strlen($_GET['passkey']) != 32) {
	$lenpasskey = strlen($_GET['passkey']);
	if ($lenpasskey > 32 && preg_match('/^([0-9a-f]{32})\?(([0-9a-zA-Z]|_)+)=/', $_GET['passkey'], $matches)) {
		$lenget = strlen($matches[0]);
		$valget = substr($_GET['passkey'], $lenget);
		$_GET[$matches[2]] = $valget;
		$_GET['passkey'] = $matches[1];
		$splitihpk = false;
	}
}

$encoding	= isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
$agent		= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$info_hash	= isset($_GET['info_hash']) ? $_GET['info_hash'] : '';
$peer_id	= isset($_GET['peer_id']) ? $_GET['peer_id'] : '';
$passkey	= isset($_GET['passkey']) ? $_GET['passkey'] : '';
$sp_compact = isset($_GET['compact']) ? true : false;
$compact	= $sp_compact ? (bool)(0 + $_GET['compact']) : false;
$no_peer_id	= isset($_GET['no_peer_id']) ? (bool)(0 + $_GET['no_peer_id']) : false;
$key		= isset($_GET['key']) ? $_GET['key'] : '';
$protocol	= $_SERVER['SERVER_PROTOCOL'];
$ctype		= isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$accept		= isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
$cache		= isset($_SERVER['HTTP_CACHE_CONTROL']) ? $_SERVER['HTTP_CACHE_CONTROL'] : '';
$event		= isset($_GET['event']) ? $_GET['event'] : '';
$connection	= isset($_SERVER['HTTP_CONNECTION']) ? $_SERVER['HTTP_CONNECTION'] : '';
$host		= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$https		= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true : false;
$uploaded	= isset($_GET['uploaded']) ? 0 + $_GET['uploaded'] : 0;
$downloaded	= isset($_GET['downloaded']) ? 0 + $_GET['downloaded'] : 0;
$left		= isset($_GET['left']) ? 0 + $_GET['left'] : 0;
$port 		= isset($_GET['port']) ? 0 + $_GET['port'] : 0;
$ipv4		= isset($_GET['ipv4']) ? $_GET['ipv4'] : '';
$ipv6		= isset($_GET['ipv6']) ? $_GET['ipv6'] : '';

if ($peer_id)
	list($clientid, $client_id, $client) = bt_tracker::get_client($peer_id, $agent);

$ip4		= 0;
$realip4	= 0;
$ip6		= '';
$realip6	= '';
$port4		= 0;
$port6		= 0;

foreach(array('num want', 'numwant', 'num_want') as $k) {
	if (isset($_GET[$k])) {
		$wantnum = $k;
		$numwant = $_GET[$k];
		break;
	}
}
$numwant = isset($wantnum) ? ((int)0 + $numwant) : false;

unset($esctype);
if (preg_match_all('/\%([A-Fa-f0-9]{2})/',$_SERVER['QUERY_STRING'], $cmat)) {
	foreach($cmat[1] as $c) {
		if (!ctype_digit($c)) {
			if (strtoupper($c) == $c) {
				if (!isset($esctype) || $esctype == 'up')
					$esctype = 'up';
				elseif ($esctype == 'low') {
					$esctype = 'mix';
					break;
				}
			}
			elseif (strtolower($c) == $c) {
				if (!isset($esctype) || $esctype == 'low')
					$esctype = 'low';
				elseif ($esctype == 'up') {
					$esctype = 'mix';
					break;
				}
			}
			else {
				$esctype = 'mix';
				break;
			}
		}
	}
}
?>
