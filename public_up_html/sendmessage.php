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
require_once(CLASS_PATH.'bt_session.php');

bt_loginout::db_connect(true);
bt_memcache::connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['n_pms']) || isset($_POST['pmees']))) {
	////////  MM  //
	if (get_user_class() < UC_MODERATOR)
		bt_theme::error("Error", "Permission denied");

	$session = new bt_session(true, 10800);
	$form_hash = $session->create('massmessage');

	$n_pms = (int)$_POST['n_pms'];
	$pmees = trim($_POST['pmees']);

	if (strlen($pmees) != 40 || !bt_string::is_hex($pmees))
		die('bad key');

	$key = 'usersearchs:'.$pmees;
	$query = bt_memcache::get($key);
	if ($query === bt_memcache::NO_RESULT)
		bt_theme::error('Error', 'Bad SQL Query, please go back and redo the user search');

	$body = '';

	bt_theme::head('Send message', false);
?>
<table class="main" width="750" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="embedded">
			<div align="center">
				<h1>Mass Message to <?=$n_pms?> user<?=($n_pms >1 ? 's' : '')?>!</h1>
				<form method="post" action="takemessage.php">
				<? if ($_SERVER['HTTP_REFERER']) { ?>
					<input type="hidden" name="returnto" value="<?=$_SERVER['HTTP_REFERER']?>">
				<? } ?>
					<table border="1" cellspacing="0" cellpadding="5">
						<tr>
							<td colspan="2">
								<div align="center">
									Subject: <input name="title" type="text" size="72" value="Mass PM: " /><br />
									<textarea name="msg" cols="80" rows="15"><?=$body?></textarea>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div align="center"><b>Comment: &nbsp;</b>
									<input name="comment" type="text" size="70" />
								</div>
							</td>
						</tr>
						<tr>
							<td>
								<div align="center"><b>From:&nbsp;&nbsp;</b>
									<?=bt_user::$current['username']?>
									<input name="sender" type="radio" value="self" /> &nbsp; System
									<input name="sender" type="radio" value="system" checked="checked" />
								</div>
							</td>
							<td>
								<div align="center">
									<b>Take snapshot:</b> <input name="snap" type="checkbox" value="1">
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<input type="submit" value="Send it!" class="btn" />
							</td>
						</tr>
					</table>
					<input type="hidden" name="pmees" value="<?=$pmees?>" />
					<input type="hidden" name="n_pms" value="<?=$n_pms?>" />
					<input type="hidden" name="hash" value="<?php echo $form_hash; ?>">
				</form>
			</div>
		</td>
	</tr>
</table>
  <?
}
else {
	$receiver = 0 + $_GET['receiver'];
	if (!is_valid_id($receiver))
		die;

	$replyto = 0 + $_GET['replyto'];
	if ($replyto && !is_valid_id($replyto))
		die;

	$res = bt_sql::query('SELECT `username`, `class` FROM `users` WHERE `id` = '.$receiver) or bt_sql::err(__FILE__, __LINE__);
	if (!$res->num_rows)
		die('No user with that ID.');

	$session = new bt_session(true, 3600);
	$form_hash = $session->create('sendmessage');

	$user = $res->fetch_assoc();
	$res->free();
	$username = $user['username'];
	$user_link = bt_forums::user_link($receiver, $username, $user['class']);

	$return_url = ($_GET['returnto'] || $_SERVER['HTTP_REFERER']) ? ($_GET['returnto'] ? $_GET['returnto'] : $_SERVER['HTTP_REFERER'])  : '';

	$return_to = $return_url ? '<input type="hidden" name="returnto" value="'.bt_security::html_safe($return_url).'" />' : '';

	if ($replyto) {
		$res = bt_sql::query('SELECT `m`.*, `u`.`username` FROM `messages` AS `m` LEFT JOIN `users` AS `u` ON (`u`.`id` = `m`.`sender`) '.
			'WHERE `m`.`id` = '.$replyto) or bt_sql::err(__FILE__, __LINE__);

		if (!$res->num_rows)
			die;

		$msga = $res->fetch_assoc();
		$res->free();
		if ($msga['receiver'] != bt_user::$current['id'])
			die;

		$body .= "\n\n\n".'-------- '.$msga['username'].' wrote: --------'."\n".$msga['msg']."\n";
		$subject = trim($msga['subject']);
		$subject = (bt_utf8::substr($subject, 0,3) != 'RE:' ? 'RE: '.$subject : $subject);
		$save = (bt_user::$current['flags'] & bt_options::USER_SAVE_PMS) ? ' checked="checked"' : '';
		$delete = '<input type="hidden" name="origmsg" value="'.$replyto.'" /><input type="checkbox" name="delete" value="1" '.
			((bt_user::$current['flags'] & bt_options::USER_DELETE_PMS) ? 'checked="checked"' : '').'/> Delete message you are replying to';
		$subject = bt_security::html_safe($subject);
		$body = bt_security::html_safe($body);
	}
	else {
		$subject = 'Subject';
		$body = $delete = $save = '';
	}

	bt_theme::head('Send message', false);

	$messagevars = array(
		'FORM_HASH'		=> $form_hash,
		'USER_NAME'		=> $user_link,
		'RETURN_TO'		=> $return_to,
		'SUBJECT'		=> $subject,
		'MSG'			=> $body,
		'SAVE_CHECKED'	=> $save,
		'DELETE'		=> $delete,
		'RECEIVER'		=> $receiver,
	);

	echo bt_theme_engine::load_tpl('sendmessage', $messagevars);
}

bt_theme::foot();
?>
