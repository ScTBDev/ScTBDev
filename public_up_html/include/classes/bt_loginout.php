<?php
/*
 *      ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *      Copyright (C) 2005-2011 ScTBDev.ca
 *
 *      This file is part of ScTBDev.
 *
 *      ScTBDev is free software: you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation, either version 3 of the License, or
 *      (at your option) any later version.
 *
 *      ScTBDev is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *
 *      You should have received a copy of the GNU General Public License
 *      along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_vars.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_user.php');

class bt_loginout {
	const OPT_SECURE			= BIT_1;
	const OPT_IP_SECURE			= BIT_2;

	const SESSION_KEY_PREFIX	= 'bt_loginout:::sessions::';
	const TTL_TIME				= 3600;
	const BAD_TTL_TIME			= 10800;
	const UPDATE_TIME			= 300;
	const RANDOM_LENGTH			= 512;
	const USER_FIELDS			= 'id, username, password, email, last_access, theme, info, HEX(ip) AS ip, HEX(realip) AS realip, ip_access, class, avatar, uploaded, downloaded, seeding, leeching, title, country, timezone, notifs, torrentsperpage, topicsperpage, postsperpage, last_browse, inbox_new, inbox, sentbox, comments, posts, last_forum_visit, passkey, invites, CAST(flags AS SIGNED) AS flags, CAST(chans AS SIGNED) AS chans';


	public static function db_connect($require_login = false) {
		if (!bt_sql::connect($errno, $error)) {
			header('Content-Type: text/html; charset=UTF-8');
			switch ($errno) {
				case 1040:
				case 2002:
					if ($_SERVER['REQUEST_METHOD'] == 'GET') {
						header('Refresh: '.rand(5, 15).';'.$_SERVER['REQUEST_URI']);
						$conn_error = 'The database server is down or load is too high at the moment. Retrying, please wait...';
					}
					else
						$conn_error = 'Too many users. Please press the Refresh button in your browser to retry.';
				break;

				default:
					$conn_error = '['.$errno.'] '.bt_security::html_safe($error);
				break;
			}

			echo <<<ERROR
<?xml version="1.0" encoding="UTF-8" ?>
<html>
<head>
    <title>DB Error</title>
</head>
<body>
{$conn_error}
</body>
</html>
ERROR;
			die;
		}

		self::user();

		if ($require_login)
			self::or_return();
	}

	private static function user() {
		if (!bt_config::$conf['SITE_ONLINE'])
			return false;

		$session = self::check();
		if (!$session)
			return false;

		$req_flags = bt_options::USER_ENABLED | bt_options::USER_CONFIRMED;
		$res = bt_sql::query('SELECT '.self::USER_FIELDS.' FROM users '.
			'WHERE id = '.$session['user']) or bt_sql::err(__FILE__, __LINE__);

		if (!$res->num_rows) {
			self::logout();
			$res->free();
			return false;
		}
		$user = $res->fetch_assoc();
		$res->free();

		$updateuser = array();
		bt_user::prepare_user($user);

		if (($user['flags'] & $req_flags) != $req_flags)
			return false;

		if (!($row['flags'] & bt_options::USER_BYPASS_BANS)) {
			$banned = false;
			if (bt_bans::check(bt_vars::$realip, true, true, $reason)) {
				$banned = true;
				$geoip = bt_geoip::lookup_ip(bt_vars::$realip);
			}
			else {
				if (bt_vars::$ip != bt_vars::$realip) {
					if (bt_bans::check(bt_vars::$ip, true, true, $reason)) {
						$banned = true;
						$geoip = bt_geoip::lookup_ip(bt_vars::$ip);
					}
				}
			}

			if ($banned) {
				$reason = bt_security::html_safe($reason);
				header('Content-Type: text/html; charset=UTF-8');
				echo <<<BANNED
<?xml version="1.0" encoding="UTF-8" ?>
<html>
<head>
	<title>IP Banned</title>
</head>
<body>
Your IP address is currently banned for reason {$reason}.
</body>
</html>
BANNED;
				die;
			}
		}

		if (trim($user['ip_access']) != '') {
			$invalid_ip = false;
			$ips = explode(';',trim($row['ip_access']));
			if (!bt_ip::verify_ip($ips, bt_vars::$realip)) {
				self::logout();
				echo <<<BADIP
<?xml version="1.0" encoding="UTF-8" ?>
<html>
<head>
	<title>IP Restricted</title>
</head>
<body>
Your IP address is not allowed access on this account.
</body>
</html>
BADIP;
				die;
			}
		}

		if (($user['flags'] & bt_options::USER_SSL_SITE) && !bt_vars::$ssl) {
			header('Location: '.bt_config::$conf['default_ssl_url'].$_SERVER['REQUEST_URI']);
			die;
		}

		define('USER_CLASS', $user['class']);

		$hideip = ($user['flags'] & bt_options::USER_PROTECT) || $user['class'] >= UC_VIP;
		$ip = $hideip ? NULL : bt_vars::$packed_ip;
		$realip = $hideip ? NULL : bt_vars::$packed_realip;

		if ($user['ip'] !== $ip)
			$updateuser[] = 'ip = '.bt_sql::binary_esc($ip);

		if ($user['realip'] !== $realip)
			$updateuser[] = 'realip = '.bt_sql::binary_esc($realip);

		if ($user['last_access'] < (bt_vars::$timestamp - 300))
			$updateuser[] = 'last_access = '.bt_vars::$timestamp;

		unset($user['ip'], $user['realip'], $user['last_access']);


		if (count($updateuser))
			bt_sql::query('UPDATE users SET '.implode(', ', $updateuser).' WHERE id = '.$user['id']) or bt_sql::err(__FILE__, __LINE__);

		bt_user::$current = $user;
		bt_theme_engine::load();
	}

	private static function ip_check($ip, $curip) {
		if ($ip === $curip)
			return true;

		bt_ip::type($ip, $type);
		bt_ip::type($curip, $curtype);

		if ($type !== $curtype)
			return false;

		if ($type === bt_ip::IP4) {
			return bt_ip::net_match($curip, $ip.'/16');
		}
		elseif ($type === bt_ip::IP6) {
			return bt_ip::net_match($curip, $ip.'/64');
		}
		return false;
	}

	private static function check() {
		if (isset($_COOKIE['id']) && strlen($_COOKIE['id']) == 40 && bt_string::is_hex($_COOKIE['id']))
			$session_id = $_COOKIE['id'];
		else
			return false;

		$session = bt_memcache::get(self::SESSION_KEY_PREFIX.$session_id, $cas);
		if ($session === bt_memcache::NO_RESULT) {
			$cas = false;
			$res = bt_sql::query('SELECT user, HEX(ip) AS ip, HEX(realip) as realip, time, lastaction, maxage, maxidle, '.
				'CAST(flags AS SIGNED) AS flags FROM sessions WHERE id = '.bt_sql::esc($session_id)) or bt_sql::err(__FILE__, __LINE__);

			if (!$res->num_rows) {
				bt_memcache::add(self::SESSION_KEY_PREFIX.$session_id, 0, self::BAD_TTL_TIME);
				return false;
			}

			$row = $res->fetch_assoc();
			$res->free();
			$session = array(
				'user'			=> (int)$row['user'],
				'ip'			=> bt_ip::hex2ip($row['ip']),
				'realip'		=> bt_ip::hex2ip($row['realip']),
				'time'			=> (int)$row['time'],
				'last_action'	=> (int)$row['lastaction'],
				'max_age'		=> (int)$row['maxage'],
				'max_idle'		=> (int)$row['maxidle'],
				'flags'			=> (int)$row['flags']
			);
		}
		elseif (!$session)
			return false;

		if (($session['flags'] & self::OPT_SECURE) && !bt_vars::$ssl)
			return false;

		if (((bt_vars::$timestamp - $session['max_age']) > $session['time']) || ((bt_vars::$timestamp - $session['max_idle']) > $session['last_action'])) {
			self::logout();
			return false;
		}


		$updates = array();
		$newsession = $session;

		if ($session['ip'] !== bt_vars::$ip || $session['realip'] !== bt_vars::$realip) {
			if (($session['flags'] & self::OPT_IP_SECURE) || !self::ip_check($session['ip'], bt_vars::$ip) || !self::ip_check($session['realip'], bt_vars::$realip)) {
				self::logout();
				return false;
			}


			if ($session['ip'] !== bt_vars::$ip) {
				$newsession['ip'] = bt_vars::$ip;
				$updates[] = 'ip = '.bt_sql::binary_esc(bt_vars::$packed_ip);
			}
			if ($session['realip'] !== bt_vars::$realip) {
				$newsession['realip'] = bt_vars::$realip;
				$updates[] = 'realip = '.bt_sql::binary_esc(bt_vars::$packed_realip);
			}
		}


		if ((bt_vars::$timestamp - self::UPDATE_TIME) > $session['last_action'])
			$updates[] = 'lastaction = '.bt_vars::$timestamp;

		$newsession['last_action'] = bt_vars::$timestamp;


		if (count($updates))
			bt_sql::query('UPDATE sessions SET '.implode(', ', $updates).' WHERE id = '.bt_sql::esc($session_id)) or bt_sql::err(__FILE__, __LINE__);

		if ($newsession !== $session) {
			if ($cas)
				bt_memcache::cas(self::SESSION_KEY_PREFIX.$session_id, $newsession, self::TTL_TIME, $cas);
			else
				bt_memcache::add(self::SESSION_KEY_PREFIX.$session_id, $newsession, self::TTL_TIME);
		}

		return $session;
	}

	public static function login($id, $options = 0, $updatedb = true, $maxage = 7776000, $maxidle = 604800) {
		$id = (int)$id;
		$maxage = (int)$maxage;
		$options = (int)$options;
		$maxage = (int)$maxage;
		$maxidle = (int)$maxidle;
		$session_id = hash('ripemd160', bt_string::random(self::RANDOM_LENGTH));
		$session = array(
			'user'			=> $id,
			'ip'			=> bt_vars::$ip,
			'realip'		=> bt_vars::$realip,
			'time'			=> bt_vars::$timestamp,
			'last_action'	=> bt_vars::$timestamp,
			'max_age'		=> $maxage,
			'max_idle'		=> $maxidle,
			'flags'			=> $options,
		);
		bt_memcache::add(self::SESSION_KEY_PREFIX.$session_id, $session, self::TTL_TIME);
		bt_sql::query('INSERT INTO sessions (id, user, ip, realip, time, lastaction, maxage, maxidle, flags) '.
			'VALUES('.bt_sql::esc($session_id).', '.$id.', '.bt_sql::binary_esc(bt_vars::$packed_ip).', '.
			bt_sql::binary_esc(bt_vars::$packed_realip).', '.bt_vars::$timestamp.', '.bt_vars::$timestamp.', '.$maxage.', '.
			$maxidle.', '.$options.')') or bt_sql::err(__FILE__, __LINE__);

		if (!bt_sql::$affected_rows)
			return false;

		$secure = (bool)($options & self::OPT_SECURE);
		setcookie('id', $session_id, (bt_vars::$timestamp + $maxage), '/', '', $secure, true);

		if ($updatedb)
			bt_sql::query('UPDATE users SET last_login = '.bt_vars::$timestamp.' WHERE id = '.$id) or bt_sql::err(__FILE__, __LINE__);

		return true;
	}

	public static function logout() {
		if (isset($_COOKIE['id']) && strlen($_COOKIE['id']) === 40 && bt_string::is_hex($_COOKIE['id'])) {
			$session_id = $_COOKIE['id'];
			bt_memcache::del(self::SESSION_KEY_PREFIX.$session_id);
			bt_sql::query('DELETE FROM sessions WHERE id = '.bt_sql::esc($session_id)) or bt_sql::err(__FILE__, __LINE__);
		}

		setcookie('id', '', 0x7fffffff, '/');
	}

	public static function or_return() {
		if (!bt_user::$current) {
			self::logout();
			header('Location: '.bt_vars::$base_url.'/login.php?returnto='.rawurlencode($_SERVER['REQUEST_URI']));
			exit();
		}
	}

	public static function delete_user_sessions($userid) {
		$user = (int)$userid;
		$res = bt_sql::query('SELECT id FROM sessions WHERE user = '.$user) or bt_sql::err(__FILE__, __LINE__);
		$sessions = $session_ids = array();
		while ($session = $res->fetch_row()) {
			$sessions[] = bt_sql::esc($session[0]);
			$session_ids[] = $session[0];
		}

		foreach ($session_ids as $session_id)
			bt_memcache::del(self::SESSION_KEY_PREFIX.$session_id);

		bt_sql::query('DELETE FROM sessions WHERE id IN ('.implode(', ', $sessions).')') or bt_sql::err(__FILE__, __LINE__);
	}
}
?>
