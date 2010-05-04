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

$apc = PHP_SAPI === 'cli' ? false : extension_loaded('apc');
$loaded = $apc ? apc_load_constants('BITS', true) : false;
if (!$loaded) {
	$bits = array();
	for ($i = 0, $num = PHP_INT_SIZE * 8; $i < $num; $i++)
		$bits['BIT_'.($i + 1)] = 1 << $i;

	if ($apc)
		apc_define_constants('BITS', $bits, true);
	else {
		foreach ($bits as $key => $value)
			define($key, $value);
	}
}
?>
