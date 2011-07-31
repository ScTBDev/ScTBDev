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

class bt_pretime {
	private static $bind_ip = '';
	private static $user = '';
	private static $pass = '';

	private static $host = '';
	private static $key = '';

	public static function get_pretime($rls) {
		return false; // will have to code new precheck based on different db
		$cnopts = array(
			'socket' => array(
				'bindto' => self::$bind_ip.':0',
			),
		);
		$context = @stream_context_create($cnopts);

		$hash = rawurlencode(sha1(self::$key.$rls.self::$key, true));
		$rlsn = rawurlencode(trim($rls));

		$precheck = @file_get_contents('http://'.self::$host.'/rls.php?u='.self::$user.'&p='.self::$pass.'&r='.$rlsn.'&v='.$hash, false, $context);

		if (!$precheck)
			return false;

		$preparts = explode(';', $precheck);
		if (count($preparts) != 7)
			return false;

		$sections = explode(':', $preparts[1], 2);
		$pre = array(
			'release'	=> $preparts[0],
			'section'	=> $sections[0],
			'timeofpre'	=> (int)$preparts[2],
			'pretime'	=> (int)$preparts[6],
		);

		if ($preparts[3])
			$pre['nuke_reason'] = $preparts[3];

		if ($preparts[4] && $preparts[5]) {
			$pre['files'] = (int)$preparts[4];
			$pre['size'] = (float)$preparts[5];
		}

		if ($pre['section'] == 'MP3' && $sections[1])
			$pre['genre'] = $sections[1];

		return $pre;
	}
}
?>
