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

const REQUIRED_PHP = '5.3.0';

if (!version_compare(PHP_VERSION, REQUIRED_PHP, '>='))
	die('PHP '.REQUIRED_PHP.' or higher is required.');

if (PHP_INT_SIZE < 8)
	die('A 64bit or higher OS + Processor is required.');

if (get_magic_quotes_gpc() || get_magic_quotes_runtime() || get_cfg_var('magic_quotes_sybase'))
	die('PHP is configured incorrectly. Turn off magic quotes.');

if (get_cfg_var('register_long_arrays') || get_cfg_var('register_globals') || get_cfg_var('safe_mode'))
	die('PHP is configured incorrectly. Turn off safe_mode, register_globals and register_long_arrays.');

if (!defined('PHP_INT_MIN'))
	define('PHP_INT_MIN', ~PHP_INT_MAX);

// Site Paths
define('BASE_PATH', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
define('ROOT_PATH', BASE_PATH.'public_up_html'.DIRECTORY_SEPARATOR);
define('INCL_PATH', ROOT_PATH.'include'.DIRECTORY_SEPARATOR);
define('CLASS_PATH', INCL_PATH.'classes'.DIRECTORY_SEPARATOR);
define('DBM_PATH', CLASS_PATH.'db'.DIRECTORY_SEPARATOR);
define('SCRTS_PATH', BASE_PATH.'includes'.DIRECTORY_SEPARATOR);

// Tracker Paths
define('TROOT_PATH', BASE_PATH.'tracker_html'.DIRECTORY_SEPARATOR);
define('TINCL_PATH', TROOT_PATH.'include'.DIRECTORY_SEPARATOR);


const UC_USER = 0;
const UC_POWER_USER = 1;
const UC_XTREME_USER = 2;
const UC_LOVER = 3;
const UC_WHORE = 4;
const UC_SUPER_WHORE = 5;
const UC_SEED_WHORE = 6;
const UC_OVERSEEDER = 7;
const UC_VIP = 8;
const UC_UPLOADER = 9;
const UC_FORUM_MODERATOR = 10;
const UC_MODERATOR = 11;
const UC_ADMINISTRATOR = 12;
const UC_LEADER = 13;

// Staff level starts here
const UC_STAFF = UC_FORUM_MODERATOR;
const UC_MIN = UC_USER;
const UC_MAX = UC_LEADER;
?>
