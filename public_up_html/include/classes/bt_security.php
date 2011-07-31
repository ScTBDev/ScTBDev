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
require_once(CLASS_PATH.'bt_vars.php');

class bt_security {
	private static $valid_tlds = array(
		'ac','ad','ae','af','ag','ai','al','am','an','ao','aq',
		'ar','as','at','au','aw','az','ax','ba','bb','bd','be',
		'bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt',
		'bv','bw','by','bz','ca','cc','cd','cf','cg','ch','ci',
		'ck','cl','cm','cn','co','cr','cs','cu','cv','cx','cy',
		'cz','de','dj','dk','dm','do','dz','ec','ee','eg','eh',
		'er','es','et','eu','fi','fj','fk','fm','fo','fr','ga',
		'gb','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp',
		'gq','gr','gs','gt','gu','gw','gy','hk','hm','hn','hr',
		'ht','hu','id','ie','il','im','in','io','iq','ir','is',
		'it','je','jm','jo','jp','ke','kg','kh','ki','km','kn',
		'kp','kr','kw','ky','kz','la','lb','lc','li','lk','lr',
		'ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk',
		'ml','mm','mn','mo','mp','mq','mr','ms','mt','mu','mv',
		'mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl',
		'no','np','nr','nu','nz','om','pa','pe','pf','pg','ph',
		'pk','pl','pm','pn','pr','ps','pt','pw','py','qa','re',
		'ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si',
		'sj','sk','sl','sm','sn','so','sr','st','sv','sy','sz',
		'tc','td','tf','tg','th','tj','tk','tl','tm','tn','to',
		'tp','tr','tt','tv','tw','tz','ua','ug','uk','um','us',
		'uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws',
		'ye','yt','yu','za','zm','zw','biz','com','info','name',
		'net','org','edu','xxx',

//		'aero','gov','travel','pro','int','mil','jobs','mobi',
//		'museum','coop','cat' // valid tlds but not exactly
							  // domains you want being used
					  	 	  // on torrents sites :P
		'me',
	);

	public static function html_safe($data, $onlyspecialchars = true, $strip_invalid = true) {
		if (!is_scalar($data))
			return '';

		// 0-8, 11-12, 14-31
		$invalid_chars = array("\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07","\x08","\x0b","\x0c",
			"\x0e","\x0f","\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17","\x18","\x19","\x1a","\x1b",
			"\x1c","\x1d","\x1e","\x1f", "\x7f");

		if ($strip_invalid)
			$data = str_replace($invalid_chars, '', $data);

		if ($onlyspecialchars) // now the default since everything is UTF-8
			return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', true);
		else
			return htmlentities($data, ENT_QUOTES, 'UTF-8', true);
	}

	public static function valid_email($email) {
		if (preg_match('/^[\w.+-]+@(?:[\w.-]+\.)+([a-z]{2,6})$/isD', $email, $m)) {
			if (self::valid_tld($m[1]))
				return true;
		}
		return false;
	}

	private static function valid_tld($tld) {
		$tld = strtolower($tld);
		if (in_array($tld, self::$valid_tlds, true))
			return true;
		else
			return false;
	}

	public static function redirect_base($ssl = false, $geo = '') {
		$hosts = explode('.', strtolower($_SERVER['HTTP_HOST']));
		$host = ($geo && strlen($geo) == 2) ? $geo : (strlen($hosts[1]) == 2 ? $hosts[1] : '');
		$host = in_array($host, bt_config::$conf['valid_geos'], true) ? $host : '';

		if ($ssl)
			$redirectbase = isset(bt_config::$conf['ssl_urls'][$host]) ? bt_config::$conf['ssl_urls'][$host] : bt_config::$conf['default_ssl_url'];
		else
			$redirectbase = isset(bt_config::$conf['plain_urls'][$host]) ? bt_config::$conf['plain_urls'][$host] : bt_config::$conf['default_plain_url'];

		return $redirectbase;
	}

	public static function geo_server() {
		$geoip = bt_vars::$geoip;

		$us_ccs = array('A1','A2','BN','CN','HK','ID','JP','KH','KR','LA','MM','MN','MY','PH','SG','TH','TL','TW','VN');

		$us_cn = array('AQ','NA','OC','SA');
		$eu_cn = array('AF','AS','EU');

		if ($geoip && in_array($geoip['country_code'], $us_ccs, true) || in_array($geoip['continent_code'], $us_cn, true))
			return 'us';
		else
			return 'eu';
	}
}
?>
