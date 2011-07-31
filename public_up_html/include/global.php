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
require_once(CLASS_PATH.'bt_user.php');
require_once(CLASS_PATH.'bt_utf8.php');

$smilies = array(
  ':-)' => 'smile1.gif',
  ';-)' => 'wink.gif',
  ';)' => 'wink.gif',
  ':-D' => 'grin.gif',
  ':-P' => 'tongue.gif',
  ':-(' => 'sad.gif',
  ':\'-(' => 'cry.gif',
  ':-|' => 'noexpression.gif',
  ':-/' => 'confused.gif',
  ':-O' => 'ohmy.gif',
  ':O' => 'ohmy.gif',
  ':o)' => 'clown.gif',
  '8-)' => 'cool1.gif',
  '|-)' => 'sleeping.gif',
  ':homer:' => 'homer.gif',
  ':train:' => 'train.gif',
  ':uber:' => 'uber.gif',
  ':woohoo:' => 'woohoo.gif',
  ':djcrowd:' => 'dj.gif',
  ':dancing:' => 'dancing.gif',
  ':innocent:' => 'innocent.gif',
  ':whistle:' => 'whistle.gif',
  ':unsure:' => 'unsure.gif',
  ':closedeyes:' => 'closedeyes.gif',
  ':angry:' => 'angry.gif',
  ':smile:' => 'smile2.gif',
  ':lol:' => 'laugh.gif',
  ':cool:' => 'cool2.gif',
  ':fun:' => 'fun.gif',
  ':thumbsup:' => 'thumbsup.gif',
  ':thumbsdown:' => 'thumbsdown.gif',
  ':blush:' => 'blush.gif',
  ':weep:' => 'weep.gif',
  ':unsure:' => 'unsure.gif',
  ':yes:' => 'yes.gif',
  ':no:' => 'no.gif',
  ':love:' => 'love.gif',
  ':?:' => 'question.gif',
  ':!:' => 'excl.gif',
  ':idea:' => 'idea.gif',
  ':arrow:' => 'arrow.gif',
  ':hmm:' => 'hmm.gif',
  ':hmmm:' => 'hmmm.gif',
  ':huh:' => 'huh.gif',
  ':w00t:' => 'w00t.gif',
  ':geek:' => 'geek.gif',
  ':look:' => 'look.gif',
  ':rolleyes:' => 'rolleyes.gif',
  ':kiss:' => 'kiss.gif',
  ':shifty:' => 'shifty.gif',
  ':blink:' => 'blink.gif',
  ':smartass:' => 'smartass.gif',
  ':sick:' => 'sick.gif',
  ':crazy:' => 'crazy.gif',
  ':wacko:' => 'wacko.gif',
  ':alien:' => 'alien.gif',
  ':wizard:' => 'wizard.gif',
  ':wave:' => 'wave.gif',
  ':wavecry:' => 'wavecry.gif',
  ':baby:' => 'baby.gif',
  ':ras:' => 'ras.gif',
  ':sly:' => 'sly.gif',
  ':devil:' => 'devil.gif',
  ':evil:' => 'evil.gif',
  ':evilmad:' => 'evilmad.gif',
  ':yucky:' => 'yucky.gif',
  ':nugget:' => 'nugget.gif',
  ':sneaky:' => 'sneaky.gif',
  ':smart:' => 'smart.gif',
  ':shutup:' => 'shutup.gif',
  ':shutup2:' => 'shutup2.gif',
  ':yikes:' => 'yikes.gif',
  ':flowers:' => 'flowers.gif',
  ':wub:' => 'wub.gif',
  ':osama:' => 'osama.gif',
  ':saddam:' => 'saddam.gif',
  ':santa:' => 'santa.gif',
  ':indian:' => 'indian.gif',
  ':guns:' => 'guns.gif',
  ':crockett:' => 'crockett.gif',
  ':zorro:' => 'zorro.gif',
  ':snap:' => 'snap.gif',
  ':beer:' => 'beer.gif',
  ':beer2:' => 'beer2.gif',
  ':drunk:' => 'drunk.gif',
  ':mama:' => 'mama.gif',
  ':pepsi:' => 'pepsi.gif',
  ':medieval:' => 'medieval.gif',
  ':rambo:' => 'rambo.gif',
  ':ninja:' => 'ninja.gif',
  ':hannibal:' => 'hannibal.gif',
  ':party:' => 'party.gif',
  ':snorkle:' => 'snorkle.gif',
  ':evo:' => 'evo.gif',
  ':king:' => 'king.gif',
  ':chef:' => 'chef.gif',
  ':mario:' => 'mario.gif',
  ':pope:' => 'pope.gif',
  ':fez:' => 'fez.gif',
  ':cap:' => 'cap.gif',
  ':cowboy:' => 'cowboy.gif',
  ':pirate:' => 'pirate.gif',
  ':rock:' => 'rock.gif',
  ':cigar:' => 'cigar.gif',
  ':icecream:' => 'icecream.gif',
  ':oldtimer:' => 'oldtimer.gif',
  ':wolverine:' => 'wolverine.gif',
  ':strongbench:' => 'strongbench.gif',
  ':weakbench:' => 'weakbench.gif',
  ':bike:' => 'bike.gif',
  ':music:' => 'music.gif',
  ':book:' => 'book.gif',
  ':fish:' => 'fish.gif',
  ':stupid:' => 'stupid.gif',
  ':dots:' => 'dots.gif',
  ':axe:' => 'axe.gif',
  ':hooray:' => 'hooray.gif',
  ':yay:' => 'yay.gif',
  ':cake:' => 'cake.gif',
  ':hbd:' => 'hbd.gif',
  ':hi:' => 'hi.gif',
  ':offtopic:' => 'offtopic.gif',
  ':band:' => 'band.gif',
  ':hump:' => 'hump.gif',
  ':punk:' => 'punk.gif',
  ':bounce:' => 'bounce.gif',
  ':mbounce:' => 'mbounce.gif',
  ':group:' => 'group.gif',
  ':console:' => 'console.gif',
  ':smurf:' => 'smurf.gif',
  ':soldiers:' => 'soldiers.gif',
  ':spidey:' => 'spidey.gif',
  ':smurf:' => 'smurf.gif',
  ':rant:' => 'rant.gif',
  ':pimp:' => 'pimp.gif',
  ':nuke:' => 'nuke.gif',
  ':judge:' => 'judge.gif',
  ':jacko:' => 'jacko.gif',
  ':ike:' => 'ike.gif',
  ':greedy:' => 'greedy.gif',
  ':dumbells:' => 'dumbells.gif',
  ':clover:' => 'clover.gif',
  ':shit:' => 'shit.gif',
  ':thankyou:' => 'thankyou.gif',
  ':horse:' => 'horse.gif',
  ':box:' => 'box.gif',
  ':boxing:' => 'boxing.gif',
  ':gathering:' => 'gathering.gif',
  ':hang:' => 'hang.gif',
  ':chair:' => 'chair.gif',
  ':spam:' => 'spam.gif',
  ':bandana:' => 'bandana.gif',
  ':construction:' => 'construction.gif',
  ':oops:' => 'oops.gif',
  ':rip:' => 'rip.gif',
  ':sheep:' => 'sheep.gif',
  ':tease:' => 'tease.gif',
  ':spider:' => 'spider.gif',
  ':shoot:' => 'shoot.gif',
  ':shoot2:' => 'shoot2.gif',
  ':police:' => 'police.gif',
  ':lovers:' => 'lovers.gif',
  ':kissing:' => 'kissing.gif',
  ':kissing2:' => 'kissing2.gif',
  ':jump:' => 'jump.gif',
  ':happy2:' => 'happy2.gif',
  ':clap:' => 'clap.gif',
  ':clap2:' => 'clap2.gif',
  ':chop:' => 'chop.gif',
  ':lttd:' => 'lttd.gif',
  ':whip:' => 'whip.gif',
  ':yawn:' => 'yawn.gif',
  ':bow:' => 'bow.gif',
  ':slap:' => 'slap.gif',
  ':wall:' => 'wall.gif',
  ':please:' => 'please.gif',
  ':sorry:' => 'sorry.gif',
  ':dawgie:' => 'dawgie.gif',
  ':weirdo:' => 'weirdo.gif',
  ':cylon:' => 'cylon.gif',
  ':trampoline:' => 'trampoline.gif',
  ':rofl:' => 'rofl.gif',
  ':super:' => 'super.gif',
  ':detective:' => 'detective.gif',
  ':fishing:' => 'fishing.gif',
  ':pirate2:' => 'pirate2.gif',
  ':angel:' => 'angel.gif',
  ':hug:' => 'hug.gif',
  ':smirk:' => 'smirk.gif',
  ':gslocked:' => 'gslocked.gif',
  ':chlocked:' => 'chlocked.gif',
  ':bangin:' => 'bangin.gif',
  ':huggin:' => 'hug.gif',
  ':blowup:' => 'blowup.gif',
  ':drive1:' => 'drive1.gif',
  ':fish2:' => 'fish2.gif',
  ':helpme:' => 'helpsmilie.gif',
  ':huglove:' => 'huglove.gif',
  ':lmfao:' => 'lmfao.gif',
  ':feellock:' => 'lock.gif',
  ':nono:' => 'nono.gif',
  ':respect:' => 'respect.gif',
  ':slap1:' => 'slap1.gif',
  ':shock1:' => 'shock1.gif',
  ':xmas:' => 'xmas.gif',
  ':bedhump:' => 'bedhump.gif',
  ':wank:' => 'wank.gif',
  ':catfight:' => 'catfight.gif',
  ':dog:' => 'dog.gif',
  ':drool:' => 'drool.gif',
  ':banana:' => 'banana.gif',
  ':fans:' => 'fans.gif',
  ':flood:' => 'flood.gif',
  ':help:' => 'help.gif',
  ':joker:' => 'joker.gif',
  ':lazy:' => 'lazy.gif',
  ':patsak:' => 'patsak.gif',
  ':pilot:' => 'pilot.gif',
  ':protest:' => 'protest.gif',
  ':rap:' => 'rap.gif',
  ':read:' => 'read.gif',
  ':sctlove:' => 'sctlove.gif',
  ':secret:' => 'secret.gif',
  ':snack:' => 'snack.gif',
  ':woh:' => 'woh.gif',
  ':yahoo:' => 'yahoo.gif',
  ':yes2:' => 'yes2.gif',
  ':yu:' => 'yu.gif',
  ':welcome:' => 'welcome.gif',
  ':jester:' => 'jester.gif',
  ':)' => 'smile1.gif',
  ':wink:' => 'wink.gif',
  ':D' => 'grin.gif',
  ':P' => 'tongue.gif',
  ':(' => 'sad.gif',
  ':\'(' => 'cry.gif',
  ':|' => 'noexpression.gif',
  ':Boozer:' => 'alcoholic.gif',
  ':deadhorse:' => 'deadhorse.gif',
  ':spank:' => 'spank.gif',
  ':yoji:' => 'yoji.gif',
  ':locked:' => 'locked.gif',
  ':grrr:' => 'angry.gif',
  'O:-' => 'innocent.gif',
  ':sleeping:' => 'sleeping.gif',
  '-_-' => 'unsure.gif',
  ':clown:' => 'clown.gif',
  ':mml:' => 'mml.gif',
  ':rtf:' => 'rtf.gif',
  ':morepics:' => 'morepics.gif',
  ':nwpsign:' => 'sign-nwp1.gif',
);

function get_row_count($table, $suffix = '') {
	if ($suffix)
		$suffix = ' '.$suffix;
	($r = bt_sql::query('SELECT COUNT(*) FROM '.bt_sql::escape($table).$suffix)) or bt_sql::err(__FILE__,__LINE__);
	($a = $r->fetch_row()) or bt_sql::err(__FILE__,__LINE__);
	$r->free();
	return $a[0];
}


function sqlerr($file = '', $line = '') {
	bt_sql::err($file, (int)$line);
}

function format_time($timestamp = NULL) {
	return bt_time::format($timestamp);
}

function format_urls($s) {
	$link = bt_theme::$settings['bbcode']['link'];
	return preg_replace(
		'/(\A|\s)((?:http|ftp|https|ftps|irc):\/\/[^()<>\s]+)/i',
		'$1<a href="/out.php?url=$2"'.$link.'>$2</a>', $s);
}

function format_quotes($s) {
	$bbcode = bt_theme::$settings['bbcode'];

	while ($old_s != $s) {
		$old_s = $s;

		//find first occurrence of [/quote]
		$close = bt_utf8::stripos($s, '[/quote]');
		if ($close === false)
			return $s;

		// find last [quote] before first [/quote]
		// note that there is no check for correct syntax
		$open = bt_utf8::strripos(bt_utf8::substr($s,0,$close), '[quote');
		if ($open === false)
			return $s;

		$quote = bt_utf8::substr($s,$open,$close - $open + 8);

		 //[quote]Text[/quote]
		$quote = preg_replace(
			'/\[quote\]\s*(.+?)\s*\[\/quote\]\s*/is',
            $bbcode['quote'][0].'Quote:'.$bbcode['quote'][1].$bbcode['quote'][2].'$1'.$bbcode['quote'][3], $quote);

          //[quote=Author]Text[/quote]
          $quote = preg_replace(
            '/\[quote=([ \S]+?)\]\s*(.+?)\s*\[\/quote\]\s*/is',
            $bbcode['quote'][0].'$1 wrote:'.$bbcode['quote'][1].$bbcode['quote'][2].'$2'.$bbcode['quote'][3], $quote);

          $s = bt_utf8::substr($s,0,$open) . $quote . bt_utf8::substr($s,$close + 8);
  }

        return $s;
}

function format_comment($text, $strip_html = true) {
	global $smilies;
	$bbcode = bt_theme::$settings['bbcode'];
	$link = $bbcode['link'];

	$s = $text;
	// the requested [you] tag
	$s = str_replace ('[you]', bt_user::$current['username'], $s);
	if ($strip_html)
		$s = bt_security::html_safe($s);

	// [*]
	if (bt_utf8::stripos($s, '[*]') !== false) {
		for ($i = true, $n = 0; $i && $n < 5; $n++)
			$s = preg_replace('/\[\*\](.+?)(?:[\r\n]+|\[\/\*\]|\Z|\s*(\[\*\]))/i', $bbcode['*'][0].'$1'.$bbcode['*'][1].'$2'.$bbcode['b'][2], $s, -1, $i);
	}

	// [center]Center text[/center]
	if (bt_utf8::stripos($s, '[center]') !== false)
		$s = preg_replace('/\[center\](.+?)\[\/center\]/is', $bbcode['center'][0].'$1'.$bbcode['center'][1], $s);

	// [b]Bold[/b]
	if (bt_utf8::stripos($s, '[b]') !== false)
		$s = preg_replace('/\[b\](.+?)\[\/b\]/is', $bbcode['b'][0].'$1'.$bbcode['b'][1], $s);

	// [i]Italic[/i]
	if (bt_utf8::stripos($s, '[i]') !== false)
		$s = preg_replace('/\[i\](.+?)\[\/i\]/is', $bbcode['i'][0].'$1'.$bbcode['i'][1], $s);

	// [u]Underline[/u]
	if (bt_utf8::stripos($s, '[u]') !== false)
		$s = preg_replace('/\[u\](.+?)\[\/u\]/is', $bbcode['u'][0].'$1'.$bbcode['u'][1], $s);

	if (bt_utf8::stripos($s, '[img') !== false) {
		// [img]http://www/image.gif[/img]
		$s = preg_replace('/\[img\](https?:\/\/[^\s\'\"<>]+(\.gif|\.jpg|\.png))\[\/img\]/i', '<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" />', $s);
		// [img=http://www/image.gif]
		$s = preg_replace('/\[img=(https?:\/\/[^\s\'\"<>]+(\.gif|\.jpg|\.png))\]/i', '<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" />', $s);

		// [imgpo]http://www/image.gif[/imgpo]
		$s = preg_replace('/\[imgpo\](https?:\/\/[^\s\'\"<>]+(\.gif|\.jpg|\.png))\[\/imgpo\]/i', (!(bt_user::$current['flags'] & bt_options::USER_SHOW_PO_AVATARS) ?
			'<a href="\\1"'.$link.'>(potentially offensive picture, click here to view)</a>' :
			'<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" />'), $s);
		// [imgpo=http://www/image.gif]
		$s = preg_replace('/\[imgpo=(https?:\/\/[^\s\'\"<>]+(\.gif|\.jpg|\.png))\]/i',  (!(bt_user::$current['flags'] & bt_options::USER_SHOW_PO_AVATARS) ?
			'<a href="\\1"'.$link.'>(potentially offensive picture, click here to view)</a>' :
			'<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" />'), $s);

		// [imgw]http://www/image.gif[/imgw]
		$s = preg_replace('/\[imgw\](https?:\/\/[^\s\'\"<>]+(\.(jpg|gif|png)))\[\/imgw\]/i',
			'<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" /><br />'.
			'<span style="font-size: 7pt">This image has been resized, click <a href="\\1"'.$link.'>'.
			'<b>here</b></a> to view the full-sized image.</span><br />', $s);
        // [imgw=http://www/image.gif]
        $s = preg_replace('/\[imgw=(https?:\/\/[^\s\'\"<>]+(\.(gif|jpg|png)))\]/i',
			'<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" /><br />'.
			'<span style="font-size: 7pt">This image has been resized, click <a href="\\1"'.$link.'>'.
			'<b>here</b></a> to view the full-sized image.</span><br />', $s);

		// [imgpow]http://www/image.gif[/imgpow]
		$s = preg_replace('/\[imgpow\](https?:\/\/[^\s\'\"<>]+(\.gif|\.jpg|\.png))\[\/imgpow\]/i',
			(!(bt_user::$current['flags'] & bt_options::USER_SHOW_PO_AVATARS) ?
			'<a href="\\1"'.$link.'>(potentially offensive picture, click here to view)</a>' :
			'<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" />'.
			'<br /><span style="font-size: 7pt">This image has been resized, click '.
			'<a href="\\1"'.$link.'><b>here</b></a> to view '.
			'the full-sized image.</span><br />'), $s);
		// [imgpow=http://www/image.gif]
		$s = preg_replace('/\[imgpow=(https?:\/\/[^\s\'\"<>]+(\.gif|\.jpg|\.png))\]/i',
			(!(bt_user::$current['flags'] & bt_options::USER_SHOW_PO_AVATARS) ?
			'<a href="\\1"'.$link.'>(potentially offensive picture, click here to view)</a>' :
			'<img alt="user posted image" src="\\1" style="border: none; max-width: 500px" />'.
			'<br /><span style="font-size: 7pt">This image has been resized, click '.
			'<a href="\\1"'.$link.'><b>here</b></a> to view '.
			'the full-sized image.</span><br />'), $s);
	}

	if (bt_utf8::stripos($s, '[color=') !== false) {
		// [color=blue]Text[/color]
		$s = preg_replace(
			'/\[color=([a-zA-Z]+)\](.+?)\[\/color\]/is',
			'<span style="color: \\1">\\2</span>', $s);

		// [color=#ffcc99]Text[/color]
		$s = preg_replace(
			'/\[color=(#[a-f0-9]{6})\](.+?)\[\/color\]/is',
			'<span style="color: \\1">\\2</span>', $s);
	}

	if (bt_utf8::stripos($s, 'hxxp') !== false)
		$s = preg_replace('/hxxp(s?)\:/i','http\\1:',$s);


	if (bt_utf8::stripos($s, '[url') !== false) {
		// [url=http://www.example.com]Text[/url]
		$s = preg_replace(
			'/\[url=((?:(?:http|ftp|https|ftps|irc):\/\/|www\.)[^()<>\s]+?)\](.+?)\[\/url\]/is',
			'<a href="/out.php?url=$1"'.$link.'>$2</a>', $s);

		// [url]http://www.example.com[/url]
		$s = preg_replace(
			'/\[url\](((?:(?:http|ftp|https|ftps|irc):\/\/|www\.)[^()<>\s]+?))\[\/url\]/i',
			'<a href="/out.php?url=$1"'.$link.'>$1</a>', $s);
	}

	// [size=4]Text[/size]
	if (bt_utf8::stripos($s, '[size=') !== false)
		$s = preg_replace('/\[size=([0-9]+)\](.+?)\[\/size\]/is','<span style="font-size: \\1pt">\\2</span>', $s);

	// [font=Arial]Text[/font]
	if (bt_utf8::stripos($s, '[font=') !== false)
		$s = preg_replace('/\[font=([a-zA-Z ,]+)\](.+?)\[\/font\]/is','<span style="font-family: \\1">\\2</span>', $s);

	// Quotes
	$s = format_quotes($s);

	// URLs
	$s = format_urls($s);
	$s = preg_replace('/\/out.php\?url\=https?:\/\/(?:www\.)?(?:(?:us|eu)\.)?scenetorrents\.org/i','',$s);

	// Linebreaks
	$s = nl2br($s);

	// [pre]Preformatted[/pre]
	$s = bt_forums::format_block_tag($s, 'pre', $bbcode['pre'][0], $bbcode['pre'][1], true, false);

	//[spoiler]Text[/spoiler]
	$s = bt_forums::format_block_tag($s, 'spoiler', $bbcode['spoiler'][0], $bbcode['spoiler'][1], false, true);

	// fix ;) and ;-) smilie bug
	$s = preg_replace('/(&[a-z]+;)(-?\))/i', '$1 $2', $s);

	// Maintain spacing
	$s = str_replace(bt_utf8::NBSP, ' ', $s);
	$s = str_replace('  ', ' '.bt_utf8::NBSP, $s);

	reset($smilies);
	$sms = $smr = array();
	foreach ($smilies as $code => $url) {
		$sms[] = $code;
		$smr[] = '<img src="'.bt_config::$conf['pic_base_url'].'smilies/'.$url.'" alt="'.bt_utf8::html_safe($code).'" style="border: none" />';
	}

	$s = str_replace($sms, $smr, $s);
	return $s;
}


function get_user_class() {
	return bt_user::$current['class'];
}

function get_user_class_name($class) {
	return bt_user::get_class_name($class);
}

function is_valid_user_class($class) {
	return (bool)(is_numeric($class) && floor($class) == $class && $class >= UC_MIN && $class <= UC_MAX);
}

function is_valid_id($id) {
	return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}


function get_ratio_color($ratio) {
	return bt_theme::ratio_color($ratio);
}

  function get_slr_color($ratio)
  {
    if ($ratio < 0.025) return "#ff0000";
    if ($ratio < 0.05) return "#ee0000";
    if ($ratio < 0.075) return "#dd0000";
    if ($ratio < 0.1) return "#cc0000";
    if ($ratio < 0.125) return "#bb0000";
    if ($ratio < 0.15) return "#aa0000";
    if ($ratio < 0.175) return "#990000";
    if ($ratio < 0.2) return "#880000";
    if ($ratio < 0.225) return "#770000";
    if ($ratio < 0.25) return "#660000";
    if ($ratio < 0.275) return "#550000";
    if ($ratio < 0.3) return "#440000";
    if ($ratio < 0.325) return "#330000";
    if ($ratio < 0.35) return "#220000";
    if ($ratio < 0.375) return "#110000";
    return "#000000";
  }
  function get_upr_color($ratio)
  {
    if ($ratio > 0.375) return "#ff0000";
    if ($ratio > 0.35) return "#ee0000";
    if ($ratio > 0.325) return "#dd0000";
    if ($ratio > 0.3) return "#cc0000";
    if ($ratio > 0.275) return "#bb0000";
    if ($ratio > 0.25) return "#aa0000";
    if ($ratio > 0.225) return "#990000";
    if ($ratio > 0.2) return "#880000";
    if ($ratio > 0.175) return "#770000";
    if ($ratio > 0.15) return "#660000";
    if ($ratio > 0.125) return "#550000";
    if ($ratio > 0.1) return "#440000";
    if ($ratio > 0.075) return "#330000";
    if ($ratio > 0.05) return "#220000";
    if ($ratio > 0.025) return "#110000";
    return "#000000";
  }

function get_elapsed_time($ts) {
	return bt_time::ago_time($ts);
}
function get_until_time($ts)
{
  $mins = floor(($ts - time()) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  if ($hours > 0)
    return "{$hours}h" . ($hours > 1 ? "s" : "");
  if ($mins > 0)
    return "{$mins}m" . ($mins > 1 ? "s" : "");
  return "< 1m";
}

function get_class_color($class)
  {
   switch ($class)
     {
      case UC_LEADER:
        $colclass = "coder";
        break;
      case UC_ADMINISTRATOR:
        $colclass = "admin";
        break;
      case UC_MODERATOR:
        $colclass = "moder";
        break;
      case UC_FORUM_MODERATOR:
        $colclass = "fmoder";
        break;
      case UC_UPLOADER:
        $colclass = "upldr";
        break;
      case UC_VIP:
        $colclass = "vip";
        break;
      case UC_WHORE:
        $colclass = "whore";
        break;
      case UC_LOVER:
        $colclass = "lover";
        break;
      case UC_XTREME_USER:
        $colclass = "xtreme";
        break;
      case UC_POWER_USER:
        $colclass = "powu";
        break;
      case UC_USER:
        $colclass = "user";
        break;
      default:
        $colclass = "peas";
        break;
     }
   return $colclass;
  }

function format_elapsed_time($secs) {
   $days = floor($secs / 86400);
   $hours = floor($secs / 3600);
   $mins = floor($secs / 60);
   $hours -= $days * 24;
   $mins -= ($days * 1440) + ($hours * 60);
   $secs -= ($days * 86400) + ($hours * 3600) + ($mins * 60);

   $diff = array();

   $time = '';

   if ($days > 0)
     $time .= $days.'d ';
   $time .= str_pad($hours, 2, '0', STR_PAD_LEFT).':'.str_pad($mins, 2, '0', STR_PAD_LEFT).':'.str_pad($secs, 2, '0', STR_PAD_LEFT);

   return $time;
}

function format_log($text) {
	global $smilies;
	$bbcode = bt_theme::$settings['bbcode'];
    $link = $bbcode['link'];

	$s = $text;
	$s = bt_string::cut_word($s, 100, ' ');
	$s = bt_security::html_safe($s);

	// [center]Center text[/center]
	if (bt_utf8::stripos($s, '[center]') !== false)
		$s = preg_replace('/\[center\](.+?)\[\/center\]/is', $bbcode['center'][0].'$1'.$bbcode['center'][1], $s);

	// [anon]anon username[/anon]
	if (bt_utf8::stripos($s, '[anon]') !== false)
		$s = preg_replace('/\[anon\](.+?)\[\/anon\]/is', $bbcode['i'][0].(bt_user::required_class(UC_MODERATOR) ? '$1' : 'Anonymous').
			$bbcode['i'][1], $s);

	// [b]Bold[/b]
	if (bt_utf8::stripos($s, '[b]') !== false)
		$s = preg_replace('/\[b\](.+?)\[\/b\]/is', $bbcode['b'][0].'$1'.$bbcode['b'][1], $s);

	// [i]Italic[/i]
	if (bt_utf8::stripos($s, '[i]') !== false)
		$s = preg_replace('/\[i\](.+?)\[\/i\]/is', $bbcode['i'][0].'$1'.$bbcode['i'][1], $s);

	// [u]Underline[/u]
	if (bt_utf8::stripos($s, '[u]') !== false)
		$s = preg_replace('/\[u\](.+?)\[\/u\]/is', $bbcode['u'][0].'$1'.$bbcode['u'][1], $s);

	if (bt_utf8::stripos($s, '[color=') !== false) {
		// [color=blue]Text[/color]
		$s = preg_replace(
			'/\[color=([a-zA-Z]+)\](.+?)\[\/color\]/is',
			'<span style="color: \\1">\\2</span>', $s);

		// [color=#ffcc99]Text[/color]
		$s = preg_replace(
			'/\[color=(#[a-f0-9]{6})\](.+?)\[\/color\]/is',
			'<span style="color: \\1">\\2</span>', $s);
	}

	$s = preg_replace('/hxxp(s?)\:/i','http\\1:',$s);

	if (bt_utf8::stripos($s, '[url') !== false) {
		// [url=http://www.example.com]Text[/url]
		$s = preg_replace(
			'/\[url=((?:(?:http|ftp|https|ftps|irc):\/\/|www\.)[^()<>\s]+?)\](.+?)\[\/url\]/is',
			'<a href="/out.php?url=$1"'.$link.'>$2</a>', $s);

		// [url]http://www.example.com[/url]
		$s = preg_replace(
			'/\[url\](((?:(?:http|ftp|https|ftps|irc):\/\/|www\.)[^()<>\s]+?))\[\/url\]/i',
			'<a href="/out.php?url=$1"'.$link.'>$1</a>', $s);
	}

	$s = format_urls($s);
	$s = preg_replace('/\/out.php\?url\=https?:\/\/(?:www\.)?scenetorrents\.org/i','',$s);

	return $s;
}

function write_log($text, $type = 'INFO') {
	$text = bt_sql::esc($text);
	switch($type) {
		case 'INFO':
			$type = 0;
			break;
		case 'EDIT':
			$type = 1;
			break;
		case 'DELE':
			$type = 2;
			break;
		case 'UPLD':
			$type = 3;
			break;
		default:
			$type = 0;
		break;
	}
	bt_sql::query('INSERT INTO sitelog (added, type, txt) VALUES('.time().', '.$type.', '.$text.')') or bt_sql::err(__FILE__, __LINE__);
}

function write_staff_log($text, $type = 'INFO') {
	$text = sqlesc($text);
	switch($type) {
		case 'INFO':
			$type = 0;
			break;
		case 'EDIT':
			$type = 1;
			break;
		case 'BAN':
			$type = 2;
			break;
		case 'UBAN':
			$type = 3;
			break;
		default:
			$type = 0;
			break;
		}
	bt_sql::query('INSERT INTO stafflog (added, type, txt) VALUES('.time().', '.$type.', '.$text.')') or bt_sql::err(__FILE__, __LINE__);
}
?>
