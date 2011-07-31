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

bt_loginout::db_connect(true);

$UPAPPFORUM  = 22;

$mupspeed    = array(1 => 'Unknown',2=>'<= 5 Mbps',3=>'5+ Mbps - 10 Mbps',4=>'10+ Mbps - 20 Mbps',
                     5=>'20+ Mbps - 30 Mbps',6=>'30+ Mbps - 50 Mbps',7=>'50+ Mbps - 100 Mbps',8=>'> 100 Mbps');
$muplocation = array(1=>'USA Or Canada',2=>'Europe',3=>'Other');
$msources    = array(1=>'Kazaa',2=>'Other torrent sites',3=>'Other P2P networks',4=>'Top Sites',5=>'Need Supplying');
$mpretimes   = array(1=>'< 20 mins',2=>'~ 1 hour',3=>'~ 6 hours',4=>'~ 1 day',5=>'~ 5 days',6=>'What\'s a "pre time"?');


$upspeed     = 0 + $_POST['upspeed'];
$uplocation  = 0 + $_POST['uploc'];
$sources     = 0 + $_POST['sources'];
$pretimes    = 0 + $_POST['pretime'];
$cats        = $_POST['cats'];

$faq         = 0 + $_POST['chk1'];
$rules       = 0 + $_POST['chk2'];
$maketorrent = 0 + $_POST['chk3'];
$uptorrents  = 0 + $_POST['chk4'];
$agreeseed   = 0 + $_POST['chk5'];
$meanconn    = 0 + $_POST['chk6'];
$amconn      = 0 + $_POST['chk7'];
$notupother  = 0 + $_POST['chk8'];
$irc         = 0 + $_POST['chk9'];
$comments    = $_POST['comments'];

if ($upspeed < 1 || $uplocation < 1 || $sources < 1 || $pretimes < 1 || !$cats)
  bt_theme::error('Error', 'Please make sure to fill out all sections of the application.');

if ($upspeed > 8 || $uplocation > 3 || $sources > 5 || $pretimes > 6)
  bt_theme::error('Error', 'You have sent an invalid value.');

if ($upspeed < 7 || $sources < 4 || $pretimes >= 4 || !$faq || !$rules || !$maketorrent || !$uptorrents || !$agreeseed)
  $ignore = true;

if (!$ignore)
  {
   $topicname = 'App: '.bt_user::$current['username'];
   $catlist = bt_mem_caching::get_cat_list();
   $mcats = array();
   foreach($catlist as $catid => $cat)
     {
      if (in_array($catid, $cats))
        $mcats[] = $cat['name'];
     }

   $posttext = '[b]Ratio:[/b] '.number_format(bt_user::$current['uploaded'] / bt_user::$current['downloaded'], 3)."\n".
               '[b]Uploaded:[/b] '.bt_theme::mksize(bt_user::$current['uploaded'])."\n".
               '[b]Upload Speed:[/b] '.$mupspeed[$upspeed]."\n".
               '[b]Upload Location:[/b] '.$muplocation[$uplocation]."\n".
               '[b]Sources:[/b] '.$msources[$sources]."\n".
               '[b]Pre Times:[/b] '.$mpretimes[$pretimes]."\n\n".
               '[b]Read the FAQs:[/b] '.($faq ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Read the Rules:[/b] '.($rules ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Knows how to make torrents:[/b] '.($maketorrent ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Has uploaded torrents:[/b] '.($uptorrents ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Agrees to seed as long as needed:[/b] '.($agreeseed ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Knows what being connectable is:[/b] '.($meanconn ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Is connectable:[/b] '.($amconn ? 'Yes' : '[color=red]No[/color]')."\n".
               '[b]Uploads on other site(s):[/b] '.($notupother ? 'No' : '[color=red]Yes[/color]')."\n".
               '[b]Can idle on IRC regularly:[/b] '.($irc ? 'Yes' : '[color=red]No[/color]')."\n\n".
               '[b]Categories planed to upload in:[/b] '.implode(', ',$mcats).
               ($comments != '' ? "\n\n\n".'[b]Comments:[/b] '.$comments : '');

   $topic   = mysql_query('INSERT INTO `topics` (`userid`,`subject`,`forumid`) '.
                        'VALUES ("'.bt_user::$current['id'].'", '.sqlesc($topicname).', "'.$UPAPPFORUM.'")') or sqlerr(__FILE__, __LINE__);
   $topicid = mysql_insert_id();
   $post    = mysql_query('INSERT INTO `posts` (`topicid`,`userid`,`added`,`body`) '.
                        'VALUES('.$topicid.','.bt_user::$current['id'].', '.time().', '.sqlesc($posttext).')') or sqlerr(__FILE__, __LINE__);
   $postid  = mysql_insert_id();
   mysql_query('UPDATE `topics` SET `lastpost` = "'.$postid.'" WHERE `id` = "'.$topicid.'"') or sqlerr(__FILE__, __LINE__);
   mysql_query('UPDATE `forums` SET `postcount` = (`postcount` + 1), `topiccount` = (`topiccount` + 1) WHERE `id` = "'.$UPAPPFORUM.'"') or sqlerr(__FILE__, __LINE__);
  }

header('Location: '.bt_vars::$base_url.'/upapp.php?ok=1');
?>
