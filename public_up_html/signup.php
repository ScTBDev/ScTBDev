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
require_once(CLASS_PATH.'bt_location.php');

bt_loginout::db_connect(false);

function check_ban($ip) {
	$sip = ip2long($ip);
	$resip = bt_sql::query('SELECT COUNT(*) FROM `signupbans` WHERE `first` <= '.$sip.' AND `last` >= '.$sip) or bt_sql::err(__FILE__, __LINE__);
	$n = $resip->fetch_row();
	$resip->free();
	if ($n[0] > 0)
		return true;
	else
		return false;
}

$id = 0 + $_GET['id'];
$invid = trim($_GET['invite']);

if ($id && $invid) {
	if ($id < 1 || strlen($invid) != 40 || !bt_string::is_hex($invid))
		bt_theme::error('Sorry','Invalid invite');

	$inv = bt_sql::query('SELECT i.inviteid, i.userid, u.class '.
		'FROM invites AS i '.
		'JOIN users AS u ON (u.id = i.userid) '.
		'WHERE i.id = '.$id.' AND (u.flags & '.bt_options::USER_ENABLED.')') or bt_sql::err(__FILE__,__LINE__);

	if ($inv->num_rows) {
		$invite = $inv->fetch_assoc();
		$invhash = sha1(bt_string::hex2str($invite['inviteid']));
		if ($invhash != $invid)
			bt_theme::error('Sorry','Invalid invite or invite expired');
	}
	else
		bt_theme::error('Sorry','Invalid invite or invite expired');
}
elseif (!bt_config::$conf['allow_signups'])
	bt_theme::error('Sorry', 'Sorry, signups are closed');

$ip = bt_ip::get_ip();
$rip = bt_vars::$realip;
$geoip = bt_geoip::lookup_ip($rip);
$cc = $geoip['country_code'];
$ccid = bt_location::country_by_cc($cc);

if ((check_ban($ip) || bt_bans::check($ip)) || ($ip != $rip && (check_ban($rip) || bt_bans::check($rip))))
	bt_theme::error('Sorry', 'Sorry, signups are closed!');

$res = bt_sql::query('SELECT COUNT(*) FROM `users`') or sqlerr(__FILE__, __LINE__);
$arr = $res->fetch_row();
$res->free();

if ($arr[0] >= bt_config::$conf['maxusers'] && !($invite && $invite['class'] >= UC_STAFF))
	bt_theme::error('Sorry', 'The current user account limit ('.number_format(bt_config::$conf['maxusers']).') has been reached. '.
		'Inactive accounts are pruned all the time, please check back again later...');

bt_theme::head('Signup');

$countries = bt_location::country_list($cc);
$list = array();
foreach ($countries as $cid => $carr)
	$list[] = bt_theme::$settings['signup']['list_prefix'].'<option value="'.$cid.'"'.
		($id == $ccid ? ' selected="selected"' : '').'>'.$carr['name'].'</option>';

$country_list = implode("\n", $list);

$signupvars = array(
	'COUNTRY_LIST'	=> $country_list,
	'ID'			=> $id,
	'INVITE'		=> $invid,
);

echo bt_theme_engine::load_tpl('signup', $signupvars);

bt_theme::foot();
?>
