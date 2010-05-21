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

require_once(__DIR__.DIRECTORY_SEPARATOR.'defines.php');

// Add Click-Jacking and XSS Protection
header('X-Frame-Options: DENY');
header('X-Content-Security-Policy: allow "self"; frame-ancestors "none"');


if (!isset($_NO_CACHEHEADERS))
	header('Cache-Control: no-store, no-transform, private, must-revalidate, max-age=0');
else
	header('Cache-Control: private');

$load = sys_getloadavg();
if ($load[0] > 16)
  die('Load is too high, DO NOT continuously refresh, or you will just make the problem last longer');

define('_START_MICROTIME_', microtime(true));

require_once(CLASS_PATH.'bt_config.php');

// needed by bittorrent.php itself
require_once(CLASS_PATH.'bt_security.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_theme.php');
require_once(CLASS_PATH.'bt_geoip.php');
require_once(CLASS_PATH.'bt_bitmask.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_bans.php');
require_once(CLASS_PATH.'bt_ip.php');
require_once(CLASS_PATH.'bt_vars.php');

// not needed by bittorrent.php, but in a lot of pages
require_once(CLASS_PATH.'bt_time.php');
require_once(CLASS_PATH.'bt_string.php');



$hosts = explode('.',$_SERVER['HTTP_HOST']);
if (!isset($_NO_REDIRECT) && isset($_SERVER['HTTP_HOST']) && $hosts[0] != 'www') {
	header('Location: '.bt_config::$conf['default_base_url'].$_SERVER['REQUEST_URI']);
	die();
}

if (!bt_vars::$ssl && in_array(bt_vars::$geoip['country_code'], bt_config::$conf['ssl_only_ccs'])) {
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
if (preg_match('/(\< *(java|script)|script\:|\+document\.)/i', serialize($_SERVER)))
  httperr(403,'Forbidden');
if (preg_match('/(\< *(java|script)|script\:|\+document\.)/i', serialize($_GET)))
  httperr(403, 'Forbidden');
if (preg_match('/(\< *(java|script)|script\:|\+document\.)/i', serialize($_POST)))
  httperr(403, 'Forbidden');
if (preg_match('/(\< *(java|script)|script\:|\+document\.)/i', serialize($_COOKIE)))
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


function dbconn($login = true) {
	if (!bt_sql::connect($errno, $error)) {
		if (!$login)
			die;

		switch ($errno) {
			case 1040:
			case 2002:
				if ($_SERVER['REQUEST_METHOD'] == 'GET')
					die('<html>
<head>
	<meta http-equiv=refresh content="'.rand(5, 15).';'.$_SERVER['REQUEST_URI'].'">
</head>
<body>
	<table border=0 width=100% height=100%>
		<tr>
			<td>
				<h3 align=center>The server load is very high at the moment. Retrying, please wait...</h3>
			</td>
		</tr>
	</table>
</body>
</html>');
				else
					die('Too many users. Please press the Refresh button in your browser to retry.');
			break;
			default:
				die('['.$errno.'] '.htmlentities($error));
		}
	}

	if ($login)
		userlogin();
}

function userlogin() {
    global $CONFIG, $CURUSER;
    $CURUSER = array();
	$banned = false;

    if (!$CONFIG['SITE_ONLINE'] || empty($_COOKIE['uid']) || empty($_COOKIE['pass']))
        return;

    $id = 0 + $_COOKIE['uid'];
    if (!$id || strlen($_COOKIE['pass']) != 40)
        return;

	$sres = bt_sql::query('SELECT * FROM `sessions` WHERE `sid` = '.bt_sql::esc($_COOKIE['pass']).' AND `uid` = '.$id) or bt_sql::err(__FILE__, __LINE__);
	$srow = $sres->fetch_assoc();
	$sres->free();

	$sip = (int)$srow['realip'];

	if (!$srow || (bt_vars::$long_realip & 0xffff0000 != $sip & 0xffff0000) || bt_vars::$timestamp > ($srow['added'] + $srow['maxage']) ||
		bt_vars::$timestamp > ($srow['lastaction'] + $srow['maxidle'])) {
		logoutcookie();
		loggedinorreturn();
		die('Invalid Session');
	}

    $res = bt_sql::query('SELECT *, CAST(flags AS SIGNED) AS flags_signed FROM users WHERE id = '.$id.' AND enabled = "yes" AND (flags & '.bt_options::FLAGS_CONFIRMED.')')
		or bt_sql::err(__FILE__, __LINE__);
    $row = $res->fetch_assoc();
	$res->free();
    if (!$row)
        return;

	$updateuser = $updatesession = array();
	bt_user::prepare_curuser($row);

	if (!$row['settings']['bypass_ban']) {
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
			header('Content-Type: text/html; charset=utf-8');
			echo '<?xml version="1.0" encoding="utf-8" ?>
<html>
<head>
	<title>IP Banned</title>
</head>
<body>
Your IP address is currently banned for reason '.bt_security::html_safe($reason).'.'./*
				(in_array($geoip['country_code'], array('RO')) ? ' If your ip '.
				'is in Poland, Israel or Romania, this ban may be part of country wide bans. This is due to very high rate of cheating/hacking/invite '.
				'trading/dupe accounts going on from these countries. This does not mean that we think you are doing any of those things, '.
				'which is why it is relatively easy to get your account access back. All you have to do is connect to our irc network at '.
				'<a href="irc://irc.scenetorrents.org">irc.scenetorrents.org</a> and join '.
				'<a href="irc://irc.scenetorrents.org/sct.support">#sct.support</a>. Once in this irc channel, just wait around and '.
				'idle, you will not be able to talk when first joining because of the +m channel mode, please do not PM staff members, a '.
				'staff member will eventually get to you and give you a +v so you can talk while we review your account (this usually takes '.
				'around a minute if all is well) and if everything seems to be in good order, that staff member will enable your account to '.
				'bypass these ip bans. It does not matter if you have a dynamic ip or not, the ban bypass is based on your account, not your '.
				'ip. If you have been cheating on your account, we will be able to tell, so please don\'t waste our time if you have been.'.
				'<br /><br />Thanks,<br />ScT Staff'.($geoip['country_code'] == 'PL' ? '<br /><br />In Polish:<br />'.
				'Twój adres IP został zabanowany. Jeżeli łączysz się z Polski bądź Izraela, prawdopodobnie jest to '.
				'spowodowane blokadą na te dwa kraje. Blokada ta podyktowana jest ogromną ilością cheaterów/hackerów/handlarzy '.
				'zaproszeń/posiadaczy wielu kont łączących się z tych dwóch krajów. To oczywiście nie oznacza, że podejrzewamy Ciebie o '.
				'takie praktyki, dlatego też odzyskanie  dostępu do konta jest dosyć proste. Wszystko co musisz zrobić, to połączyć się z '.
				'naszą siecią IRC, której adres to <a href="irc://irc.scenetorrents.org">irc.scenetorrents.org</a>, po czym wejść na kanał '.
				'<a href="irc://irc.scenetorrents.org/sct.support">#sct.support</a>. Gdy już wejdziesz na ten '.
				'kanał, zwyczajnie poczekaj - z powodu ustawień kanału i tak nikt nie będzie w stanie odczytać tego co napiszesz. Nie wysyłaj '.
				'wiadomości do załogi - ktoś zajmie się Twoim problemem najszybciej jak to będzie możliwe. Gdy to nastąpi, zostanie Ci nadane '.
				'prawo głosu (+v), co pozwoli Ci rozmawiać gdy my będziemy sprawdzać Twoje konto (Zwykle trwa to około minuty, jeżeli nie ma '.
				'żadnych problemów z Twoim kontem). Jeżeli wszystko będzie w należytym porządku, Twoje konto zostanie odblokowane. Ponieważ '.
				'usunięcie blokady dotyczy Twojego konta a nie adresu IP, nie ma znaczenia czy Twój adres IP jest stały czy zmienny. Jeżeli '.
				'oszukiwałeś na swoim koncie, zauważymy to, więc prosimy, nie trać swojego i naszego czasu.<br /><br />Dziękujemy,<br />'.
				'Załoga ScT.' : '') ($geoip['country_code'] == 'RO' ? '<br /><br />In Romanian:<br />
Daca IP-ul dumneavoastra este din Polonia, Israel sau Romania, acest ban face parte din categoria celor ce afecteaza tarile intregi. Aceasta se datoreaza numarului mare de cheateri/fakeri/hackeri/traderilor/conturilor duble venind din aceasta tara. Asta nu inseamna ca noi credem ca dumneavoastra chiar ati facut aceste lucruri, de aceea fiind relativ usor sa va obtineti conturile inapoi. Tot ce trebuie sa faceti este sa va conectati pe serverul nostru de IRC - irc.scenetorrents.org si sa intrati pe canalul #sct.support . Odata intrat pe acest canal, asteptati pana primiti abilitatea de a conversa; va rugam nu trimiteti mesaje private membrilor staffului, eventual veti primi +v (voice) sa va expuneti problema cat timp va verificam contul (de obicei dureaza un minut), iar daca totul este in ordine, membrul staffului va va reda accesul la contul dumneavoastra si va face bypass la banul ip-ului dumneavoastra. Nu conteaza daca aveti sau nu ip dinamic, bypassul este pe contul dumneavoastra, nu pe ip. Daca ati facut fake, vom putea face diferenta, asa ca nu ne irositi timpul degeaba.' : '') : '').*/'
</body>
</html>';
			die;
//			header('HTTP/1.0 403 Forbidden');
		}
	}

	$invalid_ip = false;
	if (trim($row['ip_access']) != '') {
		$ips = explode(';',trim($row['ip_access']));
		if (!bt_ip::verify_ip($ips, bt_vars::$realip))
			$invalid_ip = true;
	}

    if ($invalid_ip) {
		logoutcookie();
		loggedinorreturn();
		die('ERROR');
	}

	if ($row)
		define('USER_CLASS', $row['class']);

	if ($row['settings']['ssl_site'] && !bt_vars::$ssl) {
		header('Location: '.bt_config::$conf['default_ssl_url'].$_SERVER['REQUEST_URI']);
		die;
	}

	$hideip = $row['settings']['protect'] || $row['class'] >= bt_user::UC_VIP;
    $newip = $hideip ? 0 : bt_vars::$long_ip;
	$newrealip = $hideip ? 0 : bt_vars::$long_realip;

	if ($newip != $row['ip']) {
		$updateuser[] = '`ip` = '.$newip;
		$row['ip'] = $newip;
	}
	if ($newrealip != $row['realip']) {
		$updateuser[] = '`realip` = '.$newrealip;
		$row['realip'] = $newrealip;
	}
	if ($row['last_access'] < (bt_vars::$timestamp - 300))
		$updateuser[] = '`last_access` = '.bt_vars::$timestamp;

	if ($srow['ip'] != bt_vars::$long_ip)
		$updatesession[] = '`ip` = '.bt_vars::$long_ip;

	if ($srow['realip'] != bt_vars::$long_realip)
		$updatesession[] = '`realip` = '.bt_vars::$long_realip;

	if ($srow['lastaction'] < (bt_vars::$timestamp - 300))
		$updatesession[] = '`lastaction` = '.bt_vars::$timestamp;

	if (count($updateuser))
		bt_sql::query('UPDATE `users` SET '.implode(', ', $updateuser).' WHERE `id` = '.$row['id']) or bt_sql::err(__FILE__, __LINE__);

	if (count($updatesession))
		bt_sql::query('UPDATE `sessions` SET '.implode(', ', $updatesession).' WHERE `sid` = '.bt_sql::esc($srow['sid'])) or bt_sql::err(__FILE__, __LINE__);

	bt_user::$current = $row;
	$GLOBALS['CURUSER'] =& bt_user::$current;

	bt_theme_engine::load();
}

function mksize($bytes) {
	return bt_theme::mksize($bytes);
}

function deadtime() {
    global $CONFIG;
    return bt_vars::$timestamp - floor($CONFIG['announce_interval'] * 2);
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

function logincookie($id, $ssl_only = false, $updatedb = 1, $maxage = 7776000) {
	$id = 0 + $id;
	$maxage = 0 + $maxage;
	$secure = $ssl_only === true;
	$passhash = sha1(mksecret());
	bt_sql::query('INSERT INTO `sessions` (`sid`, `uid`, `ip`, `realip`, `added`, `lastaction`, `maxage`) '.
		'VALUES ('.bt_sql::esc($passhash).', '.$id.', '.bt_vars::$long_ip.', '.bt_vars::$long_realip.', '.
		bt_vars::$timestamp.', '.bt_vars::$timestamp.', '.$maxage.')') or bt_sql::err(__FILE__, __LINE__);

	setcookie('uid', $id, bt_vars::$timestamp + $maxage, '/', '', $secure, true);
	setcookie('pass', $passhash, bt_vars::$timestamp + $maxage, '/', '', $secure, true);

	if ($updatedb)
		bt_sql::query('UPDATE `users` SET `last_login` = '.bt_vars::$timestamp.' WHERE `id` = '.$id) or bt_sql::err(__FILE__, __LINE__);
}


function logoutcookie() {
	bt_sql::query('DELETE FROM `sessions` WHERE `sid` = '.bt_sql::esc($_COOKIE['pass']));
	setcookie('uid', '', 0x7fffffff, '/');
	setcookie('pass', '', 0x7fffffff, '/');
}

function loggedinorreturn()
  {
   global $CURUSER;
   if (!$CURUSER)
     {
      header('Location: '.$BASEURL.'/login.php?returnto='.urlencode($_SERVER['REQUEST_URI']));
      exit();
     }
  }

function deletetorrent($id) {
	global $CONFIG;
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

	@unlink($CONFIG['torrent_dir'].'/'.$id.'.torrent');

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

function pager($rpp, $count, $href, $opts = array()) {
    $pages = ceil($count / $rpp);

    if (!isset($opts["lastpagedefault"]) || !$opts["lastpagedefault"])
        $pagedefault = 0;
    else {
        $pagedefault = floor(($count - 1) / $rpp);
        if ($pagedefault < 0)
            $pagedefault = 0;
    }

    if (isset($_GET["page"])) {
        $page = 0 + $_GET["page"];
        if ($page < 0)
            $page = $pagedefault;
    }
    else
        $page = $pagedefault;

    $pager = "";

    $mp = $pages - 1;
    $as = "<b>&lt;&lt;&nbsp;Prev</b>";
    if ($page >= 1) {
        $pager .= "<a href=\"{$href}page=" . ($page - 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;
    $pager .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $as = "<b>Next&nbsp;&gt;&gt;</b>";
    if ($page < $mp && $mp >= 0) {
        $pager .= "<a href=\"{$href}page=" . ($page + 1) . "\">";
        $pager .= $as;
        $pager .= "</a>";
    }
    else
        $pager .= $as;

    if ($count) {
        $pagerarr = array();
        $dotted = 0;
        $dotspace = 3;
        $dotend = $pages - $dotspace;
        $curdotend = $page - $dotspace;
        $curdotstart = $page + $dotspace;
        for ($i = 0; $i < $pages; $i++) {
            if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
                if (!$dotted)
                    $pagerarr[] = "...";
                $dotted = 1;
                continue;
            }
            $dotted = 0;
            $start = $i * $rpp + 1;
            $end = $start + $rpp - 1;
            if ($end > $count)
                $end = $count;
            $text = "$start&nbsp;-&nbsp;$end";
            if ($i != $page)
                $pagerarr[] = "<a href=\"{$href}page=$i\"><b>$text</b></a>";
            else
                $pagerarr[] = "<b>$text</b>";
        }
        $pagerstr = join(" | ", $pagerarr);
        $pagertop = '<p style="text-align: center">'.$pager.'<br />'.$pagerstr.'</p>'."\n";
        $pagerbottom = '<p style="text-align: center">'.$pagerstr.'<br />'.$pager.'</p>'."\n";
    }
    else {
        $pagertop = '<p style="text-align: center">'.$pager.'</p>'."\n";
        $pagerbottom = $pagertop;
    }

    $start = $page * $rpp;

    return array($pagertop, $pagerbottom, "LIMIT $start,$rpp");
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

function get_user_icons($arr, $big = false) {
	if ($big) {
		$donorpic = 'starbig.gif';
		$warnedpic = 'warnedbig.gif';
		$disabledpic = 'disabledbig.gif';
		$style = 'style="margin-left: 4pt"';
	}
	else {
		$donorpic = 'star.gif';
		$warnedpic = 'warned.gif';
		$disabledpic = 'disabled.gif';
		$style = 'style="margin-left: 2pt"';
	}

	$pics = $arr['settings']['donor'] ? '<img src="pic/'.$donorpic.'" alt="Donor" border="0" '.$style.'>' : '';
	if ($arr['enabled'] == 'yes')
		$pics .= $arr['settings']['warned'] ? '<img src="pic/'.$warnedpic.'" alt="Warned" border="0" '.$style.'>' : '';
	else
		$pics .= '<img src="pic/'.$disabledpic.'" alt="Disabled" border="0" '.$style.'>'."\n";

	return $pics;
}

require_once(INCL_PATH.'global.php');
?>
