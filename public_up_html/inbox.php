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

$out = (bool)0 + $_GET['out'];

$title = $out ? 'Sentbox' : 'Inbox';
bt_theme::head($title, false);

$inbox_link = $out ? '<a href="/inbox.php"'.bt_theme::$settings['inbox']['inbox_link'].'>Inbox</a>' : 'Inbox';
$sentbox_link = !$out ? '<a href="/inbox.php?out=1"'.bt_theme::$settings['inbox']['sentbox_link'].'>Sentbox</a>' : 'Sentbox';


$col = $out ? 'receiver' : 'sender';
$ucol = !$out ? 'receiver' : 'sender';
$loc = $out ? 'out' : 'in';

$where = 'm.'.$ucol.' = '.bt_user::$current['id'].' AND m.location IN ("'.$loc.'","both")';

$countq = bt_sql::query('SELECT COUNT(*) FROM messages AS m WHERE '.$where) or bt_sql::err(__FILE__, __LINE__);
$count = $countq->fetch_row();
$countq->free();
$count = 0 + $count[0];

$upunread = array();

$to_from = $out ? 'To' : 'From';
$link = bt_theme::$settings['inbox']['link'];

if ($count) {
	list($pager, $limit) = bt_theme::pager(25, $count, '/inbox.php?'.($out ? 'out=1&amp;' : ''), bt_theme::PAGER_SHOW_PAGES);
	$query = 'SELECT m.*, u.username, CAST(u.flags AS SIGNED) AS flags, u.avatar, u.title, u.class FROM messages AS m '.
		'LEFT JOIN users AS u ON (u.id = m.'.$col.') WHERE '.$where.' ORDER BY m.added DESC '.$limit;

	$res = bt_sql::query($query) or bt_sql::err(__FILE__, __LINE__);
	if ($res->num_rows) {
		$messages = array();
		while($arr = $res->fetch_assoc()) {
			$arr['flags'] = (int)$arr['flags'];
			$id = 0 + $arr['id'];
			$userid = 0 + $arr[$col];
			$unread = $arr['unread'] == 'yes';
			$subject = trim($arr['subject']);
			$disp_subject = bt_string::shorten_string($subject, 45);
			$has_name = trim($arr['username']);
			$has_subject = $subject != '';
			$user_title = trim($arr['title']);
			$avatar_url = trim($arr['avatar']);
			$system_message = $userid == 0;
			
			$user_link = bt_forums::user_link($userid, $arr['username'], $arr['class']);

			$reply_link = !$out && $has_name ? '<a href="/sendmessage.php?receiver='.$userid.'&amp;replyto='.$id.'"'.$link.'>Reply</a>'.
				bt_theme::$settings['inbox']['reply_sep'] : '';
			$new = $unread ? bt_theme::$settings['inbox']['new_message'] : '';
			$new = $out ? (bt_user::required_class(UC_STAFF) ? $new : '') : $new;
			list($date, $time) = explode(' ', format_time($arr['added']), 2);
			$subject_title = $subject != $disp_subject ? ' title="'.bt_security::html_safe($subject).'"' : '';
			$subject = $has_subject ? bt_security::html_safe($disp_subject) : '[No Subject]';
			$ago = get_elapsed_time($arr['added']);

			if ($user_title == '')
				$user_title = $system_message ? 'System Message' : ($has_name ? bt_user::get_class_name($arr['class']) : 'Deleted User');

            $stars = bt_forums::user_stars($arr['flags']);
			$avatar_po = (bool)($arr['flags'] & bt_options::USER_AVATAR_PO);
			bt_forums::avatar($avatar_url, $avtext, $avatar_po);
			$avatar_txt = $avtext ? ' title="'.$avtext.'"' : '';

			$msg = format_comment($arr['msg']);
			
			if (!$out && $unread)
				$upunread[] = $id;

			$messagevars = array(
				'ID'			=> $id,
				'LOC'			=> $loc,
				'TO_FROM'		=> $to_from,
				'USER_NAME'		=> $user_link,
				'STARS'			=> $stars,
				'USER_TITLE'	=> bt_security::html_safe($user_title),
				'SUBJECT'		=> $subject,
				'SUBJECT_TITLE'	=> $subject_title,
				'NEW'			=> $new,
				'DATE'			=> $date,
				'TIME'			=> $time,
				'AGO'			=> $ago,
				'MESSAGE'		=> $msg,
				'REPLY'			=> $reply_link,
				'AVATAR'		=> $avatar_url,
				'AVATAR_TXT'	=> $avatar_txt,
			);

			$messages[] = bt_theme_engine::load_tpl('inbox_message', $messagevars);
		}
		$res->free();
		$messages = bt_theme::$settings['inbox']['inbox_start'].implode("\n", $messages).bt_theme::$settings['inbox']['inbox_end'];

		$massdel = sprintf(bt_theme::$settings['inbox']['massdel'], $loc);

		if (count($upunread)) {
			bt_sql::query('UPDATE messages SET unread = "no" WHERE id IN ('.implode(',',$upunread).')') or bt_sql::err(__FILE__, __LINE__);
			bt_sql::query('UPDATE users SET inbox_new = 0 WHERE id = '.bt_user::$current['id']) or bt_sql::err(__FILE__, __LINE__);
        }
	}
	else {
		$massdel = $pager = '';
		$messages[] = bt_theme::message('Information','An error occured', false, true);
	}
}
else {
	$massdel = $pager = '';
	$messages = bt_theme::message('Information','Your '.$title.' is empty!', false, true);
}

$inboxvars = array(
	'INBOX'		=> $inbox_link,
	'SENTBOX'	=> $sentbox_link,
	'PAGER'		=> $pager,
	'MESSAGES'	=> $messages,
	'DELETE'	=> $massdel,
);

echo bt_theme_engine::load_tpl('inbox', $inboxvars);
bt_theme::foot();
?>
