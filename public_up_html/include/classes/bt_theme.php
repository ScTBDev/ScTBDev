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
require_once(INCL_PATH.'define_bits.php');
require_once(CLASS_PATH.'bt_theme_engine.php');
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_security.php');
require_once(CLASS_PATH.'bt_donations.php');

class bt_theme {
	const BROWSER_FIREFOX = 1;
	const BROWSER_OPERA = 2;
	const BROWSER_IE = 3;

	const PAGER_SHOW_PAGES			= BIT_1;
	const PAGER_NO_SEPARATOR		= BIT_2;
	const PAGER_LAST_PAGE_DEFAULT	= BIT_3;
	const PAGER_NO_NAV				= BIT_4;
	const PAGER_ONLY_PAGES			= BIT_5;

	public static $settings = array();

	public static $browser = 0;
	public static $browser_version = 0.0;

	private static $header_done = false;
	private static $footer_done = false;

	public static function identify_browser() {
		if (preg_match('/Opera(?:\\/([0-9]+)\\.([0-9]+))?/', $_SERVER['HTTP_USER_AGENT'], $opv)) {
			self::$browser = self::BROWSER_OPERA;
			if ($opv[1])
				self::$browser_version = (float)$opv[1].'.'.$opv[2];
		}
		elseif(preg_match('/MSIE(?: ([0-9]+)\\.([0-9]+))?/', $_SERVER['HTTP_USER_AGENT'], $iev)) {
			self::$browser = self::BROWSER_IE;
			if ($iev[1])
				self::$browser_version = (float)$iev[1].'.'.$iev[2];
		}
		elseif (preg_match('/Firefox(?:\\/([0-9]+)\\.([0-9]+)\\.([0-9]+)(?:\\.([0-9]+)?))?/', $_SERVER['HTTP_USER_AGENT'], $ffv)) {
			self::$browser = self::BROWSER_FIREFOX;
			if (isset($ffv[1]))
				self::$browser_version = (float)$ffv[1].'.'.$ffv[2].$ffv[3].$ffv[4];
		}
		else {
			self::$browser = self::BROWSER_FIREFOX;
		}
	}

	public static function head($page_title = '', $show_alerts = true, $reload = false, $return = false) {
		if (self::$header_done)
			return;

		self::$header_done = true;

		bt_theme_engine::load();
		self::identify_browser();

		if ($reload)
			header('Refresh: '.$reload.';'.$_SERVER['REQUEST_URI']);

		if (!bt_config::$conf['SITE_ONLINE'])
			die('Site is down for maintenance, please check back again later... thanks');

		header('Content-Type: text/html; charset=UTF-8');
		$head_extra = '';
		$title = bt_config::$conf['site_name'].($page_title ? ' :: '.bt_security::html_safe($page_title) : '');

		switch (self::$browser) {
			case self::BROWSER_FIREFOX:	break;
			case self::BROWSER_OPERA: break;
			case self::BROWSER_IE:
				if (self::$browser_version >= 5.5 && self::$browser_version < 7.0)
					$head_extra .= "\n".'	<script language="JavaScript" src="themes/fixpngs.js"></script>';
			break;
		}

		$statbar = '';
		$rss_feed = bt_user::$current ? "\n".'	<link rel="alternate" title="SceneTorrents RSS Feed" href="/rss.php?passkey='.urlencode(bt_user::$current['passkey']).'" type="application/rss+xml" />' : '';


		if (bt_user::$current && (bt_user::$current['flags'] & bt_options::USER_STATBAR)) {
			$uped = self::mksize(bt_user::$current['uploaded']);
			$downed = self::mksize(bt_user::$current['downloaded']);

			if (bt_user::$current['downloaded'] > 0) {
				$ratio = bt_user::$current['uploaded'] / bt_user::$current['downloaded'];
				$ratio = '<span style="color: '.self::ratio_color($ratio).'">'.number_format($ratio, 3).'</span>';
			}
			elseif (bt_user::$current['uploaded'] > 0)
				$ratio = '&infin;';
			else
				$ratio = '---';

			$unconnl = self::$settings['statbar']['unconnl'];
			if (bt_user::$current['connectable'] == 'no')
				$conn = '<a href="http://bt.degreez.net/firewalled.html"'.$unconnl.'>NO</a>';
			elseif (bt_user::$current['connectable'] == 'unknown')
				$conn = '<span style="color: blue"><b>UNKNOWN</b></span>';
			else
				$conn = '<span style="color: black">Yes</span>';

			$stars = bt_forums::user_stars(bt_user::$current['flags']);;

			$messages = bt_user::$current['inbox'];
			$unread = bt_user::$current['inbox_new'];
			$outmessages = bt_user::$current['sentbox'];

			$inbox_title = $inbox_pic = '';
			if ($unread) {
				$inbox_title = ' (new messages)';
				$inbox_pic = '_new';
			}

			$statvars = array(
				'USER_ID'		=> bt_user::$current['id'],
				'USER_NAME'		=> bt_security::html_safe(bt_user::$current['username']),
				'CLASSC'		=> self::$settings['classes']['colors'][bt_user::$current['class']],
				'CUR_TIME'		=> format_time(),
				'INBOX'			=> $messages,
				'INBOX_NEW'		=> $unread,
				'SENTBOX'		=> $outmessages,
				'STARS'			=> $stars,
				'CONNECTABLE'	=> $conn,
				'RATIO'			=> $ratio,
				'UPED'			=> $uped,
				'DOWNED'		=> $downed,
				'INBOX_TITLE'	=> $inbox_title,
				'INBOX_PIC'		=> $inbox_pic,
			);

			$statbar = bt_theme_engine::load_tpl('statbar', $statvars);
		}


		$donbar = '';
		if (bt_user::$current) {
			$donations = bt_donations::get_donations(bt_config::$conf['donate_day']);
			$donation_current = 0 + $donations['ammount'];
			$donation_target = bt_config::$conf['require_donations'];
			$donation_date = gmdate('Y-m-d', $donations['to']);

			$donation_percent = (($donation_current / $donation_target) >= 1 ? 100 : floor(100 * ($donation_current / $donation_target)));
			$donation_bar_length = floor(($donation_percent / 100) * self::$settings['donbar']['max_length']);
			$donation_img = self::$settings['donbar']['pic_names'][($donation_percent <= 60 ? 0 : ($donation_percent <= 90 ? 1 : 2))];
			$donation_bar_length = ($donation_bar_length < 1 ? 1 : (($donation_bar_length > self::$settings['donbar']['max_length']) ?
				self::$settings['donbar']['max_length'] : $donation_bar_length));

			$donation_empty = '';
			if (self::$settings['donbar']['empty']) {
				$empty_len = self::$settings['donbar']['max_length'] - $donation_bar_length;
				if ($empty_len) {
					$emptyvars = array(
						'LENGTH'	=> $empty_len,
						'PERCENT'	=> $donation_percent,
						'CURRENT'	=> ($donation_current > $donation_target ? $donation_target : $donation_current),
						'TARGET'	=> $donation_target,
						'DATE'		=> $donation_date,
					);
					$donation_empty = bt_theme_engine::load_tpl('donbar_empty', $emptyvars);
				}
			}

			$donvars = array(
				'EMPTY'		=> $donation_empty,
				'PERCENT'	=> $donation_percent,
				'LENGTH'	=> $donation_bar_length,
				'CURRENT'	=> $donation_current,
				'TARGET'	=> $donation_target,
				'DATE'		=> $donation_date,
				'PIC'		=> $donation_img,
			);

			$donbar = bt_theme_engine::load_tpl('donbar', $donvars);
		}


		$navbar = '';
		if (bt_user::$current) {
			$upload_btn = bt_user::required_class(UC_UPLOADER) ? bt_theme_engine::load_tpl('upload_btn') : '';
			$invite_btn = (bt_user::required_class(UC_STAFF) || bt_user::$current['invites'] > 0) ? bt_theme_engine::load_tpl('invite_btn') : '';

			$navvars = array(
				'UPLOAD_BTN'	=> $upload_btn,
				'INVITE_BTN'	=> $invite_btn,
			);
			$navbar = bt_theme_engine::load_tpl('navbar', $navvars);
		}

		$alerts = '';
		if ($show_alerts && bt_user::$current) {
			$connalert = '';
			if (bt_user::$current['connectable'] == 'no' && !bt_user::required_class(UC_XTREME_USER))
				$connalert = bt_theme_engine::load_tpl('alert_conn');

			$unread = bt_user::$current['inbox_new'];
			$msgalert = '';
			if ($unread) {
				$msgvars = array(
					'NUM'	=> $unread,
					'S'		=> $unread == 1 ? '' : 's',
				);

				$msgalert = bt_theme_engine::load_tpl('alert_msg', $msgvars);
			}

			$newsalert = '';
			if (false)
				$newsalert = bt_theme_engine::load_tpl('alert_news');

			if ($connalert || $msgalert || $newsalert) {
				$alertvars = array(
					'ALERT_CONN'	=> $connalert,
					'ALERT_NEWS'	=> $newsalert,
					'ALERT_MSG'		=> $msgalert,
				);

				$alerts = bt_theme_engine::load_tpl('alerts', $alertvars);
			}
		}

		$theme = bt_user::$current ? bt_user::$current['theme'] : 0;

		$headvars = array(
			'PAGE_TITLE'	=> $title,
			'STYLE'			=> $theme,
			'RSS_FEED'		=> $rss_feed,
			'HEAD_EXTRA'	=> $head_extra,
			'STATBAR'		=> $statbar,
			'DONBAR'		=> $donbar,
			'NAVBAR'		=> $navbar,
			'ALERTS'		=> $alerts,
		);

		$header = bt_theme_engine::load_tpl('header', $headvars);
		if (!$return)
			echo $header;
		else
			return $header;
	}

	public static function foot($return = false) {
		global $MySQL_NUM_QUERIES, $MySQL_LEN_QUERIES;
		if (self::$footer_done)
			return;

		self::$footer_done = true;
		$tsettings = bt_theme::$settings['foot'];

		$now = microtime(true);
		$extime = $now - _START_MICROTIME_;
		$qnum = $MySQL_NUM_QUERIES + bt_sql::$query_count;
		$qtime = $MySQL_LEN_QUERIES + bt_sql::$query_time;
		$phpt = $extime - $qtime - bt_memcache::$time;
		$max_mem = memory_get_peak_usage();

		$set = 'SET: '.round($extime, 4).'s | DB Queries: '.$qnum.' ('.round($qtime, 4).'s) | '.
			'MC Queries: '.bt_memcache::$count.' ('.round(bt_memcache::$time, 4).'s) | '.
			'PHP: '.round($phpt, 4).'s | Mem Usage: '.self::mksize($max_mem);

		$footvars = array(
			'SET'			=> $set,
			'POWERED_BY'	=> 'Powered by <a href="http://www.sctbdev.ca/" '.$tsettings['powered_by_link'].'>ScTBDev</a>',
		);

		$footer = bt_theme_engine::load_tpl('footer', $footvars);
		if (!$return)
			echo $footer;
		else
			return $footer;
	}

	public static function message($heading, $text, $encode = false, $return = false, $small = false) {
		bt_theme_engine::load();
		$title = $encode ? bt_security::html_safe($heading) : $heading;
		$body = $encode ? bt_security::html_safe($text) : $text;
		$sm = $small ? self::$settings['stdmsg']['small'] : '';
		$msgvars = array(
			'TITLE'	=> $title,
			'BODY'	=> $body,
			'SMALL'	=> $sm,
		);

		$msg = bt_theme_engine::load_tpl('stdmsg', $msgvars);
		if (!$return)
			echo $msg;
		else
			return $msg;
	}

	public static function error($heading, $text, $encode = false, $page_title = '', $return = false) {
		$error = self::head($page_title, false, false, true);
		$error .= self::message($heading, $text, $encode, true);
		$error .= self::foot(true);
		if ($return)
			return $error;

		echo $error;
		die;
	}

	public static function pager($rpp, $count, $href, $options = 0, $pagename = 'page') {
		bt_theme_engine::load();

		$show_pages			= (bool)($options & self::PAGER_SHOW_PAGES);
		$no_sep				= (bool)($options & self::PAGER_NO_SEPARATOR);
		$lastpagedefault	= (bool)($options & self::PAGER_LAST_PAGE_DEFAULT);
		$no_nav				= (bool)($options & self::PAGER_NO_NAV);
		$only_pages			= (bool)($options & self::PAGER_ONLY_PAGES);

		$pages = ceil($count / $rpp);

		if ($only_pages)
			$dpage = ceil($pages / 2);
		else {
			$pagedefault = $lastpagedefault ? $pages : 1;

			if (isset($_GET[$pagename])) {
				$dpage = 0 + $_GET[$pagename];
				if ($dpage < 1)
					$dpage = $pagedefault;
				elseif ($dpage > $pages)
					$dpage = $pages;
			}
			else
				$dpage = $pagedefault;
		}

		$page = $dpage - 1;

		$pager = $frontpager = $backpager = $pagerstr = '';

		$startp = self::$settings['pager']['startp'];
		$endp = self::$settings['pager']['endp'];
		$spacep = $no_sep ? self::$settings['pager']['nospacep'] : self::$settings['pager']['spacep'];
		$dotp = self::$settings['pager']['dotp'];
		$sepp = self::$settings['pager']['sepp'];
		$midl = $only_pages ? self::$settings['pager']['midl_only'] : self::$settings['pager']['midl'];
		$dotspace = $show_pages ? self::$settings['pager']['dotpages'] : self::$settings['pager']['dotspace'];
		$mp = $pages - 1;

		$as = self::$settings['pager']['prev'];

		if ($page >= 1) {
			$frontpager .= '<a href="'.$href.$pagename.'='.($dpage - 1).'"'.$midl.'>';
			$frontpager .= $as;
			$frontpager .= '</a>';
		}
		else
			$frontpager .= '<b>'.$as.'</b>';

		$as = self::$settings['pager']['next'];
		if ($page < $mp && $mp >= 0) {
			$backpager .= '<a href="'.$href.$pagename.'='.($dpage + 1).'"'.$midl.'>';
			$backpager .= $as;
			$backpager .= '</a>';
		}
		else
			$backpager .= '<b>'.$as.'</b>';

		if ($count) {
			$pagerarr = array();
			$dotted = 0;
			$dotend = $pages - $dotspace;
			$curdotend = $page - $dotspace;
			$curdotstart = $page + $dotspace;
			for ($i = 0; $i < $pages; $i++) {
				if (($i >= $dotspace && $i <= $curdotend) || ($i >= $curdotstart && $i < $dotend)) {
					if (!$dotted) {
						$pagerarr[] = $dotp;
						$dotted = 1;
					}
					continue;
				}
				$dotted = 0;
				$start = $i * $rpp + 1;
				$end = $start + $rpp - 1;
				if ($end > $count)
					$end = $count;

				$text = $show_pages ? ($i + 1) : $start.$sepp.$end;

				if ($only_pages || $i != $page)
					$pagerarr[] = '<a href="'.$href.$pagename.'='.($i + 1).'"'.$midl.'>'.$text.'</a>';
				else
					$pagerarr[] = '<b>'.$text.'</b>';
			}

			$pagerstr = implode($spacep, $pagerarr);
		}
		
		$start = $page * $rpp;

		$pager = (!$only_pages ? $startp : '').(!$no_nav ? $frontpager.$spacep : '').$pagerstr.(!$no_nav ? $spacep.$backpager : '').(!$only_pages ? $endp : '');

		if ($only_pages)
			return $pager;
		else
			return array($pager, 'LIMIT '.$start.','.$rpp);
	}

	public static function torrent_table($res, $addparam = '', &$end_new = false) {
//		bt_theme_engine::load();
		$torrenttablevars = array();
		$torrenttablevars['PARAM'] = $addparam;
		$defaultsort = array(1, 0, 0, 0, 1, 0, 0, 0);

		$sort = (int)(isset($_GET['sort']) ? (0 + $_GET['sort']) : 0);
		$sortby = $sort - 1;
		$type = (int)0 + $_GET['type'];
		foreach ($defaultsort as $sortnum => $sorting) {
			if ($sortby === $sortnum)
				$sortorder = (int)!$type;
			else
				$sortorder = $defaultsort[$sortnum];

			$torrenttablevars['SORT_'.($sortnum + 1)] = $sortorder;
		}

		$midl = self::$settings['browse']['midl'];
		$newt = self::$settings['browse']['newt'];

		$torrent_rows = '';
		$rowi = 0;
		$cats = bt_mem_caching::get_cat_list();
		while ($row = $res->fetch_assoc()) {
			$rowi++;
			$row_style = $rowi % 2 == 0 ? 2 : 1;

			$id = (int)$row['id'];
			$catid = (int)$row['category'];
			$catimg = $cats[$catid]['image'];
			$catname = $cats[$catid]['ename'];
			$num_files = (int)$row['numfiles'];
			$num_comments = (int)$row['comments'];
			$tsize = 0 + $row['size'];
			$num_seeders = (int)$row['seeders'];
			$num_leechers = (int)$row['leechers'];
			
			$name = bt_security::html_safe(str_replace(array('_','.'),' ', $row['name']));
			
			if ($row['pretime'] > 0) {
				$mp3cat = 8;
				$timeofpre = $row['added'] - $row['pretime'];
				$pre = bt_time::format_elapsed_time($timeofpre, $row['added']);
				$pretime = 'Uploaded '.$pre.' after pre.'.(($catid == $mp3cat && $row['genre'] != '') ?
					' [ <a href="/browse.php?genre='.rawurlencode($row['genre']).'"'.$midl.'<b>'.bt_security::html_safe($row['genre']).'</b></a> ]' : '');
			}
			else
				$pretime = 'No pretime found.';

			if ($row['added'] > bt_user::$current['last_browse'])
				$new = $newt;
			else {
				$new = '';
				$end_new = true;
			}

			$torrent_fname = rawurlencode($row['filename']);
			$passkey = bt_user::$current['passkey'];
			$files = $row['type'] == 'multi' ? '<a href="/details.php?id='.$id.'&amp;hit=1&amp;filelist=1"'.$midl.'>'.$num_files.'</a>' : 1;
			$comments = $num_comments > 0 ? '<a href="/details.php?id='.$id.'&amp;hit=1&amp;tocomm=1"'.$midl.'>'.$num_comments.'</a>' : 0;
			list($date, $time) = explode(' ', format_time($row['added']), 2);
			list($size, $size_scale) = explode(' ', self::mksize($tsize), 2);
			$dl_size = $row['pretime'] ? floor($tsize * bt_config::$conf['tracker_settings']['dnld_multiplier']) : 0;
			list($dlsize, $dlsize_scale) = explode(' ', self::mksize($dl_size), 2);
			$snatched = (int)$row['times_completed'];
			$snatched_s = $snatched !== 1 ? 's' : '';
			$seeders = $num_seeders > 0 ? '<a href="/details.php?id='.$id.'&amp;hit=1&amp;toseeders=1"'.$midl.'>'.$num_seeders.'</a>' : 0;
			$leechers = $num_leechers > 0 ? '<a href="/details.php?id='.$id.'&amp;hit=1&amp;todlers=1"'.$midl.'>'.$num_leechers.'</a>' : 0;

			$torrenttable_rowvars = array(
				'ROW'			=> $row_style,
				'CATID'			=> $catid,
				'CATIMG'		=> $catimg,
				'CATNAME'		=> $catname,
				'ID'			=> $id,
				'NAME'			=> $name,
				'PRETIME'		=> $pretime,
				'NEW'			=> $new,
				'TORRENT_FNAME'	=> $torrent_fname,
				'PASSKEY'		=> $passkey,
				'FILES'			=> $files,
				'COMMENTS'		=> $comments,
				'DATE'			=> $date,
				'TIME'			=> $time,
				'SIZE'			=> $size,
				'SIZE_SCALE'	=> $size_scale,
				'SNATCHED'		=> $snatched,
				'SNATCHED_S'	=> $snatched_s,
				'SEEDERS'		=> $seeders,
				'LEECHERS'		=> $leechers,
				'DLSIZE'		=> $dlsize,
				'DLSIZE_SCALE'	=> $dlsize_scale,
			);

			$torrent_rows .= bt_theme_engine::load_tpl('browse_torrenttable_row', $torrenttable_rowvars);
		}

		$torrenttablevars['TORRENT_ROWS'] = $torrent_rows;
		$torrenttable = bt_theme_engine::load_tpl('browse_torrenttable', $torrenttablevars);
		return $torrenttable;
	}

	public static function ratio($uploaded, $downloaded) {
		if ($downloaded > 0) {
			$ratio = number_format($uploaded / $downloaded, 3);
			$ratio = sprintf(self::$settings['theme']['ratio']['ratio'], self::ratio_color($ratio), $ratio);
		}
		elseif ($uploaded > 0)
			$ratio = self::$settings['theme']['ratio']['infin'];
		else
			$ratio = self::$settings['theme']['ratio']['blank'];

		return $ratio;
	}

	public static function ratio_color($ratio) {
		foreach (self::$settings['theme']['ratio']['ratios'] as $id => $limit) {
			if ($ratio < $limit)
				return self::$settings['theme']['ratio']['colors'][$id];
		}
		return self::$settings['theme']['ratio']['def_color'];
	}

	public static function mksize($bytes) {
		$bytes = max(0, $bytes);
		// Kilobytes 1024^1
		if ($bytes < 1024000)
			return number_format($bytes / 1024, 2).' KiB';
		// Megabytes 1024^2
		elseif ($bytes < 1048576000)
			return number_format($bytes / 1048576, 2).' MiB';
		// Gigebytes 1024^3
		elseif ($bytes < 1073741824000)
			return number_format($bytes / 1073741824, 2).' GiB';
		// Terabytes 1024^4
		elseif ($bytes < 1099511627776000)
			return number_format($bytes / 1099511627776, 3).' TiB';
		// Petabytes 1024^5
		elseif ($bytes < 1125899906842624000)
			return number_format($bytes / 1125899906842624, 3).' PiB';
		// Exabytes 1024^6
		elseif ($bytes < 1152921504606846976000)
			return number_format($bytes / 1152921504606846976, 3).' EiB';
		// Zettabyres 1024^7
		elseif ($bytes < 1180591620717411303424000)
			return number_format($bytes / 1180591620717411303424, 3).' ZiB';
		// Yottabytes 1024^8
		else
			return number_format($bytes / 1208925819614629174706176, 3).' YiB';
	}
}
?>
