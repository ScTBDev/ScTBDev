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
require_once(CLASS_PATH.'bt_nfo.php');

bt_loginout::db_connect(true);

$id = 0 + $_GET['id'];
$download = (bool)0 + $_GET['down'];
if (!bt_user::required_class(UC_POWER_USER) || !is_valid_id($id))
	die();

$r = bt_sql::query('SELECT `name`, `nfo` FROM `torrents` WHERE `id` = '.$id) or bt_sql::err(__FILE__,__LINE__);
$a = $r->fetch_assoc() or die('Puke');
$nfo = bt_nfo::clean($a['nfo']);
$nfo_name = bt_nfo::nfo_name($nfo);
$nfo_png = $nfo_name.'.png';
$nfo_txt = $nfo_name.'.nfo';


$png_file = bt_config::$conf['nfo_dir'].'/'.$nfo_png;
$nfo_file =  bt_config::$conf['nfo_dir'].'/'.$nfo_txt;


if (!file_exists($png_file) || !file_exists($nfo_file)) {
	bt_nfo::nfo2png($nfo, $png_file);
	file_put_contents($nfo_file, $nfo);
}
else {
	touch($png_file);
	touch($nfo_file);
}

if ($download) {
	header('Location: '.bt_config::$conf['default_base_url'].bt_config::$conf['nfo_url'].$nfo_txt);
	die;
}
bt_theme::head('NFO for "'.$a['name'].'"');
$nfovars = array(
	'ID'		=> $id,
	'NAME'		=> bt_security::html_safe($a['name']),
	'NFO_PIC'	=> bt_config::$conf['nfo_url'].$nfo_png,
);
echo bt_theme_engine::load_tpl('viewnfo', $nfovars);
bt_theme::foot();
?>
