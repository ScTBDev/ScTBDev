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
require_once(CLASS_PATH.'allowed_staff.php');

bt_loginout::db_connect(true);

if (!bt_user::required_class(UC_ADMINISTRATOR))
	bt_theme::error('Error', 'Access Denied');

$as = new allowed_staff;
if (!$as->check('forums'))
	die();
$pv = new bt_session(true, 900);

$type			= $_POST['type'];
$form_hash		= trim($_POST['hash']);
$classread		= 0 + $_POST['readclass'];
$classwrite		= 0 + $_POST['writeclass'];
$classcreate	= 0 + $_POST['createclass'];
$sort			= 0 + $_POST['sort'];
$forumname		= trim($_POST['name']);
$desc			= trim($_POST['description']);

if (!bt_user::required_class($classread) || !bt_user::required_class($classwrite) || !bt_user::required_class($classcreate))
	bt_theme::error('Error', 'Acceed denied');

if (empty($forumname))
	bt_theme::error('Error','You must specify a name for the forum');

if (empty($desc))
	bt_theme::error('Error','You must provide a description for the forum.');

if ($sort < 0 || $sort > 255)
	bt_theme::error('Error','Sorting value specified is not valid.');


switch ($type) {
	case 'new':
		if (!$pv->check($form_hash, 'newforum'))
			die('h4x');

		bt_sql::query('INSERT INTO `forums` (`name`, `description`, `minclassread`, `minclasswrite`, `minclasscreate`, `sort`) '.
			'VALUES('.bt_sql::esc($forumname).', '.bt_sql::esc($desc).', '.$classread.', '.$classwrite.', '.$classcreate.', '.$sort.')')
			or bt_sql::err(__FILE__, __LINE__);

		$forumid = bt_sql::$insert_id;
		if ($forumid < 1)
			bt_theme::error('Error', 'No forum ID returned');

		header('Location: forums_viewforum.php?id='.$forumid);
		die;
	break;
	case 'edit':
		if (!$pv->check($form_hash, 'editforum'))
			die('h4x');
		$forumid = 0 + $_POST['id'];
		if ($forumid < 1)
			die;

		$forum = bt_forums::get_forum($forumid);
		if (!$forum)
			bt_theme::error('Error', 'Forum not found.');

		bt_sql::query('UPDATE `forums` SET `name` = '.bt_sql::esc($forumname).', `description` = '.bt_sql::esc($desc).', '.
			'`minclassread` = '.$classread.', `minclasswrite` = '.$classwrite.', `minclasscreate` = '.$classcreate.', '.
			'`sort` = '.$sort.' WHERE `id` = '.$forumid) or bt_sql::err(__FILE__, __LINE__);

		bt_forums::delete_forum_cache($forumid);

		header('Location: forums_index.php');
		die;
	break;
	default:
		die;
}
?>
