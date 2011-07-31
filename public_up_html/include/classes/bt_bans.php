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
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_ip.php');
require_once(CLASS_PATH.'bt_geoip.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_dns.php');

class bt_bans {
	public static $cc_bans = array('A1','A2','IL','PL','RO');
	public static $bypass_cc_bans_ranges = array('83.233.168.0/23','83.233.180.0/22');

	private static function keys($ip, &$key) {
        $hip = bt_ip::ip2hex($ip);
		if (!$hip)
			return false;

        $key	= 'bt_bans::bans:::'.$hip;
		return true;
	}

	public static function add($ip, $reason, $add_to_db = false) {
		bt_memcache::connect();

		if (!self::keys($ip, $key))
			return false;

		bt_memcache::add($key, $reason, 86400);
		return true;
	}

	public static function del($ip, $del_from_db = false) {
		bt_memcache::connect();

		if (!self::keys($ip, $key))
			return false;

		bt_memcache::del($key);
		return true;
	}

	public static function check($ip, $dnsbl = false, $addgood = true, &$reason = '') {
		$packed_ip = bt_ip::type($ip, $type);
		if (!$packed_ip) {
			$reason = 'Invalid IP';
			return true;
		}

		bt_memcache::connect();

		if (!self::keys($ip, $key))
			return true;

		$ban = bt_memcache::get($key);
		if ($ban === bt_memcache::NO_RESULT) {
			if (!empty(self::$cc_bans)) {
				$geoip = bt_geoip::lookup_ip($ip);
				if ($geoip && in_array($geoip['country_code'], self::$cc_bans) && !bt_ip::verify_ip(self::$bypass_cc_bans_ranges, $ip)) {
					$reason = 'Banned country code ('.$geoip['country_code'].')';
					self::add($ip, $reason);
					return true;
				}
			}

			bt_sql::connect();
			$banq = bt_sql::query('SELECT comment FROM bans WHERE LENGTH(first) = '.($type === bt_ip::IP6 ? '16' : '4').' '.
					bt_sql::binary_esc($packed_ip).' BETWEEN first AND last LIMIT 1');
			if ($banq->num_rows) {
				$comment = $banq->fetch_row();
				$banq->free();
				$reason = 'Manual Ban ('.$comment[0].')';
				self::add($ip, $reason);
				return true;
			}
			$banq->free();

			if ($type === bt_ip::IP4) {
				if ($dnsbl && self::dnsbl_check($ip, $matches, $rbl)) {
					$reason = 'Listed in DNS BlackList '.$rbl.' ('.implode(', ', $matches).')';
					self::add($ip, $reason);
					return true;
				}
			}
			elseif ($type === bt_ip::IP6) {
				/* May add IPv6 DNS Blacklist here later */
			}

			if ($addgood)
				bt_memcache::add($key, 0, 86400);

			return false;
		}
		elseif (!$ban)
			return false;
		else {
			$reason = $ban;
			return true;
		}
	}

	public static function dnsbl_check($ip, &$matches = array(), &$rbl = '') {
		$addrs = array(
			1	=> 'Tor exit node',
		);

		$rbl = 'tor.dnsbl.sectoor.de';
		if (bt_dns::check_dnsbl($rbl, $ip, bt_dns::ADDR, $addrs, $matches))
			return true;


		$addrs = array(
			2	=> 'TOR (either as a transit or exit node)',
			3	=> 'TOR exit node',
//			4	=> 'Recently seen running a TOR exit node on EFNet',
		);

		$rbl = 'tor.ahbl.org';
		if (bt_dns::check_dnsbl($rbl, $ip, bt_dns::ADDR, $addrs, $matches))
			return true;

		

		$addrs = array(
			1	=> 'Open Proxy',
			2	=> 'Trojan spreader',
			3	=> 'Trojan infected client',
//			4	=> 'TOR exit node',
			5	=> 'Drones / Flooding',
		);

		$rbl = 'rbl.efnetrbl.org';
		if (bt_dns::check_dnsbl($rbl, $ip, bt_dns::ADDR, $addrs, $matches))
			return true;


		$addrs = array(
			2	=> 'Open proxy',
		);

		$rbl = 'dnsbl.proxybl.org';
		if (bt_dns::check_dnsbl($rbl, $ip, bt_dns::ADDR, $addrs, $matches))
			return true;


		$addrs = array(
//			2	=> 'Sample',
			3	=> 'IRC Drone',
			5	=> 'Bottler',
			6	=> 'Unknown spambot or drone',
			7	=> 'DDOS Drone',
			8	=> 'SOCKS Proxy',
			9	=> 'HTTP Proxy',
			10	=> 'Proxy Chain',
			13	=> 'Brute force attackers',
			14	=> 'Open WINGATE Proxy',
			15	=> 'Compromised router / gateway',
			255	=> 'Uncategorized threat class',
		);

		$rbl = 'dnsbl.dronebl.org';
		if (bt_dns::check_dnsbl($rbl, $ip, bt_dns::ADDR, $addrs, $matches))
			return true;


		$addrs = array(
			2	=> 'SOCKS Proxy',
			3	=> 'IRC Proxy',
			4	=> 'HTTP Proxy',
			5	=> 'IRC Drone',
//			6	=> 'TOR',
		);
		$rbl = 'dnsbl.swiftbl.net';
		if (bt_dns::check_dnsbl($rbl, $ip, bt_dns::ADDR, $addrs, $matches))
			return true;


		$rbl = '';
		return false;
	}
}
?>
