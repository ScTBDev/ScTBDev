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

$fsettings = bt_theme::$settings['forums_viewforum'];

$forumid = 0 + $_GET['id'];
if ($forumid < 1)
	die;

//------ Get forum name
$forum = bt_forums::get_forum($forumid);
if (!$forum)
	die;

if (!bt_user::required_class($forum['minclassread']))
	die;

$forumname = $forum['en_name'];
$tperpage = bt_user::$current['topicsperpage'] > 0 ? (bt_user::$current['topicsperpage'] < 10 ? 10 : bt_user::$current['topicsperpage']) : 25;
$pperpage = bt_user::$current['postsperpage'] > 0 ? (bt_user::$current['postsperpage'] < 10 ? 10 : bt_user::$current['postsperpage']) : 25;
$ppager_opts = bt_theme::PAGER_SHOW_PAGES | bt_theme::PAGER_NO_SEPARATOR | bt_theme::PAGER_NO_NAV | bt_theme::PAGER_ONLY_PAGES;

$count = $forum['topiccount'];
if ($count) {
	$fbid = bt_forums::settings_to_forum_theme(bt_user::$current['flags']);
	if (!isset(bt_forums::$buttons[$fbid]))
		$fbid = 0;
	$fbname = bt_forums::$buttons[$fbid];

	list($pager, $limit) = bt_theme::pager($tperpage, $forum['topiccount'], '/forums_viewforum.php?id='.$forumid.'&amp;', bt_theme::PAGER_SHOW_PAGES);
	//------ Get topics data
	$topicsres = bt_sql::query('SELECT `t`.*, `p`.`userid` AS `lastuserid`, `p`.`added`, `tu`.`username` AS `topicusername`, '.
		'`tu`.`class` AS `topicclass`, `pu`.`username` AS `postusername`, `pu`.`class` AS `postclass`, `r`.`lastpostread`, '.
		'IF((SELECT COUNT(*) FROM `posts` '.
		'WHERE `topicid` = `t`.`id` AND `userid` = '.bt_user::$current['id'].') > 0,1,0) AS `posted` '.
		'FROM `topics` AS `t` '.
		'LEFT JOIN `posts` AS `p` ON (`p`.`id` = `t`.`lastpost`) '.
		'LEFT JOIN `users` AS `tu` ON (`tu`.`id` = `t`.`userid`) '.
		'LEFT JOIN `users` AS `pu` ON (`pu`.`id` = `p`.`userid`) '.
		'LEFT JOIN `readposts` AS `r` ON (`r`.`topicid` = `t`.`id` AND `r`.`userid` = '.bt_user::$current['id'].') '.
		'WHERE `t`.`forumid` = '.$forumid.' ORDER BY `t`.`sticky`, `t`.`lastpost` DESC '.$limit) or bt_sql::err(__FILE__,__LINE__);

	$numtopics = $topicsres->num_rows;

	if ($numtopics) {
		$topics_rows = '';
		while ($topicarr = $topicsres->fetch_assoc()) {
			$topicid = 0 + $topicarr['id'];
			$topic_userid = 0 + $topicarr['userid'];
			$topic_views = 0 + $topicarr['views'];
			$views = number_format($topic_views);
			$locked = $topicarr['locked'] == 'yes';
			$sticky = $topicarr['sticky'] == 'yes';

			$posts = 0 + $topicarr['posts'];
			$replies = max(0, $posts - 1);
			$tpages = ceil($posts / $pperpage);

			$topic_pages = '';
			if ($tpages > 1) {
				$row_class = 'pages';
				$topic_pages = sprintf($fsettings['multipage'], bt_theme_engine::$theme_pic_dir);
				$topic_pages .= bt_theme::pager($pperpage, $posts, '/forums_viewtopic.php?id='.$topicid.'&amp;', $ppager_opts);
			}
			else
				$row_class = 'no_pages';

			$row_class = $fsettings[$row_class];

			$lppostid = 0 + $topicarr['lastpost'];
			$lpuserid = 0 + $topicarr['lastuserid'];
			$lpreadid = 0 + $topicarr['lastpostread'];

			$lpaddedtime = 0 + $topicarr['added'];
			list($lpdate, $lptime) = explode(' ', format_time($lpaddedtime), 2);

			//------ Get name of last poster
			$lpusername = bt_forums::user_link($lpuserid, $topicarr['postusername'], $topicarr['postclass']);

			//------ Get author
			$author = bt_forums::user_link($topic_userid, $topicarr['topicusername'], $topicarr['topicclass']);

			//---- Print row
			if (bt_user::$current['last_forum_visit'] > 0 && $lpaddedtime < bt_user::$current['last_forum_visit'])
				$new = false;
			else
				$new = $lppostid > $lpreadid;

			$topicpic = (!$locked ? 'un' : '').'locked'.($new ? 'new' : '').($topicarr['posted'] ? 'posted' : '');
			$label = array();
			if ($locked)
				$label[] = 'Locked Topic';
			$label[] = $new ? 'New posts' : 'No new posts';
			if ($topicarr['posted'])
				$label[] = 'You have posted';
			
			$img_label = implode(', ', $label);

			$sticky_prefix = $sticky ? $fsettings['sticky'] : '';
			$subject = trim($topicarr['subject']);

			$topicvars = array(
				'ID'			=> $topicid,
				'IMG'			=> $fbname.$topicpic,
				'NEW_POSTS'		=> $img_label,
				'PAGE'			=> $row_class,
				'STICKY'		=> $sticky_prefix,
				'NAME'			=> bt_security::html_safe($subject),
				'PAGES'			=> $topic_pages,
				'REPLIES'		=> $replies,
				'VIEWS'			=> $views,
				'AUTHOR'		=> $author,
				'DATE'			=> $lpdate,
				'TIME'			=> $lptime,
				'POSTER'		=> $lpusername,
			);

			$topics_rows .= bt_theme_engine::load_tpl('forums_viewforum_table_row', $topicvars);
		} // while
		$topicsres->free();

		$topictablevars = array(
			'TOPIC_ROWS'	=> $topics_rows,
		);
		$topicslist = bt_theme_engine::load_tpl('forums_viewforum_table', $topictablevars);
	}
	else {
		$topicslist = bt_theme::message('Error', 'An error occured', false, true);
		$pager = '';
	}
}
else {
	$topicslist = bt_theme::message('Error', 'No topics found', false, true);
	$pager = '';
}

$maypost = (bt_user::required_class($forum['minclassread']) && bt_user::required_class($forum['minclasswrite']) && bt_user::required_class($forum['minclasscreate']));
if (!(bt_user::$current['flags'] & bt_options::USER_POST_ENABLE) && !bt_user::required_class(UC_FORUM_MODERATOR))
	$maypost = false;

$new_topic = $maypost ? sprintf($fsettings['newtopic'], $forumid) : $fsettings['no_posts'];
$quick_jump = bt_forums::insert_quick_jump_menu($forumid, true);

bt_theme::head('View forum :: '.$forum['name']);

$viewforumvars = array(
	'NAME'			=> $forumname,
	'TOPICS_LIST'	=> $topicslist,
	'PAGER'			=> $pager,
	'NEWTOPIC'		=> $new_topic,
	'QUICK_JUMP'	=> $quick_jump,
);

echo bt_theme_engine::load_tpl('forums_viewforum', $viewforumvars);

bt_theme::foot();
?>
