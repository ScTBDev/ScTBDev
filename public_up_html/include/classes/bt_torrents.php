<?php
/*
 *  ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *  Copyright (C) 2005-2011 ScTBDev.ca
 *
 *  This file is part of ScTBDev.
 *
 *  ScTBDev is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  ScTBDev is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');

class bt_torrents {
	private static $db_file = NULL;
	private static $file = 'torrents.sqlite3';
	private static $sqlite = NULL;
	private static $sqlite_write = false;

	private static function connect($write = false) {
		if ($write === self::$sqlite_write && self::$sqlite)
			return true;

		if (!self::$db_file)
			self::$db_file = SQLITE_PATH.self::$file;

		$flags = $write ? SQLITE3_OPEN_READWRITE : SQLITE3_OPEN_READONLY;
		if (self::$sqlite)
			self::$sqlite->close();

		self::$sqlite = new SQLite3(self::$db_file, $flags);
		self::$sqlite_write = (bool)$write;
		self::$sqlite->busyTimeout(60000);

		return (bool)self::$sqlite;
	}

	public static function add_torrent($id, $info_hash, $data) {
		self::connect(true);
		$torrent = self::$sqlite->prepare('INSERT INTO torrent_files (id, info_hash, data) VALUES(:id, :info_hash, :data)');
		$torrent->bindValue('id', $id, SQLITE3_INTEGER);
		$torrent->bindValue('info_hash', $info_hash, SQLITE3_TEXT);
		$torrent->bindValue('data', $data, SQLITE3_BLOB);
		$torrent->execute();
		$torrent->close();

		return (bool)self::$sqlite->changes();
	}

	public static function get_torrent($id) {
		self::connect(false);
		$torrent = self::$sqlite->prepare('SELECT data FROM torrent_files WHERE id = :id');
		$torrent->bindValue('id', $id, SQLITE3_INTEGER);
		$res = $torrent->execute();
		
		$data = $res->fetchArray(SQLITE3_NUM);
		$res->finalize();
		$torrent->close();
		if (!$data)
			return false;

		return $data[0];
	}

	public static function remove_torrent($id) {
		self::connect(true);
		$torrent = self::$sqlite->prepare('DELETE FROM torrent_files WHERE id = :id');
		$torrent->bindValue('id', $id, SQLITE3_INTEGER);
		$torrent->execute();
		$torrent->close();
		self::remove_nfo($id);

		return true;
	}

	public static function add_nfo($id, $nfo, $png) {
		self::connect(true);
		$nfos = self::$sqlite->prepare('INSERT INTO nfo_files (id, nfo, png) VALUES(:id, :nfo, :png)');
		$nfos->bindValue('id', $id, SQLITE3_INTEGER);
		$nfos->bindValue('nfo', $nfo, SQLITE3_BLOB);
		$nfos->bindValue('png', $png, SQLITE3_BLOB);
		$nfos->execute();
		$nfos->close();

		return (bool)self::$sqlite->changes();
	}

	public static function get_nfo($id, $png = false) {
		self::connect(false);
		$nfos = self::$sqlite->prepare('SELECT '.($png ? 'png' : 'nfo').' FROM nfo_files WHERE id = :id');
		$nfos->bindValue('id', $id, SQLITE3_INTEGER);
		$datares = $nfos->execute();
		$data = $datares->fetchArray(SQLITE3_NUM);
		$datares->finalize();
		$nfos->close();
		if (!$data)
			return false;

		return $data[0];
	}

	public static function update_nfo($id, $nfo, $png) {
		self::connect(true);
		$nfos = self::$sqlite->prepare('UPDATE nfo_files SET nfo = :nfo, png = :png WHERE id = :id');
		$nfos->bindValue('id', $id, SQLITE3_INTEGER);
		$nfos->bindValue('nfo', $nfo, SQLITE3_BLOB);
		$nfos->bindValue('png', $png, SQLITE3_BLOB);
		$nfos->execute();
		$nfos->close();
		
		return (bool)self::$sqlite->changes();
	}

	public static function remove_nfo($id) {
		self::connect(true);
		$nfos = self::$sqlite->prepare('DELETE FROM nfo_files WHERE id = :id');
		$nfos->bindValue('id', $id, SQLITE3_INTEGER);
		$nfos->execute();
		$nfos->close();
		
		return true;
	}

	public static function get_torrent_ids() {
		self::connect(false);
		$torrents = self::$sqlite->query('SELECT id FROM torrent_files ORDER BY id DESC');
		$ids = array();
		while ($torrent = $torrents->fetchArray(SQLITE3_NUM)) {
			$ids[] = (int)$torrent[0];
		}
		$torrents->finalize();
		return $ids;
	}
}
?>
