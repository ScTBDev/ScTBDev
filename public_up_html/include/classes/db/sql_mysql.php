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

require_once(__DIR__.DIRECTORY_SEPARATOR.'sql.php');

class sql_database_mysql extends sql_database {
	public function __destruct() {
		parent::__destruct();
	}

	public function connect() {
		if ($this->db)
			$this->close();

		$flags = 0;
		if ($this->_compress)
			$flags |= MYSQL_CLIENT_COMPRESS;
		if ($this->_ssl)
			$flags |= MYSQL_CLIENT_SSL;

		if ($this->_persistent)
			$this->db = mysql_pconnect($this->_host, $this->_user, $this->_pass, $flags);
		else
			$this->db = mysql_connect($this->_host, $this->_user, $this->_pass, true, $flags);

		if (!$this->db) {
			$this->errno = mysql_errno();
			$this->error = $this->errno ? mysql_error() : '';
			trigger_error('Error connecting to MySQL Server in '.__METHOD__.
				($this->errno ? ': '.$this->errno.' ('.$this->error.')' : ''), E_USER_WARNING);
			return false;
		}
		elseif (!mysql_select_db($this->_db, $this->db)) {
			$this->set_error();
			trigger_error('Error seleting MySQL DB in '.__METHOD__.
				($this->errno ? ': '.$this->errno.' ('.$this->error.')' : ''), E_USER_WARNING);
			return false;
		}

		$this->character_set_name = mysql_client_encoding($this->db);
		return true;
	}

	protected function _close() {
		return @mysql_close($this->db);
	}

	public function set_error() {
		$this->errno = mysql_errno($this->db);
		$this->error = $this->errno ? mysql_error($this->db) : '';
	}

	public function ping() {
		$ping = @mysql_ping($this->db);
		$this->set_error();

		if (!$ping) {
			trigger_error('Database link did not reply to ping in '.__METHOD__.
				($this->errno ? ': '.$this->errno.' ('.$this->error.')' : '').' attempting to reconnect', E_USER_WARNING);

			$this->connect();
		}
		return $ping;
	}

	public function query($sql, $buffered = true) {
		$this->query_count++;
		$nowtime = microtime(true);
		$result = $buffered ? @mysql_query($sql, $this->db) : @mysql_unbuffered_query($sql, $this->db);
		$this->query_time += (microtime(true) - $nowtime);

		$this->set_error();
		if (!$result) {
			switch ($this->errno) {
				case 0:
					// no error
					break;
				case 1062:
					// Not an important error, duplicate key
					trigger_error('SQL ERROR in '.__METHOD__.' '.$this->errno.' ('.$this->error.'): '.$sql, E_USER_NOTICE);
					break;
				default:
					trigger_error('SQL ERROR in '.__METHOD__.' '.$this->errno.' ('.$this->error.'): '.$sql, E_USER_WARNING);
					break;
			}

			return false;
		}

		if ($result === true) {
			$this->affected_rows	= @mysql_affected_rows($this->db);
			$this->insert_id		= @mysql_insert_id($this->db);
			return true;
		}
		else {
			$this->affected_rows	= 0;
			$this->insert_id		= 0;
			return new sql_result_mysql($result);
		}
	}

	public function escape_string($string) {
		return mysql_real_escape_string($string, $this->db);
	}

	public function set_charset($charset, $collation = false) {
		$char_set = mysql_set_charset($charset, $this->db);
		$this->set_error();
		if (!$char_set) {
			trigger_error('Unable to set character set to "'.$charset.'" in '.__METHOD__.
				($this->errno ? ': '.$this->errno.' ('.$this->error.')' : ''), E_USER_WARNING);
			return false;
		}
		else {
			$this->character_set_name = mysql_client_encoding($this->db);
			if ($collation) {
				$collation = $this->character_set_name.'_'.$collation;
				$collate = $this->query('SET NAMES '.$this->character_set_name.' COLLATE '.mysql_real_escape_string($collation, $this->db));
				if (!$collate) {
					trigger_error('Unable to set character set collation to "'.$collation.'" in '.__METHOD__.
						($this->errno ? ': '.$this->errno.' ('.$this->error.')' : ''), E_USER_WARNING);
					return false;
				}
			}
		}

		return $char_set;
	}
}

class sql_result_mysql extends sql_result {
	public function __destruct() {
		parent::__destruct();
	}

	public function __construct($result) {
		if (!is_resource($result)) {
			trigger_error('Argument 1 for '.__METHOD__.' must be of type resource, '.
				gettype($result).' given', E_USER_ERROR);
			return;
		}
		if (get_resource_type($result) !== 'mysql result') {
			trigger_error('Argument 1 for '.__METHOD__.' must be resource type "mysql result", '.
				get_resource_type($result).' given', E_USER_ERROR);
			return;
		}

		$this->result	= $result;
		$this->num_rows	= mysql_num_rows($this->result);
    }

	public function fetch_array() {
		return mysql_fetch_array($this->result, MYSQL_BOTH);
	}

	public function fetch_assoc() {
		return mysql_fetch_assoc($this->result);
	}

	public function fetch_row() {
		return mysql_fetch_row($this->result);
	}

	public function fetch_object() {
		return mysql_fetch_object($this->result);
	}

	protected function _free() {
		return @mysql_free_result($this->result);
	}
}
?>
