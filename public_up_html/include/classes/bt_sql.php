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
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_security.php');

class bt_sql {
	private static $connected = false;
	public static $DB = NULL;

	public static $errno				= 0;
	public static $affected_rows		= 0;
	public static $insert_id			= 0;
	public static $error				= '';
	public static $character_set_name	= '';

	public static function connect(&$errno = 0, &$error = '', $utf8 = true) {
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

//			if ($utf8)
				self::utf8_on();
//			else
//				self::utf8_off();
		}

		self::$errno 				=& self::$DB->errno;
		self::$error				=& self::$DB->error;
		self::$affected_rows		=& self::$DB->affected_rows;
		self::$insert_id			=& self::$DB->insert_id;
		self::$character_set_name	=& self::$DB->character_set_name;

		unset($SECRETS['mysql']);
		return true;
	}

	public static function esc($string) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}
		
		return '"'.self::$DB->escape_string($string).'"';
	}

	public static function escape($string) {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

		return self::$DB->escape_string($string);
	}

	public static function binary_esc($string) {
		return '0x'.bt_string::str2hex($string);
	}

	public static function wildcard_esc($string) {
		return '"'.self::wildcard_escape($string).'"';
	}

	public static function wildcard_escape($string) {
		return str_replace(array('%', '_'), array('\%','\_'), self::$DB->escape_string($string));
	}

	public static function err($file = '', $line = 0) {
		$line = (int)$line;
		echo '<table border="0" bgcolor="blue" align="left" cellspacing="0" cellpadding="10" style="background: blue">
	<tr>
		<td class="embedded">
			<font color="white"><h1>SQL Error</h1>
			<b>['.self::$DB->errno.']'.self::$DB->error.($file && $line ? '<p>in '.bt_security::html_safe($file).', line '.$line.'</p>' : '').'</b></font>
		</td>
	</tr>
</table>';
		die;
	}

	public static function utf8_on() {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

		if (self::$DB->character_set_name != 'utf8')
			return self::$DB->set_charset('utf8');
	}

	public static function utf8_off() {
		if (!self::$connected) {
			trigger_error('Not connected to SQL server in '.__METHOD__, E_USER_ERROR);
			return false;
		}

//		if (self::$DB->character_set_name != 'binary')
//			return self::$DB->set_charset('binary');
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
