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
require_once(CLASS_PATH.'bt_pm.php');
require_once(CLASS_PATH.'bt_session.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST')
	bt_theme::error('Error', 'Method');

bt_loginout::db_connect(true);

$form_hash = isset($_POST['hash']) ? trim($_POST['hash']) : '';

if (isset($_POST['n_pms'])) {
	//////  MM  ///
	$session = new bt_session(true, 10800);
	if (!$session->check($form_hash, 'massmessage'))
		die('h4x');
	
    if (get_user_class() < UC_MODERATOR)
          bt_theme::error("Error", "Permission denied");

	$title = trim($_POST['title']);
    $msg = trim($_POST['msg']);
	if (!$msg || !$title)
		bt_theme::error("Error","Please enter something!");

    $sender_id = ($_POST['sender'] == 'system' ? 0 : bt_user::$current['id']);

	$n_pms = (int)$_POST['n_pms'];
	$pmees = trim($_POST['pmees']);

	if (strlen($pmees) != 40 ||  !bt_string::is_hex($pmees))
		die('bad key');

	$key = 'usersearchs:'.$pmees;
	bt_memcache::connect();
	$query = bt_memcache::get($key);
	if ($query === bt_memcache::NO_RESULT)
		bt_theme::error('Error', 'Bad SQL Query, please go back and redo the user search');


    $from_is = $query;

    $query = "INSERT INTO messages (sender, receiver, added, subject, msg) ".
             "SELECT $sender_id, u.id, " . time() .", ".sqlesc($title). ", ". sqlesc($msg)." ".$from_is;

    mysql_query($query) or sqlerr(__FILE__, __LINE__);
    $n = mysql_affected_rows();

    $comment = $_POST['comment'];
    $snapshot = $_POST['snap'];

    // add a custom text or stats snapshot to comments in profile
    if ($comment || $snapshot)
    {
            $res = mysql_query("SELECT u.id, u.uploaded, u.downloaded, u.modcomment ".$from_is) or sqlerr(__FILE__, __LINE__);
            if (mysql_num_rows($res) > 0)
            {
              $l = 0;
              while ($user = mysql_fetch_assoc($res))
              {
                unset($new);
                $old = $user['modcomment'];
                if ($comment)
                  $new = $comment;
                if ($snapshot)
                {
                  $new .= ($new?"\n":"") .
                    "MMed, " . gmdate("Y-m-d") . ", " .
                    "UL: " . bt_theme::mksizegb($user['uploaded']) . ", " .
                    "DL: " . bt_theme::mksizegb($user['downloaded']) . ", " .
                    "r: " . ratios($user['uploaded'],$user['downloaded'], False) . " - " .
                    ($_POST['sender'] == "system" ? "System" : bt_user::$current['username']);
                }
                      $new .= $old?("\n".$old):$old;
                      mysql_query("UPDATE users SET modcomment = " . sqlesc($new) . " WHERE id = " . $user['id'])
                        or sqlerr(__FILE__, __LINE__);
                      if (mysql_affected_rows())
                        $l++;
              }
            }
    }
   header('Location: '.bt_vars::$base_url.'/staff.php');
   die;
  }
  else
  {                                                                                                                                                                                                       //////  PM  ///
          $receiver = 0 + $_POST["receiver"];
          $subject = $_POST['subject'];
          $origmsg = 0 + $_POST['origmsg'];
          $save = (bool)0 + $_POST['save'];
          $returnto = $_POST["returnto"];

		$session = new bt_session(true, 3600);
		if (!$session->check($form_hash, 'sendmessage'))
			die('h4x');

          if (!is_valid_id($receiver) || ($origmsg && !is_valid_id($origmsg)))
                  bt_theme::error('Error','Invalid ID');

          $msg = trim($_POST['msg']);
          if (!$msg)
            bt_theme::error("Error","Please enter something!");

          $location = $save ? bt_pm::PM_BOTH : bt_pm::PM_INBOX;

          $res = mysql_query('SELECT email, CAST(flags AS SIGNED) AS flags, last_access as la FROM users WHERE id = '.$receiver) or sqlerr(__FILE__, __LINE__);
          $user = mysql_fetch_assoc($res);
          if (!$user)
            bt_theme::error("Error", "No user with that receiver ID.");

		  $user['flags'] = (int)$user['flags'];

          //Make sure recipient wants this message
			if (!bt_user::required_class(UC_STAFF))
                {
            if ($user['flags'] & bt_options::USER_ACCEPT_PMS)
            {
              $res2 = mysql_query("SELECT * FROM blocks WHERE userid=$receiver AND blockid=" . bt_user::$current["id"]) or sqlerr(__FILE__, __LINE__);
              if (mysql_num_rows($res2) == 1)
                bt_theme::error("Refused", "This user has blocked PMs from you.");
            }
            elseif ($user['flags'] & bt_options::USER_ACCEPT_FRIEND_PMS)
            {
              $res2 = mysql_query("SELECT * FROM friends WHERE userid=$receiver AND friendid=" . bt_user::$current["id"]) or sqlerr(__FILE__, __LINE__);
              if (mysql_num_rows($res2) != 1)
                bt_theme::error("Refused", "This user only accepts PMs from users in his friends list.");
            }
            else
              bt_theme::error("Refused", "This user does not accept PMs.");
          }

          $sent = bt_pm::send(bt_user::$current['id'], $receiver, $msg, $subject, $location);
          if (!$sent)
            bt_theme::error('Error', 'Error sending PM');

           if ($user['flags'] & bt_options::USER_PM_NOTIFICATION)
          {

            if (time() - $user["la"] >= 300)
            {
             $username = bt_user::$current["username"];
$body = 'You have received a PM from '.$username.'!

You can use the URL below to view the message (you may have to login).

'.bt_vars::$base_url.'/inbox.php

--
'.bt_config::$conf['site_name'];


            mail($user["email"], "You have received a PM from " . $username . "!", $body, 'From: '.bt_config::$conf['site_email']);
            }
          }
          $delete = (bool)0 + $_POST['delete'];

          if ($origmsg)
          {
      if ($delete)
      {
              // Make sure receiver of $origmsg is current user
              $res = mysql_query("SELECT * FROM messages WHERE id=$origmsg") or sqlerr(__FILE__, __LINE__);
              if (mysql_num_rows($res) == 1)
              {
                $arr = mysql_fetch_assoc($res);
                if ($arr["receiver"] != bt_user::$current["id"])
                  bt_theme::error("w00t","This shouldn't happen.");
                if ($arr["location"] == "in")
                        {
                         mysql_query("DELETE FROM messages WHERE id=$origmsg AND location = 'in'") or sqlerr(__FILE__, __LINE__);
                         mysql_query('UPDATE users SET inbox = (inbox - 1) WHERE id = '.bt_user::$current['id']);
                        }
                elseif ($arr["location"] == "both")
                        {
                         mysql_query("UPDATE messages SET location = 'out' WHERE id=$origmsg AND location = 'both'") or sqlerr(__FILE__, __LINE__);
                         mysql_query('UPDATE users SET inbox = (inbox - 1) WHERE id = '.bt_user::$current['id']);
                        }
              }
      }
             if (!$returnto)
                     $returnto = bt_vars::$base_url.'/inbox.php';
          }

    if ($returnto)
    {
      header("Location: $returnto");
      die;
    }

          bt_theme::head();
          bt_theme::message("Succeeded", (($n_pms > 1) ? "$n messages out of $n_pms were" : "Message was").
            " successfully sent!" . ($l ? " $l profile comment" . (($l>1) ? "s were" : " was") . " updated!" : ""));

        bt_theme::foot();
        exit;
       }
?>
