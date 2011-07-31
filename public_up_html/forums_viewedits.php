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

if (!bt_user::required_class(UC_FORUM_MODERATOR))
	bt_theme::error('Error', 'Permission denied');

$postid = 0 + $_GET['id'];
if ($postid < 1)
	die;

$where = '`p`.`id` = '.$postid;
$countq = bt_sql::query('SELECT COUNT(*) FROM `posts` AS `p` WHERE '.$where);
$num = $countq->fetch_row();
$countq->free();
$editcount = 0 + $num[0];

$perpage = bt_user::$current['postsperpage'] > 0 ? (bt_user::$current['postsperpage'] < 10 ? 10 : bt_user::$current['postsperpage']) : 25;
list($pager, $limit) = bt_theme::pager($perpage, $editcount, '/forums_viewedits.php?id='.$postid.'&amp;', bt_theme::PAGER_SHOW_PAGES);

$res = bt_sql::query('SELECT `p`.`id`, `p`.`topicid`, `p`.`userid`, `u`.`username`, `t`.`subject`, `p`.`edits` '.
	'FROM `posts` AS `p` '.
	'JOIN `topics` AS `t` ON (`t`.`id` = `p`.`topicid`) '.
	'LEFT JOIN `users` AS `u` ON (`u`.`id` = `p`.`userid`) '.
	'WHERE '.$where.' '.$limit) or bt_sql::err(__FILE__,__LINE__);

if ($res->num_rows > 0) {
	$post = $res->fetch_assoc();	
	$res->free();
	if ($post['edits'] == 0)
		bt_theme::error('Error','This post has no saved edits');

	// grab edits
	$eres = bt_sql::query('SELECT `e`.*, `u`.`username` '.
		'FROM `posts_edits` AS `e` '.
		'LEFT JOIN `users` AS `u` ON (`u`.`id` = `e`.`userid`) '.
		'WHERE `e`.`postid` = '.$postid.' '.
		'ORDER BY `e`.`id` DESC') or bt_sql::err(__FILE__,__LINE__);
	$num = $eres->num_rows;
	if ($num > 0) {
		bt_theme::head('Edits for post #'.$postid);
		begin_main_frame();
		echo $pager."\n";
		begin_frame('Edits for post <a href="/forums_viewtopic.php?id='.$post['topicid'].'&amp;page=p'.
			$postid.'">#'.$postid.'</a> by <a href="/userdetails.php?id='.$post['userid'].'">'.
			bt_security::html_safe($post['username']).'</a> in <a href="/forums_viewtopic.php?id='.
			$post['topicid'].'">'. bt_security::html_safe($post['subject']).'</a>');

		while ($edit = $eres->fetch_assoc()) {
			echo '<p class="sub">#'.$num.' by <a href="/userdetails.php?id='.$edit['userid'].'"><strong>'.
				bt_security::html_safe($edit['username']).'</strong></a> at '.format_time($edit['added']).
				' ('.(get_elapsed_time($edit['added'])).' ago)</p>'."\n";
			$num--;
			begin_table(true);
			echo '  <tr valign="top">
    <td class="comment">'.format_comment($edit['body']).'</td>
  </tr>
</table>'."\n";
		}
		$eres->free();
		end_frame();
		echo $pager;
		end_main_frame();
		bt_theme::foot();
		die;
	}
	else
		bt_theme::error('Error','This post has no saved edits');
}
else {
	$res->free();
	bt_theme::error('Error','Post with that ID does not exist');
}
?>
