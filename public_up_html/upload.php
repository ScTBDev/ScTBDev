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
require_once(CLASS_PATH.'bt_mem_caching.php');

bt_loginout::db_connect(true);

bt_theme::head('Upload');

if (!bt_user::required_class(UC_UPLOADER)) {
	bt_theme::message('Sorry...', 'You are not authorized to upload torrents.  (See <a href="/faq.php#up">Uploading</a> in the FAQ.)');
	bt_theme::foot();
	die;
}

if (bt_user::$current['flags'] & bt_options::USER_SSL_TRACKER)
  $urls = $announce_urls_ssl;
elseif (bt_user::$current['flags'] & bt_options::USER_PROXY_TRACKER)
  $urls = $announce_urls_proxy;
else
  $urls = $announce_urls;

$num = count($urls);
$tracker_url = $urls[mt_rand(0, ($num - 1))].'?passkey='.bt_user::$current['passkey'];

$types = array();
$cats = bt_mem_caching::get_cat_list();
foreach ($cats as $catid => $cat)
	$types[] = bt_theme::$settings['upload']['list_prefix'].'<option value="'.$catid.'">'.$cat['ename'].'</option>';

$type_list = implode("\n", $types);
$checked = ' checked="checked"';
$anon = (bool)(bt_user::$current['flags'] & bt_options::USER_ANON);
$anon_unchecked = !$anon ? $checked : '';
$anon_checked = $anon ? $checked : '';

$uploadvars = array(
	'USER_NAME'			=> bt_security::html_safe(bt_user::$current['username']),
	'TRACKER_URL'		=> bt_security::html_safe($tracker_url),
	'MAX_FILE_SIZE'		=> bt_config::$conf['max_torrent_size'],
	'TYPE_LIST'			=> $type_list,
	'ANON_UNCHECKED'	=> $anon_unchecked,
	'ANON_CHECKED'		=> $anon_checked,
);

echo bt_theme_engine::load_tpl('upload', $uploadvars);

bt_theme::foot();
?>
