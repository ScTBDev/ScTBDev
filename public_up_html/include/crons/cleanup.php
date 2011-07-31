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

require_once(__DIR__.DIRECTORY_SEPARATOR.'cron-config.php');
require_once(INCL_PATH.'bittorrent.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_pm.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_options.php');
require_once(CLASS_PATH.'bt_config.php');

ini_set('memory_limit', '64M');
bt_sql::connect();
$time = time();
set_time_limit(0);
ignore_user_abort(true);

$max_torrents_del = 25;
$torrent_seeds = $torrent_leeches = $user_seeds = $user_leeches = array();

do {
	$res = bt_sql::query('SELECT id FROM torrents');
	$ar = array();
	while ($row = $res->fetch_row()) {
		$id = $row[0];
		$ar[$id] = 1;
	}
	$res->free();

	if (!count($ar))
		break;

	$dp = @opendir(bt_config::$conf['torrent_dir']);
	if (!$dp)
		break;

	$ar2 = array();
	$delff = array();
	while (($file = readdir($dp)) !== false) {
		if (!preg_match('/^(\d+)\.torrent$/', $file, $m))
			continue;
		$id = $m[1];
		$ar2[$id] = 1;
		if (isset($ar[$id]) && $ar[$id])
			continue;

		$delff[] = bt_config::$conf['torrent_dir'].'/'.$file;
	}
	closedir($dp);

	if (!count($ar2))
		break;

	$delids = array();
	foreach (array_keys($ar) as $k) {
		if (isset($ar2[$k]) && $ar2[$k])
			continue;
		$delids[] = $k;
		unset($ar[$k]);
	}

	if (count($delff)) {
		if (count($delff) > $max_torrents_del)
			die('Script trying to delete too many torrent files, please investigate, something is probably gone wrong.'."\n");

		foreach ($delff as $ff)
			@unlink($ff);
	}

	if (count($delids)) {
		if (count($delids) >= $max_torrents_del)
			die('Script trying to delete too many torrents, please investigate, something is probably gone wrong.'."\n");

		bt_sql::query('DELETE FROM torrents WHERE id IN ('.join(',', $delids).')');
	}

	foreach ($delids as $delid) {
		bt_mem_caching::remove_torrent_peers($delid);
	}

	$res = bt_sql::query('SELECT torrent FROM peers GROUP BY torrent');
	$delids = array();
	while ($row = $res->fetch_row()) {
		$id = $row[0];
		if (isset($ar[$id]) && $ar[$id])
			continue;
		$delids[] = $id;
	}
	$res->free();

	if (count($delids)) {
		$res = bt_sql::query('SELECT userid, seeder FROM peers WHERE torrent IN ('.join(',', $delids).')');
		while ($peer = $res->fetch_assoc()) {
			$userid = 0 + $peer['userid'];
			$seed = $peer['seeder'] === 'yes';

			if (!isset($user_seeds[$userid]))
				$user_seeds[$userid] = $user_leeches[$userid] = 0;

			if ($seed)
				$user_seeds[$userid]++;
			else
				$user_leeches[$userid]++;
		}
		$res->free();
		bt_sql::query('DELETE FROM peers WHERE torrent IN ('.join(',', $delids).')');
	}

	$res = bt_sql::query('SELECT torrent FROM files GROUP BY torrent');
	$delids = array();
	while ($row = $res->fetch_row()) {
		$id = $row[0];
		if ($ar[$id])
			continue;
		$delids[] = $id;
	}
	$res->free();

	if (count($delids))
		bt_sql::query('DELETE FROM files WHERE torrent IN ('.join(',', $delids).')');

	unset($delids);
} while(false);

$deadtime = deadtime();
$dead_peers = bt_sql::query('SELECT torrent, userid, peer_id, seeder FROM peers WHERE last_action < '.$deadtime);
while ($dead_peer = $dead_peers->fetch_assoc()) {
	$torrentid = 0 + $dead_peer['torrent'];
	$userid = 0 + $dead_peer['userid'];
	$seed = $dead_peer['seeder'] === 'yes';
	bt_sql::query('UPDATE snatched SET last_action = "Ghost" WHERE torrent = '.$torrentid.' AND user = '.$userid);
	bt_sql::query('DELETE FROM peers WHERE torrent = '.$torrentid.' AND peer_id = '.bt_sql::esc($dead_peer['peer_id']));

	if (!isset($torrent_seeds[$torrentid]))
		$torrent_seeds[$torrentid] = $torrent_leeches[$torrentid] = 0;
	if (!isset($user_seeds[$userid]))
		$user_seeds[$userid] = $user_leeches[$userid] = 0;

	if ($seed) {
		$torrent_seeds[$torrentid]++;
		$user_seeds[$userid]++;
	}
	else {
		$torrent_leeches[$torrentid]++;
		$user_leeches[$userid]++;
	}
}

$deadtime -= bt_config::$conf['max_dead_torrent_time'];
bt_sql::query('UPDATE torrents SET visible = "no" WHERE visible = "yes" AND last_action < '.$deadtime);


// remove incomplete singups
$deadtime = time() - bt_config::$conf['signup_timeout'];
$res = bt_sql::query('SELECT `id`, `invitedby` FROM `users` WHERE !(`flags` & '.bt_options::USER_CONFIRMED.') AND `added` < '.$deadtime.' AND `last_login` < '.$deadtime.' AND `last_access` < '.$deadtime);
while ($row = $res->fetch_assoc()) {
	// delete the user
	bt_sql::query('DELETE FROM `users` WHERE `id` = '.$row['id']);
	// give invite back to the inviter
	bt_sql::query('UPDATE `users` SET `invites` = (`invites` + 1) WHERE `id` = '.$row['invitedby']);
}
$res->free();



//remove expired warnings
$res = bt_sql::query('SELECT `id` FROM `users` WHERE (`flags` & '.bt_options::USER_WARNED.')  AND `warneduntil` < '.$time.
	' AND `warneduntil` != 0') or bt_sql::err(__FILE__, __LINE__);
if ($res->num_rows > 0) {
	$msg = 'Your warning has been removed. Please keep in your best behaviour from now on.';
	$subject = 'Warning removed';
	while ($arr = $res->fetch_row()) {
		$id = $arr[0];
		bt_sql::query('UPDATE `users` SET `flags` = (`flags` & ~'.bt_options::USER_WARNED.'), `warneduntil` = 0 '.
			'WHERE `id` = '.$id) or bt_sql::err(__FILE__, __LINE__);
		bt_pm::send(0, $id, $msg, $subject, bt_pm::PM_INBOX);
	}
}
$res->free();

//////////////////////////////////////////////
//				 Promotions					//
//////////////////////////////////////////////

$limit = 50 * 1024 * 1024 * 1024;  $minratio = 1.05; $regtime = 86400 * 21;
$extmsg = 'Your new privileges as Power User:'."\n".
          '[*] Access to torrent NFOs.';
bt_user::auto_promote(UC_USER, UC_POWER_USER, $minratio, $limit, $regtime, $extmsg);			// promote power users

$limit = 250 * 1024 * 1024 * 1024; $minratio = 1.2; $regtime = 86400 * 35;
$extmsg = 'Your new privileges as Xtreme User:'."\n".
          '[*] Access to Top 10 page.';
bt_user::auto_promote(UC_POWER_USER, UC_XTREME_USER, $minratio, $limit, $regtime, $extmsg);	// promote xtreme users

$limit = 500 * 1024 * 1024 * 1024; $minratio = 1.5;  $regtime = 86400 * 70;
$add_chans = bt_bitmask::chans('allow_tracers');
$extmsg = 'Your new privileges as ScT Lover:
[*] Access to #sct.tracers IRC channel (must be enabled in your profile).';
bt_user::auto_promote(UC_XTREME_USER, UC_LOVER, $minratio, $limit, $regtime, $extmsg, 0, $add_chans);	// promote sct lover's

$limit = 1024 * 1024 * 1024 * 1024; $minratio = 2; $regtime = 86400 * 105;
$add_chans = bt_bitmask::chans('invite_pre','allow_pre');
$extmsg = 'Your new privileges as ScT Whore:'."\n".
          '[*] Access to IRC Pre Channel.'."\n".
          '[*] Ability to edit custom title.'."\n".
          '[*] Can set appear as anonymous.';
bt_user::auto_promote(UC_LOVER, UC_WHORE, $minratio, $limit, $regtime, $extmsg, 0, $add_chans);				// promote sct whore's

$limit = 3 * 1024 * 1024 * 1024 * 1024;  $minratio = 3;  $regtime = 86400 * 140;
bt_user::auto_promote(UC_WHORE, UC_SUPER_WHORE, $minratio, $limit, $regtime);					// promote sct super whore's

$limit = 6 * 1024 * 1024 * 1024 * 1024; $minratio = 4; $regtime = 86400 * 175;
bt_user::auto_promote(UC_SUPER_WHORE, UC_SEED_WHORE, $minratio, $limit, $regtime);			// promote sct seed whore's

$limit = 30 * 1024 * 1024 * 1024 * 1024; $minratio = 1.1; $regtime = 86400 * 365; $downlimit = 3 * 1024 * 1024 * 1024 * 1024;
bt_user::auto_promote(UC_SEED_WHORE, UC_OVERSEEDER, $minratio, $limit, $regtime, '', 0, 0, $downlimit);


//////////////////////////////////////////////
//				  Demotions					//
//////////////////////////////////////////////


bt_user::auto_demote(UC_OVERSEEDER, UC_SEED_WHORE, 1.09);
bt_user::auto_demote(UC_SEED_WHORE, UC_SUPER_WHORE, 3.9);		// demote sct seed whore's
bt_user::auto_demote(UC_SUPER_WHORE, UC_WHORE, 2.9);			// demote sct super whore's
$remove_flags = bt_options::USER_ANON;
$remove_chans = bt_bitmask::chans('invite_pre','allow_pre');
bt_user::auto_demote(UC_WHORE, UC_LOVER, 1.9, $remove_flags, $remove_chans);	// demote sct whore's
$remove_chans = bt_bitmask::chans('invite_tracers','allow_tracers');
bt_user::auto_demote(UC_LOVER, UC_XTREME_USER, 1.4, 0, $remove_chans);		// demote sct lover's
bt_user::auto_demote(UC_XTREME_USER, UC_POWER_USER, 1.1);		// demote xtreme users's
bt_user::auto_demote(UC_POWER_USER, UC_USER, 0.95);			// demote power users's


// Delete Old System Messages
$maxdays = 21;
$maxdate = time() - ($maxdays * 86400);
bt_sql::query('DELETE FROM `messages` WHERE `sender` = 0 AND `added` < '.$maxdate);


// WARNINGS
function autowarn($length, $limit, $minratio) {
	$msg = 'This is a automatic warning because your share ratio is too low at this moment.'."\n\n".
		'You have '.$length.' days to higher your share ratio.'."\n".'If your share ratio is too '.
		'low after these '.$length.' days you will be banned permanently!';
	$until = time() + (($length+0.1)*86400);
	$limit = $limit * 1024 * 1024 * 1024;
	$res = bt_sql::query('SELECT id FROM users WHERE class = '.UC_USER.' AND (flags & '.bt_options::USER_ENABLED.') AND downloaded >= '.$limit.
		' AND (uploaded / downloaded) < '.$minratio.' AND (flags & '.bt_options::USER_WARNED.') = 0') or bt_sql::err(__FILE__, __LINE__);
	while ($arr = $res->fetch_row()) {
		$id = $arr[0];
		bt_user::init_mod_comment($id);
		bt_sql::query('UPDATE users SET flags = (flags | '.bt_options::USER_WARNED.'), warneduntil = '.$until.' '.
			'WHERE id = '.$id) or bt_sql::err(__FILE__, __LINE__);
		bt_user::mod_comment($id, 'Auto-warned for low ratio');
		bt_pm::send(0, $id, $msg, 'Warning received', bt_pm::PM_INBOX);
	}
	$res->free();
}

autowarn(14,10,0.1);
autowarn(14,20,0.2);
autowarn(14,40,0.3);
autowarn(21,80,0.4);
autowarn(21,160,0.5);

//AUTO-BAN
function autoban($length, $limit, $minratio) {
	$secs = $length*86400; // 1 day
	$length = time() + $secs; // less than 1 day of warning left
	$limit = $limit*1024*1024*1024;
	$reqflags = bt_options::USER_ENABLED | bt_options::USER_WARNED;
	$res = bt_sql::query('SELECT id, ip, username, passkey FROM users WHERE class = '.UC_USER.' AND '.
		'(flags & '.$reqflags.') = '.$reqflags.' AND warneduntil < '.$length.' AND downloaded > '.$limit.' AND '.
		'(uploaded / downloaded) < '.$minratio) or bt_sql::err(__FILE__,__LINE__);
	while ($arr = $res->fetch_assoc()) {
		$id = 0 + $arr['id'];
		bt_user::init_mod_comment($id);

		bt_sql::query('UPDATE users SET flags = (flags & ~'.bt_options::USER_ENABLED.') WHERE `id` = '.$id) or bt_sql::err(__FILE__, __LINE__);
		bt_mem_caching::remove_passkey($arr['passkey'], true);
		bt_user::mod_comment($id, 'Auto-banned for low ratio');
		$ip = ip2long($arr['ip']);
		$username = $arr['username'];
		$comment = 'Autoban user: [url=https://www.scenetorrents.org/userdetails.php?id='.$id.']'.$username.'[/url]';
		$comment = bt_sql::esc($comment);
		bt_sql::query('INSERT INTO signupbans (added, addedby, comment, first, last) '.
			'VALUES('.time().', 0, '.$comment.', '.$ip.', '.$ip.')') or bt_sql::err(__FILE__, __LINE__);
	}
	$res->free();
}

autoban(0.1,10,0.10);
autoban(0.1,20,0.20);
autoban(0.1,40,0.30);
autoban(0.1,80,0.40);
autoban(0.1,160,0.50);


// delete old dead torrents
$days = 10;
$dt = time() - ($days * 86400);
$del_torrents = array();
$res = bt_sql::query('SELECT id, name FROM torrents WHERE visible = "no" AND last_action < '.$dt);
while ($arr = $res->fetch_assoc()) {
	$tid = 0 + $arr['id'];
	$del_torrents[$tid] = $arr['name'];
}
$res->free();

if (count($del_torrents)) {
	if (count($del_torrents) > $max_torrents_del)
		die ('Script trying to delete too many torrents, please investigate, something is probably gone wrong.'."\n");

	foreach ($del_torrents as $tid => $tname) {
		bt_mem_caching::remove_torrent_peers($tid);
		@unlink(bt_config::$conf['torrent_dir'].'/'.$tid.'.torrent');
		write_log('Torrent '.$tid.' ('.$tname.') was deleted by system (dead for more than '.$days.' days)','DELE');
	}

	$tids = implode(', ', array_keys($del_torrents));
	bt_sql::query('DELETE FROM torrents WHERE id IN ('.$tids.')');
	bt_sql::query('DELETE FROM comments WHERE torrent IN ('.$tids.')');
	bt_sql::query('DELETE FROM files WHERE torrent IN ('.$tids.')');
	$peeres = bt_sql::query('SELECT userid, seeder FROM peers WHERE torrent IN ('.$tids.')');
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
	bt_sql::query('DELETE FROM peers WHERE torrent IN ('.$tids.')');
}

// Delete old signup bans
$delafter = time() - (3*30*24*60*60); // 3 months old
bt_sql::query('DELETE FROM `bans` WHERE `added` < '.$delafter.' AND `comment` LIKE "Autoban user: %"');


$invites = bt_sql::query('SELECT `id`, `userid` FROM `invites` WHERE (`added` + 432000) < '.time());
while ($invite = $invites->fetch_assoc()) {
	bt_sql::query('UPDATE `users` SET `invites` = (`invites` + 1) WHERE `id` = '.$invite['userid']);
	bt_sql::query('DELETE FROM `invites` WHERE `id` = '.$invite['id']);
}
$invites->free();




// Keep Seed/Leecher count in sync
foreach (array_keys($torrent_seeds) as $tid) {
	$update = array();
	bt_mem_caching::adjust_torrent_peers($tid, -$torrent_seeds[$tid], -$torrent_leeches[$tid], 0);
	if ($torrent_seeds[$tid])
		$update[] = 'seeders = (seeders - '.$torrent_seeds[$tid].')';
	if ($torrent_leeches[$tid])
		$update[] = 'leechers = (leechers - '.$torrent_leeches[$tid].')';

	bt_sql::query('UPDATE torrents SET '.implode(', ', $update).' WHERE id = '.$tid);
}

foreach (array_keys($user_seeds) as $uid) {
	$update = array();
//	bt_mem_caching::adjust_user_peers($uid, -$user_seeds[$uid], -$user_leeches[$uid]);
	if ($user_seeds[$uid])
		$update[] = 'seeding = (seeding - '.$user_seeds[$uid].')';
	if ($user_leeches[$uid])
		$update[] = 'leeching = (leeching - '.$user_leeches[$uid].')';

	bt_sql::query('UPDATE users SET '.implode(', ', $update).' WHERE id = '.$uid);
}


bt_user::comit_mod_comments();

///////////////////////////////////////////////////////////////////
$TIMES['end'] = microtime(true);
$extime = round($TIMES['end'] - $TIMES['start'], 4);

if ($argv[1] == '-d')
  echo 'Cleanup took '.$extime.' seconds ['.bt_sql::$DB->query_count.' queries ('.round(bt_sql::$DB->query_time, 4).'s)] Using up to '.bt_theme::mksize(memory_get_peak_usage()).' Memory'."\n";
?>
