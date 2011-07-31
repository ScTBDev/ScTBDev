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
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_security.php');
require_once(CLASS_PATH.'bt_utf8.php');
require_once(CLASS_PATH.'bt_memcache.php');

class bt_sql {
	private static $connected = false;
	private static $DB = NULL;

	public static $errno				= 0;
	public static $affected_rows		= 0;
	public static $insert_id			= 0;
	public static $query_count			= 0;
	public static $query_time			= 0.0;
	public static $error				= '';
	public static $character_set_name	= '';


	public static function connect(&$errno = 0, &$error = '') {
		global $SECRETS;
		if (self::$connected)
			return true;

		if (!defined('NEED_SECRETS'))
			define('NEED_SECRETS', true);
		if (!defined('MYSQL_NEEDED'))
			define('MYSQL_NEEDED', true);

		if (!$SECRETS)
			require_once(SCRTS_PATH.'secrets.php');

		require_once(DBM_PATH.'sql_mysql.php');

		if (!self::$DB || !self::$DB->ping()) {
			self::$DB = new sql_database_mysql($SECRETS['mysql']['host'], $SECRETS['mysql']['user'],
				$SECRETS['mysql']['pass'], $SECRETS['mysql']['db'], $SECRETS['mysql']['persist']);

			if (!self::$DB->connect()) {
				$errno = self::$DB->errno;
				$error = self::$DB->error;
				return false;
			}
			self::$connected = true;
			$key = 'bt_sql:::charsets::utf8mb4';
			$utf8mb4 = bt_memcache::get($key);
			if ($utf8mb4 === bt_memcache::NO_RESULT) {
				$cs = self::$DB->query('SHOW CHARACTER SET WHERE Charset = "utf8mb4"');
				if ($cs->num_rows)
					$utf8mb4 = 1;
				else
					$utf8mb4 = 0;
				$cs->free();

				bt_memcache::add($key, $utf8mb4, 3600);
			}
			
			if ($utf8mb4)
				self::$DB->set_charset('utf8mb4', 'unicode_ci');
			else
				self::$DB->set_charset('utf8', 'unicode_ci');
		}

		self::$errno 				=& self::$DB->errno;
		self::$error				=& self::$DB->error;
		self::$affected_rows		=& self::$DB->affected_rows;
		self::$insert_id			=& self::$DB->insert_id;
		self::$character_set_name	=& self::$DB->character_set_name;
		self::$query_count			=& self::$DB->query_count;
		self::$query_time			=& self::$DB->query_time;

		unset($SECRETS['mysql']);
		return true;
	}

	public static function esc($string) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

		if ($string === NULL)
			return 'NULL';

		// This code is more for development purposes
		if (!bt_utf8::is_utf8($string)) {
			trigger_error('Non UTF-8 string "'.bt_string::b64_encode($string).'" (base64 encoded) given in '.__METHOD__.', please use binary_esc instead', E_USER_WARNING);
			return self::binary_esc($string);
		}
		
		return '\''.self::$DB->escape_string($string).'\'';
	}

	public static function escape($string) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

		return self::$DB->escape_string($string);
	}

	public static function binary_esc($string) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

		if ($string === NULL)
			return 'NULL';

		return 'UNHEX(\''.bt_string::str2hex($string).'\')';
	}

	public static function wildcard_esc($string) {
		return '\''.self::wildcard_escape($string).'\'';
	}

	public static function wildcard_escape($string) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}
		return str_replace(array('%', '_'), array('\\%','\\_'), self::$DB->escape_string($string));
	}

	public static function err($file = '', $line = 0, $html = true) {
		$line = (int)$line;
		if ($html) {
			$error = 'SQL Error'.($file ? ' in <b>'.bt_security::html_safe($file).'</b>'.($line ? ', line <b>'.$line.'</b>' : '') : '').'.<br /><br />'."\n".
				'<b>['.self::$DB->errno.']</b> '.self::$DB->error;
			bt_theme::error('SQL Error', $error, false, 'SQL Error');
		}
		else {
			echo 'SQL Error'.($file ? ' in '.$file.($line ? ', line '.$line : '') : '').'.'."\n\n".'['.self::$DB->errno.'] '.self::$DB->error;
			die;
		}
		die;
	}

	public static function query($sql, $buffered = true) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

		return self::$DB->query($sql, $buffered);
	}
}
?>
