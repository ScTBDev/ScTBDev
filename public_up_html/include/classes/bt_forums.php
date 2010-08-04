<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2010 ScTBDev.ca
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
require_once(INCL_PATH.'define_bits.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_security.php');

class bt_forums {
	const MAX_SUBJECT_LENGTH = 50;
	const MAX_BLOCK_TAGS = 100;

	public static $buttons = array('black','darkblue','gradient','th0r','old');

	public static function insert_quick_jump_menu($startforum = 0, $return = false) {
		$list = array();
		$forums = self::get_forums();
		foreach ($forums as $forum) {
			if ($forum['minclassread'] <= bt_user::$current['class'])
				$list[] = bt_theme::$settings['forums']['quick_jump']['list_prefix'].'<option value="'.$forum['id'].'"'.
					($startforum == $forum['id'] ? ' selected="selected"' : '').'>'.$forum['name'].'</option>';
		}
		if (count($list)) {
			$quickjumpvars = array(
				'LIST'	=> implode("\n", $list),
			);
			$quick_jump = bt_theme_engine::load_tpl('forums_quick_jump', $quickjumpvars);
		}
		else
			$quick_jump = '';

		if ($return)
			return $quick_jump;

		echo $quick_jump;
	}

	public static function get_forums() {
		bt_memcache::connect();
		$key = 'forums::list';
		$forums = bt_memcache::get($key);
		if (!$forums) {
			$forums = array();
			$forumsq = bt_sql::query('SELECT id, name, minclassread, minclasswrite, minclasscreate FROM forums ORDER BY name ASC')
				or bt_sql::err(__FILE__, __LINE__);

			if ($forumsq->num_rows) {
				while ($forum = $forumsq->fetch_assoc()) {
					$forum['id'] = 0 + $forum['id'];
					$forum['minclassread'] = 0 + $forum['minclassread'];
					$forum['minclasswrite'] = 0 + $forum['minclasswrite'];
					$forum['minclasscreate'] = 0 + $forum['minclasscreate'];
					$forum['name'] = bt_security::html_safe($forum['name']);
					$forums[] = $forum;
				}
			}
			$forumsq->free();
			bt_memcache::add($key, $forums, 10800);
		}
		return $forums;
	}

	public static function get_forum($id) {
		$forumid = 0 + $id;
		bt_memcache::connect();
		$key = 'forum::id:::'.$forumid;

		$forum = bt_memcache::get($key);
		if ($forum === false) {
			$forumq = bt_sql::query('SELECT name, description, minclassread, minclasswrite, minclasscreate, topiccount, '.
				'postcount, sort, lasttopic FROM forums WHERE id = '.$forumid) or bt_sql::err(__FILE__, __LINE__);

			if (!$forumq->num_rows) {
				bt_memcache::add($key, 0, 86400);
				return false;
			}

			$forum = $forumq->fetch_assoc();
			$forumq->free();
			$forum['en_name']			= bt_security::html_safe($forum['name']);
			$forum['en_description']	= bt_security::html_safe($forum['description']);
			$forum['minclassread']		= 0 + $forum['minclassread'];
			$forum['minclasswrite']		= 0 + $forum['minclasswrite'];
			$forum['minclasscreate']	= 0 + $forum['minclasscreate'];
			$forum['topiccount']		= 0 + $forum['topiccount'];
			$forum['postcount']			= 0 + $forum['postcount'];
			$forum['sort']				= 0 + $forum['sort'];
			$forum['lasttopic']			= 0 + $forum['lasttopic'];

			bt_memcache::add($key, $forum, 10800);
		}
		elseif (!$forum)
			return false;

		return $forum;
	}

	public static function delete_forum_cache($id) {
		$forumid = 0 + $id;
		bt_memcache::connect();
		$key = 'forums::list';
		$key2 = 'forum::id:::'.$forumid;
		bt_memcache::del($key);
		bt_memcache::del($key2);
	}

	public static function delete_topic_cache($id) {
		$topicid = 0 + $id;
		bt_memcache::connect();
		$key = 'topic::id:::'.$topicid;
		bt_memcache::del($key);
	}

	public static function delete_post_cache($id) {
		$postid = 0 + $id;
		$key1 = 'post::showpo:::'.$postid;
		$key2 = 'post::hidepo:::'.$postid;
		bt_memcache::connect();
		bt_memcache::del($key1);
		bt_memcache::del($key2);
	}

	public static function get_topic($id) {
		$topicid = 0 + $id;
		bt_memcache::connect();
		$key = 'topic::id:::'.$topicid;

		$topic = bt_memcache::get($key);
		if ($topic === false) {
			$topicq = bt_sql::query('SELECT subject, locked, forumid, sticky, posts, lastpost FROM topics WHERE id = '.$topicid) or bt_sql::err(__FILE__, __LINE__);

			if (!$topicq->num_rows) {
				bt_memcache::add($key, 0, 86400);
				return false;
			}

			$topic = $topicq->fetch_assoc();
			$topicq->free();
			$topic['en_subject'] = bt_security::html_safe(trim($topic['subject']));
			$topic['locked'] = (bool)($topic['locked'] == 'yes');
			$topic['forumid'] = 0 + $topic['forumid'];
			$topic['sticky'] = (bool)($topic['sticky'] == 'yes');
			$topic['posts'] = 0 + $topic['posts'];
			$topic['lastpost'] = 0 + $topic['lastpost'];

			bt_memcache::add($key, $topic, 10800);
		}

		return $topic;
	}

	public static function get_formated_post($id, $text) {
		$postid = 0 + $id;
		$type = bt_user::$current['settings']['avatars_po'] ? 'showpo' : 'hidepo';
		$key = 'post::'.$type.':::'.$postid;
		bt_memcache::connect();
		$formated = bt_memcache::get($key);
		if (!$formated) {
			$formated = format_comment($text);
			$compress = strlen($text) > 1024;
			bt_memcache::add($key, $formated, 604800, $compress);
		}
		return $formated;
	}

	public static function avatar(&$url, &$text, $is_po) {
		$url = trim($url);
		if ($url == '' || !bt_user::$current['settings']['avatars']) {
			$url = bt_theme_engine::$theme_pic_dir.'avatar_default.png';
			$text = '';
		}
		elseif (bt_user::$current['settings']['avatars_po'] || !$is_po)
			$text = '';
		else {
			$url = bt_theme_engine::$theme_pic_dir.'avatar_disabled.png';
			$text = 'Avatar Hidden';
		}

		return true;
	}

	public static function user_link($userid, &$username, $class, $link = true, $extra_link = '') {
		$uclass = bt_theme::$settings['inbox']['uclass'];
		$userid = 0 + $userid;
		$class = 0 + $class;
		$username = trim($username);
		$has_name = $userid ? $username != '' : false;
		$username = $has_name ? bt_security::html_safe($username) : ($userid ? 'unknown['.$userid.']' : '<b>System</b>');
		$class_class = $has_name ? bt_theme::$settings['classes']['colors'][$class] : '';

		if ($link)
			$user_link = $has_name ? '<a href="/userdetails.php?id='.$userid.$extra_link.'" class="'.$class_class.' '.$uclass.'">'.$username.'</a>' : $username;
		else
			$user_link = $has_name ? '<span class="'.$class_class.' '.$uclass.'">'.$username.'</span>' : $username;

		return $user_link;
	}

	public static function user_stars($settings) {
		$stars = '';
		if (isset($settings['donor']) && $settings['donor'])
			$stars .= ' <img src="'.bt_theme_engine::$theme_pic_dir.'donor_small.png" alt="Donor" title="Donor" />';

		if (isset($settings['warned']) && $settings['warned'])
			$stars .= ' <a href="/rules.php#warning"><img src="'.bt_theme_engine::$theme_pic_dir.'warning_small.png" alt="Warned" '.
				'title="Warned" style="border: none" /></a>';

		return $stars;
	}

	public static function settings_to_forum_theme($settings) {
		$theme = 0;
		if ($settings['forum_1'])
			$theme |= BIT_1;
		if ($settings['forum_2'])
			$theme |= BIT_2;
		if ($settings['forum_3'])
			$theme |= BIT_3;
		if ($settings['forum_4'])
			$theme |= BIT_4;

		return (int)$theme;
	}

	public static function forum_theme_to_settings($theme) {
		if ($theme < 0 || $theme > 15)
			$theme = 0;

		$settings = array(
			'forum_1'	=> (bool)($theme & BIT_1),
			'forum_2'	=> (bool)($theme & BIT_2),
			'forum_3'	=> (bool)($theme & BIT_3),
			'forum_4'	=> (bool)($theme & BIT_4),
		);
		return $settings;
	}

	public static function format_block_tag($text, $name, $prefix, $suffix, $strip_br = false, $strip_tags = false) {
		$namelen = strlen($name);
		$pos = 0;
		$i = 0;
		while (($pos = @stripos($text, '['.$name.']', $pos)) !== false && $i < self::MAX_BLOCK_TAGS) {
			$i++;
			$startstr = substr($text, 0, $pos);
			$pos += $namelen + 2;
			$endpos = stripos($text, '[/'.$name.']', $pos);
			if ($endpos === false)
				break;

			$strlen = $endpos - $pos;
			$str = substr($text, $pos, $strlen);
			$pos = $endpos + $namelen + 3;
			$endstr = @substr($text, $pos);
			if ($strip_tags)
				$str = strip_tags($str, '<br>');
			if ($strip_br)
				$str = str_replace('<br />', '', $str);

			$text = $startstr.$prefix.$str.$suffix.$endstr;
			unset($startstr, $str, $endstr);
		}

		return $text;
	}
}
?>
