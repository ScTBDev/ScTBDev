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
require_once(CLASS_PATH.'allowed_staff.php');

bt_loginout::db_connect(true);

if (!bt_user::required_class(UC_ADMINISTRATOR))
	bt_theme::error('Error', 'Access Denied');

$as = new allowed_staff;
if (!$as->check('forums'))
	die();

$pv = new bt_session(true, 900);

$forumid = 0 + $_GET['id'];
$sure = 0 + $_GET['sure'];

if($forumid < 1)
	die;

if(!$sure) {
	$form_hash = $pv->create('deleteforum');

	$forum = bt_forums::get_forum($forumid);
	if (!$forum)
		bt_theme::error('Error', 'Forum not found.');

	bt_theme::head('Delete forum');
	begin_main_frame();
	begin_frame('** WARNING! **');
	echo 'Deleting forum ID '.$forumid.' '.$forum['en_name'].' will also delete '.$forum['postcount'].' posts in '.$forum['topiccount'].' topics. '.
		'[<a class="altlink" href="forums_deleteforum.php?hash='.$form_hash.'&amp;id='.$forumid.'&amp;sure=1">ACCEPT</a>] '.
		'[<a class="altlink" href="forums_index.php">CANCEL</a>]';

	end_frame();
	end_main_frame();
	bt_theme::foot();
	die;
}

$form_hash = trim($_GET['hash']);
if (!$pv->check($form_hash, 'deleteforum'))
	die('h4x');

$users = array();
$topics = array();
$rt = bt_sql::query('SELECT `id` FROM `topics` WHERE `forumid` = '.$forumid) or bt_sql::err(__FILE__, __LINE__);
while($topic = $rt->fetch_assoc()) {
	$posts = array();
	$topics[] = $topic['id'];
	$pq = bt_sql::query('SELECT `id`, `userid` FROM `posts` WHERE `topicid` = '.$topic['id']) or bt_sql::err(__FILE__, __LINE__);
	while ($p = $pq->fetch_assoc()) {
		if (!isset($users[$p['userid']]))
			$users[$p['userid']] = 0;

		$users[$p['userid']]++;
		$posts[] = $p['id'];
	}
	bt_sql::query('DELETE FROM `posts` WHERE `topicid` = '.$topic['id']) or bt_sql::err(__FILE__, __LINE__);
	foreach ($posts as $postid)
		bt_forums::delete_post_cache($postid);
	bt_sql::query('DELETE FROM `posts_edits` WHERE `postid` IN ('.join(',',$posts).')') or bt_sql::err(__FILE__, __LINE__);
}

foreach($users as $userid=>$nump)
	bt_sql::query('UPDATE `users` SET `posts` = (`posts` - '.$nump.') WHERE `id` = '.$userid) or bt_sql::err(__FILE__, __LINE__);

bt_sql::query('DELETE FROM `topics` WHERE `forumid` = '.$forumid) or bt_sql::err(__FILE__, __LINE__);
foreach($topics as $topicid)
	bt_forums::delete_topic_cache($topicid);

bt_sql::query('DELETE FROM `forums` WHERE `id` = '.$forumid) or bt_sql::err(__FILE__, __LINE__);
bt_forums::delete_forum_cache($forumid);
header('Location: forums_index.php');
?>
