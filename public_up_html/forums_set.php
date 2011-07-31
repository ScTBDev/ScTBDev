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

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_forums.php');
require_once(CLASS_PATH.'bt_session.php');

bt_loginout::db_connect(true);

$session = new bt_session(true, 3600);

$topicid = (int)$_POST['id'];
$locked = (bool)(0 + $_POST['locked']);
$sticky = (bool)(0 + $_POST['sticky']);
$location = (int)$_POST['location'];
$subject = (string)trim($_POST['subject']);
$delete = (bool)(0 + $_POST['delete']);
$page = (int)$_POST['page'];
$form_hash = isset($_POST['hash']) ? trim($_POST['hash']) : '';
if (!$session->check($form_hash, 'forums_set'))
	die('h4x');


if ($topicid < 1 || !bt_user::required_class(UC_FORUM_MODERATOR))
	die;

$topic = bt_forums::get_topic($topicid);
if (!$topic)
	bt_theme::error('Error', 'Topic not found.');

$forum = bt_forums::get_forum($topic['forumid']);
if (!$forum)
	bt_theme::error('Error', 'Topic is orphaned from forum.');

if (!bt_user::required_class($forum['minclasswrite']))
	bt_theme::error('Error', 'You do not have permission to write to this forum.');


$update_forums[$location] = $update_forums[$topic['forumid']] = $update_topic = $update_users = array();

if ($delete) {
	bt_forums::delete_topic_cache($topicid);
	$update_forums[$topic['forumid']] = array();
	if ($forum['lasttopic'] == $topicid) {
		//------- Get the id of the last topic before the one we're deleteing
		$lpq = bt_sql::query('SELECT `id` FROM `topics` WHERE `forumid` = '.$topic['forumid'].' ORDER BY `lastpost` DESC LIMIT 1,1') or bt_sql::err(__FILE__, __LINE__);
		$lp = @$lpq->fetch_row();
		$lpq->free();
		$newlasttopic = 0 + $lp[0];
		$update_forums[$topic['forumid']][] = '`lasttopic` = '.$newlasttopic;
	}

	$posts = array();
	$users = array();
	$num = 0;
	$pcres = bt_sql::query('SELECT `id`, `userid` FROM `posts` WHERE `topicid` = '.$topicid) or bt_sql::err(__FILE__, __LINE__);
	while ($p = $pcres->fetch_assoc()) {
		$num++;

		if (!isset($users[$p['userid']])) {
			$update_users[$p['userid']] = array();
			$users[$p['userid']] = 0;
		}

		$users[$p['userid']]++;
		$posts[] = $p['id'];
	}
	$pcres->free();

	foreach($users as $userid => $nump)
		$update_users[$userid][] = '`posts` = (`posts` - '.$nump.')';

	$update_forums[$topic['forumid']][] = '`postcount` = (`postcount` - '.$num.')';
	$update_forums[$topic['forumid']][] = '`topiccount` = (`topiccount` - 1)';

	bt_sql::query('DELETE FROM `topics` WHERE `id` = '.$topicid) or bt_sql::err(__FILE__, __LINE__);
	if (!bt_sql::$affected_rows)
		bt_theme::error('Error', 'Unable to delete topic.');
	bt_forums::delete_topic_cache($topicid);

	bt_sql::query('DELETE FROM `posts` WHERE `topicid` = '.$topicid) or bt_sql::err(__FILE__, __LINE__);
	foreach ($posts as $post)
		bt_forums::delete_post_cache($post);

	bt_sql::query('DELETE FROM `posts_edits` WHERE `postid` IN ('.implode(',', $posts).')');
}
else {
	if ($topic['locked'] != $locked) 
		$update_topic[] = '`locked` = "'.($locked ? 'yes' : 'no').'"';
	if ($topic['sticky'] != $sticky)
		$update_topic[] = '`sticky` = "'.($sticky ? 'yes' : 'no').'"';

	if ($topic['subject'] != $subject) {
		if ($subject == '')
			bt_theme::error('Error', 'You must enter a title!');

		if (bt_utf8::strlen($subject) > bt_forums::MAX_SUBJECT_LENGTH)
			bt_theme::error('Error','Subject is limited to '.bt_forums::MAX_SUBJECT_LENGTH.' characters');

		$update_topic[] = '`subject` = '.bt_sql::esc($subject);
	}

	if ($topic['forumid'] != $location) {
		$dest_forum = bt_forums::get_forum($location);
		if (!$dest_forum)
			bt_theme::error('Error', 'Destination forum does not exist.');

		if (!bt_user::required_class($dest_forum['minclasswrite']) || !bt_user::required_class($dest_forum['minclasscreate']))
			bt_theme::error('Error', 'You do not have permission create a topic in the destination forum.');

		$update_topic[] = '`forumid` = '.$location;
		$update_forums[$topic['forumid']][] = '`topiccount` = (`topiccount` - 1)';
		$update_forums[$topic['forumid']][] = '`postcount` = (`postcount` - '.$topic['posts'].')';
		

		if ($forum['lasttopic'] == $topicid) {
			//------- Get the id of the last topic before the one we're moving
			$lpq = bt_sql::query('SELECT `id` FROM `topics` WHERE `forumid` = '.$topic['forumid'].' ORDER BY `lastpost` DESC LIMIT 1,1') or bt_sql::err(__FILE__, __LINE__);
			$lp = @$lpq->fetch_row();
			$lpq->free();
			$oldlasttopic = 0 + $lp[0];
			$update_forums[$topic['forumid']][] = '`lasttopic` = '.$oldlasttopic;
		}

		$update_forums[$location][] = '`topiccount` = (`topiccount` + 1)';
		$update_forums[$location][] = '`postcount` = (`postcount` + '.$topic['posts'].')';

		$last_dest_topic = bt_forums::get_topic($dest_forum['lasttopic']);
		if ($last_dest_topic['lastpost'] < $topic['lastpost'])
			$update_forums[$location][] = '`lasttopic` = '.$topicid;
	}
}

if (count($update_topic)) {
	bt_forums::delete_topic_cache($topicid);
	bt_sql::query('UPDATE `topics` SET '.implode(', ', $update_topic).' WHERE `id` = '.$topicid) or bt_sql::err(__FILE__, __LINE__);
	if (!bt_sql::$affected_rows)
		bt_theme::error('Error', 'Unable to update topic.');
}
foreach ($update_forums as $fid => $updates) {
	if (count($updates)) {
		bt_forums::delete_forum_cache($fid);
		bt_sql::query('UPDATE `forums` SET '.implode(', ', $updates).' WHERE `id` = '.$fid) or bt_sql::err(__FILE__, __LINE__);
	}
}
foreach ($update_users as $uid => $updates)
	bt_sql::query('UPDATE `users` SET '.implode(', ', $updates).' WHERE `id` = '.$uid) or bt_sql::err(__FILE__, __LINE__);

if ($delete)
	header('Location: /forums_viewforum.php?id='.$topic['forumid']);
else
	header('Location: /forums_viewtopic.php?id='.$topicid.($page ? '&page='.$page : ''));

?>
