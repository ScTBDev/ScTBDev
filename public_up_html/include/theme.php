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

require_once(__DIR__.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_forums.php');

function torrenttable($res, $variant = 'index', $addparam = '', &$end_new = false) {
	echo '<table border="1" cellspacing="0" cellpadding="5">
	<tr>'."\n";


	$array = array(
		'Name'		=> 'left',
		'Files'		=> 'right',
		'Comm'		=> 'right',
		'Added'		=> 'center',
		'Size'		=> 'center',
		'Snatched'	=> 'center',
		'Seeders'	=> 'right',
		'Leechers'	=> 'right',
	);

	$i = 1;
	foreach($array as $value => $alignment) {
		if($i == 1)
			echo '		<td class="colhead" align="center">Type</td>';

		elseif($i == 2) {
			if ($wait)
				echo '		<td class="colhead" align="center">Wait</td>'."\n";

			if ($variant == 'mytorrents') {
				echo '		<td class="colhead" align="center">Edit</td>'."\n";
				echo '		<td class="colhead" align="center">Visible</td>'."\n";
			}
			else
				echo '		<td class="colhead" align="center">DL</td>'."\n";
		}


		$sort = isset($_GET['sort']) ? (0 + $_GET['sort']) : 0;
		if($sort == $i) {
			switch($_GET['type']) {
				case 'desc':
					$sortment = 'asc';
					break;
				case 'asc':
					$sortment = 'desc';
					break;
				default:
					$sortment = 'desc';
					break;
			}

			echo '		<td class="colhead" align="'.$alignment.'">
			<a class="colhead" href="/browse.php?'.$addparam.'sort='.$i.'&amp;type='.$sortment.'">'.$value.'</a>
		</td>'."\n";
		}
		else {
			if ($i == 10 || $i == 1 || $i == 5)
				$sortment = 'asc';
			else
				$sortment = 'desc';

			echo '		<td class="colhead" align="'.$alignment.'">
			<a class="colhead" href="/browse.php?'.$addparam.'sort='.$i.'&amp;type='.$sortment.'">'.$value.'</a>
		</td>'."\n";
		}

		if ($i == 8 && $variant == 'index')
			echo '		<td class="colhead" align="center">DL Size</td>';

		$i++;
	}

	echo '	</tr>'."\n";

	while ($row = $res->fetch_assoc()) {
		if ($row['banned'] && bt_user::$current['class'] < UC_STAFF)
			continue;

		$id = 0 + $row['id'];
		echo '	<tr>
		<td align="center" style="padding: 0px">
			<a href="/browse.php?cat='.$row['category'].'">'.($row['cat_pic'] != '' ? 
			'<img src="'.bt_config::$conf['pic_base_url'].'cats/'.$row['cat_pic'].'" alt="'.$row['cat_name'].'" />' :
			$row['cat_name']).'</a>
		</td>'."\n";



		$dispname = bt_security::html_safe(str_replace(array('_','.'),' ', $row['name']));
		echo '		<td align="left"><a href="/details.php?'.($variant == 'mytorrents' ?	
			'returnto='.urlencode($_SERVER['REQUEST_URI']).'&amp;' : '').'id='.$id.($variant == 'index' ?
			'&amp;hit=1' : '').'"><b>'.$dispname.'</b></a>'."\n";

		if ($row['pretime'] > 0) {
			$mp3cat = 8;
			$timeofpre = $row['added'] - $row['pretime'];
			$pre = bt_time::format_elapsed_time($timeofpre, $row['added']);
			echo '<br />Uploaded '.$pre.' after pre.'.(($row['category'] == $mp3cat && $row['genre'] != '') ?
				' [ <a href="/browse.php?genre='.rawurlencode($row['genre']).'"><b>'.bt_security::html_safe($row['genre']).'</b></a> ]' : '')."\n";
		}
		else
			echo '<br />No pretime found.'."\n";

		if ($row['added'] > bt_user::$current['last_browse'])
			echo ' &nbsp; (<b><span style="color: red">NEW!</span></b>)';
		else
			$end_new = true;

		echo "\n".'		</td>'."\n";

		echo '		<td align="left">[<b><a href="/download.php/'.$row['id'].'/'.rawurlencode($row['filename']).
			'?passkey='.bt_user::$current['passkey'].'">DL</a></b>]</td>'."\n";


		if ($wait) {
			$elapsed = floor((time() - $row['added']) / 3600);
			if ($elapsed < $wait) {
				$color = dechex(floor(127 * ($wait - $elapsed) / 48 + 128) * 65536);
				echo '		<td align="center" class="nobr">
	<a href="/faq.php#dl8"><span style="color: #'.$color.'">'.number_format($wait - $elapsed).' h</span></a>
</td>'."\n";
	}
	else
		echo '		<td align="center">None</td>'."\n";
}

if ($variant == 'mytorrents')
	echo '		<td align="center">
	<a href="/edit.php?returnto='.urlencode($_SERVER['REQUEST_URI']).'&amp;id='.$row['id'].'">edit</a>
</td>
<td align="right">'.($row['visible'] == 'no' ? '<b>no</b>' : 'yes').'</td>'."\n";


$multi = $row['type'] == 'multi';
echo '		<td align="right">'.($multi ? '
	<b><a href="/details.php?id='.$id.($variant == 'index' ? '&amp;hit=1' : '').'&amp;filelist=1">' : '').$row['numfiles'].
	($multi ? '</a></b>'."\n".'		' : '').'</td>'."\n";


$comments = 0 + $row['comments'];
echo '		<td align="right">'.($comments ? '
	<b><a href="/details.php?id='.$id.'&amp;'.($variant == 'index' ? 'hit=1&amp;tocomm=1' : 'page=0#startcomments').'">' : '').
	$comments.($comments ? '</a></b>'."\n".'		' : '').'</td>'."\n";


$snatches = 0 + $row['times_completed'];
$size = 0 + $row['size'];
echo '		<td align="center" class="nobr">'.str_replace(' ', '<br />', format_time($row['added'])).'</td>
		<td align="center">'.str_replace(' ', '<br />', mksize($size)).'</td>
		<td align="center">'.number_format($snatches).'<br />time'.($snatches != 1 ? 's' : '').'</td>'."\n";


		$seeders = 0 + $row['seeders'];
		$leechers = 0 + $row['leechers'];
		$slr = $leechers ? $seeders / $leechers : 1;

		echo '		<td align="right">'.($seeders ? '
			<b><a '.($variant == 'index' ?  '' : 'class="'.linkcolor($seeders).'" ').
			'href="/details.php?id='.$id.'&amp;'.($variant == 'index' ? 'hit=1&amp;toseeders=1' : 'dllist=1#seeders').'">' : 
			'<span class="'.linkcolor($seeders).'">').(($seeders && $variant == 'index') ? '<span style="color: '.get_slr_color($slr).'">' : '').
			$seeders.($seeders ? ($variant == 'index' ? '</span>' : '').'</a></b>'."\n".'		' : '</span>').'</td>'."\n";


		echo '		<td align="right">'.($leechers ? '
			<b><a '.($variant == 'index' ?  '' : 'class="'.linkcolor($leechers).'" ').
			'href="/details.php?id='.$id.'&amp;'.($variant == 'index' ? 'hit=1&amp;todlers=1' : 'dllist=1#leechers').'">' : '').
			$leechers.($leechers ? '</a></b>'."\n".'		' : '').'</td>'."\n";


		$dl_size = $row['pretime'] ? floor($size * bt_config::$conf['tracker_settings']['dnld_multiplier']) : 0;
		echo '		<td align="center">'.str_replace(' ', '<br />', mksize($dl_size)).'</td>'."\n";

		echo '	</tr>'."\n";
	}

	echo '</table>'."\n";
	return $rows;
} // torrenttable

function commenttable($rows) {
	begin_main_frame();
	begin_frame();
	$count = 0;
	foreach ($rows as $row) {
		$row['flags'] = (int)$row['flags'];
		echo '<p class=sub>#'.$row['id'].' by ';
		if (isset($row['username'])) {
			$title = $row['title'];
			if ($title == '')
				$title = get_user_class_name($row["class"]);
			else
				$title = bt_security::html_safe($title);

			echo '<a name="comm'.$row['id'].'" href="/userdetails.php?id='.$row['user'].'"><b>'.
				bt_security::html_safe($row['username']).'</b></a>'.(($row['flags'] & bt_options::USER_DONOR) ?
				'<img src="'.bt_config::$conf['pic_base_url'].'star.gif" alt="Donor">' : '').
				(($row['flags'] & bt_options::USER_WARNED) ? '<img src="'.bt_config::$conf['pic_base_url'].'warned.gif" alt="Warned">' : '').' ('.$title.')'."\n";
		}
		else
			echo '<a name="comm'.$row['id'].'"><i>(orphaned)</i></a>'."\n";

		echo ' at '.format_time($row['added']).(($row['user'] == bt_user::$current['id'] && (bt_user::$current['flags'] & bt_options::USER_POST_ENABLE))
			|| get_user_class() >= UC_FORUM_MODERATOR ? '- [<a href="/comment.php?action=edit&amp;cid='.$row['id'].'">Edit</a>]' : '').
			(get_user_class() >= UC_FORUM_MODERATOR ? '- [<a href="/comment.php?action=delete&amp;cid='.$row['id'].'">Delete</a>]' : '').
			($row['editedby'] && get_user_class() >= UC_FORUM_MODERATOR ?
			'- [<a href="/comment.php?action=vieworiginal&amp;cid='.$row['id'].'">View original</a>]' : '').'</p>'."\n";

		$avatar = $row['avatar'];
		bt_forums::avatar($avatar, $avtext, ((bool)($row['flags'] & bt_options::USER_AVATAR_PO)));

		$text = format_comment($row['text']);
		if ($row["editedby"])
			$text .= '<p><font size="1" class="small">Last edited by <a href="/userdetails.php?id='.$row['editedby'].'">'.
				'<b>'.bt_security::html_safe($row['username']).'</b></a> at '.format_time($row['editedat']).'</font></p>'."\n";

		begin_table(true);
		echo '<tr valign="top">'."\n".'<td align="center" width="150" style="padding: 0px"><img width="150" '.
			'src="'.bt_security::html_safe($avatar).'">'.($avtext ? '<br />'.$avtext : '').'</td>'."\n".
			'<td class="text">'.$text.'</td>'."\n".'</tr>'."\n";
		end_table();
	}
	end_frame();
	end_main_frame();
} // commenttable


//-------- Begins a main frame
function begin_main_frame()
  {
   print('<table class="main" width="750" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="embedded">'."\n");
  }

//-------- Ends a main frame
function end_main_frame()
  {
   print('    </td>
  </tr>
</table>'."\n");
  }

function begin_frame($caption = '', $center = false, $padding = 10, $class = '') {
	$tdextra = '';
	if (is_null($padding))
		$padding = 10;

	if (is_null($center))
		$center = false;

	if ($caption)
		echo '<h2>'.$caption.'</h2>'."\n";

	if ($center)
		$tdextra .= ' align="center"';

	if ($class != '')
		$tdextra .= ' class="'.$class.'"';

	echo '<table width="100%" border="1" cellspacing="0" cellpadding="'.$padding.'">
	<tr>
		<td'.$tdextra.'>'."\n";
}

function attach_frame($padding = 10)
  {
   print('    </td>
  </tr>
  <tr>
    <td style="border-top: 0px">'."\n");
  }

function end_frame() {
	echo '		</td>
	</tr>
</table>'."\n";
}

function begin_table($fullwidth = false, $padding = 5)
  {
   if ($fullwidth)
     $width = ' width="100%"';
   print('<table class="main"'.$width.' border="1" cellspacing="0" cellpadding="'.$padding.'">'."\n");
  }

function end_table()
  {
   print('    </td>
  </tr>
</table>'."\n");
  }

?>
