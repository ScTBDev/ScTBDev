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

$keywords = trim($_GET['keywords']);
if (strlen($keywords) > 255)
	bt_theme::error('Error','Search keywords longer than 255 chars, please refine your search');

bt_theme::head('Forum Search');
print('<h1>Forum Search</h1>'."\n");
if ($keywords != '') {
	$perpage = 50;
	$page = max(1, 0 + $_GET['page']);

	$ekeywords = bt_sql::esc($keywords);
	$nkeywords = bt_security::html_safe($keywords);
	$ukeywords = rawurlencode($keywords);

	print('<p>Query: <strong>'.$nkeywords.'</strong></p>'."\n");
	$res = bt_sql::query('SELECT COUNT(*) FROM `posts` WHERE MATCH (`body`) AGAINST ('.$ekeywords.' IN BOOLEAN MODE)') or bt_sql::err(__FILE__, __LINE__);
	$arr = $res->fetch_row();
	$res->free();
	$hits = 0 + $arr[0];
	if ($hits == 0)
		echo '<p><strong>Sorry, nothing found!</strong></p>';
	else {
		$pages = 0 + ceil($hits / $perpage);
		if ($page > $pages)
			$page = $pages;
		for ($i = 1; $i <= $pages; ++$i)
			if ($page == $i)
				$pagemenu1 .= '<span class="gray"><strong>'.$i.'</strong></span>'."\n";
			else
				$pagemenu1 .= '<a href="forums_search.php?keywords='.$ukeywords.'&amp;page='.$i.'"><strong>'.$i.'</strong></a>'."\n";

		if ($page == 1)
			$pagemenu2 = '<span class="gray"><strong>&lt;&lt; Prev</strong></span>'."\n";
		else
			$pagemenu2 = '<a href="forums_search.php?keywords='.$ukeywords.'&amp;page='.($page - 1).'"><strong>&lt;&lt; Prev</strong></a>'."\n";

		$pagemenu2 .= '&nbsp; &nbsp; &nbsp; '."\n";
		if ($page == $pages)
			$pagemenu2 .= '<span class="gray"><strong>Next &gt;&gt;</strong></span>'."\n";
		else
			$pagemenu2 .= '<a href="forums_search.php?keywords='.$ukeywords.'&amp;page='.($page + 1).'"><strong>Next &gt;&gt;</strong></a>'."\n";

		$offset = ($page * $perpage) - $perpage;

		$res = bt_sql::query('SELECT `p`.`id`, `p`.`topicid`, `p`.`userid`, `p`.`added`, `t`.`forumid`, '.
			'`t`.`subject`, `f`.`name`, `f`.`minclassread`, `u`.`username` '.
			'FROM `posts` AS `p` '.
			'LEFT JOIN `topics` AS `t` ON (`t`.`id` = `p`.`topicid`) '.
			'JOIN `forums` AS `f` ON (`f`.`id` = `t`.`forumid`) '.
			'LEFT JOIN `users` AS `u` ON (`u`.`id` = `p`.`userid`) '.
			'WHERE MATCH (`body`) AGAINST ('.$ekeywords.' IN BOOLEAN MODE) '.
			'AND `f`.`minclassread` <= '.bt_user::$current['class'].' '.
			'LIMIT '.$offset.','.$perpage) or bt_sql::err(__FILE__, __LINE__);

		echo '<p>'.$pagemenu1.'<br />'.$pagemenu2.'</p>
<table border="1" cellspacing="0" cellpadding="5">
  <tr>
    <td class="colhead">Post</td>
    <td class="colhead" align="left">Topic</td>
    <td class="colhead" align="left">Forum</td>
    <td class="colhead" align="left">Posted by</td>
  </tr>'."\n";

		while ($post = $res->fetch_assoc()) {
			if ($post['username'] == '')
				$username = 'unknown['.$post['userid'].']';
			else
				$username = '<a href="userdetails.php?id='.$post['userid'].'"><strong>'.bt_security::html_safe($post['username']).'</strong></a>';
			echo '  <tr>
    <td>'.$post['id'].'</td>
    <td align="left"><a href="forums_viewtopic.php?id='.$post['topicid'].'&amp;page=p'.$post['id'].'"><strong>'.
      bt_security::html_safe($post['subject']).'</strong></a></td>
    <td align="left"><a href="forums_viewforum.php?id='.$post['forumid'].'"><strong>'.bt_security::html_safe($post['name']).'</strong></a></td>
    <td align="left">'.$username.'<br /> at '.format_time($post['added']).'</td>
  </tr>'."\n";
		}
		$res->free();
		echo '</table>
<p>'.$pagemenu2.'<br />'.$pagemenu1.'</p>
<p>Found '.$hits.' posts.</p>
<p><strong>Search again</strong></p>'."\n";
	}
}
echo '<form method="get" action="forums_search.php">
  <table border="1" cellspacing="0" cellpadding="5">
    <tr>
      <td class="rowhead">Key words</td>
      <td align="left">
        <input type="text" size="55" name="keywords" /><br />
        <span class="small">Enter one or more words to search for.<br />
        Very common words and words with less than 3 characters are ignored.</span>
      </td>
    </tr>
    <tr>
      <td>
        <input type="submit" value="Search" class="btn" />
      </td>
      <td style="text-align: center">
        <b><a href="/searching.php">Searching How-To</a></b>
      </td>
    </tr>
  </table>
</form>'."\n";
bt_theme::foot();
?>
