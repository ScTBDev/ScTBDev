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
require_once(CLASS_PATH.'bt_memcache.php');

class bt_dns {
	const MIN_TTL	= 900;
	const MAX_TTL	= 432000;
	const ERR_TTL	= 21600;

	const ADDR		= 1;
	const MASK		= 2;

	private static $record_types = array(
		'A'		=> DNS_A,
		'A6'	=> DNS_A6,
		'AAAA'	=> DNS_AAAA,
		'CNAME'	=> DNS_CNAME,
		'HINFO'	=> DNS_HINFO,
		'MX'	=> DNS_MX,
		'NAPTR'	=> DNS_NAPTR,
		'NS'	=> DNS_NS,
		'PTR'	=> DNS_PTR,
		'SOA'	=> DNS_SOA,
		'SRV'	=> DNS_SRV,
		'TXT'	=> DNS_TXT,

		'ALL'	=> DNS_ALL,
		'ANY'	=> DNS_ANY,
	);

	private static function to_idn($host) {
		return idn_to_ascii($host, (IDNA_DEFAULT | IDNA_USE_STD3_RULES));
	}

	private static function to_utf8($host) {
		return idn_to_utf8($host, (IDNA_DEFAULT | IDNA_USE_STD3_RULES));
	}


	private static function lookup_record($host, $type = 'ANY') {
		if (!isset(self::$record_types[$type])) {
			trigger_error('Invalid record type giving in '.__METHOD__, E_USER_WARNING);
			return false;
		}

		$record_type = self::$record_types[$type];
		$key = 'DNS:'.$type.':'.$host;

		bt_memcache::connect();

		$records = bt_memcache::get($key);
		if ($records === bt_memcache::NO_RESULT) {
			$record = @dns_get_record($host, $record_type, $authns, $addtl);
			$records = array(
				'record'	=> $record,
				'authns'	=> $authns,
				'addtl'		=> $addtl,
			);

			if (isset($record[0]['ttl']))
				$ttl = $record[0]['ttl'] < self::MIN_TTL ? self::MIN_TTL :
					$record[0]['ttl'] > self::MAX_TTL ? self::MAX_TTL : $record[0]['ttl'];
			else
				$ttl = self::ERR_TTL;

			bt_memcache::add($key, $records, $ttl);
		}
		
		return $records;
	}

	public static function ptr_ip($ip, &$type = NULL) {
		$addr = bt_ip::type($ip, $type);
		if (!$addr)
			return false;

		if ($type === bt_ip::IP4)
			return implode('.', array_reverse(unpack('C*', $addr)));
		elseif ($type === bt_ip::IP6)
			return implode('.', str_split(strrev(bin2hex($addr))));
		else
			return false;
	}

	public static function get_any_ip_from_name($host, $prefer_ipv6 = false, $only_one = true) {
		$prefer_ip6 = (bool)$prefer_ipv6;
		$only1 = (bool)$only_one;

		$first_ip = self::get_ip_from_name($host, $prefer_ip6);
		if ($first_ip !== false && $only1)
			return $first_ip;

		$second_ip = self::get_ip_from_name($host, !$prefer_ip6);
		if ($second_ip !== false) {
			if ($first_ip !== false) {
				$ips = array();
				if (!is_array($first_ip))
					$ips[] = $first_ip;
				else {
					foreach ($first_ip as $ip)
						$ips[] = $ip;
				}

				if (!is_array($second_ip))
					$ips[] = $second_ip;
				else {
					foreach ($second_ip as $ip)
						$ips[] = $ip;
				}

				return $ips;
			}
			else
				return $second_ip;
		}
		elseif ($first_ip !== false)
			return $first_ip;
		else
			return false;
	}

	public static function get_ip_from_name($host, $ipv6 = false) {
		if (bt_ip::type($host, $iptype))
			return false;

		$host = self::to_idn($host);
		if (!$host)
			return false;

		$ip6 = (bool)$ipv6;
		$type = $ip6 ? 'AAAA' : 'A';
		$records = self::lookup_record($host, $type);
		if (empty($records['record']))
			return false;

        if (count($records['record']) > 1) {
			$ip = array();
			foreach ($records['record'] as $record) {
				if ($ip6)
					$ip[] = $record['ipv6'];
				else
					$ip[] = $record['ip'];
			}
		}
		else {
			if ($ip6)
				$ip = $records['record'][0]['ipv6'];
			else
				$ip = $records['record'][0]['ip'];
		}

		return $ip;
	}

	public static function get_name_from_ip($ip) {
		$ptr = self::ptr_ip($ip, $type);
		if (!$ptr)
			return false;

		if ($type === bt_ip::IP4)
			$host = $ptr.'.in-addr.arpa';
		elseif ($type === bt_ip::IP6)
			$host = $ptr.'.ip6.arpa';
		else
			return false;

		$records = self::lookup_record($host, 'PTR');

		if (empty($records['record']))
			return false;

		if (count($records['record']) > 1) {
			$names = array();
			foreach ($records['record'] as $record) {
				$name = self::to_utf8($record['target']);
				if ($name)
					$names[] = $name;
			}
			if (empty($names))
				$names = false;;
		}
		else
			$names = self::to_utf8($records['record'][0]['target']);


		return $names;
	}

	public static function check_dnsbl($dnsbl, $ip, $type = self::ADDR, $matchtypes = array(), &$matches = array()) {
		$ptr = self::ptr_ip($ip);
		if (!$ptr)
			return false;

		$host = $ptr.'.'.$dnsbl;
		$records = self::get_ip_from_name($host, false);
		if (!$records)
			return false;

		if (empty($matchtypes))
			return true;

		if ($type === self::MASK) {
			if (is_array($records))
				return false; // dnsbl must be screwed up, in a mask dnsbl there can't be more than 1 response

			$response = self::dnsbl_ip($records);
			if ($response < 1)
				return false; // mask can only contain 24 bits max

			$matches = array();
			foreach ($matchtypes as $int => $matchtype) {
				if ($response & $int)
					$matches[] = $matchtype;
			}

			if (count($matches))
				return true;
		}
		elseif ($type === self::ADDR) {
			if (is_array($records)) {
				$matches = array();
				foreach ($records as $record) {
					$response = self::dnsbl_ip($record);
					if ($response < 1)
						return false; // mask can only contain 24 bits max
					if (isset($matchtypes[$response]))
						$matches[] = $matchtypes[$response];
				}
				if (count($matches))
					return true;
			}
			else {
				$response = self::dnsbl_ip($records);
				if ($response < 1)
					return false; // mask can only contain 24 bits max

				if (isset($matchtypes[$response])) {
					$matches = array($matchtypes[$response]);
					return true;
				}
			}
		}

		return false;
	}

	private static function dnsbl_ip($ip) {
		if (!bt_ip::type($ip, $iptype))
			return false;
		if ($iptype !== bt_ip::IP4)
			return false;

		return (int)((ip2long($ip) & ~2130706432) & 0xffffffff);
	}

	public static function verify_rdns($ip) {
		if (!bt_ip::type($ip, $type))
			return false;

		$name = self::get_name_from_ip($ip);
		if (!$name || is_array($name))
			return false;

		$dnsip = self::get_ip_from_name($name, ($type === bt_ip::IP6));
		if (!$dnsip)
			return false;

		if (is_array($dnsip)) {
			foreach ($dnsip as $dip) {
				bt_ip::type($dip, $dnstype);
				if ($type === $dnstype && $ip === $dip)
					return $name;
			}
		}
		else {
			bt_ip::type($dnsip, $dnstype);
			if ($type === $dnstype && $ip === $dnsip)
				return $name;
		}
		return false;
	}
};
?>
