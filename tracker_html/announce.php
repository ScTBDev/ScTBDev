<?
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

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'tdefines.php');

// Configurations vars
require_once(TROOT_PATH.'announce_settings.php');

// Announce specific functions
require_once(TINCL_PATH.'announce_funcs.php');
require_once(TINCL_PATH.'init_vars.php');

// Deny access made with a browser...
if (stripos($agent, 'Mozilla') !== false || stripos($agent, 'Opera') !== false || stripos($agent, 'Links') !== false ||
	stripos($agent, 'Lynx') !== false || stripos($agent, 'Wget') !== false || strpos($peer_id, 'OP') === 0)
		bt_tracker::err('MiSsInG kEy');

if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || isset($_SERVER['HTTP_ACCEPT_CHARSET']) || isset($_SERVER['HTTP_COOKIE'])
	|| isset($_SERVER['HTTP_REFERER']))
	bt_tracker::err('Your client is banned!!!');


if (is_float($uploaded) || is_float($downloaded) || is_float($left))
	bt_tracker::err('Stats gone crazy!');

// Make Sure we have the required vars
foreach(array('info_hash','peer_id','port','uploaded','downloaded','left','passkey') as $x) {
	if (!isset($_GET[$x]))
		bt_tracker::err('missing key');
}

foreach (array('info_hash','peer_id') as $x) {
	if (strlen($$x) != 20)
		bt_tracker::err('invalid '.$x.' (' . strlen($GLOBALS[$x]) . ' - ' . urlencode($GLOBALS[$x]) . ')');
}

if (!$sp_compact)
	bt_tracker::err('Your client does not support the "compact" tracker feature, please change/upgrade your torrent client to one that does.');

// Check for validity of port
if ($port < 0 || $port > 65535)
	bt_tracker::err('invalid port');
if (portblacklisted($port))
	bt_tracker::err('Port '.$port.' is blacklisted.');

$select_bits = $selectnot_bits = $set_bits = $clr_bits = 0;

// Validate all info
$ip = bt_vars::$packed6_ip;
$realip = bt_vars::$packed6_realip;
$ip2 = $ip_4 = $ip_6 = $ipaddr_4 = $ipaddr_6 = $port4 = $port6 = NULL;

if (bt_vars::$ip_type === bt_ip::IP4) {
	$ip_4 = bt_vars::$ip;
	$ipaddr_4 = bt_vars::$packed_ip;
	$port4 = $port;

	if ($ipv6) {
		$addr6 = bt_ip::ip_port($ipv6, $ip_6, $port6, $ip2_type, $addr);
		if ($addr6) {
			if ($ip2_type === bt_ip::IP6) {
				if (!$port6 || portblacklisted($port6))
					$port6 = $port4;

				$ip2 = $addr6;
				$ipaddr_6 = $addr;
			}
		}
	}
}
elseif (bt_vars::$ip_type === bt_ip::IP6) {
	$ip_6 = bt_vars::$ip;
	$ipaddr_6 = bt_vars::$packed_ip;
	$port6 = $port;

	if ($ipv4) {
		$addr6 = bt_ip::ip_port($ipv4, $ip_4, $port4, $ip2_type, $addr);
		if ($addr6) {
			if ($ip2_type === bt_ip::IP4) {
				if (!$port4 || portblacklisted($port4))
					$port4 = $port6;

				$ip2 = $addr6;
				$ipaddr_4 = $addr;
			}
		}
	}
	elseif (bt_vars::$ip2) {
		if (bt_ip::valid_ip(bt_vars::$ip2)) {
			$ip_4 = bt_vars::$ip2;
			$ipaddr_4 = bt_ip::type($ip_4, $ip2_type);
			$ip2 = bt_ip::ip2addr6($ip_4);
			$port4 = $port6;
		}
	}
}

$ip4 = bt_vars::$ip_type === bt_ip::IP4 ? $ip : ($ip2 ? $ip2 : NULL);
$ip6 = bt_vars::$ip_type === bt_ip::IP6 ? $ip : ($ip2 ? $ip2 : NULL);


$hinfo_hash = bt_string::str2hex($info_hash);

if ($uploaded < 0)
	bt_tracker::err('invalid uploaded (less than 0)');
if ($downloaded < 0)
	bt_tracker::err('invalid downloaded (less than 0)');
if ($left < 0)
	bt_tracker::err('invalid left (less than 0)');

// Announce size
$rsize = $numwant === false ? 50 : ($numwant > 0 ? ($numwant > 500 ? 500 : $numwant) : 0);


// Is this peer a seed?
$seeder = $left == 0;

// If peer is a seed, don't send peer list with seeders, save some bandwith.
if ($seeder)
	$selectnot_bits |= bt_options::PEER_SEEDER;

$updatetorrent = $updateuser = $updatesnatched = array();
$user_set_bits = $user_clr_bits = 0;

if (!preg_match('/^[0-9a-f]{32}$/iD', $passkey))
	bt_tracker::err('passkey not valid, please redownload your torrent file');

bt_memcache::connect();

$user = bt_mem_caching::get_user_from_passkey($passkey);
if (!$user)
	bt_tracker::err('Invalid passkey');

$userid = $user['id'];

if (!($user['flags'] & bt_options::USER_BYPASS_BANS)) {
	if (bt_bans::check($realip, false))
		bt_tracker::err('IP Banned');
	elseif ($ip != $realip) {
		if (bt_bans::check($ip, false))
			bt_tracker::err('IP Banned');
	}
}

// Get info about the torrent requested
$torrent = bt_mem_caching::get_torrent_from_hash($hinfo_hash);
if (!$torrent)
	bt_tracker::err('torrent not registered with tracker');

if (!$torrent['pretime'])
	$SETTINGS['dnld_multiplier'] = 0;

// Set torrent details variable
$torrentid = $torrent['id'];

$limit = '';
if (($torrent['seeders'] + $torrent['leechers']) > $rsize)
	$limit = 'ORDER BY RAND() LIMIT '.$rsize;

bt_tracker::dbconnect();
$res = bt_sql::query('SELECT * FROM snatched WHERE torrent = '.$torrentid.' AND user = '.$userid);
if ($res->num_rows) {
	$snatched = $res->fetch_assoc();
	$res->free();

	$snatched['torrent']	= 0 + $snatched['torrent'];
	$snatched['user']		= 0 + $snatched['user'];
	$snatched['start_time']	= 0 + $snatched['start_time'];
	$snatched['time']		= 0 + $snatched['time'];
	$snatched['last_time']	= 0 + $snatched['last_time'];
	$snatched['seed_time']	= 0 + $snatched['seed_time'];
	$snatched['total_time']	= 0 + $snatched['total_time'];
	$snatched['uploaded']	= 0 + $snatched['uploaded'];
	$snatched['downloaded']	= 0 + $snatched['downloaded'];
	$snatched['clientid']	= 0 + $snatched['clientid'];
}

// Part of the sql query that finds this specific peer
$selfwhere = 'torrent = '.$torrentid.' AND peer_id = '.bt_sql::esc($peer_id);

// Is this peer already in the peer list, if so, get information about it
$res = bt_sql::query('SELECT uploaded, downloaded, ip, realip, ip6, realip6, port, port6, to_go, '.
	'last_action, clientid, flags'.((($event == 'stopped' && $seeder) || !$snatched) ?
	', started, finishedat' : '').' FROM peers WHERE '.$selfwhere);

$self = false;
if ($res->num_rows) {
	$self = $res->fetch_assoc();
	$res->free();
}

if ($self) {
	$self['uploaded']		= 0 + $self['uploaded'];
	$self['downloaded']		= 0 + $self['downloaded'];
	$self['to_go']			= 0 + $self['to_go'];
	$self['last_action']	= 0 + $self['last_action'];

	if ($self['port'] !== NULL)
		$self['port']		= 0 + $self['port'];
	if ($self['port6'] !== NULL)
		$self['port6']		= 0 + $self['port6'];

	$self['clientid']		= 0 + $self['clientid'];
	$self['flags']			= (int)$self['flags'];

	$seeding				= (bool)($self['flags'] & bt_options::PEER_SEEDER);
	$probed4				= (bool)($self['flags'] & bt_options::PEER_PROBED4);
	$probed6				= (bool)($self['flags'] & bt_options::PEER_PROBED6);
	$connectable4			= $probed4 ? (($self['flags'] & bt_options::PEER_CONN4) ? bt_tracker::CONN_YES : bt_tracker::CONN_NO) : bt_tracker::CONN_CHECK;
	$connectable6			= $probed6 ? (($self['flags'] & bt_options::PEER_CONN6) ? bt_tracker::CONN_YES : bt_tracker::CONN_NO) : bt_tracker::CONN_CHECK;

	if (isset($self['started'])) {
		$self['started']	= 0 + $self['started'];
		$self['finishedat']	= 0 + $self['finishedat'];
	}

	$updatepeer = array();
}

$ext_ip = NULL;

// Banned clients - Not included in main source yet
#require_once(TINCL_PATH.'client_bans.php');

//// Up/down stats ////////////////////////////////////////////////////////////

if ($user['class'] < UC_STAFF) {
	if ($snatched) {
		if ($snatched['ip'] !== $ip)
			$updatesnatched[] = 'ip = '.bt_sql::binary_esc($ip);
		if ($snatched['realip'] !== $realip)
			$updatesnatched[] = 'realip = '.bt_sql::binary_esc($realip);

		if ($ip2 && $snatched['ip2'] !== $ip2)
			$updatesnatched[] = 'ip2 = '.bt_sql::binary_esc($ip2);
	}
}

if (!$self) {
	if (bt_config::$conf['maxips']) {
		$ipq = bt_sql::query('SELECT COUNT(DISTINCT realip) FROM peers WHERE userid = '.$userid.' AND realip != '.bt_sql::binary_esc($realip));
		$ipn = $ipq->fetch_row();
		$ipq->free();

		if ($ipn[0] >= bt_config::$conf['maxips'])
			bt_tracker::err('You are already connected from '.bt_config::$conf['maxips'].' locations, this is the limit ('.$userid.')');
	}
	$since = 0;
}
else {
	$upthis = 0 + max(0, $uploaded - $self['uploaded']);
	$downthis = 0 + max(0, $downloaded - $self['downloaded']);

	$upspeed = $downspeed = 0.0;
	$since = bt_vars::$timestamp - $self['last_action'];
	$since = $since < 1 ? 1 : $since;

	if ($upthis > 0) {
		$upspeed = round(($upthis / $since) / 1024, 2);
		$updatesnatched[] = 'uploaded = (uploaded + '.$upthis.')';
		if ($SETTINGS['upld_multiplier'] > 0)
			$updateuser[] = 'uploaded = (uploaded + '.floor($upthis * $SETTINGS['upld_multiplier']).')';
	}
	if ($downthis > 0) {
		$downspeed = round(($downthis / $since) / 1024, 2);
		$updatesnatched[] = 'downloaded = (downloaded + '.$downthis.')';
		if ($SETTINGS['dnld_multiplier'] > 0)
			$updateuser[] = 'downloaded = (downloaded + '.floor($downthis * $SETTINGS['dnld_multiplier']).')';
	}
}

///////////////////////////////////////////////////////////////////////////////
// If the peer has stoped downloading, remove from peer list
if ($event == 'stopped') {
	if ($self) {
		bt_sql::query('DELETE FROM peers WHERE '.$selfwhere);
		if (bt_sql::$affected_rows) {
			if ($seeding) {
				$updatetorrent[] = 'seeders = (seeders - 1)';
				$updateuser[] = 'seeding = (seeding - 1)';

				if ($torrent['seeders'] > 0)
					$torrent['seeders']--;

				bt_mem_caching::adjust_torrent_peers($torrentid, -1, 0, 0);
			}
			else {
				$updatetorrent[] = 'leechers = (leechers - 1)';
				$updateuser[] = 'leeching = (leeching - 1)';

				if ($torrent['leechers'] > 0)
					$torrent['leechers']--;

				bt_mem_caching::adjust_torrent_peers($torrentid, 0, -1, 0);
			}

			if ($seeding)
				$updatesnatched[] = 'seed_time = (seed_time + '.$since.')';
			$updatesnatched[] = 'total_time = (total_time + '.$since.')';
			$last_action = 'Stop';
		}
	}
}
else {
	// Update number of times completed
	if ($event == 'completed') {
		if ($self && !$seeding) {
			$completed = true;
			$updatetorrent[] = 'times_completed = (times_completed + 1)';
			$torrent['times_completed']++;
			bt_mem_caching::adjust_torrent_peers($torrentid, 0, 0, 1);

			if (!$snatched['time'])
				$updatesnatched[] = 'time = '.bt_vars::$timestamp;
			$last_action = 'Complete';
		}
	}

	if ($self) {
		// Update peer information
		$cur_time = bt_vars::$timestamp;
		if ($self['uploaded'] != $uploaded)
			$updatepeer[] = 'uploaded = '.$uploaded;

		if ($self['downloaded'] != $downloaded)
			$updatepeer[] = 'downloaded = '.$downloaded;

		if ($self['to_go'] != $left)
			$updatepeer[] = 'to_go = '.$left;

		if ($seeding != $seeder) {
			if ($seeder)
				$set_bits = bt_options::PEER_SEEDER;
			else
				$clr_bits = bt_options::PEER_SEEDER;
		}

		if ($completed)
			$updatepeer[]= 'finishedat = '.$cur_time;

		if ($self['last_action'] != $cur_time)
			$updatepeer[]= 'last_action = '.$cur_time;

		if ($self['ip'] != $ip) {
			$updatepeer[] = 'ip = '.bt_sql::$ip;
			$ext_ip = bt_vars::$packed_ip;
			if (bt_vars::$ip_type === bt_ip::IP4) {
				$ext_ip = $ipaddr_4;
				$compactcache = $ipaddr_4.pack('n', $port4);
				$updatepeer[] = 'compact = '.bt_sql::binary_esc($compactcache);
			}
			else {
				$ext_ip = $ipaddr_6;
				$compact6cache = $ipaddr_6.pack('n', $port6);
				$updatepeer[] = 'compact6 = '.bt_sql::binary_esc($compact6cache);
			}
		}

		if ($self['ip2'] != $ip2) {
			$updatepeer[] = 'ip2 = '.bt_sql::binary_esc($ip2);
			if ($ip2) {
				if ($ip2_type === bt_ip::IP6) {
					$compact6cache = $ipaddr_6.pack('n', $port6);
					$updatepeer[] = 'compact6 = '.bt_sql::binary_esc($compact6cache);
				}
				else {
					$compactcache = $ipaddr_4.pack('n', $port4);
					$updatepeer[] = 'compact = '.bt_sql::binary_esc($compactcache);
				}
			}
		}

		if ($self['port'] != $port4)
			$updatepeer[] = 'port = '.$port4;
		if ($self['port6'] != $port6)
			$updatepeer[] = 'port6 = '.$port6;

		if ($self['realip'] != $realip)
			$updatepeer[] = 'realip = '.bt_sql::binary_esc($realip4);

		if ($self['clientid'] != $clientid)
			$updatepeer[] = 'clientid = '.$clientid;

		// Update peer information
		if ($snatched) {
			if ($snatched['clientid'] != $clientid)
				$updatesnatched[] = 'clientid = '.$clientid;
		}


		if ($set_bits)
			$updatepeer[] = 'flags = (flags | '.$set_bits.')';

		if ($clr_bits)
			$updatepeer[] = 'flags = (flags & ~'.$clr_bits.')';

		if (count($updatepeer))
			bt_sql::query('UPDATE peers SET '.implode(', ', $updatepeer).' WHERE '.$selfwhere);

		if (bt_sql::$affected_rows) {
			if ($seeding != $seeder) {
				if ($seeder) {
					$updatetorrent[] = 'seeders = (seeders + 1)';
					$updatetorrent[] = 'leechers = (leechers - 1)';

					$torrent['seeders']++;
					if ($torrent['leechers'] > 0)
						$torrent['leechers']--;

					bt_mem_caching::adjust_torrent_peers($torrentid, 1, -1, 0);

					$updateuser[] = 'seeding = (seeding + 1)';
					$updateuser[] = 'leeching = (leeching - 1)';
				}
 				else {
					$updatetorrent[] = 'seeders = (seeders - 1)';
					$updatetorrent[] = 'leechers = (leechers + 1)';

					$torrent['leechers']++;
					if ($torrent['seeders'])
						$torrent['seeders']--;

					bt_mem_caching::adjust_torrent_peers($torrentid, -1, 1, 0);
					$updateuser[] = 'seeding = (seeding - 1)';
					$updateuser[] = 'leeching = (leeching + 1)';
				}
			}
			else {
				if ($seeder)
					$updatesnatched[] = 'seed_time = (seed_time + '.$since.')';
			}
			$updatesnatched[] = 'total_time = (total_time + '.$since.')';
		}
	}
	else {
		if ($seeder)
			$set_bits |= bt_options::PEER_SEEDER;

		$connectable4 = $connectable6 = bt_tracker::CONN_NO;
		if ($ip4) {
			$connkey = 'conn:::'.bt_ip::ip2hex($ip_4).':'.$port4;
			$conncache = bt_memcache::get($connkey);
			if ($conncache === false) {
				// set checking cache
				if (bt_memcache::add($connkey, bt_tracker::CONN_CHECK, 15)) {
					// Is this peer connectable?
					$sockres = probe_port($ip_4, $port4);
					$where4 = '(ip = '.bt_sql::binary_esc($ip4).' OR ip2 = '.bt_sql::binary_esc($ip4).') AND port = '.$port4.' AND (flags & '.bt_options::PEER_PROBED4.') = 0';

					if (!$sockres) {
						$connectable4 = bt_tracker::CONN_NO;
						bt_sql::query('UPDATE peers SET flags = ((flags | '.bt_options::PEER_PROBED4.') & ~'.bt_options::PEER_CONN4.') WHERE '.$where4);
					}
					else {
						fclose($sockres);
						$connectable4 = bt_tracker::CONN_YES;
						bt_sql::query('UPDATE peers SET flags = (flags | '.(bt_options::PEER_PROBED4 | bt_options::PEER_CONN4).') WHERE '.$where4);
					}

					bt_memcache::set($connkey, $connectable4, ($connectable4 === bt_tracker::CONN_NO ? 900 : 21600));
				}
				else
					$connectable4 = bt_tracker::CONN_CHECK;
			}
			else
				$connectable4 = $conncache;
		}
		if ($ip6 && isset(bt_config::$conf['probe_ip6'])) {
			$connkey = 'conn:::'.bt_ip::ip2hex($ip_6).':'.$port6;
			$conncache = bt_memcache::get($connkey);
			if ($conncache === false) {
				// set checking cache
				if (bt_memcache::add($connkey, bt_tracker::CONN_CHECK, 15)) {
					// Is this peer connectable?
					$sockres = probe_port($ip_6, $port6);
					$where6 = '(ip = '.bt_sql::binary_esc($ip6).' OR ip2 = '.bt_sql::binary_esc($ip6).') AND port6 = '.$port6.' AND (flags & '.bt_options::PEER_PROBED6.') = 0';

					if (!$sockres) {
						$connectable6 = bt_tracker::CONN_NO;
						bt_sql::query('UPDATE peers SET flags = ((flags | '.bt_options::PEER_PROBED6.') & ~'.bt_options::PEER_CONN6.') WHERE '.$where6);
					}
					else {
						fclose($sockres);
						$connectable6 = bt_tracker::CONN_YES;
						bt_sql::query('UPDATE peers SET flags = (flags | '.(bt_options::PEER_PROBED6 | bt_options::PEER_CONN6).') WHERE '.$where6);
					}

					bt_memcache::set($connkey, $connectable6, ($connectable6 === bt_tracker::CONN_NO ? 900 : 21600));
				}
				else
					$connectable6 = bt_tracker::CONN_CHECK;
			}
			else
				$connectable6 = $conncache;
		}

		if ($connectable4 !== bt_tracker::CONN_CHECK) {
			if (!($user['flags'] & bt_options::USER_PROBED))
				$user_set_bits |= bt_options::USER_PROBED;

			$set_bits |= bt_options::PEER_PROBED4;
		}
		if ($connectable6 !== bt_tracker::CONN_CHECK) {
			if (!($user['flags'] & bt_options::USER_PROBED))
				$user_set_bits |= bt_options::USER_PROBED;
			$set_bits |= bt_options::PEER_PROBED6;
		}

		if ($connectable4 === bt_tracker::CONN_YES)
			$set_bits |= bt_options::PEER_CONN4;
		if ($connectable6 === bt_tracker::CONN_YES)
			$set_bits |= bt_options::PEER_CONN6;


		if ($connectable4 === bt_tracker::CONN_YES || $connectable6 === bt_tracker::CONN_YES)
			$user_set_bits |= bt_options::USER_CONNECTABLE;
		elseif ($connectable4 === bt_tracker::CONN_NO && $connectable6 === bt_tracker::CONN_NO)
			$user_clr_bits |= bt_options::USER_CONNECTABLE;


		// Peer Caching
		$ext_ip = bt_vars::$packed_ip;

		$compactcache = $ip4 ? $ipaddr_4.pack('n', $port4) : NULL;
		$compact6cache = $ip6 ? $ipaddr_6.pack('n', $port6) : NULL;

		if ($snatched && $snatched['clientid'] != $clientid)
			$updatesnatched[] = 'clientid = '.$clientid;


		// Add new peer to table
		$ret = bt_sql::query('INSERT INTO peers (torrent, peer_id, ip, ip2, realip, port, port6, uploaded, downloaded, '.
			'to_go, started, last_action, userid, clientid, client, uploadoffset, downloadoffset, flags, compact, compact6) '.
			'VALUES ('.$torrentid.', '.bt_sql::esc($peer_id).', '.bt_sql::binary_escape($ip).', '.bt_sql::binary_escape($ip2).', '.
			bt_sql::binary_escape($realip).', '.$port4.', '.$port6.', '.$uploaded.', '.$downloaded.', '.$left.', '.
			bt_vars::$timestamp.', '.bt_vars::$timestamp.', '.$userid.', '.$clientid.', '.bt_sql::esc($client).', '.$uploaded.', '.
			$downloaded.', '.$set_bits.', '.bt_sql::esc($compactcache).', '.bt_sql::esc($compact6cache).')');

		if (bt_sql::$affected_rows) {
			if ($seeder) {
				$updatetorrent[] = 'seeders = (seeders + 1)';
				$torrent['seeders']++;
				bt_mem_caching::adjust_torrent_peers($torrentid, 1, 0, 0);
				$updateuser[] = 'seeding = (seeding + 1)';
			}
			else {
				$updatetorrent[] = 'leechers = (leechers + 1)';
				$torrent['leechers']++;
				bt_mem_caching::adjust_torrent_peers($torrentid, 0, 1, 0);
				$updateuser[] = 'leeching = (leeching + 1)';
			}
		}
	}
}


if ($user_set_bits)
	$updateuser[] = 'flags = (flags | '.$user_set_bits.')';
if ($user_clr_bits)
	$updateuser[] = 'flags = (flags & ~'.$user_clr_bits.')';

// Update all the stats if they need updating
if (count($updatetorrent))
	bt_sql::query('UPDATE torrents SET ' . join(',', $updatetorrent) . ' WHERE id = '.$torrentid);

if (count($updateuser))
	bt_sql::query('UPDATE users SET ' . join(',', $updateuser) . ' WHERE id = '.$userid);


if ($snatched) {
	if (bt_vars::$timestamp > $snatched['last_time'])
		$updatesnatched[] = 'last_time = '.bt_vars::$timestamp;

	$last_action = $last_action ? $last_action : ($seeder ? 'Seed' : 'Leech');
	if ($snatched['last_action'] != $last_action)
		$updatesnatched[] = 'last_action = "'.$last_action.'"';

	if ($updatesnatched)
		bt_sql::query('UPDATE snatched SET '.join(', ', $updatesnatched).' WHERE torrent = '.$torrentid.' AND user = '.$userid);
}
else {
	$start_time = $self['started'] ? $self['started'] : bt_vars::$timestamp;
	$comp_time = $seeding ? ($self['finishedat'] ? $self['finishedat'] : 0) : 0;
	$seed_time = $self['finishedat'] ? bt_vars::$timestamp - $self['finishedat'] : 0;
	$total_time = $self['started'] ? bt_vars::$timestamp - $self['started'] : 0;
	$last_action = $last_action ? $last_action : 'Start';
	bt_sql::query('INSERT INTO snatched (torrent, user, start_time, time, last_time, seed_time, total_time, '.
		($user['class'] < UC_STAFF ? 'ip, realip, ip6, realip6, ' : '').'clientid, client, last_action) '.
		'VALUES('.$torrentid.', '.$userid.', '.$start_time.', '.$comp_time.', '.bt_vars::$timestamp.', '.$seed_time.', '.$total_time.', '.
		($user['class'] < UC_STAFF ? $ip4.', '.$realip4.', '.bt_sql::esc($ip6).', '.bt_sql::esc($realip6).', ' : '').
		$clientid.', '.bt_sql::esc($client).', "'.$last_action.'")');
}


// Make intervals more random to prevent hitting server too many times simultaineously
// announce_unconn_interval_fuzz
$connectable = ($connectable4 === bt_tracker::CONN_YES || $connectable6 === bt_tracker::CONN_YES);
$ann_int = $connectable ? bt_config::$conf['announce_interval'] :  bt_config::$conf['announce_unconn_interval'];
$ann_min = $connectable ? bt_config::$conf['min_announce_interval'] : bt_config::$conf['min_announce_unconn_interval'];
$ann_fuzz = $connectable ? bt_config::$conf['announce_interval_fuzz'] : bt_config::$conf['announce_unconn_interval_fuzz'];

$interval = mt_rand(($ann_int - $ann_fuzz), ($ann_int + $ann_fuzz));
$min_interval = $ann_min;

// Make peer list
$resp = 'd8:completei'.$torrent['seeders'].'e10:incompletei'.$torrent['leechers'].'e8:intervali'.$interval.
        'e12:min intervali'.$min_interval.'e5:peers';


$columns = array();
if ($ip4)
	$columns[] = 'compact';
if ($ip6)
	$columns[] = 'compact6';

// Get peer list
$peers4 = $peers6 = '';
if ($rsize) {
	$peers_res = bt_sql::query('SELECT '.implode(', ', $columns).', flags FROM peers WHERE torrent = '.$torrentid.($select_bits ? 
		' AND (flags & '.$select_bits.') = '.$select_bits : '').($selectnot_bits ? ' AND (flags & '.$selectnot_bits.') = 0' : '').' '.$limit);

	while ($row = $peers_res->fetch_assoc()) {
		$row['flags'] = (int)$row['flags'];
		if ($row['compact'] && ($row['flags'] & bt_options::PEER_CONN4))
			$peers4 .= $row['compact'];

		if ($row['compact6'] && ($row['flags'] & bt_options::PEER_CONN6))
			$peers6 .= $row['compact6'];
	}
	$peers_res->free();
}

$resp .= strlen($peers4).':'.$peers4.($ip6 ? '6:peers6'.strlen($peers6).':'.$peers6 : '');
if ($ext_ip)
	$resp .= '11:external ip'.strlen($ext_ip).':'.$ext_ip;

if ($warn)
	$resp .= '15:warning message'.strlen($warn).':'.$warn;

$resp .=  'e';


header('Content-Type: text/plain');
echo $resp;
?>
