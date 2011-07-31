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

$tsettings = bt_theme::$settings['forums_post'];
$psettings = bt_theme::$settings['forum_post'];

$session = new bt_session(true, 10800);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$forumid = 0 + $_GET['forumid'];
	$topicid = 0 + $_GET['topicid'];
	$postid = 0 + $_GET['postid'];
	$editid = 0 + $_GET['editid'];
	$preview = false;
	$method = 'get';
	$form_hash = $session->create('forums_post');
}
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$body = trim($_POST['body']);
	$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
	$forumid = 0 + $_POST['forumid'];
	$topicid = 0 + $_POST['topicid'];
	$editid = 0 + $_POST['editid'];
	$preview = (bool)isset($_POST['preview']);
	$method = 'post';
	$form_hash = isset($_POST['hash']) ? trim($_POST['hash']) : '';
	if (!$session->check($form_hash, 'forums_post'))
		die('h4x');

	if ($preview)
		$form_hash = $session->create('forums_post');
}
else
	die;

if ($forumid > 0) {
	$forum = bt_forums::get_forum($forumid);
	if (!$forum)
		bt_theme::error('Error', 'Forum not found');
	$type = 'new';
	if ($method == 'get') {
		$body = '';
		$subject = '';
	}
}
elseif ($topicid > 0) {
	$topic = bt_forums::get_topic($topicid);
	if (!$topic)
		bt_theme::error('Error', 'Topic not found');
	$forumid = $topic['forumid'];
	$forum = bt_forums::get_forum($forumid);
	if (!$forum)
		bt_theme::error('Error','Topic orphaned from forum');
	
	$type = 'reply';
	if ($method == 'get')
		$body = '';
}
elseif ($editid > 1) {
	$type = 'edit';
	$opostq = bt_sql::query('SELECT p.topicid, p.body, p.userid'.
		($method == 'post' ? ', p.added, p.editedby, p.editedat, u.username, CAST(u.flags AS SIGNED) AS flags, u.title, u.class, '.
		'u.avatar' : '').' FROM posts AS p '.($method == 'post' ? 'LEFT JOIN users AS u ON (u.id = p.userid) ' : '').
		'WHERE p.id = '.$editid) or bt_sql::err(__FILE__, __LINE__);

	if (!$opostq->num_rows)
		bt_theme::error('Error', 'Post not found');

	$opost = $opostq->fetch_assoc();
	$opostq->free();

	if (!bt_user::required_class(UC_FORUM_MODERATOR) && (bt_user::$current['id'] != $opost['userid'] || $locked))
		bt_theme::error('Error', 'Denied!');

	if ($method == 'get')
		$body = $opost['body'];
	$topicid = 0 + $opost['topicid'];
	$topic = bt_forums::get_topic($topicid);
	if (!$topic)
		bt_theme::error('Error', 'Post orphaned from topic');
	$forumid = $topic['forumid'];
	$forum = bt_forums::get_forum($forumid);
	if (!$forum)
		bt_theme::error('Error','Topic orphaned from forum');
}
elseif ($method == 'get' && $postid > 1) {
	$type = 'reply';
	$opostq = bt_sql::query('SELECT p.topicid, p.userid, p.body, u.username FROM posts AS p '.
		'LEFT JOIN users AS u ON (u.id = p.userid) WHERE p.id = '.$postid) or bt_sql::err(__FILE__, __LINE__);

	if (!$opostq->num_rows)
		bt_theme::error('Error', 'Post not found');

	$opost = $opostq->fetch_assoc();
	$opostq->free();
	$username = $opost['username'] != '' ? trim($opost['username']) : 'unknown('.$opost['userid'].')';
	$body = '[quote='.$username.']'.$opost['body'].'[/quote]';
	$topicid = 0 + $opost['topicid'];
	$topic = bt_forums::get_topic($topicid);
	if (!$topic)
		bt_theme::error('Error', 'Post orphaned from topic');
	$forumid = $topic['forumid'];
	$forum = bt_forums::get_forum($forumid);
	if (!$forum)
		bt_theme::error('Error','Topic orphaned from forum');
}
else
	die;


if (!bt_user::required_class($forum['minclassread']) || !bt_user::required_class($forum['minclasswrite']) ||
	($type == 'new' && !bt_user::required_class($forum['minclasscreate'])))
	bt_theme::error('Error', 'Permission denied');

if (!bt_user::required_class(UC_FORUM_MODERATOR)) {
	if (!(bt_user::$current['flags'] & bt_options::USER_POST_ENABLE))
		bt_theme::error('Error', 'Your posting rights have been revoked');

	if ($type == 'reply') {
		if ($topic['locked'])
			bt_theme::error('Error', 'This topic is locked.');

		$lp = bt_sql::query('SELECT userid FROM posts WHERE topicid = '.$topicid.' ORDER BY added DESC LIMIT 1');
		$lu = $lp->fetch_row();
		$lp->free();

		if ($lu[0] == bt_user::$current['id'])
			bt_theme::error('Error', 'Double posting is not allowed, please edit your previous post');
	}
}

if ($method == 'post') {
	if ($body == '')
		bt_theme::error('Error', 'You must enter some text for the body of the post');

	if ($type == 'new') {
		if ($subject == '')
			bt_theme::error('Error', 'You must enter a subject');

		if (bt_utf8::strlen($subject) > bt_forums::MAX_SUBJECT_LENGTH)
			bt_theme::error('Error', 'Subject is limited to '.bt_forums::MAX_SUBJECT_LENGTH.' characters');
	}
}

if ($method == 'get' || $preview) {
	if ($type == 'new')
		bt_theme::head('New topic in '.$forum['name'].' forum');
	elseif ($type == 'edit')
		bt_theme::head('Edit Post #'.$editid.' from topic '.$topic['subject']); 
	else
		bt_theme::head('Post reply :: '.$topic['subject']);
}

if ($method == 'post') {
	if ($preview) {
		if ($type != 'edit') {
			$preview_data = array(
				'id'		=> bt_user::$current['id'],
				'username'	=> bt_user::$current['username'],
				'class'		=> bt_user::$current['class'],
				'flags'		=> bt_user::$current['flags'],
				'title'		=> bt_user::$current['title'],
				'time'		=> NULL,
				'avatar'	=> bt_user::$current['avatar'],
				'last_edit'	=> '',
				'ago'		=> time(),
				'post_link'	=> $psettings['post_prev'],
			);
		}
		else {
			$preview_data = array(
				'id'		=> 0 + $opost['userid'],
				'username'	=> $opost['username'],
				'class'		=> 0 + $opost['class'],
				'flags'		=> (int)$opost['flags'],
				'title'		=> $opost['title'],
				'time'		=> $opost['added'],
				'avatar'	=> $opost['avatar'],
				'last_edit'	=> sprintf($psettings['last_edit'], bt_forums::user_link(bt_user::$current['id'],
					bt_user::$current['username'], bt_user::$current['class']), bt_time::format()),
				'ago'		=> $opost['added'],
				'post_link'	=> sprintf($psettings['post_link'], $topicid, $editid),
			);
		}
		$user_link = bt_forums::user_link($preview_data['id'], $preview_data['username'], $preview_data['class']);
		$user_stars = bt_forums::user_stars($preview_data['flags']);
		$user_title = $preview_data['title'] != '' ? $preview_data['title'] : bt_user::get_class_name($preview_data['class']);
		$post_time = bt_time::format($preview_data['time']);
		$ago_time = bt_time::ago_time($preview_data['ago']);
		$avatar = $preview_data['avatar'];
		$avatar_po = (bool)($preview_data['flags'] & bt_options::USER_AVATAR_PO);
		bt_forums::avatar($avatar, $avtext, $avatar_po);		

		$postprevvars = array(
			'USER_LINK'		=> $user_link,
			'USER_STARS'	=> $user_stars,
			'USER_TITLE'	=> bt_security::html_safe($user_title),
			'POST_TIME'		=> $post_time,
			'POST_AGO'		=> $ago_time,
			'POST_LINK'		=> $preview_data['post_link'],
			'TO_TOP'		=> '',
			'AVATAR_URL'	=> bt_security::html_safe($avatar),
			'USERNAME'		=> bt_security::html_safe($preview_data['username']),
			'BODY'			=> format_comment($body),
			'LAST_EDITED'	=> $preview_data['last_edit'],
			'TOOLS'			=> '',
			'NEW'			=> '',
		);

		$post_preview = bt_theme_engine::load_tpl('forum_post', $postprevvars);
	}
	else {
		$userid = bt_user::$current['id'];
		if ($type == 'new') {
			//---- Create topic
			bt_sql::query('INSERT INTO topics (userid, forumid, subject) '.
				'VALUES('.$userid.', '.$forumid.', '.bt_sql::esc($subject).')') or bt_sql::err(__FILE__, __LINE__);
			bt_forums::delete_forum_cache($forumid);
			$topicid = 0 + bt_sql::$insert_id;
			if ($topicid < 1)
				bt_theme::error('Error','No topic ID returned');
		}

		if ($type != 'edit') {
			//------ Insert post
			bt_sql::query('INSERT INTO posts (topicid, userid, added, body) ' .
				'VALUES('.$topicid.', '.$userid.', '.time().', '.bt_sql::esc($body).')') or bt_sql::err(__FILE__, __LINE__);

			$postid = 0 + bt_sql::$insert_id;
			if ($postid < 1)
				bt_theme::error('Error','No post ID returned'); 

			if ($type != 'new')
				bt_forums::delete_topic_cache($topicid);

			bt_sql::query('UPDATE users SET posts = (posts + 1) WHERE id = '.$userid);


			//------ Update topic last post
			bt_sql::query('UPDATE topics SET lastpost = '.$postid.', posts = (posts + 1) WHERE id = '.$topicid);
			bt_sql::query('UPDATE forums SET '.($type == 'new' ? 'topiccount = (topiccount + 1), ' : '').
				'lasttopic = '.$topicid.', postcount = (postcount + 1) WHERE id = '.$forumid);
				bt_forums::delete_forum_cache($forumid);

		}
		else {
			$oldbody = trim($opost['body']);
			if ($body != $oldbody) {
				$oldid = 0 + ($opost['editedby'] != 0 ? $opost['editedby'] : $opost['userid']);
				$oldtime = 0 + ($opost['editedat'] != 0 ? $opost['editedat'] : $opost['added']);

				bt_sql::query('INSERT INTO posts_edits (postid, userid, added, body) '.
					'VALUES ('.$editid.','.$oldid.','.$oldtime.','.bt_sql::esc($oldbody).')') or bt_sql::err(__FILE__, __LINE__);

				bt_sql::query('UPDATE posts SET body = '.bt_sql::esc($body).', editedat = '.time().', '.
					'editedby = '.bt_user::$current['id'].', edits = (edits + 1) WHERE id = '.$editid) or bt_sql::err(__FILE__, __LINE__);

				bt_forums::delete_post_cache($editid);
			}
		}

		//------ All done, redirect user to the post
		header('Location: forums_viewtopic.php?id='.$topicid.($type != 'new' ? '&page=p'.($type != 'edit' ? $postid : $editid) : ''));
		die;
	}
}

//------ Get 10 last posts if this is a reply
if ($type == 'reply') {
	$postsq = bt_sql::query('SELECT p.*, u.avatar, CAST(u.flags AS SIGNED) AS flags, u.username, u.class, u.title FROM posts AS p '.
        'LEFT JOIN users AS u ON (u.id = p.userid) WHERE p.topicid= '.$topicid.' ORDER BY p.id DESC LIMIT 10')
		or bt_sql::err(__FILE__, __LINE__);

//    begin_frame('10 last posts, in reverse order');
	$posts = array();
	while ($post = $postsq->fetch_assoc()) {
		//-- Get poster details
		$username = $post['username'];
		$post['flags'] = (int)$post['flags'];
		$user_link = bt_forums::user_link($post['userid'], $username, $post['class']);
		$user_stars = bt_forums::user_stars($post['flags']);
		$user_title = $post['title'] != '' ? $post['title'] : bt_user::get_class_name($post['class']);
		$post_time = bt_time::format($post['added']);
		$post_ago = bt_time::ago_time($post['added']);
		$avatar = $post['avatar'];
		$avatar_po = (bool)($post['flags'] & bt_options::USER_AVATAR_PO);
		bt_forums::avatar($avatar, $avtext, $avatar_po);

		$prevpostvars = array(
			'USER_LINK'		=> $user_link,
			'USER_STARS'	=> $user_stars,
			'USER_TITLE'	=> bt_security::html_safe($user_title),
			'POST_TIME'		=> $post_time,
			'POST_AGO'		=> $post_ago,
            'POST_LINK'     => sprintf($psettings['post_link'], $topicid, $post['id']),
            'TO_TOP'        => '',
            'AVATAR_URL'    => bt_security::html_safe($avatar),
            'USERNAME'      => bt_security::html_safe($username),
            'BODY'          => bt_forums::get_formated_post($post['id'], $post['body']),
            'TOOLS'         => '',
			'LAST_EDITED'	=> '',
			'NEW'			=> '',
        );

		$posts[] = bt_theme_engine::load_tpl('forum_post', $prevpostvars);
    }
    $postsq->free();

	$last = implode($tsettings['last_join'], $posts);
}
else
	$last = '';


$postvars = array(
	'FORM_HASH'	=> $form_hash,
	'PREVIEW'	=> $preview ? $post_preview : '',
	'LAST'		=> $last,
	'ID'		=> ($type != 'edit' ? ($type == 'new' ? $forumid : $topicid) : $editid),
	'TYPE'		=> ($type != 'edit' ? ($type == 'new' ? 'forumid' : 'topicid') : 'editid'),
	'BODY'		=> bt_security::html_safe($body),
	'SUBJECT'	=> ($type == 'new' ? sprintf($tsettings['subject'], bt_forums::MAX_SUBJECT_LENGTH, bt_security::html_safe($subject)) : ''),
	'ACTION'	=> ($type != 'edit' ? 'Compose' : 'Edit Post'),
);

echo bt_theme_engine::load_tpl('forums_post', $postvars);

bt_forums::insert_quick_jump_menu();
bt_theme::foot();
?>
