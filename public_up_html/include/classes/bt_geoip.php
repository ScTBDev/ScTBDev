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

class bt_geoip {
	private static $dbinfo = NULL;

	private static function load_dbinfo() {
		if (is_null(self::$dbinfo)) {
			if (!extension_loaded('geoip')) {
				self::$dbinfo = false;
				return false;
			}

			bt_memcache::connect();
			$key = 'geoip_db_info';
			$geodb = bt_memcache::get($key);
			if ($geodb === bt_memcache::NO_RESULT) {
				$geodb = geoip_db_get_all_info();
				bt_memcache::add($key, $geodb, 900);
			}

			self::$dbinfo = $geodb;
		}
	}

	public static function lookup_ip($ip) {
		bt_ip::type($ip, $type);

		// No IPv6 support yet
		if ($type !== bt_ip::IP4)
			return false;

		if (!bt_ip::valid_ip($ip))
			return false;

		if (!$hip = bt_ip::ip2hex6($ip))
			return false;

		bt_memcache::connect();
		$key = 'geoip_'.$hip;

		$lookup = bt_memcache::get($key);
		if ($lookup === bt_memcache::NO_RESULT) {
			$lookup = self::geodb_lookup($ip);
			bt_memcache::add($key, $lookup, 604800);
		}

		$geoip = empty($lookup) ? false : $lookup;
		return $geoip;
	}

	private static function geodb_lookup($ip) {
		self::load_dbinfo();
		if (!self::$dbinfo)
			return array();

		$continent_code = $country_code = $country_code3 = $country_code3 = $country_name = $region = $city = $isp = $organization = 
			$postal_code = $latitude = $longitude = $dma_code = $area_code = $netspeed = $asn = NULL;

		if (self::$dbinfo[GEOIP_CITY_EDITION_REV0]['available'] || self::$dbinfo[GEOIP_CITY_EDITION_REV1]['available']) {
			$city_details = @geoip_record_by_name($ip);

			if ($city_details) {
				if (isset($city_details['continent_code']))
					$continent_code = $city_details['continent_code'];

				$country_code = $city_details['country_code'];
				$region = $city_details['region'];
				$city = $city_details['city'];
				$postal_code = $city_details['postal_code'];
				$latitude = $city_details['latitude'];
				$longitude = $city_details['longitude'];
				$dma_code = $city_details['dma_code'];
				$area_code = $city_details['area_code'];
			}
		}

		if (self::$dbinfo[GEOIP_REGION_EDITION_REV0]['available'] || self::$dbinfo[GEOIP_REGION_EDITION_REV1]['available']) {
			if (!$city_details) {
				$region_details = @geoip_region_by_name($ip);

				if ($region_details) {
					$country_code = $region_details['country_code'];
					$region = $region_details['region'];
				}
			}
		}

		if (self::$dbinfo[GEOIP_COUNTRY_EDITION]['available']) {
			if (!$city_details && !$region_details)
				$country_code = @geoip_country_code_by_name($ip);
				
			$country_code3 = @geoip_country_code3_by_name($ip);
			$country_name = @geoip_country_name_by_name($ip);
		}

		if (self::$dbinfo[GEOIP_ORG_EDITION]['available']) {
			$organization = @geoip_org_by_name($ip);
		}

		if (self::$dbinfo[GEOIP_ISP_EDITION]['available']) {
			if (function_exists('geoip_isp_by_name'))
				$isp = @geoip_isp_by_name($ip);
		}

		if (self::$dbinfo[GEOIP_ASNUM_EDITION]['available']) {
		}

		$geoip_details = array();

		if ($continent_code)
			$geoip_details['continent_code']	= (string)$continent_code;
		if ($country_code)
			$geoip_details['country_code']		= (string)$country_code;
		if ($country_code3)
			$geoip_details['country_code3']		= (string)$country_code3;
		if ($country_name)
			$geoip_details['country_name']		= (string)$country_name;
		if ($region)
			$geoip_details['region']			= (string)$region;
		if ($city)
			$geoip_details['city']				= (string)$city;
		if ($isp)
			$geoip_details['isp']				= (string)$isp;
		if ($organization)
			$geoip_details['organization']		= (string)$organization;
		if ($postal_code)
			$geoip_details['postal_code']		= (string)$postal_code;
		if ($latitude || $longitude) {
			$geoip_details['latitude']			= (float)$latitude;
			$geoip_details['longitude']			= (float)$longitude;
		}
		if ($dma_code)
			$geoip_details['dma_code']			= (int)$dma_code;
		if ($area_code)
			$geoip_details['area_code']			= (int)$area_code;
		if ($asn)
			$geoip_details['asn']				= (string)$asn;

		return $geoip_details;
	}
}
?>
