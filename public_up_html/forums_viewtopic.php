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

$tsettings = bt_theme::$settings['forums_viewtopic'];
$psettings = bt_theme::$settings['forum_post'];

$topicid = 0 + $_GET['id'];
$page = $_GET['page'];

if ($topicid < 1)
	die;

//------ Get topic info
$topic = bt_forums::get_topic($topicid);
if (!$topic)
	bt_theme::error('Forum error', 'Topic not found');


$post_enable = (bool)(bt_user::$current['flags'] & bt_options::USER_POST_ENABLE);
$subject = $topic['en_subject'];
$nsubject = $topic['subject'];
$forumid = $topic['forumid'];

//------ Update hits column
bt_sql::query('UPDATE `topics` SET `views` = (`views` + 1) WHERE `id` = '.$topicid) or bt_sql::err(__FILE__, __LINE__);

//------ Get forum
$forum = bt_forums::get_forum($forumid);
if (!$forum)
	die('Forum = NULL');

$forumname = $forum['en_name'];

if (!bt_user::required_class($forum['minclassread']))
	bt_theme::error('Error', 'You are not permitted to view this topic.');

//------ Make page menu
$postcount = $topic['posts'];
$perpage = bt_user::$current['postsperpage'] > 0 ? (bt_user::$current['postsperpage'] < 10 ? 10 : bt_user::$current['postsperpage']) : 25;
$pages = 0 + ceil($postcount / $perpage);

if ($page == 'last') {
	$page = $pages;
	header('Location: forums_viewtopic.php?id='.$topicid.'&page='.$page.'#last');
	die;
}
elseif ($page{0} == 'p') {
	$findpost = 0 + substr($page, 1);
	if ($findpost < 1)
		die;

	$res = bt_sql::query('SELECT `id` FROM `posts` WHERE `topicid` = '.$topicid.' ORDER BY `added`') or bt_sql::err(__FILE__, __LINE__);
	$i = 1;
	while ($arr = $res->fetch_row()) {
		if ($arr[0] == $findpost)
			break;
		$i++;
	}
	$res->free();
	$page = 0 + ceil($i / $perpage);
	header('Location: forums_viewtopic.php?id='.$topicid.'&page='.$page.'#p'.$findpost);
	die;
}
else
	list($pager, $limit) = bt_theme::pager($perpage, $postcount, '/forums_viewtopic.php?id='.$topicid.'&amp;', bt_theme::PAGER_SHOW_PAGES);


//------ Get posts
$postsq = bt_sql::query('SELECT p.*, u.username, u.class, u.avatar, u.title, u.enabled, '.
	'CAST(u.flags AS SIGNED) AS flags, e.username AS eusername, e.class as eclass FROM posts AS p '.
	'LEFT JOIN users AS u ON (u.id = p.userid) '.
	'LEFT JOIN users AS e ON (e.id = p.editedby) '.
	'WHERE p.topicid = '.$topicid.' ORDER BY p.id ASC '.$limit) or bt_sql::err(__FILE__, __LINE__);

bt_theme::head('View topic :: '.$nsubject);

$pc = $postsq->num_rows;
$pn = 0;
$r = bt_sql::query('SELECT lastpostread FROM readposts WHERE userid = '.bt_user::$current['id'].' AND topicid = '.$topicid)
	or bt_sql::err(__FILE__, __LINE__);

$lpr = false;
if ($r->num_rows) {
	$a = $r->fetch_row();
	$r->free();
	$lpr = (int)$a[0];
}

$posts = array();
while ($post = $postsq->fetch_assoc()) {
	$pn++;
	$postid = (int)$post['id'];
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

	if ($post['editedby'] && $post['eusername']) {
		$euser = bt_forums::user_link($post['editedby'], $post['eusername'], $post['eclass']);
		$etime = bt_time::format($post['editedat']);
		$last_edit = sprintf($psettings['last_edit'], $euser, $etime);
	}
	else
		$last_edit = '';
	$ptools = array();

	if (bt_user::required_class(UC_FORUM_MODERATOR) && $post['edits'] > 0)
		$ptools[] = sprintf($psettings['tools']['viewe'], $postid);

	if ((!$topic['locked'] && $post_enable) || bt_user::required_class(UC_FORUM_MODERATOR))
		$ptools[] = sprintf($psettings['tools']['quote'], $postid);

	if ((bt_user::$current['id'] == $post['userid'] && !$topic['locked'] && $post_enable) ||
		bt_user::required_class(UC_FORUM_MODERATOR))
		$ptools[] = sprintf($psettings['tools']['edit'], $postid);

	if (bt_user::required_class(UC_FORUM_MODERATOR))
		$ptools[] = sprintf($psettings['tools']['delete'], $postid);


	$tools = implode($psettings['tools']['join'], $ptools);

	$last_post = '';
	$new = $post['added'] > bt_user::$current['last_forum_visit'] && $postid > $lpr;
	$new_post = $new ? $psettings['new'] : '';

	if ($pn == $pc) {
		$last_post = $psettings['last_post'];
		if ($new) {
			if ($lpr === false)
				bt_sql::query('INSERT INTO readposts (userid, topicid, lastpostread) '.
					'VALUES('.bt_user::$current['id'].', '.$topicid.', '.$postid.')') or bt_sql::err(__FILE__, __LINE__);
			else
				bt_sql::query('UPDATE readposts SET lastpostread = '.$postid.' WHERE userid = '.bt_user::$current['id'].' '.
					'AND topicid = '.$topicid) or bt_sql::err(__FILE__, __LINE__);
		}
	}


	$postvars = array(
		'USER_LINK'		=> $user_link,
		'USER_STARS'	=> $user_stars,
		'USER_TITLE'	=> bt_security::html_safe($user_title),
		'POST_TIME'		=> $post_time,
		'POST_AGO'		=> $post_ago,
		'POST_LINK'		=> sprintf($psettings['post_link'], $topicid, $post['id']).$last_post,
		'TO_TOP'		=> sprintf($psettings['to_top'], bt_theme_engine::$theme_pic_dir),
		'AVATAR_URL'	=> bt_security::html_safe($avatar),
		'USERNAME'		=> bt_security::html_safe($username),
		'BODY'			=> bt_forums::get_formated_post($post['id'], $post['body']),
		'TOOLS'			=> $tools,
		'LAST_EDITED'	=> $last_edit,
		'NEW'			=> $new_post,
	);
	$posts[] = bt_theme_engine::load_tpl('forum_post', $postvars);
}
$postsq->free();

$fposts = implode($tsettings['post_join'], $posts);
$quick_jump = bt_forums::insert_quick_jump_menu($forumid, true);

$maypost = true;
$message = '';
if (!bt_user::required_class(UC_FORUM_MODERATOR)) {
	$modtools = '';
	if (!$post_enable) {
		$maypost = false;
		$message = $tsettings['no_post'];
	}
	elseif ($topic['locked']) {
		$maypost = false;
		$message = $tsettings['locked'];
	}
	elseif (!bt_user::required_class($forum['minclasswrite'])) {
		$maypost = false;
		$message = $tsettings['no_fpost'];
	}
	else {
		$lp = bt_sql::query('SELECT userid FROM posts WHERE topicid = '.$topicid.' ORDER BY added DESC LIMIT 1');
		$lu = $lp->fetch_row();
		$lp->free();

		if ($lu[0] == bt_user::$current['id']) {
			$maypost = false;
			$message = $tsettings['double_post'];
		}
	}
}
else {
	$forums = bt_forums::get_forums();
	$locs = array();

	foreach ($forums as $fm) {
		$selected = $fm['id'] == $forumid;
		if ((bt_user::required_class($fm['minclasswrite']) && bt_user::required_class($fm['minclasscreate'])) || $selected) {
			$locs[] = sprintf($tsettings['locations'], $fm['id'], ($selected ? $tsettings['list_on'] : ''), $fm['name']);
		}
	}
	$locations = implode($tsettings['location_join'], $locs);

	$form_hash = $session->create('forums_set');

	$modtoolvars = array(
		'FORM_HASH'		=> $form_hash,
		'TOPIC_ID'		=> $topicid,
		'STICKY_ON'		=> $topic['sticky'] ? $tsettings['radio_on'] : '',
		'STICKY_OFF'	=> !$topic['sticky'] ? $tsettings['radio_on'] : '',
		'LOCKED_ON'		=> $topic['locked'] ? $tsettings['radio_on'] : '',
		'LOCKED_OFF'	=> !$topic['locked'] ? $tsettings['radio_on'] : '',
		'SUBJECT'		=> $subject,
		'LOC_LIST'		=> $locations,
		'PAGE'			=> (int)$page,
	);

	$modtools = bt_theme_engine::load_tpl('forums_viewtopic_modtools', $modtoolvars);
}

$form_hash = $maypost ? $session->create('forums_post') : '';

$viewtopicvars = array(
	'FORM_HASH'		=> $form_hash,
	'TOPIC_ID'		=> $topicid,
	'FORUM_ID'		=> $forumid,
	'FORUM_NAME'	=> $forumname,
	'PAGER'			=> $pager,
	'NAME'			=> $subject,
	'POSTS'			=> $fposts,
	'QUICK_JUMP'	=> $quick_jump,
	'REPLY_EN'		=> !$maypost ? $tsettings['reply_disable'] : '',
	'MESSAGE'		=> $message,
	'MOD_TOOLS'		=> $modtools,
);

echo bt_theme_engine::load_tpl('forums_viewtopic', $viewtopicvars);
bt_theme::foot();
?>
