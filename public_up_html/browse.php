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
require_once(TROOT_PATH.'announce_settings.php');
require_once(CLASS_PATH.'bt_mem_caching.php');

bt_config::$conf['tracker_settings'] = $SETTINGS;
bt_loginout::db_connect(true);

$cats = bt_mem_caching::get_cat_list();
$last_torrents = bt_mem_caching::get_last_torrents();

$searchstr = $_GET['search'];
$sortby = 0 + $_GET['sort'];
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : false;

if (isset($_GET['type']))
	$type = (int)$_GET['type'];

if (bt_utf8::strlen($searchstr) > 255)
	bt_theme::error('Error','Search string longer than 255 chars, please refine your search');

$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr))
	unset($cleansearchstr);

$orderby = 'ORDER BY id DESC';

$addparam = '';
$wherea = array();
$wherecatina = array();

$incldead = (int)$_GET['incldead'];

if ($incldead == 1)
	$addparam .= 'incldead=1&amp;';
elseif ($incldead == 2) {
	$addparam .= 'incldead=2&amp;';
	$wherea[] = 'visible = "no"';
}
else
	$wherea[] = 'visible = "yes"';

$category = 0 + $_GET['cat'];
$categories = $_GET['c'];
if (isset($_GET['titleonly']) || !isset($_GET['search']))
	$titleonly = true;


$all = 0 + $_GET['all'];

if (!$all)
  {
	if ($genre) {
		$wherea[] = 'genre = '.sqlesc($genre);
		$addparam .= 'genre='.bt_security::html_safe($genre).'&amp;';
	}
	elseif ($category)
     {
      if (!is_valid_id($category))
        bt_theme::error('Error', 'Invalid category ID.');
      $wherecatina[] = $category;
      $addparam .= "cat=$category&amp;";
     }
   else
     {
      if (is_array($categories))
        {
         foreach($categories as $catg)
           {
            $catg = (int)$catg;
            if (!is_valid_id($catg))
              bt_theme::error('Error', 'Invalid category ID.');
            if ($catg)
              {
               $wherecatina[] = $catg;
               $addparam .= 'c[]='.$catg.'&amp;';
              }
           }
        }

      if (!count($wherecatina))
        foreach ($cats as $catid => $cat)
          if (strpos(bt_user::$current['notifs'], '[cat' . $catid.']') !== false)
            $wherecatina[] = $catid;
     }
  }

elseif ($all)
{
  $wherecatina = array();
  $addparam = "all=1&amp;".($_GET["incldead"] ? "incldead=".(0+$_GET['incldead'])."&amp;" : "");
}

if (count($wherecatina) > 1)
	$wherecatin = join(',', $wherecatina);
elseif (count($wherecatina) == 1)
	$wherea[] = 'category = '.$wherecatina[0];

$wherebase = $wherea;
$columns = 'id, category, filename, leechers, seeders, name, times_completed, size, added, comments, type, '.
		'numfiles, pretime, genre, banned ';
$from = 'torrents';

//----------------  new search designed by djGrrr  -------------------//
//--------------------------------------------------------------------//
if ($searchstr != '')
  {
   $addparam .= 'search='.rawurlencode($searchstr).'&amp;';
   if ($titleonly)
     {
      $addparam .= 'titleonly=1&amp;';
      $searchstring = str_replace(array('_','.','-'),' ',$searchstr);

      $s = array('*','?','.','-',' ');
      $r = array('%','_','_','_','_');

      if (preg_match('/^\"(.+)\"$/i', $searchstring, $matches))
        $wherea[] = 'name LIKE '.sqlesc('%'.str_replace($s, $r, $matches[1]).'%');
      elseif (strpos($searchstr, '*') !== false || strpos($searchstr, '?') !== false)
		$wherea[] = 'name LIKE '.sqlesc(str_replace($s, $r, $searchstr));
	  elseif (preg_match('/^[A-Za-z0-9][a-zA-Z0-9()._-]+-[A-Za-z0-9_]*[A-Za-z0-9]$/iD', $searchstr))
		$wherea[] = 'name = '.sqlesc($searchstr);
	  else
		$wherea[] = 'MATCH (search_text) AGAINST ('.sqlesc($searchstr).' IN BOOLEAN MODE)';
     }
   else
     $wherea[] = 'MATCH (search_text, descr) AGAINST ('.sqlesc($searchstr).' IN BOOLEAN MODE)';
   $orderby = 'ORDER BY id DESC';
  }
function revtype($type)
  {
   if (strtoupper($type) == "ASC")
     return "DESC";
   elseif (strtoupper($type) == "DESC")
     return "ASC";
  }

$sql_type = $type ? 'ASC' : 'DESC';
switch($sortby)
{
        case 1:
                $orderby = "ORDER BY name {$sql_type}"; // Torrent Name
        break;
        case 2:
                $orderby = "ORDER BY numfiles {$sql_type}"; // Files
        break;
        case 3:
                $orderby = "ORDER BY comments {$sql_type}"; // Comments
        break;
        case 4:
                $orderby = "ORDER BY id {$sql_type}"; // Added
        break;
        case 5:
                $orderby = "ORDER BY size {$sql_type}"; // Size
        break;
        case 6:
                $orderby = "ORDER BY times_completed {$sql_type}"; // Snatched
        break;
        case 7:
                $orderby = "ORDER BY seeders {$sql_type}, leechers ".revtype($sql_type); // Seeders
        break;
        case 8:
                $orderby = "ORDER BY leechers {$sql_type}"; //Leechers
        break;
}
if ($_GET['sort'])
  $newparam = "sort={$sortby}&amp;type={$type}&amp;";
if ($_GET['page'])
  $newparam2 = "page=".(0+$_GET['page'])."&amp;";
if ($wherecatin)
	$wherea[] = 'category IN('.$wherecatin.')';

$where = count($wherea) ? 'WHERE '.implode(' AND ', $wherea) : '';

$where_key = 'browse_where:'.sha1($where);
$count = bt_memcache::get($where_key);
if ($count === bt_memcache::NO_RESULT) {
	$res = bt_sql::query('SELECT COUNT(*) FROM torrents '.$where) or bt_sql::err(__FILE__,__LINE__);
	$row = $res->fetch_row();
	$res->free();
	$count = 0 + $row[0];

	bt_memcache::set($where_key, $count, 60);
}

$torrentsperpage = bt_user::$current['torrentsperpage'] ? bt_user::$current['torrentsperpage'] : 25;

if ($count) {
	list($pager, $limit) = bt_theme::pager($torrentsperpage, $count, '/browse.php?'.$addparam.$newparam);
	$query = 'SELECT '.$columns.' FROM torrents '.$where.' '.$orderby.' '.$limit;
	$res = bt_sql::query($query) or bt_sql::err(__FILE__,__LINE__);
}
else
	unset($res);
if (isset($cleansearchstr))
	bt_theme::head('Search results for "'.$searchstr.'"');
else
	bt_theme::head('Browse');

///////////////////////////////////////////////////////////////////////////////
$catlist = $catrow = '';
$catset = bt_theme::$settings['browse']['catlist'];
$catsperrow = $catset['per_row'];
$catrows = array();
$ncats = count($cats);
$i = 0;
foreach ($cats as $catid => $cat) {
	$catrows[] = $catset['cat_start'].'<input name="c[]" type="checkbox" value="'.$catid.'"'.(in_array($catid, $wherecatina) ?
		' checked="checked"' : '').' /><a href="/browse.php?cat='.$catid.'"'.$catset['link'].'>'.$cat['ename'].'</a>'.$catset['cat_end'];
	$i++;
	$catsleft = $i % $catsperrow;
	if ($catsleft == 0 || $i == $ncats) {
		$catrow = implode("\n", $catrows);
		$catrows = array();
		$catlist .= $catset['row_start'].$catrow.$catset['row_end'];
	}
}

$active = $incldead === 0 ? ' selected="selected"' : '';
$incl_dead = $incldead === 1 ? ' selected="selected"' : '';
$only_dead = $incldead === 2 ? ' selected="selected"' : '';
$all_incl_dead = ($incldead === 1 || $incldead === 2) ? '&amp;incldead='.$incldead : '';

$search_str = $searchstr ? bt_security::html_safe($searchstr) : '';
$pages = $count ? $pager : '';
if ($count)
	$torrent_list = bt_theme::torrent_table($res, $addparam.$newparam2, $end_new);
else {
	if (isset($cleansearchstr))
		$torrent_list = bt_theme::message('Nothing found!', 'Try again with a refined search string.', false, true);
	else
		$torrent_list = bt_theme::message('Nothing here!', 'Sorry pal :(', false, true);
}

$browsevars = array(
	'SEARCH_STR'	=> $search_str,
	'ACTIVE'		=> $active,
	'INCL_DEAD'		=> $incl_dead,
	'ONLY_DEAD'		=> $only_dead,
	'ALL_INCL_DEAD'	=> $all_incl_dead,
	'CAT_LIST'		=> $catlist,
	'PAGES'			=> $pages,
	'TORRENT_LIST'	=> $torrent_list,
);

$browse = bt_theme_engine::load_tpl('browse', $browsevars);
echo $browse;

//////////////////////////////////////////////////////////////////////////////////////
/*
if (isset($cleansearchstr))
	print("<h2>Search results for \"" . htmlentities($searchstr) . "\"</h2>\n");
*/
bt_theme::foot();
if (!isset($_GET['search']) && !isset($_GET['sort']) && !isset($_GET['type']) && !isset($_GET['cat']) && !isset($_GET['c']) &&
	($end_new || bt_user::$current['last_browse'] == 0)) {
	if (bt_user::$current['last_browse'] < (time() - 300))
		bt_sql::query('UPDATE `users` SET `last_browse` = '.time().' WHERE id = '.bt_user::$current['id']);
}
?>
