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

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(INCL_PATH.'define_bits.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_memcache.php');

class bt_tracker {
	const CONN_NO			= 0;
	const CONN_YES			= 1;
	const CONN_CHECK		= 2;

	// Standard Clients
	const CLIENT_UTORRENT		= 1;
	const CLIENT_RTORRENT		= 2;
	const CLIENT_AZUREUS		= 3;
	const CLIENT_DELUGE			= 4;
	const CLIENT_TRANSMISSION	= 5;
	const CLIENT_BITTORNADO		= 6;
	const CLIENT_MAINLINE		= 7;
	const CLIENT_ENH_CTORRENT	= 8;
	const CLIENT_KTORRENT		= 9;
	const CLIENT_RASTERBAR_LT	= 100;
	const CLIENT_LIBTORRENT		= 101;

	// Banned clients
	const CLIENT_BITCOMET		= 200;
	const CLIENT_OPERA			= 201;
	const CLIENT_ARES			= 202;
	const CLIENT_LIMEWIRE		= 203;
	const CLIENT_BITSPIRIT		= 204;
	const CLIENT_ABC			= 205;

	// Reserved
	const CLIENT_OTHER			= 0;
	const CLIENT_FAKE			= 254;
	const CLIENT_BANNED			= 255;
	

	public static function err($msg) {
		header('Content-Type: text/plain');
		exit('d14:failure reason'.strlen($msg).':'.$msg.'e');
	}

	public static function dbconnect() {
		if (!bt_sql::connect($errno, $error)) {
			switch ($errno) {
				case 1040:
				case 2002:
					header('Content-Type: text/plain');
					die('d8:intervali1400e5:peerslee');
				break;
				default:
					self::err('['.$errno.'] '.$error);
			}
		}
	}

	public static function get_client($peer_id, $agent) {
		list($client, $client_id, $peer_identity, $user_agent) = self::identify_client($peer_id, $agent);
		$key = 'clients::'.sha1($peer_identity).':::'.sha1($user_agent);
		bt_memcache::connect();
		$cache = bt_memcache::get($key);
		if ($cache === bt_memcache::NO_RESULT) {
			bt_sql::connect();
			$pid = bt_sql::esc($peer_identity);
			$usa = bt_sql::esc($user_agent);
			$res = bt_sql::query('SELECT id FROM clients WHERE peer_identity = '.$pid.' AND user_agent = '.$usa);
			if ($res->num_rows) {
				$row = $res->fetch_row();
				$res->free();

				$id = (int)$row[0];
			}
			else {
				bt_sql::query('INSERT INTO clients (client, peer_identity, user_agent) VALUES'.
					'('.bt_sql::esc($client).', '.$pid.', '.$usa.')');
				$id = (int)bt_sql::$insert_id;
			}
			$cache = array($id, $client_id, $client);

			bt_memcache::add($key, $cache, 86400);
		}
		elseif ($cache[1] != $client_id || $cache[2] != $client) {
			$id = $cache[0];
			bt_sql::connect();
			bt_sql::query('UPDATE clients SET client = '.bt_sql::esc($client).' WHERE id = '.$id);
			$cache = array($id, $client_id, $client);
			bt_memcache::set($key, $cache, 86400);
		}

		return $cache;
	}

	public static function identify_client($peer_id, $agent) {
		$microid = $shortid = $version = '';
		$client_id = self::CLIENT_OTHER;
		$b64 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.';
		$valid_chars = $b64.'-_/,*()=:;';

		if (preg_match('#^-([A-Za-z][A-Za-z~])([0-9A-Z]{4})-#', $peer_id, $matches)) {
			$shortid = $matches[1];
			$version_id = $matches[2];
		}
		elseif (preg_match('#^([A-Z])((.)-(?:([0-9]{2})-([0-9])|(.)-(.)-))-#', $peer_id, $matches)) {
			$microid = $matches[1];
			$version_id = $matches[2];
			if (ctype_digit($matches[3]) && ctype_digit($matches[4]) && ctype_digit($matches[5]))
				$version = $matches[3].'.'.$matches[4].'.'.$matches[5];
			elseif (ctype_digit($matches[3]) && ctype_digit($matches[6]) && ctype_digit($matches[7])) {
				$new_format = true;
				$version = $matches[3].'.'.$matches[6].'.'.$matches[7];
			}
			else {
				$new_format = false;
				$version = bt_string::$ord[$matches[3][0]].'.'.bt_string::$ord[$matches[6][0]].'.'.bt_string::$ord[$matches[7][0]];
			}
		}
		elseif (preg_match('#^([A-Z])([0-9A-Za-z.-]{4}-)([0-9A-Za-z.-]{3})#', $peer_id, $matches)) {
			$microid = $matches[1];
			$version_id = $matches[2];
			$version_extra = $matches[3];
			$len = strlen($version_id);
			$versions = array();
			for ($i = 0; $i < $len; $i++) {
				$pos = strpos($b64, $version_id[$i]);
				if ($pos === false)
					break;

				$versions[] = $pos;
			}

			$version = implode('.', $versions);
		}
		elseif (preg_match('#^-([A-Za-z][A-Za-z~])([0-9]{4})[^-]#', $peer_id, $matches)) {
			$shortid = $matches[1];
			$version_id = $matches[2];
		}
		else {
			$zero2 = $peer_id[0].$peer_id[1];
			$two2 = $peer_id[2].$peer_id[3];
			$zero4 = $zero2.$two2;
			$one3 = $peer_id[1].$two2;
		}
		

		if ($shortid === 'UT' || $shortid === 'UM' || strpos($agent, 'uTorrent') === 0) {
			$am			= preg_match('#^uTorrent(Mac)?/([0-9]{3}[0B])(?:\\(([0-9]+)\\))?#', $agent, $amat);
			if (in_array($shortid, array('BC'), true)) {
				$peer_id	= substr($peer_id, 0, 8);
				$client		= 'BitComet Spoof';
				$client_id	= self::CLIENT_FAKE;
			}
			else {
				$mac		= ($shortid === 'UM' || ($am && $amat[1] === 'Mac'));
				$ver		= intval($version_id[0].$version_id[1].$version_id[2]);
				$vere		= $version_id[3];
				$beta		= $vere === 'B';
				$buildnum	= $peer_id[8].$peer_id[9];
				$buildvar	= unpack('v', $buildnum);
				$buildv		= $buildvar[1];

				if ($mac) {
					$buildbvar  = unpack('n', $buildnum);
					$buildbv = $buildbvar[1];

					if (isset($amat[3]) && $amat[3] == $buildbv)
						$build = $buildbv;
					else
						$build = $buildv;
				}
				else {
					if ($ver > 180)
						$build = $buildv;
					elseif ($ver < 180)
						$build = $buildv & 16383;
					else {
						if ($beta && $buildv & 49152)
							$build = $buildv & 16383;
						else
							$build = $buildv;
					}
				}

				$peer_id	= substr($peer_id, 0, 10);
				if ($am)
					$agent	= $amat[0];
				$client		= "\xB5".'Torrent'.($mac ? 'Mac' : '').'/'.$version_id[0].'.'.$version_id[1].'.'.$version_id[2].
					($beta ? ' Beta' : '').' ('.$build.')';
				$client_id	= self::CLIENT_UTORRENT;
			}
		}

		elseif ($shortid === 'lt' && strpos($agent, 'rtorrent') === 0) {
			$am			= preg_match('#^rtorrent/([0-9]+\\.[0-9]+\\.[0-9]+)/([0-9]+\\.[0-9]+\\.[0-9]+)#', $agent, $amat);
			$peer_id	= substr($peer_id, 0, 8);
			$client		= 'rTorrent'.($am ? $amat[1].' ('.$amat[2].')' : '');
			$agent		= $amat[0];
			$client_id	= self::CLIENT_RTORRENT;
		}

		elseif ($shortid === 'AZ' || strpos($agent, 'Azureus') === 0) {
			$am			= preg_match('#^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)#', $agent, $amat);
			$version	= ctype_digit($version_id) ? $version_id[0].'.'.$version_id[1].'.'.$version_id[2].'.'.$version_id[3] :
				($am ? $amat[1] : '');
			$client		= 'Azureus'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 8);
			if ($am)
				$agent	= $amat[0];

			$client_id	= self::CLIENT_AZUREUS;
		}

		elseif ($shortid === 'DE' || strpos($agent, 'Deluge') === 0) {
			$am			= preg_match('#^Deluge ([0-9]+\\.[0-9]+\\.[0-9]+(?:\\.[0-9]+)?)#', $agent, $amat);
			$ver		= $version_id[0].$version_id[1].$version_id[2];
			$vere		= (int)$version_id[3];
			$version	= ctype_digit($ver) ? $ver[0].'.'.$ver[1].'.'.$ver[2].($vere ? '.'.$vere : '') : ($am ? $amat[1] : '');
			$client		= 'Deluge'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 8);
			if ($am)
				$agent	= $amat[0];
			$client_id	= self::CLIENT_DELUGE;
		}

		elseif ($shortid === 'TR' || strpos($agent, 'Transmission') === 0) {
			$am			= preg_match('#^Transmission/([0-9])\\.([0-9]{2})\\+?(?: \\(([0-9]+)\\))#', $agent, $amat);
			$major		= (int) $version_id[0];
			$minor		= (int) $version_id[1].$version_id[2];
			$plus		= $version_id[3] === 'Z';
			$build		= $am ? 0 + $amat[3] : 0;
			$version	= $major.'.'.str_pad($minor, 2, '0', STR_PAD_LEFT).($plus ? '+' : '').($build ? ' ('.$build.')' : '');
			$client		= 'Transmission '.$version;
			$peer_id	= substr($peer_id, 0, 8);
			if ($am)
				$agent	= $amat[0];
			$client_id	= self::CLIENT_TRANSMISSION;
		}

		elseif ($shortid === 'CD' || strpos($agent, 'Enhanced-CTorrent') === 0) {
			$am			= preg_match('#^Enhanced-CTorrent/dnh([0-9]\\.[0-9](?:\\.[0-9])?)#', $agent, $amat);
			$peer_id	= substr($peer_id, 0, 8);
			$client		= 'Enhanced CTorrent'.($am ? ' '.$amat[1] : '');
			if ($am)
				$agent		= $amat[0];
			$client_id	= self::CLIENT_ENH_CTORRENT;
		}

		elseif ($shortid === 'KT' || stripos($agent, 'KTorrent') === 0) {
			$am			= preg_match('#^[kK][tT]orrent/((?:[0-9]\\.[0-9])(?:\\.[0-9]|dev))#', $agent, $amat);
			$major		= (int) $version_id[0];
			$minor		= (int) $version_id[1];
			$tiny		= $version_id[2].$version_id[3] == 'DV' ? '-dev' : '.'.((int)$version_id[2]);
			$client		= 'KTorrent '.$major.'.'.$minor.$tiny;
			$peer_id	= substr($peer_id, 0, 8);
			if ($am)
				$agent	= $amat[0];
			$client_id	= self::CLIENT_KTORRENT;
		}

		elseif (($microid === 'T' && count($versions) >= 3) || stripos($agent, 'BitTornado') === 0) {
			$am			= preg_match('#^BitTornado/T-([0-9a-z.]+)#', $agent, $amat);
			$extver		= 'bcdefghijklmnopqrstuvwxyz';
			$nver		= implode('.', array($versions[0], $versions[1], $versions[2]));
			$version	= $nver.(isset($versions[3]) ? $extver[$versions[3]] : '');
			$client		= 'BitTornado '.$version;
			$peer_id	= substr($peer_id, 0, 6);
			if ($am)
				$agent	= $amat[0];
			$client_id	= self::CLIENT_BITTORNADO;
		}

		elseif ($microid === 'M' && preg_match('#^BitTorrent/([0-9.]+)(?:\\(([0-9]+)\\))?#', $agent, $amat)) {
			if ($new_format && version_compare($version, '6.1.0', '>=')) {
				$buildnum	= $peer_id[8].$peer_id[9];
				$buildvar	= unpack('v', $buildnum);
				$build		= $buildvar[1];
				$peer_id	= substr($peer_id, 0, 10);
			}
			else {
				$build		= isset($amat[2]) ? 0 + $amat[2] : 0;
				$peer_id	= substr($peer_id, 0, 8);
			}

			$client		= 'Mainline '.$version.($build ? ' ('.$build.')': '');
			$agent		= $amat[0];
			$client_id	= self::CLIENT_MAINLINE;
		}


		// Unknown Rasterbar libtorrent client
		elseif ($shortid === 'LT') {
			$versions = array();
			for ($i = 0; $i < 3; $i++) {
				$pos = strpos($b64, $version_id[$i]);
				if ($pos === false)
					break;

				$versions[] = $pos;
			}
			$version	= implode('.', $versions);
			$client		= 'Rasterbar libtorrent'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 8);
			$client_id	= self::CLIENT_RASTERBAR_LT;
		}

		// Unknown libTorrent client (rTorrent)
		elseif ($shortid === 'lt') {
			$versions = array();
			for ($i = 0; $i < 3; $i++) {
				$pos = strpos($b64, $version_id[$i]);
				if ($pos === false)
					break;

				$versions[] = $pos;
			}
			$version	= implode('.', $versions);
			$client		= 'libTorrent'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 8);
			$client_id	= self::CLIENT_LIBTORRENT;
		}

		// Banned Clients
		elseif ($shortid === 'BC' || strpos($agent, 'BitComet') === 0) {
			$major		= intval($version_id[0].$version_id[1]);
			$minor		= intval($version_id[2].$version_id[3]);
			$peer_id	= substr($peer_id, 0, 8);
			if (stripos($agent, 'utorrent') !== false) {
				$client		= 'BitComet Spoof';
				$client_id	= self::CLIENT_FAKE;
			}
			else {
				$client		= 'BitComet '.$major.'.'.$minor;
				$client_id	= self::CLIENT_BITCOMET;
			}
		}

		elseif ($shortid === 'AG' || $shortid === 'A~' || strpos($agent, 'Ares') === 0) {
			$version	= ctype_digit($version_id) ? $version_id[0].'.'.$version_id[1].'.'.$version_id[2].'.'.$version_id[3] : '';
			$client		= 'Ares'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 8);
			$client_id	= self::CLIENT_ARES;
		}

		elseif ($shortid === 'LW' || strpos($agent, 'LimeWire') === 0 || strpos($agent, 'Frosty') === 0) {
			$am			= preg_match('#^[A-Za-z0-9_.-]+/([0-9.]+)#', $agent, $amat);
			$version	= $am ? $amat[1] : '';
			$client		= 'LimeWire'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 8);
			if ($am)
				$agent	= $amat[0];
			$client_id	= self::CLIENT_LIMEWIRE;
		}

		elseif ($shortid === 'SP' || strpos($agent, 'BitSpirit') === 0 || strpos($agent, 'BTSP') === 0) {
			$version	= ctype_digit($version_id) ? $version_id[0].'.'.$version_id[1].'.'.$version_id[2].'.'.$version_id[3] : '';
			$client     = 'BitSpirit'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 7);
			$client_id	= self::CLIENT_BITSPIRIT;
		}

		elseif ($microid === 'A' || strpos($agent, 'ABC') === 0) {
			$client		= 'ABC'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 6);
			$client_id	= self::CLIENT_ABC;
		}

		elseif ($zero2 === 'OP' || strpos($agent, 'Opera' !== false)) {
			$am			= preg_match('#Opera/([0-9.]+)#', $agent, $amat);
			$version	= $am ? $amat[1] : '';
			$client		= 'Opera'.($version ? ' '.$version : '');
			$peer_id	= substr($peer_id, 0, 6);
			if ($am)
				$agent	= $amat[0];
			$client_id	= self::CLIENT_OPERA;
		}

		elseif (in_array($shortid, array('AT','AX','BE','BF','BO','BP','BR','BS','BT','BX','CT','DP','EB','ES','FC','FG','FT','hk',
			'HN','LH','LP','MT','NX','OS','PD','QD','QT','RT','SB','SD','SS','st','SZ','TN','TS','TT','UL','VG','WT','WY','XL','XX',
			'XT','ZT'), true) || in_array($microid, array('O','Q','R','S','U'), true) || $zero4 === 'exbc' || $one3 === 'UTB' ||
			$two2 ===  'BS' || strpos($agent, 'FrostWire') !== false || strpos($agent, 'BitTorrent Pro') !== false) {

			$client_id	= self::CLIENT_BANNED;
			$client		= 'Banned';
			$peer_id	= '';
			$agent		= 'Banned';
		}

		// Last Ditch effort, check for general format peer_id's
		elseif ($shortid)
			$client = $peer_id = str_pad('-'.$shortid.$version_id.'-', 20, '-', STR_PAD_RIGHT);
		elseif ($microid)
			$client = $peer_id = str_pad($microid.$version_id, 20, '-', STR_PAD_RIGHT);
		else {
			$client = '';

			for ($i = 0; $i < 20; $i++) {
				$pos = strpos($valid_chars, $peer_id[$i]);
				if ($pos === false)
					break;

				$client .= $peer_id[$i];
			}

			$peer_id = '';
		}


		// Take the identifying part of the peer_id and pad with with - to 20 chars
		$peer_id = str_pad($peer_id, 20, '-', STR_PAD_RIGHT);

		return array($client, $client_id, $peer_id, $agent);
	}
};
?>
