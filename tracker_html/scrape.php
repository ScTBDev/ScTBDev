<?
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

const SCRAPE_ERR = 'd5:filesdee';

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'tdefines.php');


$q = explode('&',$_SERVER['QUERY_STRING']);
$_GET = array();
foreach ($q as $p)
  {
   $ps = explode('=',$p,2);
   $p1 = rawurldecode(trim($ps[0]));
   $p2 = rawurldecode(trim($ps[1]));
   if (strlen($p1) > 0)
     {
      if (!isset($_GET[$p1]))
        {
         $_GET[$p1] = $p2;
        }
      elseif (!is_array($_GET[$p1]))
        {
         $temp = $_GET[$p1];
         unset($_GET[$p1]);
         $_GET[$p1] = array();
         $_GET[$p1][] = $temp;
         $_GET[$p1][] = $p2;
        }
      else
        {
         $_GET[$p1][] = $p2;
        }
     }
  }

/////////////////////////////////////////
header('Content-Type: text/plain');

require_once(TINCL_PATH.'announce_funcs.php');

if (isset($_GET['passkey']) && strlen($_GET['passkey']) != 32)
  {
   $lenpasskey = strlen($_GET['passkey']);
   if ($lenpasskey > 32 && preg_match('/^([0-9a-f]{32})\?(([0-9a-zA-Z]|_)+)\=/',$_GET['passkey'], $matches))
     {
      $lenget = strlen($matches[0]);
      $valget = substr($_GET['passkey'], $lenget);
      if (!isset($_GET[$matches[2]]))
        $_GET[$matches[2]] = $valget;
      elseif (!is_array($_GET[$matches[2]]))
        {
         $temp = $_GET[$matches[2]];
         $_GET[$matches[2]] = array();
         $_GET[$matches[2]][] = $temp;
         $_GET[$matches[2]][] = $valget;
        }
      else
        $_GET[$matches[2]][] = $valget;

      $_GET['passkey'] = $matches[1];
     }
   else
     bt_tracker::err('passkey not valid, please redownload your torrent file');
  }

$passkey = $_GET['passkey'];
if (!$passkey)
	die(SCRAPE_ERR);

$numhash = count($_GET['info_hash']);
$torrents = array();
if ($numhash < 1)
  die(SCRAPE_ERR);
elseif ($numhash == 1) {
	$torrent = bt_mem_caching::get_torrent_from_hash(bt_string::str2hex($_GET['info_hash']));
	if ($torrent)
		$torrents[$_GET['info_hash']] = $torrent;
}
else {
	foreach($_GET['info_hash'] as $hash) {
		$torrent = bt_mem_caching::get_torrent_from_hash(bt_string::str2hex($hash));
		if ($torrent)
			$torrents[$hash] = $torrent;
	}
}

$user = bt_mem_caching::get_user_from_passkey($passkey);
if (!$user || !count($torrents))
	die(SCRAPE_ERR);

if (!($user['flags'] & bt_options::USER_BYPASS_BANS)) {
	$rip = bt_vars::$realip;
	$ip = bt_vars::$ip;
	if (bt_bans::check($rip, false))
		bt_tracker::err('IP Banned');
	elseif ($ip != $rip) {
		if (bt_bans::check($ip, false))
			bt_tracker::err('IP Banned');
	}
}

$r = 'd5:filesd';

foreach ($torrents as $info_hash => $torrent)
	$r .= '20:'.$info_hash.'d8:completei'.$torrent['seeders'].'e10:downloadedi'.$torrent['times_completed'].'e10:incompletei'.$torrent['leechers'].'ee';

$r .= 'ee';

die($r);
?>
