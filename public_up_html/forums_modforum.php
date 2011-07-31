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
$type = $_GET['type'];

switch ($type) {
	case 'new':
		$form_hash = $pv->create('newforum');
		bt_theme::head('New forum');
		begin_main_frame();
		begin_frame('New Forum', 'center');
		$classread		= UC_USER;
		$classwrite		= UC_USER;
		$classcreate	= UC_USER;
		$sort			= 0;
		$forumname		= '';
		$desc			= '';
	break;
	case 'edit':
		$form_hash = $pv->create('editforum');
		$forumid = 0 + $_GET['id'];
		if ($forumid < 1)
			die;

		$forum = bt_forums::get_forum($forumid);
		if (!$forum)
			bt_theme::error('Error', 'Forum not found.');

		bt_theme::head('Edit forum');
		begin_main_frame();
		begin_frame('Edit Forum', 'center');
		$classread		= $forum['minclassread'];
		$classwrite		= $forum['minclasswrite'];
		$classcreate	= $forum['minclasscreate'];
		$sort			= $forum['sort'];
		$forumname		= $forum['en_name'];
		$desc			= $forum['en_description'];
	break;
	default:
		die;
}

echo '<form method="post" action="forums_submitforum.php">'."\n".
	'<input type="hidden" name="type" value="'.$type.'" />'."\n".
	'<input type="hidden" name="hash" value="'.$form_hash.'" />'."\n".
	($type === 'edit' ? '<input type="hidden" name="id" value="'.$forumid.'" />'."\n" : '');
begin_table();
echo '<tr><td class="rowhead">Forum name</td>' .
	'<td align="left" style="padding: 0px"><input type="text" size="60" maxlength="'.bt_forums::MAX_SUBJECT_LENGTH.'" name="name" '.
	'style="height: 19px" value="'.$forumname.'" /> &nbsp; &nbsp; <input type="text" size="3" maxlength="3" name="sort" '.
	'style="height: 19px" value="'.$sort.'" /></td></tr>'."\n".
	'<tr><td class="rowhead">Description</td>' .
	'<td align="left" style="padding: 0px"><textarea name="description" cols="68" rows="3" style="border: 0px">'.$desc.'</textarea></td></tr>'."\n".
	'<tr><td class="rowhead"></td><td align="left" style="padding: 0px">&nbspMinimum <select name="readclass">';

for ($i = UC_MIN; $i <= bt_user::$current['class']; ++$i)
	echo '<option value="'.$i.'"'.($i == $classread ? ' selected="selected"' : '').' />'.bt_user::get_class_name($i).'</option>'."\n";

echo '</select> Class required to View<br />'."\n".'&nbspMinimum <select name="writeclass">';
for ($i = UC_MIN; $i <= bt_user::$current['class']; ++$i)
	echo '<option value="'.$i.'"'.($i == $classwrite ? ' selected="selected"' : '').' />'.bt_user::get_class_name($i).'</option>'."\n";

echo '</select> Class required to Post<br />'."\n".'&nbspMinimum <select name="createclass">';
for ($i = UC_MIN; $i <= bt_user::$current['class']; ++$i)
	echo '<option value="'.$i.'"'.($i == $classcreate ? ' selected="selected"' : '').' />'.bt_user::get_class_name($i).'</option>'."\n";

echo '</select> Class required to Create Topics</td></tr>'."\n".
	'<tr><td colspan="2" align="center"><input type="submit" class="btn" value="Submit"></td></tr>'."\n";

end_table();
echo '</form>'."\n";
end_frame();
end_main_frame();
bt_theme::foot();
?>
