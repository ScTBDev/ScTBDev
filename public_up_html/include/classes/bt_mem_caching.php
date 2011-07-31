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
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_security.php');

class bt_mem_caching {
	const TTL_TIME = 21600;
	const BAD_TTL_TIME = 86400;

	public static function get_torrent_from_hash($hinfo_hash) {
		if (strlen($hinfo_hash) !== 40 || !bt_string::is_hex($hinfo_hash))
			return false;
		$info_hash = bt_string::hex2str($hinfo_hash);

		$key = 'torrents::hash:::'.$hinfo_hash;
		bt_memcache::connect();

		$torrent = bt_memcache::get($key);
		if ($torrent === bt_memcache::NO_RESULT) {
			bt_sql::connect();
			$res = bt_sql::query('SELECT id, seeders, leechers, size, piece_length, pretime, times_completed FROM torrents WHERE info_hash = '.bt_sql::binary_esc($info_hash));
			if ($res->num_rows) {
				$torrentq = $res->fetch_assoc();
				$res->free();

				$torrent['id']				= (int)$torrentq['id'];
				$torrent['size']			= (int)$torrentq['size'];
				$torrent['piece_length']	= (int)$torrentq['piece_length'];
				$torrent['pretime']			= (int)$torrentq['pretime'];

				bt_memcache::add($key, $torrent, self::TTL_TIME);

				$torrent['seeders']			= (int)$torrentq['seeders'];
				$torrent['leechers']		= (int)$torrentq['leechers'];
				$torrent['times_completed']	= (int)$torrentq['times_completed'];

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
			$peers = bt_memcache::get(array($seed_key, $leech_key, $comp_key));
			$torrent['seeders']			= $peers[$seed_key];
			$torrent['leechers']		= $peers[$leech_key];
			$torrent['times_completed']	= $peers[$comp_key];

			if ($torrent['seeders'] === bt_memcache::NO_RESULT || $torrent['leechers'] === bt_memcache::NO_RESULT || $torrent['times_completed'] === bt_memcache::NO_RESULT) {
				bt_sql::connect();
				$res = bt_sql::query('SELECT seeders, leechers, times_completed FROM torrents WHERE id = '.$torrent['id']);
				if ($res->num_rows) {
					$torrentq = $res->fetch_assoc();

					$torrent['seeders']			= (int)$torrentq['seeders'];
					$torrent['leechers']		= (int)$torrentq['leechers'];
					$torrent['times_completed']	= (int)$torrentq['times_completed'];

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

	public static function remove_torrent($hinfo_hash) {
		if (strlen($hinfo_hash) != 40 || !bt_string::is_hex($hinfo_hash))
			return false;

		bt_memcache::connect();
		$key = 'torrents::hash:::'.$hinfo_hash;
		$torrent = bt_memcache::get($key);
		if ($torrent === bt_memcache::NO_RESULT)
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
		if ($user === bt_memcache::NO_RESULT) {
			bt_sql::connect();
			$reqflags = bt_options::USER_ENABLED | bt_options::USER_CONFIRMED;
			$usersql = 'SELECT id, class, CAST(flags AS SIGNED) AS flags FROM users WHERE passkey = '.bt_sql::esc($passkey).' AND (flags & '.$reqflags.') = '.$reqflags;
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

		if ($cats === bt_memcache::NO_RESULT) {
			$cats = array();

			bt_sql::connect();
			$res = bt_sql::query('SELECT * FROM categories ORDER BY name ASC') or bt_sql::err(__FILE__, __LINE__);

			while ($row = $res->fetch_assoc()) {
				$catid = (int)$row['id'];
				$cat = array();
				$cat['name'] = bt_utf8::trim($row['name']);
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

	public static function get_genre_list() {
		$key = 'genres::cache';
		bt_memcache::connect();
		$genres = bt_memcache::get($key);

		if ($genres === bt_memcache::NO_RESULT) {
			$genres = array();

			bt_sql::connect();
			$res = bt_sql::query('SELECT * FROM genres ORDER BY name ASC') or bt_sql::err(__FILE__, __LINE__);

			while ($row = $res->fetch_assoc()) {
				$genreid = (int)$row['id'];
				$genre = array();
				$genre['id3'] = (int)$row['id3'];
				$genre['name'] = trim($row['name']);
				$genre['ename'] = bt_security::html_safe($genre['name']);				
			}
		}
	}

	public static function get_last_torrents() {
		bt_memcache::connect();
		$last_torrents = bt_memcache::get('last_torrents');
		if ($last_torrents === bt_memcache::NO_RESULT) {
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
