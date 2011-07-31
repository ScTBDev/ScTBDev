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

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'defines.php');
require_once(CLASS_PATH.'bt_theme.php');
require_once(CLASS_PATH.'bt_theme_engine.php');

$style = isset($_GET['theme']) ? ((int) $_GET['theme']) : 0; 

bt_theme_engine::load_theme($style);
bt_theme::identify_browser();

switch (bt_theme::$browser) {
	case bt_theme::BROWSER_IE:
		$extra_css = 'theme_ie.css';
	break;
	case bt_theme::BROWSER_OPERA:
		$extra_css = 'theme_op.css';
	break;
	case bt_theme::BROWSER_FIREFOX:
	default:
		$extra_css = 'theme_ff.css';
	break;
}

$css1 = bt_theme_engine::$theme_dir.'theme.css';
$css2 = bt_theme_engine::$theme_dir.$extra_css;
$css1_stat = stat($css1);
$css2_stat = stat($css2);

$last_modified = max($css1_stat['mtime'], $css2_stat['mtime']);

$timezone = new DateTimeZone('UTC');
$ex_time = new DateInterval('PT1H');
$lm = new DateTime('now', $timezone);
$ex = new DateTime('now', $timezone);
$lm->setTimestamp($last_modified);
$ex->add($ex_time);

$lm_date = $lm->format(DateTime::RFC2822);
$ex_date = $ex->format(DateTime::RFC2822);

$stop = false;
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	$since = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE'], 2);
	try {
		$since = new DateTime($since[0]);
	}
	catch (Exception $e) {
		$since = false;
	}
	if ($since == $lm) {
		header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
		$stop = true;
	}
}
header('Expires: '.$ex_date);
header('Cache-Control: private');
if ($stop)
	die();

$top_rotate = (string)((floor(time() / 3600) % 2) + 1);
header('Last-Modified: '.$lm_date);
header('Content-Type: text/css');

$cssvars = array(
	'THEME_DIR'		=> bt_theme_engine::$theme_dir,
	'THEME_PIC_DIR'	=> bt_theme_engine::$theme_pic_dir,
	'TOP_ROTATE'	=> $top_rotate,
);

$css = file_get_contents($css1)."\n".file_get_contents($css2);
$css = bt_theme_engine::prepare_tpl($css, $cssvars);
echo $css;
?>
