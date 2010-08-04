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

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_bitmask.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_security.php');

class bt_mem_caching {
	const TTL_TIME = 21600;
	const BAD_TTL_TIME = 86400;

	public static function get_torrent_from_hash($info_hash) {
		if (strlen($info_hash) != 40 || !bt_string::is_hex($info_hash))
			return false;

		$key = 'torrents::hash:::'.$info_hash;
		bt_memcache::connect();

		$torrent = bt_memcache::get($key);
		if ($torrent === false) {
			bt_sql::connect();
			$res = bt_sql::query('SELECT id, seeders, leechers, size, piece_length, pretime, times_completed FROM torrents WHERE info_hash = '.bt_sql::esc($info_hash));
			if ($res->num_rows) {
				$torrentq = $res->fetch_assoc();
				$res->free();

				$torrent['id']				= 0 + $torrentq['id'];
				$torrent['size']			= 0 + $torrentq['size'];
				$torrent['piece_length']	= 0 + $torrentq['piece_length'];
				$torrent['pretime']			= 0 + $torrentq['pretime'];

				bt_memcache::add($key, $torrent, self::TTL_TIME);

				$torrent['seeders']			= 0 + $torrentq['seeders'];
				$torrent['leechers']		= 0 + $torrentq['leechers'];
				$torrent['times_completed']	= 0 + $torrentq['times_completed'];

				$seed_key = 'torrents::seeds:::'.$torrent['id']; $leech_key = 'torrents::leechs:::'.$torrent['id']; $comp_key = 'torrents::comps:::'.$torrent['id'];
				bt_memcache::add($seed_key, $torrent['seeders'], self::TTL_TIME);
				bt_memcache::add($leech_key, $torrent['leechers'], self::TTL_TIME);
				bt_memcache::add($comp_key, $torrent['times_completed'], self::TTL_TIME);
			}
			else {
				bt_memcache::add($key, 0, self::BAD_TTL_TIME);
				return false;
			}
		}
		elseif (!$torrent)
			return false;
		else {
			$seed_key = 'torrents::seeds:::'.$torrent['id']; $leech_key = 'torrents::leechs:::'.$torrent['id']; $comp_key = 'torrents::comps:::'.$torrent['id'];
			$torrent['seeders']			= bt_memcache::get($seed_key);
			$torrent['leechers']		= bt_memcache::get($leech_key);
			$torrent['times_completed']	= bt_memcache::get($comp_key);

			if ($torrent['seeders'] === false || $torrent['leechers'] === false || $torrent['times_completed'] === false) {
				bt_sql::connect();
				$res = bt_sql::query('SELECT seeders, leechers, times_completed FROM torrents WHERE id = '.$torrent['id']);
				if ($res->num_rows) {
					$torrentq = $res->fetch_assoc();

					$torrent['seeders']			= 0 + $torrentq['seeders'];
					$torrent['leechers']		= 0 + $torrentq['leechers'];
					$torrent['times_completed']	= 0 + $torrentq['times_completed'];

					bt_memcache::add($seed_key, $torrent['seeders'], self::TTL_TIME);
					bt_memcache::add($leech_key, $torrent['leechers'], self::TTL_TIME);
					bt_memcache::add($comp_key, $torrent['times_completed'], self::TTL_TIME);
				}
				else {
					bt_memcache::del($key);
					return false;
				}
			}
		}

		return $torrent;
	}

	public static function adjust_torrent_peers($id, $seeds = 0, $leechers = 0, $completed = 0) {
		if (!is_int($id) || $id < 1)
			return false;

		if (!$seeds && !$leechers && !$completed)
			return false;

		$adjust = 0;
		$seed_key = 'torrents::seeds:::'.$id; $leech_key = 'torrents::leechs:::'.$id; $comp_key = 'torrents::comps:::'.$id;

		bt_memcache::connect();

		if ($seeds > 0)
			$adjust += (bool)bt_memcache::inc($seed_key, $seeds);
		elseif ($seeds < 0)
			$adjust += (bool)bt_memcache::dec($seed_key, -$seeds);

		if ($leechers > 0)
			$adjust += (bool)bt_memcache::inc($leech_key, $leechers);
		elseif ($leechers < 0)
			$adjust += (bool)bt_memcache::dec($leech_key, -$leechers);

		if ($completed > 0)
			$adjust += (bool)bt_memcache::inc($comp_key, $completed);

		return (bool)$adjust;
	}

	public static function remove_torrent($info_hash) {
		if (strlen($info_hash) != 40 || !bt_string::is_hex($info_hash))
			return false;

		bt_memcache::connect();
		$key = 'torrents::hash:::'.$info_hash;
		$torrent = bt_memcache::get($key);
		if ($torrent === false)
			return false;

		bt_memcache::del($key);

		if (is_array($torrent))
			self::remove_torrent_peers($torrent['id']);

		return true;
	}

	public static function remove_torrent_peers($id) {
		if (!is_int($id) || $id < 1)
			return false;

		$delete = 0;
		$seed_key = 'torrents::seeds:::'.$id; $leech_key = 'torrents::leechs:::'.$id; $comp_key = 'torrents::comps:::'.$id;

		bt_memcache::connect();
		$delete += bt_memcache::del($seed_key, 5); $delete += bt_memcache::del($leech_key, 5); $delete += bt_memcache::del($comp_key, 5);

		return (bool)$delete;
	}

	public static function get_user_from_passkey($passkey) {
		if (strlen($passkey) != 32 || !bt_string::is_hex($passkey))
			return false;

		bt_memcache::connect();

		$key = 'user::passkey:::'.$passkey;
		$user = bt_memcache::get($key);
		if ($user === false) {
			bt_sql::connect();
			$usersql = 'SELECT id, class, flags FROM users WHERE passkey = '.bt_sql::esc($passkey).' AND (flags & '.bt_options::FLAGS_ENABLED.')';
			$userq = bt_sql::query($usersql) or bt_sql::err(__FILE__, __LINE__);
			if (!$userq->num_rows) {
				bt_memcache::add($key, 0, self::BAD_TTL_TIME);
				return false;
			}

			$user = $userq->fetch_assoc();
			$userq->free();

			$user['id'] = (int)$user['id'];
			$user['class'] = (int)$user['class'];
			$user['flags'] = (int)$user['flags'];

			$user['settings'] = bt_bitmask::fetch_all($user['flags']);
			unset($user['flags']);

			bt_memcache::add($key, $user, self::TTL_TIME);
		}
		elseif (!$user)
			return false;

		return $user;
	}

	public static function remove_passkey($passkey, $make_bad = false) {
		if (strlen($passkey) != 32 || !bt_string::is_hex($passkey))
			return false;

		bt_memcache::connect();

		$key = 'user::passkey:::'.$passkey;

		bt_memcache::del($key);
		if ($make_bad)
			bt_memcache::set($key, 0, self::BAD_TTL_TIME);
	}

	public static function get_cat_list() {
		$key = 'categories::cache';
		bt_memcache::connect();
		$cats = bt_memcache::get($key);

		if (!$cats) {
			$cats = array();

			bt_sql::connect();
			$res = bt_sql::query('SELECT * FROM categories ORDER BY name ASC') or bt_sql::err(__FILE__, __LINE__);

			while ($row = $res->fetch_assoc()) {
				$catid = (int)$row['id'];
				$cat = array();
				$cat['name'] = trim($row['name']);
				$cat['image'] = bt_security::html_safe(trim($row['image']));
				$cat['ename'] = bt_security::html_safe($cat['name']);
				$cats[$catid] = $cat;
			}
			$res->free();

			ksort($cats, SORT_NUMERIC);
			bt_memcache::add($key, $cats, self::TTL_TIME);
		}

		return $cats;
	}

	public static function get_last_torrents() {
		bt_memcache::connect();
		$last_torrents = bt_memcache::get('last_torrents');
		if (!$last_torrents) {
			$last_torrents = array();
			bt_sql::connect();
			$ltorrentsq = bt_sql::query('SELECT category, MAX(id) FROM torrents GROUP BY category') or bt_sql::err(__FILE__, __LINE__);
			while ($lt = $ltorrentq->fetch_row())
				$last_torrents[$lt[0]] = (int)$lt[1];
			$ltorrentsq->free();

			ksort($last_torrents, SORT_NUMERIC);
			bt_memcache::add('last_torrents', $last_torrents, 10800);
		}

		return $last_torrents;
	}

	public static function remove_last_torrents() {
		bt_memcache::connect();
		bt_memcache::del('last_torrents');
	}
}
?>
