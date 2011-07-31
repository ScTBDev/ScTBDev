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
require_once(CLASS_PATH.'bt_forums.php');
require_once(CLASS_PATH.'bt_chans.php');
require_once(CLASS_PATH.'bt_location.php');

bt_loginout::db_connect(true);

$user = bt_user::$current;
$pv = new bt_session(true, 1800);
$form_hash = $pv->create('profile');
$settings = $user['settings'];
$tsettings = bt_theme::$settings['my'];

$radio_c = $tsettings['radio_checked'];
$check_c = $tsettings['check_checked'];
$list_s = $tsettings['list_selected'];

bt_theme::head('Edit Profile', false);

$message = '';
if (isset($_GET['edited'])) {
	$message = 'Profile updated! Click <a href="/userdetails.php?id='.$user['id'].'"'.$tsettings['link'].'>here</a> to see it!';
	if (isset($_GET['mailsent']))
		$message .= '<br /><br />'."\n".'Confirmation email has been sent!';
}
elseif (isset($_GET['emailch']))
	$message = 'Email address changed!';
elseif (isset($_GET['ircch']))
	$message = 'Your IRC invite code has been updated, click <a href="/irc.php"'.$tsettings['link'].'>here</a> to see it!';

$msg = $message != '' ? bt_theme::message('Info', $message, false, true, true) : '';

$username = bt_security::html_safe($user['username']);

$themesl = bt_theme_engine::$themes;
$themes = array();
$donor = (bool)(bt_user::$current['flags'] & bt_options::USER_DONOR);
foreach ($themesl as $tid => $theme) {
	if ((!$theme['donor'] || ($theme['donor'] && !$donor)) && !bt_user::required_class($theme['class']))
		continue;

	$selected = $user['theme'] == $tid ? $list_s : '';
	$themes[] = sprintf($tsettings['themes_entry'], $tid, $selected, bt_security::html_safe($theme['full_name']));
}
$theme_list = implode($tsettings['themes_join'], $themes);


$countries = bt_location::countries();
$countrys = array();
foreach ($countries as $cid => $carr) {
    $selected = $user['country'] == $cid ? $list_s : '';
    $countrys[] = sprintf($tsettings['country_entry'], $cid, $selected, $carr['name']);
}
$country_list = implode($tsettings['country_join'], $countrys);

$timezones = array();
foreach (bt_time::$time_zones as $offset => $name) {
	$selected = $user['timezone'] == $offset ? $list_s : '';
	$timezones[] = sprintf($tsettings['timezone_entry'], $offset, $selected, $name);
}
$timezone_list = implode($tsettings['timezone_join'], $timezones);
$dst = $settings['dst'] ? $check_c : '';

$dst_offsets = array();
foreach (bt_time::$dst_offsets as $offset => $name) {
	$selected = $user['dst_offset'] == $offset ? $list_s : '';
	$dst_offsets[] = sprintf($tsettings['dst_offset_entry'], $offset, $selected, $name);
}
$dst_offset_list = implode($tsettings['dst_offset_join'], $dst_offsets);

$accept_pm_all = $settings['acceptpms'] && $settings['acceptfriendpms'] ? $radio_c : '';
$accept_pm_friends = !$settings['acceptpms'] && $settings['acceptfriendpms'] ? $radio_c : '';
$accept_pm_staff = !$settings['acceptpms'] && !$settings['acceptfriendpms'] ? $radio_c : '';

$delete_pm = $settings['deletepms'] ? $check_c : '';
$save_pm = $settings['savepms'] ? $check_c : '';
$pm_notif = $settings['pmnotif'] ? $check_c : '';
$proxy = $settings['proxy'] ? $check_c : '';
$ssl_tracker = $settings['ssl_tracker'] ? $check_c : '';
$ssl_site = $settings['ssl_site'] ? $check_c : '';

$avatar_url = bt_security::html_safe($user['avatar']);
$avatar_po = $settings['avatar_po'] ? $check_c : '';
$avatars_all = $settings['avatars'] && $settings['avatars_po'] ? $radio_c : '';
$avatars_some = $settings['avatars'] && !$settings['avatars_po'] ? $radio_c : '';
$avatars_none = !$settings['avatars'] ? $radio_c : '';

$statbar = $settings['statbar'] ? $check_c : '';
$torrents_pp = (int)min(200, max(0, $user['torrentsperpage']));
$topics_pp = (int)min(100, max(0, $user['topicsperpage']));
$posts_pp = (int)min(100, max(0, $user['topicsperpage']));

$profile_info = bt_security::html_safe($user['info']);
$email = bt_security::html_safe($user['email']);


$whore = '';
if (bt_user::required_class(UC_WHORE)) {
	$anon_off = !$settings['privacy'] ? $radio_c : '';
	$anon_on = $settings['privacy'] ? $radio_c : '';
	$hide_stats = $settings['hide_stats'] ? $check_c : '';

	$whore_myvars = array(
		'ANON_OFF'		=> $anon_off,
		'ANON_ON'		=> $anon_on,
		'HIDE_STATS'	=> $hide_stats,
		'USERNAME'		=> $username,
	);

	$whore = bt_theme_engine::load_tpl('my_whore', $whore_myvars);
}

$donate = '';
if (bt_user::required_class(UC_WHORE) || $settings['donor']) {
	$title = bt_security::html_safe($user['title']);

	$donate_myvars = array(
		'CUSTOM_TITLE'	=> $title,
	);

	$donate = bt_theme_engine::load_tpl('my_donate', $donate_myvars);
}
$staff = '';
if (bt_user::required_class(UC_STAFF)) {
	$ip_access = explode(';', $user['ip_access']);
	$ip_access = bt_security::html_safe(implode("\n", $ip_access));

	$staff_myvars = array(
		'IP_ACCESS'		=> $ip_access,
	);

	$staff = bt_theme_engine::load_tpl('my_staff', $staff_myvars);
}

$cats = bt_mem_caching::get_cat_list();
$catrows = $catentrys = array();
$ncats = count($cats);
$i = 0;

foreach ($cats as $catid => $cat) {
	$checked = strpos($user['notifs'], '[cat'.$catid.']') !== false ? $check_c : '';
    $catentrys[] = sprintf($tsettings['catlist']['entry'], $catid, $checked,  $cat['ename']);
    $i++;
    $catsleft = $i % $tsettings['catlist']['per_row'];
    if ($catsleft == 0 || $i == $ncats) {
        $catrows[] = implode($tsettings['catlist']['join'], $catentrys);
        $catentrys = array();
    }
}
$cat_table = implode($tsettings['catlist']['row_join'], $catrows);


$channels = bt_bitmask::fetch_all(bt_user::$current['chans'], true);
$chans = array();
foreach (bt_chans::$channels as $chid => $chan) {
	if ($channels['allow_'.$chid]) {
		$chans[] = array(
			'id'		=> $chid,
			'name'		=> bt_security::html_safe($chan),
			'enabled'	=> $channels['invite_'.$chid],
		);
	}

}
$chanrows = $chanentrys = array();
$nchans = count($chans);
$i = 0;

foreach ($chans as $chan) {
	$checked = $chan['enabled'] ? $check_c : '';
	$chanentrys[] = sprintf($tsettings['irclist']['entry'], $chan['id'], $checked, $chan['name']);
	$i++;
	$chansleft = $i % $tsettings['irclist']['per_row'];
	if ($chansleft == 0 || $i == $nchans) {
		$chanrows[] = implode($tsettings['irclist']['join'], $chanentrys);
		$chanentrys = array();
	}
}
$irc_table = implode($tsettings['irclist']['row_join'], $chanrows);


$forum_buttons = bt_forums::settings_to_forum_theme($user['flags']);

$imgtypes = array('unlocked','unlockedposted','unlockednew','unlockednewposted','locked','lockedposted','lockednew','lockednewposted');
$fbs = array();
foreach (bt_forums::$buttons as $bid => $name) {
	$imgs = '';
	foreach ($imgtypes as $imgname)
        $imgs .= sprintf($tsettings['ficons_img'], bt_config::$conf['pic_base_url'], $name, $imgname);

	$selected = $bid === $forum_buttons ? $radio_c : '';
	$fbs[] = sprintf($tsettings['ficons_entry'], $bid, $selected, $imgs);
}
$forum_icons = implode($tsettings['ficons_join'], $fbs);



/////////////////////////////////////////////
$myvars = array(
	'FORM_HASH'			=> $form_hash,
	'ACCEPT_PM_ALL'		=> $accept_pm_all,
	'ACCEPT_PM_FRIENDS'	=> $accept_pm_friends,
	'ACCEPT_PM_STAFF'	=> $accept_pm_staff,
	'DELETE_PM'			=> $delete_pm,
	'SAVE_PM'			=> $save_pm,
	'PM_NOTIF'			=> $pm_notif,
	'PROXY'				=> $proxy,
	'SSL_TRACKER'		=> $ssl_tracker,
	'SSL_SITE'			=> $ssl_site,
	'THEME_LIST'		=> $theme_list,
	'COUNTRY_LIST'		=> $country_list,
	'TIMEZONE_LIST'		=> $timezone_list,
	'DST'				=> $dst,
	'DST_OFFSET_LIST'	=> $dst_offset_list,
	'AVATAR_URL'		=> $avatar_url,
	'AVATAR_PO'			=> $avatar_po,
	'AVATARS_ALL'		=> $avatars_all,
	'AVATARS_SOME'		=> $avatars_some,
	'AVATARS_NONE'		=> $avatars_none,
	'STATBAR'			=> $statbar,
	'TORRENTS_PP'		=> $torrents_pp,
	'TOPICS_PP'			=> $topics_pp,
	'POSTS_PP'			=> $posts_pp,
	'PROFILE_INFO'		=> $profile_info,
	'EMAIL'				=> $email,
	'STAFF'				=> $staff,
	'DONATE'			=> $donate,
	'WHORE'				=> $whore,
	'CAT_TABLE'			=> $cat_table,
	'IRC_TABLE'			=> $irc_table,
	'FORUM_ICONS'		=> $forum_icons,
	'MSG'				=> $msg,
);
echo bt_theme_engine::load_tpl('my', $myvars);

bt_theme::foot();
?>
