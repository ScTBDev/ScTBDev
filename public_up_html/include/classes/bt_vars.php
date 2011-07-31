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
require_once(CLASS_PATH.'bt_ip.php');
require_once(CLASS_PATH.'bt_geoip.php');

/* Standard variables that will be of much use instead of using functions to get them all the time wasting cpu */
bt_vars::init();
class bt_vars {
	public static $ip = '';
	public static $ip2 = NULL;
	public static $packed_ip = '';
	public static $ip_type = 0;

	public static $realip = '';
	public static $realip2 = NULL;
	public static $packed_realip = '';
	public static $realip_type = 0;

	public static $timestamp = 0;
	public static $geoip = array();
	public static $ssl = false;

	public static $base_url = '';

	public static function init() {
		self::$timestamp		= time();
		self::$ssl				= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

		if (isset($_SERVER['REMOTE_ADDR'])) {
			self::$realip			= $_SERVER['REMOTE_ADDR'];
			self::$packed_realip	= bt_ip::type(self::$realip, self::$realip_type, self::$realip2);
			if (!self::$packed_realip)
				die('IP Address Error');

			self::$ip				= bt_ip::get_ip();
			self::$packed_ip		= bt_ip::type(self::$ip, self::$ip_type, self::$ip2);

			self::$geoip			= bt_geoip::lookup_ip(self::$realip);

			if (isset($_SERVER['HTTP_HOST'])) {
				$hosts = explode(':', $_SERVER['HTTP_HOST']);
				$num = count($hosts) - 1;
				if ($hosts[$num] == $_SERVER['SERVER_PORT'])
					unset($hosts[$num]);

				$host = implode(':', $hosts);
			}
			else
				$host = $_SERVER['SERVER_ADDR'];

			$port = self::$ssl ? ($_SERVER['SERVER_PORT'] == 443 ? 0 : $_SERVER['SERVER_PORT']) : ($_SERVER['SERVER_PORT'] == 80 ? 0 : $_SERVER['SERVER_PORT']);

			self::$base_url = 'http'.(self::$ssl ? 's' : '').'://'.$host.($port ? ':'.$port : '');
		}
	}
}
?>
