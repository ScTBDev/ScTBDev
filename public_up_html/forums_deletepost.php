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

bt_loginout::db_connect(true);

$tsettings = bt_theme::$settings['forums_deletepost'];

$postid = 0 + $_GET['id'];
$sure = 0 + $_GET['sure'];

if ($postid < 1 || !bt_user::required_class(UC_FORUM_MODERATOR))
	die;

//------- Make sure we know what we do :-)
if (!$sure)
	bt_theme::error('Delete post', sprintf($tsettings['sanity'], $postid));

//------- Get topic id
$postq = bt_sql::query('SELECT `p`.`topicid`, `p`.`userid` '.
	'FROM `posts` AS `p` WHERE `p`.`id` = '.$postid) or bt_sql::err(__FILE__, __LINE__);

$post = $postq->fetch_assoc() or bt_theme::error('Error', 'Post not found');
$postq->free();
$topicid = 0 + $post['topicid'];
$userid = 0 + $post['userid'];
$topic = bt_forums::get_topic($topicid);
if (!$topicid)
	die;

$forumid = $topic['forumid'];

if ($topic['posts'] <= 1)
	bt_theme::error('Error', sprintf($tsettings['error'], $topicid));


//------- Get the id of the last post before the one we're deleting
$res = bt_sql::query('SELECT `id` FROM `posts` WHERE `topicid` = '.$topicid.' AND `id` < '.$postid.' ORDER BY `id` DESC LIMIT 1')
	or bt_sql::err(__FILE__, __LINE__);

if ($res->num_rows == 0)
	$redirtopost = '';
else {
	$arr = $res->fetch_row();
	$newlastpost =  0 + $arr[0];
	$redirtopost = '&page=p'.$newlastpost;
}
$res->free();

//------- Delete post
bt_sql::query('DELETE FROM `posts` WHERE `id` = '.$postid) or bt_sql::err(__FILE__, __LINE__);
bt_forums::delete_post_cache($postid);

//------- Update user
bt_sql::query('UPDATE `users` SET `posts` = (`posts` - 1) WHERE `id` = '.$userid);

//------- Update topic
bt_sql::query('UPDATE `topics` SET `lastpost` = '.$newlastpost.', `posts` = (`posts` - 1) WHERE `id` = '.$topicid);
bt_forums::delete_topic_cache($topicid);
bt_sql::query('UPDATE `forums` SET `postcount` = (`postcount` - 1) WHERE `id` = '.$forumid);
bt_forums::delete_forum_cache($forumid);

header('Location: forums_viewtopic.php?id='.$topicid.$redirtopost);
?>
