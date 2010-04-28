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

require_once(__DIR__.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(CLASS_PATH.'bt_forums.php');

function stdhead($title = '', $msgalert = true, $reload = false) {
	global $SITENAME;
	if (defined('BT_HEADER_DONE'))
		return;

	define('BT_HEADER_DONE', true);

	if (bt_user::$current['id'] == 17455)
		$fakesite = true;

	if ($reload)
		header('Refresh: '.$reload.';'.$_SERVER['REQUEST_URI']);


	if (!bt_config::$conf['SITE_ONLINE'])
		die("Site is down for maintenance, please check back again later... thanks");

	header('Content-Type: text/html; charset=utf-8');
	if ($title == "")
		$title = $SITENAME;
	else
		$title = "$SITENAME :: " . htmlspecialchars($title);


	if (preg_match('/Opera(?:\\/([0-9]+)\\.([0-9]+))?/', $_SERVER['HTTP_USER_AGENT'], $opv)) {
		$b = 'OP';
		if ($opv[1])
			$v = $opv[1].'.'.$opv[2];
	}
	elseif(preg_match('/MSIE(?: ([0-9]+)\\.([0-9]+))?/', $_SERVER['HTTP_USER_AGENT'], $iev)) {
		$b = 'IE';
		if ($iev[1])
			$v = $iev[1].'.'.$iev[2];
	}
	elseif (preg_match('/Firefox(?:\\/([0-9]+)\\.([0-9]+)\\.([0-9]+)\\.([0-9]+))?/', $_SERVER['HTTP_USER_AGENT'], $ffv)) {
		$b = 'FF';
		if ($ffv[1])
			$v = $ffv[1].'.'.$ffv[2].$ffv[3].$ffv[4];
	}
	else {
		$b = 'MZ';
		$v = 0.0;
	}

	$v = (float)$v;


	if ($b == 'IE')
		$ss_uri = 'default_ie.css';
	else
		$ss_uri = 'default.css';

	if ($msgalert && bt_user::$current)
		$unread = bt_user::$current['inbox_new'];

	if (bt_user::$current['connectable'] == 'no')
		$unconnectable = TRUE;

echo '<?xml version="1.0" encoding="iso-8859-1" ?>'."\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?=$title ?></title>
	<link rel="stylesheet" href="/<?=$ss_uri?>" type="text/css" />
<? if (bt_user::$current && !$fakesite) { ?>
	<link rel="alternate" title="SceneTorrents RSS Feed" href="/rss.php?passkey=<?= bt_user::$current['passkey'] ?>" type="application/rss+xml" />
<? } if ($b == 'IE' && ($v >= 5.5 && $v < 7.0)) { ?>
	<script language="JavaScript">
function correctPNG() // correctly handle PNG transparency in Win IE 5.5 & 6.
{
   if (document.body.filters)
   {
      for(var i=0; i<document.images.length; i++)
      {
         var img = document.images[i]
         var imgName = img.src.toUpperCase()
         if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
         {
            var imgID = (img.id) ? "id='" + img.id + "' " : ""
            var imgClass = (img.className) ? "class='" + img.className + "' " : ""
            var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
            var imgStyle = "display:inline-block;" + img.style.cssText 
            if (img.align == "left") imgStyle = "float:left;" + imgStyle
            if (img.align == "right") imgStyle = "float:right;" + imgStyle
            if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
            var strNewHTML = "<span " + imgID + imgClass + imgTitle
            + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
            + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
            + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
            img.outerHTML = strNewHTML
            i = i-1
         }
      }
   }
}
window.attachEvent("onload", correctPNG);
	</script>
<? } ?>
</head>

<body>
<div align="center">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td align="center" colspan="3" style="border: 0">
				<form action="/browse.php" method="get" style="margin: 0px; padding: 0px">
					<div class="headlogo">
<?
if (!$fakesite && bt_user::$current) {
	require_once(CLASS_PATH.'bt_donations.php');
	if (!@mysql_ping())
		dbconn();
	$donations = bt_donations::get_donations(bt_config::$conf['donate_day']);
	$a = 0 + $donations['ammount']; $t = bt_config::$conf['require_donations'];
	$fd = gmdate('Y-m-d', $donations['from']);
	$td = gmdate('Y-m-d', $donations['to']);

	$p = (($a / $t) >= 1 ? 100 : floor(100 * ($a / $t))); $tp = floor(($p / 100) * 42); $img = ($p <= 60 ? 'red' :
		($p > 60 && $p <= 90 ? 'yellow' : 'green'));
	$ph = ($tp == 0 ? 1 : ($tp > 42 ? 42 : $tp));
?>
						<div class="donbar">
							<a href="/donate.php"><img src="/pic/donate.png" alt="Donate" /></a>
							<img src="<?=bt_config::$conf['pic_base_url'].'donations-'.$img.'.png'?>" alt="don bar" title="<?=$p.'% [ '.$a.' of '.$t.'&euro; ] - Target Date: '.$td?>" style="left: 1px; height: <?=$ph?>px; width: 20px" />
						</div>
<? } ?>
					</div>
				</form>
			</td>
		</tr>
		<tr>
			<td style="border: 0"><img src="<?=bt_config::$conf['pic_base_url']?>menubarleft.gif" border="0" alt="" /></td>
			<td style="background: url(<?=bt_config::$conf['pic_base_url']?>menubarbg.gif); border: 0"></td>
			<td style="border: 0;"><img src="<?=bt_config::$conf['pic_base_url']?>menubarright.gif" border="0" alt="" /></td>
		</tr>
		<tr style="height: 450px; border: 0; width: 99%">
			<td valign="top" style="border: 0; background: url(<?=bt_config::$conf['pic_base_url']?>sidebardownleft.gif); background-repeat: no-repeat; background-position: center bottom">
				<img src="<?=bt_config::$conf['pic_base_url']?>sidebarleft.gif" border="0" alt="" />
			</td>
			<td valign="top" style="border: 0;">
				<div align="center" style="margin-top: 4px">
					<a href="/"><img src="<?=bt_config::$conf['pic_base_url']?>btn_news.png" alt="News" class="navbuttons" /></a>
					<a href="/browse.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_browse.png" alt="Browse" class="navbuttons" /></a>
<? if (bt_user::required_class(bt_user::UC_UPLOADER)) { ?>
					<a href="/upload.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_upload.png" alt="Upload" class="navbuttons" /></a>
<? } ?>
					<a href="/my.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_profile.png" alt="Profile" class="navbuttons" /></a>
					<a href="/forums_index.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_forums.png" alt="Forums" class="navbuttons" /></a>
<? if (bt_user::$current['invites'] > 0 || bt_user::required_class(bt_user::UC_STAFF)) { ?>
					<a href="/invite.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_invite.png" alt="Invite" class="navbuttons" /></a>
<? } ?>
					<a href="/log.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_log.png" alt="Log" class="navbuttons" /></a>
					<a href="/rules.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_rules.png" alt="Rules" class="navbuttons" /></a>
					<a href="/faq.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_faq.png" alt="FAQ" class="navbuttons" /></a>
					<a href="/staff.php"><img src="<?=bt_config::$conf['pic_base_url']?>btn_staff.png" alt="Staff" class="navbuttons" /></a>
				</div>
				<br />
<?php
$w = 'width="100%"';
if (bt_user::$current && !$fakesite) {
	if (bt_user::$current['settings']['statbar']) {
		$uped = mksize(bt_user::$current['uploaded']);
		$downed = mksize(bt_user::$current['downloaded']);
		if (bt_user::$current['downloaded'] > 0) {
			$ratio = bt_user::$current['uploaded'] / bt_user::$current['downloaded'];
			$ratio = number_format($ratio, 3);
			$color = get_ratio_color($ratio);
			if ($color)
				$ratio = '<font color="'.$color.'">'.$ratio.'</font>';
		}
		elseif (bt_user::$current['uploaded'] > 0)
			$ratio = 'Inf.';
		else
			$ratio = '---';

		if (bt_user::$current['connectable'] == 'no' && !$fakesite)
			$conn = '<a href="http://bt.degreez.net/firewalled.html"><span style="color: red"><strong>NO</strong></span></a>';
		elseif (bt_user::$current['connectable'] == 'unknown' && !$fakesite)
			$conn = '<font color="blue"><b>UNKNOWN</b></font>';
		else
			$conn = '<font color="black">Yes</font>';

		if (bt_user::$current['settings']['donor'] && !$fakesite)
			$medaldon = '<img src="'.bt_config::$conf['pic_base_url'].'star.gif" alt="donor" title="donor" />';

		if (bt_user::$current['settings']['warned'] && !$fakesite)
			$warn = '<img src="'.bt_config::$conf['pic_base_url'].'warned.gif" alt="warned" title="warned" />';

		//// check for messages //////////////////
		$messages = bt_user::$current['inbox'];
		$outmessages = bt_user::$current['sentbox'];
		$unread = 0 + $unread;

		if ($unread)
			$inboxpic = '<img style="border: none" alt="inbox" title="inbox (new messages)" src="'.bt_config::$conf['pic_base_url'].'pn_inboxnew.gif" />';
		else
			$inboxpic = '<img style="border: none" alt="inbox" title="inbox (no new messages)" src="'.bt_config::$conf['pic_base_url'].'pn_inbox.gif" />';
		//////// start the statusbar /////////////

?>
				<table align="center" style="width: 900px; background: url(<?=bt_config::$conf['pic_base_url']?>tbarbg.gif)">
					<tr>
						<td style="border: black solid 1px">
							<table style="width:100%">
								<tr>
									<td class="layout" style="text-align: left; padding: 4px">
										Welcome, <b><a href="/userdetails.php?id=<?=bt_user::$current['id']?>"><?=bt_user::$current['username']?></a></b><?=$medaldon?><?=$warn?>&nbsp;
										[<a href="logout.php">logout</a>]&nbsp;&nbsp;<span style="color: green">Connectable:</span> <?=$conn?><br />
										<span style="color: blue">Ratio:</span> <?=$ratio?>&nbsp;&nbsp;<span style="color: green">Uploaded:</span>
										<span style="color: black"><?=$uped?></span> &nbsp;<span style="color: red">Downloaded:</span>
										<span style="color: black"><?=$downed?></span> &nbsp;
									</td>
									<td class="layout" valign="middle" style="width: 20%; text-align: center"></td>
									<td class="layout" style="text-align: right">
										<span class="smallfont">The time is now: <?echo format_time();?></span><br />
<? if ($messages) { ?>
										<span class="smallfont"><a href="/inbox.php"><?=$inboxpic?></a> <?=$messages?> (<?=$unread?> New)</span>
<? if($outmessages) { ?>
										<span class="smallfont">&nbsp;&nbsp;<a href="/inbox.php?out=1"><img style="border: none" alt="sentbox" title="sentbox" src="<?=bt_config::$conf['pic_base_url']?>pn_sentbox.gif" /></a> <?=$outmessages?></span>
<?} else { ?>
										<span class="smallfont">&nbsp;&nbsp;<a href="/inbox.php?out=1"><img height="14px" style="border: none" alt="sentbox" title="sentbox" src="<?=bt_config::$conf['pic_base_url']?>pn_sentbox.gif" /></a> 0</span>
<? } } else { ?>
										<span class="smallfont"><a href="/inbox.php"><img height="14px" style="border: none" alt="inbox" title="inbox" src="<?=bt_config::$conf['pic_base_url']?>/pn_inbox.gif" /></a> 0</span>
<? if ($outmessages) { ?>
										<span class="smallfont">&nbsp;&nbsp;<a href="/inbox.php?out=1"><img height="14px" style="border: none" alt="sentbox" title="sentbox" src="<?=bt_config::$conf['pic_base_url']?>pn_sentbox.gif" /></a> <?=$outmessages?></span>
<? } else { ?>
										<span class="smallfont">&nbsp;&nbsp;<a href="/inbox.php?out=1"><img height="14px" style="border: none" alt="sentbox" title="sentbox" src="<?=bt_config::$conf['pic_base_url']?>pn_sentbox.gif" /></a> 0</span>
<? } } ?>
										&nbsp;<a href="/friends.php"><img style="border: none" alt="Buddylist" title="Buddylist" src="<?=bt_config::$conf['pic_base_url']?>buddylist.gif" /></a>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<br />
<? } } if(!bt_user::$current) $pad = '10'; else $pad = '0'; ?>
				<table class="mainouter" <?=$w; ?> cellspacing="0" cellpadding="<?=$pad?>">
					<tr>
						<td class="layout" align="center">
<? if ($unconnectable && !bt_user::required_class(bt_user::UC_XTREME_USER)) { ?>
							<div class="alert" style="width: 250px">
								<a class="alert" href="http://bt.degreez.net/firewalled.html">You are not connectable! Please fix this!</a>
							</div>
							<br />
<? } if ($unread) { ?>
							<div class="alert" style="width: 190px">
								<a class="alert" href="/inbox.php">You have <?=$unread?> new message<?=($unread > 1 ? 's' : '')?>!</a>
							</div>
							<br />
<? } if ($fakesite) {
		stdmsg('','Due to issues with our paypal account, we have decided that rather than getting our account frozen, that it is best to close down the site.
We regret to have to do this, but we think its in our best interests.
// ScT Staff');
		stdfoot();
		die();
	}
} // stdhead

function stdfoot() {
	global $TIMES, $numusers_first, $MySQL_NUM_QUERIES, $MySQL_LEN_QUERIES;
	if (defined('BT_FOOTER_DONE'))
		return;

	define('BT_FOOTER_DONE', true);
	$TIMES['end'] = microtime(true);
	$extime = round($TIMES['end'] - $TIMES['start'],4);
	$qnum = $MySQL_NUM_QUERIES + (is_object(bt_sql::$DB) ? bt_sql::$DB->query_count : 0);
	$qtime = $MySQL_LEN_QUERIES + (is_object(bt_sql::$DB) ? bt_sql::$DB->query_time : 0);
	$phpt = $extime - $qtime - bt_memcache::$time;
	$max_mem = memory_get_peak_usage();
?>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top" style="border: 0; background: url(<?=bt_config::$conf['pic_base_url']?>sidebardownright.gif); background-repeat: no-repeat; background-position: center bottom">
				<img src="<?=bt_config::$conf['pic_base_url']?>sidebarright.gif" border="0" alt="" style="position: relative; left: 19px" />
			</td>
		</tr>
		<tr>
			<td style="border: 0">
				<img src="<?=bt_config::$conf['pic_base_url']?>bottomleft.gif" border="0" alt="" style="position: relative; left: 11px" />
			</td>
			<td style="background: url(<?=bt_config::$conf['pic_base_url']?>bottombg.gif); width: 99%; border: none"></td>
			<td style="border: 0">
				<img src="<?=bt_config::$conf['pic_base_url']?>bottomright.gif" border="0" alt="" style="position: relative; left: -8px" />
			</td>
		</tr>
	</table>
	<div style="position: relative; top: 5px; font-weight: bold">
		<a href="/links.php">Links</a> &nbsp; | &nbsp; <a href="/topten.php">Top 10</a> &nbsp; | &nbsp;
		<a href="/users.php">Users</a> &nbsp; | &nbsp; <a href="/irc.php">IRC</a> &nbsp; | &nbsp;
		<a href="/useragreement.php">Terms &amp; Conditions</a>
		<? if (bt_user::required_class(bt_user::UC_STAFF)) { ?> &nbsp; | &nbsp; <a href="/staff-bitbucket.php">Staff BitBucket</a><? } ?>
	</div>
	<br />
	<div style="color: black">
		&copy; SceneTorrents Staff 2005-2008<br />
		<span style="font-size: 7pt; color: black; position: relative; top: 5px;">SET: <?php echo $extime.'s | DB Queries: '.$qnum.' ('.round($qtime,4).'s) | '.
			'MC Queries: '.bt_memcache::$count.' ('.round(bt_memcache::$time, 4).'s) | PHP: '.round($phpt, 4).'s | Mem Usage: '.mksize($max_mem); ?></span>
	</div>
</div>
</body>
</html>
<?
} //stdfoot


function torrenttable($res, $variant = 'index', $addparam = '', &$end_new = false) {
	if (bt_user::$current['class'] < UC_POWER_USER && $DELAYS) {
		$gigs = bt_user::$current['uploaded'] / 1073741824;
		$ratio = bt_user::$current['downloaded'] > 0 ? (bt_user::$current['uploaded'] / bt_user::$current['downloaded']) : 0;
		if ($ratio < 0.25 || $gigs < 0)
			$wait = 0;
		elseif ($ratio < 0.75 || $gigs < 0)
			$wait = 0;
		else
			$wait = 0;
	}

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
	global $CURUSER, $_SERVER, $CONFIG;
	begin_main_frame();
	begin_frame();
	$count = 0;
	foreach ($rows as $row) {
		$row['settings'] = bt_bitmask::fetch($row['flags'],'avatar_po','warned','donor');
		echo '<p class=sub>#'.$row['id'].' by ';
		if (isset($row['username'])) {
			$title = $row['title'];
			if ($title == '')
				$title = get_user_class_name($row["class"]);
			else
				$title = bt_security::html_safe($title);

			echo '<a name="comm'.$row['id'].'" href="/userdetails.php?id='.$row['user'].'"><b>'.
				bt_security::html_safe($row['username']).'</b></a>'.($row['settings']['donor'] ?
				'<img src="'.$CONFIG['pic_base_url'].'star.gif" alt="Donor">' : '').
				($row['settings']['warned'] ? '<img src="'.$CONFIG['pic_base_url'].'warned.gif" alt="Warned">' : '').' ('.$title.')'."\n";
		}
		else
			echo '<a name="comm'.$row['id'].'"><i>(orphaned)</i></a>'."\n";

		echo ' at '.format_time($row['added']).(($row['user'] == $CURUSER['id'] && $CURUSER['settings']['post_enable'])
			|| get_user_class() >= UC_FORUM_MODERATOR ? '- [<a href="/comment.php?action=edit&amp;cid='.$row['id'].'">Edit</a>]' : '').
			(get_user_class() >= UC_FORUM_MODERATOR ? '- [<a href="/comment.php?action=delete&amp;cid='.$row['id'].'">Delete</a>]' : '').
			($row['editedby'] && get_user_class() >= UC_FORUM_MODERATOR ?
			'- [<a href="/comment.php?action=vieworiginal&amp;cid='.$row['id'].'">View original</a>]' : '').'</p>'."\n";

		$avatar = $row['avatar'];
		bt_forums::avatar($avatar, $avtext, $row['settings']['avatar_po']);

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

function stdmsg($heading, $text)
  {
   print('<table class="main" width="750" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="embedded">'."\n");
   if ($heading)
     print('      <h2>'.$heading.'</h2>'."\n");
   print('      <table width="100%" border="1" cellspacing="0" cellpadding="10">
        <tr>
          <td class="text">
            '.$text.'
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>'."\n");
}


function stderr($heading, $text)
{
  stdhead();
  stdmsg($heading, $text);
  stdfoot();
  die;
}

?>
