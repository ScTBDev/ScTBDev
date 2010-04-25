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

require_once(__DIR__.DIRECTORY_SEPARATOR.'sql.php');

class sql_database_mysqli extends sql_database {
	public function __destruct() {
		parent::__destruct();
	}

	public function connect() {
		if ($this->db)
			$this->close();

		$this->db = new mysqli($this->_host, $this->_user, $this->_pass, $this->db);

		$this->errno = (int)$this->db->connect_errno;
		if ($this->errno) {
			$this->error = $this->db->connect_error;
			trigger_error('Error connecting to MySQL Server in '.__CLASS__.'::'.__METHOD__.
				': '.$this->errno.' ('.$this->error.')', E_USER_WARNING);
			return false;
		}

		$this->character_set_name = $this->db->character_set_name();
		$this->errno =& $this->db->errno;
		$this->error =& $this->db->error;
		$this->affected_rows =& $this->db->affected_rows;
		$this->insert_id =& $this->db->insert_id;
		return true;
	}

	protected function _close() {
		return @$this->db->close();
	}

	public function ping() {
		$ping = @$this->db->ping();

		if (!$ping) {
			trigger_error('Database link did not reply to ping in '.__CLASS__.'::'.__METHOD__.
				($this->errno ? ': '.$this->errno.' ('.$this->error.')' : '').' attempting to reconnect', E_USER_WARNING);
			$this->connect;
		}
		return $ping;
	}

	public function query($sql, $buffered = true) {
		$this->query_count++;
		$nowtime = microtime(true);
		if ($buffered)
			$result = @$this->db->query($sql);
		else {
			$query = @$this->db->real_query($sql);
			$result = $query ? @$this->db->use_result() : false;			
		}
		$this->query_time += (microtime(true) - $nowtime);

		if (!$result) {
			if ($this->errno) {
				trigger_error('SQL ERROR in '.__CLASS__.'::'.__METHOD__.' '.$this->errno.' ('.$this->error.'): '.$sql, E_USER_WARNING);
				$this->ping();
			}
			return false;
		}

		if ($result === true)
			return true;
		else
			return new sql_result_mysqli($result);
	}

	public function escape_string($string) {
		return $this->db->real_escape_string($string);
	}

	public function set_charset($charset) {
		$char_set = $this->db->set_charset($charset);
		if (!$char_set)
			trigger_error('Unable to set character set to "'.$charset.'" in '.__CLASS__.'::'.__METHOD__.
				($this->errno ? ': '.$this->errno.' ('.$this->error.')' : ''), E_USER_WARNING);
		else
			$this->character_set_name = $this->db->character_set_name();

		return $char_set;
	}
}

class sql_result_mysqli extends sql_result {
	public function __destruct() {
		parent::__destruct();
	}

	public function __construct(mysqli_result $result) {
		$this->result			= $result;
		$this->num_rows			= $this->result->num_rows;
    }

	public function fetch_array() {
		return $this->result->fetch_array(MYSQLI_BOTH);
	}

	public function fetch_assoc() {
		return $this->result->fetch_assoc();
	}

	public function fetch_row() {
		return $this->result->fetch_row();
	}

	public function fetch_object() {
		return $this->result->fetch_object();
	}

	protected function _free() {
		return @$this->result->free();
	}
}
?>
