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
require_once(CLASS_PATH.'bt_sql.php');

class bt_install {
	public static $config = array();

	public static function sql_value($value) {
		switch ($value) {
			case 'ON':
			case 'YES':
				return true;

			case 'OFF':
			case 'NO':
			case 'DISABLED':
				return false;

			case 'NULL':
				return NULL;

			default:
				if (is_numeric($value)) {
					if ($value > PHP_INT_MIN && $value <= PHP_INT_MAX)
						return (0 + $value);
				}

				return $value;
			break;
		}
		
	}

	public static function get_sql_vars() {
		if (!empty(self::$config))
			return self::$config;

		bt_sql::connect();
		$q = bt_sql::query('SHOW GLOBAL VARIABLES');
		if (!$q)
			return false;

		self::$config = array();
		while ($conf = $q->fetch_row())
			self::$config[$conf[0]] = self::sql_value($conf[1]);

		return self::$config;
	}
}
?>
