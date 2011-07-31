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

require_once(__DIR__.DIRECTORY_SEPARATOR.'defines.php');

// Add Click-Jacking and XSS Protection
header('X-Frame-Options: DENY');
header('X-Content-Security-Policy: allow "self"; img-src *; frame-ancestors "none"');


if (!isset($_NO_CACHEHEADERS))
	header('Cache-Control: no-store, no-transform, private, must-revalidate, max-age=0');
else
	header('Cache-Control: private');

$load = sys_getloadavg();
if ($load[0] > 16)
  die('Load is too high, DO NOT continuously refresh, or you will just make the problem last longer');


require_once(CLASS_PATH.'bt_config.php');

// needed by bittorrent.php itself
require_once(CLASS_PATH.'bt_loginout.php');
require_once(CLASS_PATH.'bt_security.php');
require_once(CLASS_PATH.'bt_theme.php');
require_once(CLASS_PATH.'bt_bitmask.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_ip.php');
require_once(CLASS_PATH.'bt_vars.php');

// not needed by bittorrent.php, but in a lot of pages
require_once(CLASS_PATH.'bt_time.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_user.php');



$hosts = explode('.',$_SERVER['HTTP_HOST']);
if (!isset($_NO_REDIRECT) && isset($_SERVER['HTTP_HOST']) && $hosts[0] != 'www') {
	header('Location: '.bt_config::$conf['default_base_url'].$_SERVER['REQUEST_URI']);
	die();
}

if (!bt_vars::$ssl && in_array(@bt_vars::$geoip['country_code'], bt_config::$conf['ssl_only_ccs'])) {
	header('Location: '.bt_config::$conf['default_ssl_url'].$_SERVER['REQUEST_URI']);
	die();
}

/*
if (!isset($_NO_REDIRECT) && $_SERVER['REQUEST_METHOD'] == 'GET' && substr($_SERVER['REMOTE_ADDR'], 0, 9) != '10.200.5.' &&
	isset($_SERVER['HTTP_HOST']) && $hosts[1] != 'dev' && !in_array($hosts[1], bt_config::$conf['valid_geos'], true)) {
	$geo = bt_security::geo_server();
	$redirectbase = bt_security::redirect_base(bt_vars::$ssl, $geo);

	header('Location: '.$redirectbase.$_SERVER['REQUEST_URI']);
	die();
}
*/

// TODO: really need to do this a lot better
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_SERVER)))
  httperr(403,'Forbidden');
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_GET)))
  httperr(403, 'Forbidden');
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_POST)))
  httperr(403, 'Forbidden');
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_COOKIE)))
  httperr(403, 'Forbidden');

$TIMES['start'] = microtime(true);
$MySQL_NUM_QUERIES = 0;
$MySQL_LEN_QUERIES = 0.0;

/*
if (!isset($_NO_COMPRESS))
	if (!ob_start('ob_gzhandler'))
		ob_start();
*/

require_once(INCL_PATH.'theme.php');

function mysql_counted_query($query) {
	global $MySQL_NUM_QUERIES, $MySQL_LEN_QUERIES;
	$MySQL_NUM_QUERIES++;
	$nowtime = microtime(true);
	$res = mysql_query($query);
	$MySQL_LEN_QUERIES += (microtime(true) - $nowtime);
	return $res;
}


function getip() {
	return bt_ip::get_ip();
}


function dbconn() {
	bt_loginout::db_connect(false);
}

function mksize($bytes) {
	return bt_theme::mksize($bytes);
}

function deadtime() {
    return bt_vars::$timestamp - floor(bt_config::$conf['announce_interval'] * 2);
}

function mkprettytime($s) {
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        }
        else
            $v = $s;
        $t[$y[1]] = $v;
    }

    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
//    if ($t["min"])
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
//    return $t["sec"] . " secs";
}


// TODO: this really must be removed ASAP
function mkglobal($vars) {
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = $_GET[$v];
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = $_POST[$v];
        else
            return 0;
    }
    return 1;
}

function tr($x,$y,$noesc=0,$centerhead=0) {
    if ($noesc)
        $a = $y;
    else {
        $a = htmlentities($y);
        $a = nl2br($a);
    }
    print('<tr><td class="heading" valign="'.($centerhead ? 'middle' : 'top').'" align="right">'.$x.'</td><td valign="top" align="left">'.$a.'</td></tr>'."\n");
}

function validfilename($name) {
    return preg_match('%^[^\x00-\x1f:\\\\/?*\xff#<>|]+$%si', $name);
}

function validemail($email) {
	return bt_security::valid_email($email);
}

function sqlesc($x) {
	return bt_sql::esc($x);
}

function sqlwildcardesc($x) {
    return bt_sql::wildcard_escape($x);
}

function genbark($x,$y) {
    bt_theme::head($y);
    print("<h2>" . htmlspecialchars($y) . "</h2>\n");
    print("<p>" . htmlspecialchars($x) . "</p>\n");
    bt_theme::foot();
    exit();
}

function mksecret($len = 20) {
	return bt_string::random($len);
}

function httperr($code = 404, $msg = 'Not Found') {
    header('HTTP/1.0 '.$code.' '.$msg);
    print('<h1>'.$msg.'</h1>'."\n");
    print('<p>Sorry pal :(</p>'."\n");
    exit();
}

function logincookie($id, $ssl_only = false, $updatedb = true, $maxage = 7776000) {
	$options = 0;
	if ($ssl_only)
		$options |= bt_loginout::OPT_SECURE;

	bt_loginout::login($id, $options, $updatedb, $maxage);
}


function logoutcookie() {
	bt_loginout::logout();
}

function loggedinorreturn() {
	bt_loginout::or_return();
}

function deletetorrent($id) {
	$user_seeds = $user_leeches = array();
	$id = 0 + $id;
	bt_sql::query('DELETE FROM torrents WHERE id = '.$id) or bt_sql::err(__FILE__, __LINE__);
	bt_sql::query('DELETE FROM torrents_anon WHERE id = '.$id) or bt_sql::err(__FILE__, __LINE__);
	$peeres = bt_sql::query('SELECT userid, seeder FROM peers WHERE torrent = '.$id) or bt_sql::err(__FILE__, __LINE__);
	while ($peer = $peeres->fetch_assoc()) {
		$userid = 0 + $peer['userid'];
		$seed = $peer['seeder'] === 'yes';
		if (!isset($user_seeds[$userid]))
			$user_seeds[$userid] = $user_leeches[$userid] = 0;

		if ($seed)
			$user_seeds[$userid]++;
		else
			$user_leeches[$userid]++;
	}
	$peeres->free();

	foreach(array('peers','files','comments') as $x)
		bt_sql::query('DELETE FROM '.$x.' WHERE torrent = '.$id) or bt_sql::err(__FILE__, __LINE__);

	@unlink(bt_config::$conf['torrent_dir'].'/'.$id.'.torrent');

	foreach (array_keys($user_seeds) as $uid) {
		$update = array();
//		bt_mem_caching::adjust_user_peers($uid, -$user_seeds[$uid], -$user_leeches[$uid]);
		if ($user_seeds[$uid])
			$update[] = 'seeding = (seeding - '.$user_seeds[$uid].')';
		if ($user_leeches[$uid])
			$update[] = 'leeching = (leeching - '.$user_leeches[$uid].')';

		bt_sql::query('UPDATE users SET '.implode(', ', $update).' WHERE id = '.$uid);
	}
}

function searchfield($s) {
    return preg_replace(array('/[^a-z0-9]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function linkcolor($num) {
    if (!$num)
        return "red";
//    if ($num == 1)
//        return "yellow";
    return "green";
}

require_once(INCL_PATH.'global.php');
?>
