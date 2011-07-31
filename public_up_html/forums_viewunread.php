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

$userid = 0 + bt_user::$current['id'];
$maxresults = 0 + (bt_user::$current['postsperpage'] > 0 ? bt_user::$current['postsperpage'] : 25);

$res = bt_sql::query('SELECT `t`.`id`, `t`.`forumid`, `t`.`subject`, `t`.`lastpost`, `f`.`name`, `r`.`lastpostread` '.
	'FROM `topics` AS `t` '.
	'LEFT JOIN `posts` AS `p` ON (`p`.`id` = `t`.`lastpost`) '.
	'LEFT JOIN `forums` AS `f` ON (`f`.`id` = `t`.`forumid`) '.
	'LEFT JOIN `readposts` AS `r` ON (`r`.`topicid` = `t`.`id` AND `r`.`userid` = '.$userid.') '.
	'WHERE `p`.`added` > '.bt_user::$current['last_forum_visit'].' AND `f`.`minclassread` <= '.bt_user::$current['class'].' '.
	'AND IF (`r`.`lastpostread` != "", `r`.`lastpostread` < `t`.`lastpost` ,1) '.
	'ORDER BY `t`.`lastpost` LIMIT '.$maxresults) or bt_sql::err(__FILE__, __LINE__);

bt_theme::head();
echo '<h1>Topics with unread posts</h1>'."\n";
$n = 0;
while ($arr = $res->fetch_assoc()) {
	$topicid = 0 + $arr['id'];
	$forumid = 0 + $arr['forumid'];
	$post_date = format_time($arr['added']);

	$forumname = bt_security::html_safe($arr['name']);
	$subject = bt_security::html_safe($arr['subject']);
	$n++;

	if ($n == 1)
		echo '<table border="1" cellspacing="0" cellpadding="5">
  <tr>
    <td class="colhead" align="left">Topic</td>
    <td class="colhead" align="left">Forum</td>
  </tr>'."\n";

	echo '  <tr>
    <td align="left">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td class="embedded">
            <img src="'.bt_config::$conf['pic_base_url'].'new/unlockednew.gif" alt="unlockednew" style="margin-right: 5px" />
          </td>
          <td class="embedded">
            <a href="/forums_viewtopic.php?id='.$topicid.'&amp;page=last"><strong>'.$subject.'</strong></a>
          </td>
        </tr>
      </table>
    </td>
    <td align="left">
      <a href="/forums_viewforum.php?id='.$forumid.'"><strong>'.$forumname.'</strong></a>
    </td>
  </tr>'."\n";
}
$res->free();

if ($n > 0) {
	echo '</table>'."\n";
	if ($n > $maxresults)
		echo '<p>More than '.$maxresults.' items found, displaying first '.$maxresults.'.</p>'."\n";
	echo '<p><a href="/forums_catchup.php"><strong>Catch up</strong></a></p>'."\n";
}
else
	echo '<strong>Nothing found</strong>';
bt_theme::foot();
?>
