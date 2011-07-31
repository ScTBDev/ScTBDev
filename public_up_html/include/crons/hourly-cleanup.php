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
require_once(CLASS_PATH.'bt_forums.php');
require_once(CLASS_PATH.'bt_vars.php');
require_once(CLASS_PATH.'bt_sql.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_mem_caching.php');
require_once(CLASS_PATH.'bt_options.php');


define('START_TIME', microtime(true));

bt_sql::connect();
/////////////////////////////////// Disable cheaters at random time /////////////////////////////////////
$anti_cheatq = bt_sql::query('SELECT * FROM `cheat_disables` WHERE `added` < '.(time() - rand(57600, 259200)));
while ($cheater = $anti_cheatq->fetch_assoc()) {
	$cheaterid = 0 + $cheater['user'];
	$userq = bt_sql::query('SELECT `username`, `passkey`, `invitedby` FROM `users` WHERE `id` = '.$cheaterid);
	$user = $userq->fetch_assoc();
	$userq->free();
	if ($user) {
		bt_user::init_mod_comment($cheaterid);
		bt_sql::query('UPDATE users SET flags = (flags & ~'.bt_options::USER_ENABLED.') WHERE `id` = '.$cheaterid);
		bt_mem_caching::remove_passkey($user['passkey']);
		bt_user::mod_comment($cheaterid, 'Auto-disabled for cheating');

		write_staff_log('User '.$cheaterid.' ('.$user['username'].') Auto-disabled for cheating','BAN');
	}
	bt_sql::query('DELETE FROM `cheat_disables` WHERE `id` = '.$cheater['id']);
}
$anti_cheatq->free();
///////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////
//						Main Forum Resync Start							//
//////////////////////////////////////////////////////////////////////////

// Populate Last Post for each topic
$last_posts = $topic_post_counts = array();
$posts = bt_sql::query('SELECT id, added, topicid, userid FROM posts ORDER BY added DESC', false);
while ($post = $posts->fetch_assoc()) {
	$topicid = 0 + $post['topicid'];
	$userid = 0 + $post['userid'];

	if (!isset($last_posts[$topicid])) {
		$added = 0 + $post['added'];
		$postid = 0 + $post['id'];
		$last_posts[$topicid] = array($postid, $added);
		$topic_post_counts[$topicid] = 0;
	}

	$topic_post_counts[$topicid]++;
}
$posts->free();
unset($posts);

// Topics Table Resync
$updatetopics = $empty_topics = $forum_post_counts = $forum_topic_counts = $last_topics = $last_topic_posts = array();
$topics = bt_sql::query('SELECT id, forumid, lastpost, posts, locked FROM topics', false);
while ($topic = $topics->fetch_assoc()) {
	$topicid = 0 + $topic['id'];
	$forumid = 0 + $topic['forumid'];

	// // if there are no posts for this topic, then add it for deletion
	if (!isset($topic_post_counts[$topicid])) {
		$empty_topics[] = $topicid;
		bt_forums::delete_topic_cache($topicid);
		continue;
	}
	$posts = $topic_post_counts[$topicid];

	if (!isset($forum_topic_counts[$forumid])) {
		$forum_topic_counts[$forumid] = 0;
		$forum_post_counts[$forumid] = 0;
		$last_topics[$forumid] = 0;
		$last_topic_posts[$forumid] = 0;
	}

	list($lastpost, $lastposttime) = $last_posts[$topicid];

	if ($lastpost > $last_topic_posts[$forumid]) {
		$last_topic_posts[$forumid] = $lastpost;
		$last_topics[$forumid] = $topicid;
	}

	$auto_lock = bt_vars::$timestamp - (42 * 86400);
	$locked = ($topic['locked'] == 'no' ? ($lastposttime < $auto_lock ? 'yes' : 'no') : 'yes');

	if ($topic['lastpost'] != $lastpost || $topic['posts'] != $posts || $topic['locked'] != $locked) {
		bt_forums::delete_topic_cache($topicid);
		$updatetopics[] = '('.$topicid.', '.$posts.', '.$lastpost.', "'.$locked.'")';
	}

	$forum_topic_counts[$forumid]++;
	$forum_post_counts[$forumid] += $posts;
}
$topics->free();
unset($topics, $last_posts, $topic_post_counts);

// update all topics with changes with 1 query
if (count($updatetopics))
	bt_sql::query('INSERT INTO topics (id, posts, lastpost, locked) VALUES '.implode(', ', $updatetopics).
		' ON DUPLICATE KEY UPDATE posts = VALUES(posts), lastpost = VALUES(lastpost), locked = VALUES(locked)');
unset($updatetopics);

if (count($empty_topics))
	bt_sql::query('DELETE FROM topics WHERE id IN ('.implode(', ', $empty_topics).')');
unset($empty_topics);

// Forums Table Resync
$updateforums = array();
$forums = bt_sql::query('SELECT id, postcount, topiccount, lasttopic FROM forums', false);
while ($forum = $forums->fetch_assoc()) {
	$forumid = 0 + $forum['id'];

	if (!isset($forum_topic_counts[$forumid])) {
		$forum_topic_counts[$forumid] = 0;
		$forum_topic_counts[$forumid] = 0;
		$last_topics[$forumid] = 0;
	}

	$postcount = $forum_post_counts[$forumid];
	$topiccount = $forum_topic_counts[$forumid];
	$lasttopic = $last_topics[$forumid];

	if ($forum['postcount'] != $postcount || $forum['topiccount'] != $topiccount || $forum['lasttopic'] != $lasttopic) {
		bt_forums::delete_forum_cache($forumid);
		$updateforums[] = '('.$forumid.', '.$topiccount.', '.$postcount.', '.$lasttopic.')';
	}
}
$forums->free();
unset($forums, $forum_post_counts, $forum_topic_counts, $last_topics);

if (count($updateforums))
	bt_sql::query('INSERT INTO forums (id, topiccount, postcount, lasttopic) VALUES '.implode(', ', $updateforums).
		' ON DUPLICATE KEY UPDATE topiccount = VALUES(topiccount), postcount = VALUES(postcount), lasttopic = VALUES(lasttopic)');
unset($updateforums);

//////////////////////////////////////////////////////////////////////////
//						Main Forum Resync Complete						//
//////////////////////////////////////////////////////////////////////////


// cleanup orphaned posts (topic doesn't exist), this almost never happens
$orphaned_posts = array();
$posts_orphaned = bt_sql::query('SELECT p.id FROM posts AS p LEFT JOIN topics as t ON (t.id = p.topicid) WHERE t.id IS NULL', false);
while ($orphaned_post = $posts_orphaned->fetch_row())
	$orphaned_posts[] = 0 + $orphaned_post[0];
$posts_orphaned->free();

if (count($orphaned_posts)) {
	foreach ($orphaned_posts as $orphaned_post)
		bt_forums::delete_post_cache($orphaned_post);

	bt_sql::query('DELETE FROM posts WHERE id IN('.implode(', ', $orphaned_posts).')');
}
unset($orphaned_posts);


// cleanup edits without the adjoining post
$orphaned_edits = array();
$posts_edits = bt_sql::query('SELECT e.id FROM posts_edits AS e LEFT JOIN posts AS p ON (p.id = e.postid) WHERE p.id IS NULL', false);
while ($posts_edit = $posts_edits->fetch_row())
	$orphaned_edits[] = 0 + $posts_edit[0];
$posts_edits->free();

if (count($orphaned_edits))
	bt_sql::query('DELETE FROM posts_edits WHERE id IN('.implode(', ', $orphaned_edits).')');

unset($orphaned_edits);
////////////////////////////////////

// delete items in sitelog older than a month
$secs = 30 * 24 * 3600;
bt_sql::query('DELETE FROM sitelog WHERE ('.time().' - added) > '.$secs);

////////////////////////////////////////////
// Delete Messages that have no owner, or change their location if owned by 2 people

$update_messages = $delete_messages = array();
$rmsgs = bt_sql::query('SELECT `m`.`id`, `m`.`location` FROM `messages` AS `m` LEFT JOIN `users` AS `u` '.
	'ON (`u`.`id` = `m`.`receiver`) WHERE `m`.`location` IN ("in","both") AND `u`.`id` IS NULL '.
	'ORDER BY `m`.`id` ASC', false);
while ($rmsg = $rmsgs->fetch_assoc()) {
	$messageid = 0 + $rmsg['id'];
	if ($rmsg['location'] == 'both')
		$update_messages[] = '('.$messageid.', "out")';
	else
		$delete_messages[] = $messageid;
}
$rmsgs->free();


$smsgs = bt_sql::query('SELECT `m`.`id`, `m`.`location` FROM `messages` AS `m` LEFT JOIN `users` AS `u` '.
	'ON (`u`.`id` = `m`.`sender`) WHERE `m`.`location` IN ("out","both") AND `u`.`id` IS NULL '.
	'ORDER BY `m`.`id` ASC', false);
while ($smsg = $smsgs->fetch_assoc()) {
	$messageid = 0 + $smsg['id'];
	if ($smsg['location'] == 'both')
		$update_messages[] = '('.$messageid.', "in")';
	else
		$delete_messages[] = $messageid;
}
$smsgs->free();

if (count($update_messages))
	bt_sql::query('INSERT INTO messages (id, location) VALUES '.implode(', ', $update_messages).
        ' ON DUPLICATE KEY UPDATE location = VALUES(location)');
unset($update_messages);

if (count($delete_messages))
	bt_sql::query('DELETE FROM messages WHERE id IN ('.implode(', ', $delete_messages).')');
unset($delete_messages);

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                     User Account Cleanup                                     //
//////////////////////////////////////////////////////////////////////////////////////////////////
$delete_users = array();

//////////////////////////////////////////////////////
$secs = 42*86400;
$dt = time() - $secs;
$maxclass = UC_USER;
$delusersql = 'SELECT id, passkey FROM users WHERE (flags & '.bt_options::USER_CONFIRMED.') AND class <= '.$maxclass.' '.'AND last_access < '.$dt;
$deluserq = bt_sql::query($delusersql);
while ($user = $deluserq->fetch_assoc())
	$delete_users[$user['passkey']] = $user['id'];


$secs2 = 16*86400;
$dt2 = time() - $secs2;
$maxclass2 = UC_POWER_USER;
$delusersql = 'SELECT id, passkey FROM users WHERE (flags & '.bt_options::USER_CONFIRMED.') AND (flags & '.bt_options::USER_ENABLED.') = 0 AND class <= '.$maxclass2.' AND last_access < '.$dt;
$deluserq = bt_sql::query($delusersql);
while ($user = $deluserq->fetch_assoc())
	$delete_users[$user['passkey']] = $user['id'];


//////////////////////////////////////////////////////
$secs = 100*86400;
$dt = time() - $secs;
$maxclass = UC_LOVER;
$delusersql = 'SELECT id, passkey FROM users WHERE (flags & '.bt_options::USER_CONFIRMED.') AND class <= '.$maxclass.' AND last_access < '.$dt;
$deluserq = bt_sql::query($delusersql);
while ($user = $deluserq->fetch_assoc())
	$delete_users[$user['passkey']] = $user['id'];


$secs2 = 30*86400;
$dt2 = time() - $secs2;
$maxclass2 = UC_LOVER;
$delusersql = 'SELECT id, passkey FROM users WHERE (flags & '.bt_options::USER_CONFIRMED.') AND (flags & '.bt_options::USER_ENABLED.') = 0 AND class <= '.$maxclass2.' AND last_access < '.$dt2;
$deluserq = bt_sql::query($delusersql);
while ($user = $deluserq->fetch_assoc())
	$delete_users[$user['passkey']] = $user['id'];


//////////////////////////////////////////////////////
$secs = 180*86400;
$dt = time() - $secs;
$maxclass = UC_SEED_WHORE;
$delusersql = 'SELECT id, passkey FROM users WHERE (flags & '.bt_options::USER_CONFIRMED.') AND class <= '.$maxclass.' AND last_access < '.$dt;
$deluserq = bt_sql::query($delusersql);
while ($user = $deluserq->fetch_assoc())
	$delete_users[$user['passkey']] = $user['id'];


$secs3 = 60*86400;
$dt3 = time() - $secs3;
$maxclass3 = UC_SEED_WHORE;
$delusersql = 'SELECT id, passkey FROM users WHERE (flags & '.bt_options::USER_CONFIRMED.') AND (flags & '.bt_options::USER_ENABLED.') = 0 AND class <= '.$maxclass3.' AND last_access < '.$dt3;
$deluserq = bt_sql::query($delusersql);
while ($user = $deluserq->fetch_assoc())
	$delete_users[$user['passkey']] = $user['id'];


//////////////////////////////////////////////////////

if (count($delete_users)) {
	foreach ($delete_users as $passkey => $deleteid) {
		bt_user::init_mod_comment($deleteid, true);
		bt_mem_caching::remove_passkey($passkey);
		bt_user::mod_comment($deleteid, 'Deleted for inactivity');
	}

	$where = '`id` IN ('.implode(', ', $delete_users).')';
	bt_sql::query('INSERT INTO `users_deleted` SELECT * FROM `users` WHERE '.$where);
	bt_sql::query('DELETE FROM `users` WHERE '.$where);
}
unset($delete_users);



//////////////////////////////////////////////////////////////////////////////////////////////////
//                                     Session Cleanup                                          //
//////////////////////////////////////////////////////////////////////////////////////////////////

bt_sql::query('DELETE FROM `sessions` WHERE (`added` + `maxage`) < '.time());
bt_sql::query('DELETE FROM `sessions` WHERE (`lastaction` + `maxidle`) < '.time());

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                   Email Change Cleanup                                       //
//////////////////////////////////////////////////////////////////////////////////////////////////

$maxage = 86400; // 1 day
$lt = time() - $maxage;
bt_sql::query('DELETE FROM `email_changes` WHERE `time` < '.$lt);


bt_sql::query('UPDATE `users` SET `flags` = (`flags` & ~'.bt_options::USER_UPLOADER.') WHERE '.
	'(`flags` & '.bt_options::USER_UPLOADER.') AND `class` < '.UC_UPLOADER);


//////////////////////////////////////////////////////////////////////////////////////////////////
//                         Resync inbox/sentbox/seeding/leeching counts                         //
//////////////////////////////////////////////////////////////////////////////////////////////////

$issql = 'SELECT u.id, u.inbox, (
  SELECT COUNT(*)
  FROM messages
  WHERE receiver = u.id AND location IN ("in","both")
) AS inbox_num,
u.inbox_new, (
  SELECT COUNT(*)
  FROM messages
  WHERE receiver = u.id AND location IN ("in","both") AND unread = "yes"
) AS inbox_new_num,
u.sentbox, (
  SELECT COUNT(*)
  FROM messages
  WHERE sender = u.id AND location IN ("out","both")
) AS sentbox_num,
u.seeding, (
  SELECT COUNT(*)
  FROM peers
  WHERE userid = u.id AND seeder = "yes"
) AS seeding_num,
u.leeching, (
  SELECT COUNT(*)
  FROM peers
  WHERE userid = u.id AND seeder = "no"
) AS leeching_num,
u.comments, (
  SELECT COUNT(*)
  FROM comments
  WHERE user = u.id
) AS comments_num,
u.posts, (
  SELECT COUNT(*)
  FROM posts
  WHERE userid = u.id
) AS posts_num
FROM users AS u
ORDER BY u.id ASC';

$updateusers = array();
$uis = bt_sql::query($issql, false);
while ($is = $uis->fetch_assoc()) {
	if ($is['inbox'] != $is['inbox_num'] || $is['inbox_new'] != $is['inbox_new_num'] || $is['sentbox'] != $is['sentbox_num'] || $is['seeding'] != $is['seeding_num'] ||
		$is['leeching'] != $is['leeching_num'] || $is['comments'] != $is['comments_num'] || $is['posts'] != $is['posts_num'])
		$updateusers[] = '('.$is['id'].', '.$is['inbox_num'].', '.$is['inbox_new_num'].', '.$is['sentbox_num'].', '.
			$is['seeding_num'].', '.$is['leeching_num'].', '.$is['comments_num'].', '.$is['posts_num'].')';
}
$uis->free();

if (count($updateusers))
	bt_sql::query('INSERT INTO users (id, inbox, inbox_new, sentbox, seeding, leeching, comments, posts) VALUES '.implode(', ', $updateusers).
		' ON DUPLICATE KEY UPDATE inbox = VALUES(inbox), inbox_new = VALUES(inbox_new), sentbox = VALUES(sentbox), '.
		'seeding = VALUES(seeding), leeching = VALUES(leeching), comments = VALUES(comments), posts = VALUES(posts)');
unset($updateusers);


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                    Resync Torrent Stats                                      //
//////////////////////////////////////////////////////////////////////////////////////////////////
$tsql = 'SELECT t.id, t.seeders, (
  SELECT COUNT(*)
  FROM peers
  WHERE torrent = t.id AND seeder = "yes"
) AS seeders_num,
t.leechers, (
  SELECT COUNT(*)
  FROM peers
  WHERE torrent = t.id
  AND seeder = "no"
) AS leechers_num,
t.comments, (
  SELECT COUNT(*)
  FROM comments
  WHERE torrent = t.id
) AS comments_num
FROM torrents AS t
ORDER BY t.id ASC';

$updatetorrents = array();
$tq = bt_sql::query($tsql, false);
while ($t = $tq->fetch_assoc()) {
	if ($t['seeders'] != $t['seeders_num'] || $t['leechers'] != $t['leechers_num'] || $t['comments'] != $t['comments_num'])
		$updatetorrents[] = '('.$t['id'].', '.$t['seeders_num'].', '.$t['leechers_num'].', '.$t['comments_num'].')';
}
$tq->free();

if (count($updatetorrents))
	bt_sql::query('INSERT INTO torrents (id, seeders, leechers, comments) VALUES '.implode(', ', $updatetorrents).
        ' ON DUPLICATE KEY UPDATE seeders = VALUES(seeders), leechers = VALUES(leechers), comments = VALUES(comments)');
unset($updatetorrents);


////////////////////////////////////////////////////////////////////////////////
//                    Initialize variables for cleanups                       //
////////////////////////////////////////////////////////////////////////////////

$user_ids = $torrent_ids = array();

$uq = bt_sql::query('SELECT `id` FROM `users`', false);
while ($u = $uq->fetch_row())
	$user_ids[((int)$u[0])] = true;
$uq->free();

$tq = bt_sql::query('SELECT `id` FROM `torrents`', false);
while ($t = $tq->fetch_row())
	$torrent_ids[((int)$t[0])] = true;
$tq->free();


////////////////////////////////////////////////////////////////////////////////
//                       Cleanup `readposts` table					          //
////////////////////////////////////////////////////////////////////////////////

$readposts = array();

$rq = bt_sql::query('SELECT DISTINCT `userid` FROM `readposts`', false);
while ($r = $rq->fetch_row())
	$readposts[((int)$r[0])] = true;
$rq->free();

$del_readposts = array_keys(array_diff_key($readposts, $user_ids));

if (count($del_readposts))
	bt_sql::query('DELETE FROM `readposts` WHERE `userid` IN ('.implode(', ', $del_readposts).')');

unset($readposts, $del_readposts);


////////////////////////////////////////////////////////////////////////////////
//                      Cleanup `torrents_anon` table						  //
////////////////////////////////////////////////////////////////////////////////

$anon_torrents = array();

$aq = bt_sql::query('SELECT `id` FROM `torrents_anon`', false);
while ($a = $aq->fetch_row())
	$anon_torrents[((int)$a[0])] = true;
$aq->free();

$del_anon_torrents = array_keys(array_diff_key($anon_torrents, $torrent_ids));

if (count($del_anon_torrents))
	bt_sql::query('DELETE FROM `torrents_anon` WHERE `id` IN ('.implode(', ', $del_anon_torrents).')');

unset($anon_torrents, $del_anon_torrents);


////////////////////////////////////////////////////////////////////////////////
//                      Cleanup `snatched` table							  //
////////////////////////////////////////////////////////////////////////////////
$snatches = array();

$sq = bt_sql::query('SELECT DISTINCT torrent FROM snatched', false);
while ($s = $sq->fetch_row())
	$snatches[((int)$s[0])] = true;
$sq->free();

$del_snatches = array_keys(array_diff_key($snatches, $torrent_ids));

if (count($del_snatches))
	bt_sql::query('DELETE FROM snatched WHERE torrent IN ('.implode(', ', $del_snatches).')');

unset($snatches, $del_snatches);


// Clear Users and Torrents IDs
unset($user_ids, $torrent_ids);

// Delete 1 year old snatched entries
$old = bt_vars::$timestamp - (365 * 86400);
bt_sql::query('DELETE FROM snatched WHERE last_time < '.$old);

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

bt_user::comit_mod_comments();

////////////////////////////////////////////////////////////////
$extime = round(microtime(true) - START_TIME, 4);

if ($argv[1] == '-d')
	echo 'Cleanup took '.$extime.' seconds ['.bt_sql::$DB->query_count.' queries ('.round(bt_sql::$DB->query_time, 4).'s)] '.
		'Using up to '.bt_theme::mksize(memory_get_peak_usage()).' Memory'."\n";
?>
