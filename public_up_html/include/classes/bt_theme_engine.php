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

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_theme.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_config.php');

class bt_theme_engine {
	const THEME_DIR = 'themes/';
	const BROWSER_FIREFOX = 1;
	const BROWSER_OPERA = 2;
	const BROWSER_IE = 3;

	const TPL_CACHE_TIME = 60;

	public static $themes = array(
		array(
			'name'			=> 'blue',
			'full_name'		=> 'Blue',
			'donor'			=> false,
			'class'			=> UC_MIN,
		),
		array(
			'name'		=> 'black',
			'full_name'	=> 'Black',
			'donor'		=> false,
			'class'		=> UC_STAFF,
		),
//		array(
//			'name'		=> 'blue_ajax',
//			'full_name'	=> 'Blue with Ajax (unknownx + mx781)',
//		),
	);

	public static $templates = array(
		'header','footer','statbar','donbar','donbar_empty','upload_btn','invite_btn','navbar','login','login_error',
		'alerts','alert_conn','alert_msg','alert_news','invite','irc','irc_script','browse','upload','signup','recover',
		'stdmsg','browse_torrenttable','browse_torrenttable_row','donate','tags','tags_sample','invite_pending','anatomy',
		'rules','faq','rules_upload','rules_staff','searching','log','log_table','log_table_row','stafflog','links',
		'forums_index','forums_index_table','forums_index_table_row','forums_index_lastpost','forums_viewforum','about',
		'forums_viewforum_table','forums_viewforum_table_row','forums_viewtopic','forums_quick_jump','users',
		'inbox','inbox_message','sendmessage','invite_box','invite_confirmed','invite_confirmed_row','my','my_staff',
		'my_whore','my_donate','snatches','snatches_staff','snatches_table','snatches_staff_table','snatches_table_row',
		'snatches_staff_table_row','staff','staff_list','staff_list_row','staff_fls_row','staff_admin','staff_mod',
		'staff_tools','upapp','formats','videoformats','edituser_admin','edituser_mod','edituser_fmod','smilies','svn',
		'makepoll','report_user','adduser','bans','signupbans','rsser','viewnfo','userdetails','forums_post','forum_post',
		'forums_viewtopic_modtools','index',
	);

	public static $theme = 'blue';
	public static $theme_dir = '';
	public static $theme_pic_dir = '';

	private static $tpl_cache = array();
	private static $theme_loaded = false;

	public static function load() {
		if (self::$theme_loaded)
			return true;

		$theme = bt_user::$current ? bt_user::$current['theme'] : 0;
		$loaded = self::load_theme($theme);
	}

	public static function load_theme($theme) {
		if (!isset(self::$themes[$theme]))
			$theme = 0;

		$theme = self::$themes[$theme]['name'];

		$theme_dir = self::THEME_DIR.$theme;
		if (!file_exists($theme_dir) || !is_dir($theme_dir))
			return false;

		self::$theme = $theme;
		self::$theme_dir = $theme_dir.'/';
		self::$theme_pic_dir = '/'.self::$theme_dir.'pic/';
		require_once(self::$theme_dir.'settings.php');
		bt_theme::$settings = $THEME;
		self::$theme_loaded = true;
		return true;
	}

	public static function load_tpl($name, array $vars = array()) {
		if (!self::$theme_loaded)
			self::load();

		if (!in_array($name, self::$templates, true))
			return false;

		$vars['THEME_DIR'] = self::$theme_dir;
		$vars['THEME_PIC_DIR'] = self::$theme_pic_dir;
		$vars['PIC_DIR'] = bt_config::$conf['pic_base_url'];

		$tpl = self::get_tpl($name);
		$tpl = self::prepare_tpl($tpl, $vars);

		return $tpl;
	}

	private static function get_tpl($name) {
		if (isset(self::$tpl_cache[$name]))
			$tpl = self::$tpl_cache[$name];
		else {
			$key = 'themes::'.self::$theme.'::'.$name;
			$tpl = bt_memcache::get($key);
			if ($tpl === bt_memcache::NO_RESULT) {
				$file = self::$theme_dir.$name.'.tpl';
				$tpl = file_get_contents($file);
				bt_memcache::add($key, $tpl, self::TPL_CACHE_TIME);
			}
			self::$tpl_cache[$name] = $tpl;
		}
		return $tpl;
	}

	public static function prepare_tpl($tpl, $vars) {
		$search = array();
		$replace = array();
		foreach ($vars as $vname => $vdata) {
			$search[] = '{'.$vname.'}';
			$replace[] = $vdata;
		}
		return str_replace($search, $replace, $tpl);
	}
}
?>
