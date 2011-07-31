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
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_vars.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_hash.php');
require_once(CLASS_PATH.'bt_memcache.php');

class bt_session {
	public $expires = 1800; // task hashs can exist for 30 mins max
	private $dieinvalid = true;

	public function __construct($dieinvalid = true, $expires = 1800) {
		$this->dieinvalid = $dieinvalid ? true : false;

		if (is_int($expires) && $expires > 0)
			$this->expires = $expires;
	}

	private function task_id($task_name, $rand_data) {
		$userid = isset(bt_user::$current['id']) ? bt_user::$current['id'] : 0;

		return 'User_ID:'.$userid.'::Task_Name:'.$task_name.'::IP:'.bt_vars::$realip.':::'.$rand_data;
	}

	private function browser_id($rand_data) {
		return 'User_Agent:'.$_SERVER['HTTP_USER_AGENT'].'::Accept:'.$_SERVER['HTTP_ACCEPT'].'::Accept_Language:'.
			$_SERVER['HTTP_ACCEPT_LANGUAGE'].'::Accept_Encoding:'.$_SERVER['HTTP_ACCEPT_ENCODING'].
			'::Accept_Charset:'.$_SERVER['HTTP_ACCEPT_CHARSET'].':::'.$rand_data;
	}

	public function create($task_name = 'default') {
		$rand_data = bt_string::random(100);
		$form_hash = sha1($task_name.'::'.$rand_data);

		$userid = isset(bt_user::$current['id']) ? bt_user::$current['id'] : 0;

		$task_id = $this->task_id($task_name, $rand_data);
		$browser_id = $this->browser_id($rand_data);

		list($hash1, $hash2) = bt_hash::pick_hash();
		$task_hash = bt_hash::hash($task_id, $hash1, $hash2);
		$browser_hash = bt_hash::hash($browser_id, $hash2, $hash1);

		$task = array(
			'hash'		=> $task_hash,
			'browser'	=> $browser_hash,
			'data'		=> $rand_data
		);

		bt_memcache::connect();

		$key = 'bt_session::sessions:::'.$form_hash;
		bt_memcache::add($key, $task, $this->expires);

		return $form_hash;	// This hash must be passed via a form for extra security (Anti-XSS)
	}

	public function check($form_hash, $task_name = 'default') {
		if (strlen($form_hash) != 40 || !bt_string::is_hex($form_hash)) {
			$this->remove(NULL, 'Invalid Session ID');
			return false;
		}

		bt_memcache::connect();
		$key = 'bt_session::sessions:::'.$form_hash;
		$task = bt_memcache::get($key);
		if ($task === bt_memcache::NO_RESULT) {
			$this->remove(NULL, 'Invalid Session');
			return false;
		}

		$task_id = $this->task_id($task_name, $task['data']);
		if (!bt_hash::verify_hash($task_id, $task['hash'])) {
			$this->remove($form_hash, 'Session Invalidated');
			return false;
		}

		$browser_id = $this->browser_id($task['data']);
		if (!bt_hash::verify_hash($browser_id, $task['browser'])) {
			$this->remove($form_hash, 'Browser Invalidated');
			return false;
		}

		$this->remove($form_hash); // A task can only be used once, after that it must be discarded, and a new task be created
		return true;
	}

	private function remove($form_hash = NULL, $msg = NULL) {
		if (!empty($form_hash)) {
			bt_memcache::connect();
			$key = 'bt_session::sessions:::'.$form_hash;
			bt_memcache::del($key);
		}

		if (!empty($msg) && $this->dieinvalid)
			die($msg);
	}
}
?>
