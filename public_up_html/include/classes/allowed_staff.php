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

class allowed_staff {
	var $log_file = '/home/sct/logs/staff';	// use full path for log file
	var $logging  = true;					// logging of access errors to a log file

	var $staff = array(
		// Leaders:	2 - djGrrr, 6 - Feeling
		// Admins:	2347 - deviant, 8726 - Pedro
		// Mods:	40 - DeevZ, 273 - Terska, 1211 - Eezee
		// FMods:	

		 'forums'	=> array(2, 6, 2347, 8726),										// for adding / editing / deleting forums
		 'news'		=> array(2, 6, 40, 273, 1211, 2347, 8726),						// edit / add / delete news
		 'edituser'	=> array(2, 6, 40, 273, 1211, 2347, 8726),						// editing users
		 'adduser'	=> array(2, 6, 2347, 8726), 									// adduser page
		 'topics'	=> array(2, 6, 40, 273, 1211, 2347, 8726),						// forum mod stuff
		 'default'	=> array(2) 													// if section not specified, only allow Coders
	);

	function check($section = 'default') {
		$userid = bt_user::$current['id'];
		if (!isset($this->staff[$section]))
			die('Invalid Section');
		if (!in_array($userid, $this->staff[$section], true))
			$this->error($section);

		return true;
	}

	function error($section, $error = 'Access Denied') {
		if ($this->logging && is_file($this->log_file) && is_writeable($this->log_file)) {
			$log = $error.' for user '.bt_user::$current['id'].' ('.bt_user::$current['username'].') from '.$_SERVER['REMOTE_ADDR'].' to '.$section."\n";
			$f = fopen($this->log_file, 'a');
			fwrite($f, $log);
			fclose($f);
		}

		die($error);
	}
}
?>
