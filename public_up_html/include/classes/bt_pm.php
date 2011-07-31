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
require_once(CLASS_PATH.'bt_vars.php');

class bt_pm {
	const OPT_LOC_INBOX		= BIT_1;
	const OPT_LOC_SENTBOX	= BIT_2;
	const OPT_UNREAD		= BIT_3;

	const PM_INBOX = 1;
	const PM_SENTBOX = 2;
	const PM_BOTH = 3;

	public static $locations = array(
		self::PM_INBOX		=> 'in',
		self::PM_SENTBOX 	=> 'out',
		self::PM_BOTH		=> 'both'
	);

	public static function send($from, $to, $msg, $subject = '', $location = self::PM_INBOX) {
		$from = (int)$from;
		$to = (int)$to;
		$emsg = bt_sql::esc(trim($msg));
		$esubject = bt_sql::esc(trim($subject));

		if (!isset(self::$locations[$location]))
			return false;

		$loc = self::$locations[$location];

		$res = bt_sql::query('INSERT INTO messages (sender, receiver, added, msg, subject, location) '.
			'VALUES('.$from.', '.$to.', '.bt_vars::$timestamp.', '.$emsg.', '.$esubject.', "'.$loc.'")');
		if (!bt_sql::$affected_rows)
			return false;


		if ($location == self::PM_INBOX || $location == self::PM_BOTH) {
			bt_sql::query('UPDATE users SET inbox = (inbox + 1), inbox_new = (inbox_new + 1) WHERE id = '.$to);
			if (!bt_sql::$affected_rows)
				return false;
		}

		if ($location == self::PM_SENTBOX || $location == self::PM_BOTH) {
			bt_sql::query('UPDATE users SET sentbox = (sentbox + 1) WHERE id = '.$from);
			if (!bt_sql::$affected_rows)
				return false;
		}

		return true;
	}
}
?>
