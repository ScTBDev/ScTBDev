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

abstract class sql_database {
	protected $db = NULL;
	public $query_count = 0;
	public $query_time = 0.0;

	protected $_host = '';
	protected $_user = '';
	protected $_pass = '';
	protected $_db = '';
	protected $_persistent = false;
	protected $_compress = false;
	protected $_ssl = false;

	public $errno				= 0;
	public $affected_rows		= 0;
	public $insert_id			= 0;
	public $error				= '';
	public $character_set_name	= '';

	final public function __construct($server, $user, $pass, $db, $persistent = false, $compress = false, $ssl = false) {
		$this->_host		= $server;
		$this->_user		= $user;
		$this->_pass		= $pass;
		$this->_db			= $db;
		$this->_persistent	= (bool)$persistent;
		$this->_compress	= (bool)$compress;
		$this->_ssl			= (bool)$ssl;
    }

	// function to shutdown result
	public function __destruct() {
		$this->close();
	}

	abstract public function connect();
	abstract public function query($sql);
	abstract public function escape_string($string);
	abstract protected function _close();

	final public function close() {
		if (!$this->_persistent)
			$this->_close();

		$this->db					= NULL;
		$this->character_set_name	= '';
		unset($this->affected_rows);
		unset($this->insert_id);
		unset($this->errno);
		unset($this->error);
		$this->affected_rows		= 0;
		$this->insert_id			= 0;
		$this->errno				= 0;
		$this->error				= '';
	}
}

abstract class sql_result {
	protected $result	= NULL;
	protected $done		= false;

	public $num_rows	= 0;

	public function __construct() {
	}

	// function to shutdown result
	public function __destruct() {
		$this->free();
	}

	final public function free() {
		$this->_free();
		unset($this->result);
		unset($this->num_rows);
		$this->result	= NULL;
		$this->num_rows	= 0;
	}

	// dummy methods
	abstract public function fetch_array();
	abstract public function fetch_assoc();
	abstract public function fetch_row();
	abstract public function fetch_object();
	abstract protected function _free();

	// predefined methods
	final public function close() {
		return $this->free();
	}
	final public function free_result() {
		return $this->free();
	}
}
?>
