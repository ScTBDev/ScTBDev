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

bt_loginout::db_connect(true);

bt_theme::head('Forums');
// format last catch up time
if(bt_user::$current['last_forum_visit'] == 0)
	$last_catchup = 'Never';
else
	$last_catchup = format_time(bt_user::$current['last_forum_visit']);

$forum_rows = '';

$forums_res = bt_sql::query('SELECT `f`.*, `t`.`subject`, `t`.`lastpost`, `p`.`added`, `p`.`userid` AS `lastuserid`, `u`.`username`, `u`.`class`, '.
	'IF(`r`.`lastpostread` != "", IF(`t`.`lastpost` > `r`.`lastpostread`, 1, 0), '.
	'IF(`p`.`added` > '.bt_user::$current['last_forum_visit'].', 1, 0)) AS `new` '.
	'FROM `forums` AS `f` '.
	'LEFT JOIN `topics` AS `t` ON (`t`.`id` = `f`.`lasttopic`) '.
	'LEFT JOIN `posts` AS `p` ON (`p`.`id` = `t`.`lastpost`) '.
	'LEFT JOIN `users` AS `u` ON (`u`.`id` = `p`.`userid`) '.
	'LEFT JOIN `readposts` AS `r` ON (`r`.`topicid` = `t`.`id` AND `r`.`userid` = '.bt_user::$current['id'].') '.
	'WHERE `f`.`minclassread` <= '.bt_user::$current['class'].' '.
	'ORDER BY `f`.`sort`, `f`.`name`') or bt_sql::err(__FILE__, __LINE__);

$fbid = bt_forums::settings_to_forum_theme(bt_user::$current['flags']);
if (!isset(bt_forums::$buttons[$fbid]))
	$fbid = 0;
$fbname = bt_forums::$buttons[$fbid];
$link = bt_theme::$settings['forums']['index']['link'];

$rowi = 0;
while ($forums_arr = $forums_res->fetch_assoc()) {
	$rowi++;
	$row_style = $rowi % 2 == 0 ? 2 : 1;
	$forumid = $forums_arr['id'];
	$forumname = bt_security::html_safe($forums_arr['name']);
	$hasdescr = $forums_arr['description'] != '';
	$forumdescription = $hasdescr ? bt_theme::$settings['forums']['index']['pre_descr'].bt_security::html_safe($forums_arr['description']) : '';
	$topiccount = number_format($forums_arr['topiccount']);
	$postcount = number_format($forums_arr['postcount']);

    // Get last post info
	$new_posts = 'No New Posts';
    if ($forums_arr['lasttopic'] != 0) {
		$lastpostid = $forums_arr['lastpost'];
		$lastposterid = $forums_arr['lastuserid'];
		list($lastpostdate, $lastposttime) = explode(' ', format_time($forums_arr['added']));
		$lasttopicid = $forums_arr['lasttopic'];
		$lasttopic = bt_security::html_safe($forums_arr['subject']);

		$poster_link = bt_forums::user_link($lastposterid, $forums_arr['username'], $forums_arr['class']);

		$lastpostvars = array(
			'ID'		=> $lasttopicid,
			'POSTID'	=> $lastpostid,
			'NAME'		=> $lasttopic,
			'DATE'		=> $lastpostdate,
			'TIME'		=> $lastposttime,
			'POSTER'	=> $poster_link,
		);

		$lastpost = bt_theme_engine::load_tpl('forums_index_lastpost', $lastpostvars);

		if ($forums_arr['new']) {
			$img = 'unlockednew';
			$new_posts = 'New Posts';
		}
		else
			$img = 'unlocked';
	}
    else {
		$lastpost = 'N/A';
		$img = 'unlocked';
	}
	$admin_links = bt_user::required_class(UC_ADMINISTRATOR) ? '<span'.bt_theme::$settings['forums']['index']['staff_tools'].'> '.
            '[<a href="/forums_modforum.php?type=edit&amp;id='.$forumid.'"'.$link.'>Edit</a>] '.
            '[<a href="/forums_deleteforum.php?id='.$forumid.'"'.$link.'>Delete</a>]</span>' : '';

	$descr = $hasdescr ? 'descr' : 'no_descr';

	$rowvars = array(
		'ROW'			=> $row_style,
		'IMG'			=> $fbname.$img,
		'NEW_POSTS'		=> $new_posts,
		'DESCR'			=> bt_theme::$settings['forums']['index'][$descr],
		'ID'			=> $forumid,
		'NAME'			=> $forumname,
		'ADMIN_LINKS'	=> $admin_links,
		'DESCRIPTION'	=> $forumdescription,
		'TOPICS'		=> $topiccount,
		'POSTS'			=> $postcount,
		'LAST_POST'		=> $lastpost,
	);

	$forum_rows .= bt_theme_engine::load_tpl('forums_index_table_row', $rowvars);
}
$forums_res->free();

/*if (bt_user::required_class(UC_ADMINISTRATOR))
	echo '<form method="get" action="forums_modforum.php">
  <input type="hidden" name="type" value="new" />
  <input type="submit" value="New forum" class="btn" style="margin-left: 10px" />
</form>'."\n";*/



$forumtablevars = array(
	'FORUM_ROWS'	=> $forum_rows,
);

$forums_list = bt_theme_engine::load_tpl('forums_index_table', $forumtablevars);

$forumvars = array(
	'FORUMS_LIST'	=> $forums_list,
	'LAST_CATCHUP'	=> $last_catchup,
);

echo bt_theme_engine::load_tpl('forums_index', $forumvars);
bt_theme::foot();
?>
