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
require_once(CLASS_PATH.'bencdec.php');
require_once(CLASS_PATH.'bt_nfo.php');
require_once(CLASS_PATH.'bt_pretime.php');

ini_set('upload_max_filesize', bt_config::$conf['max_torrent_size']);
ini_set('memory_limit', '64M');

function bark($msg) {
	genbark($msg, 'Upload failed!');
}

bt_loginout::db_connect(true);

if (get_user_class() < UC_UPLOADER)
  die('go away');

foreach(array('descr','type','name') as $v)
  {
   if (!isset($_POST[$v]))
     bark('missing form data: '.$v);
  }

if (!isset($_FILES['file']))
  bark('missing form data');

$f = $_FILES['file'];
$fname = $f['name'];
if (empty($fname))
  bark('Empty filename!');

$nfofile = $_FILES['nfo'];
$nfofilename = $nfofile['tmp_name'];
$descr = trim($_POST['descr']);

$nfo = str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename));

$desc_nfo = (bool)(0 + $_POST['desc_nfo']);

if ($desc_nfo && $nfo)
	$descr = $nfo;

if (!$descr)
	bark('You must enter a description!');
elseif (isset($_POST['strip']) && $_POST['strip'] == 1)
	$descr = bt_nfo::strip($descr, true);

$descr = bt_utf8::to_utf8($descr);


$catid = (0 + $_POST['type']);
if (!is_valid_id($catid))
	bark('You must select a category to put the torrent in!');

if (!validfilename($fname))
	bark('Invalid filename!');
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
	bark('Invalid filename (not a .torrent).');

$shortfname = $torrent = $matches[1];
if (!empty($_POST['name']))
  $torrent = $_POST['name'];

$tmpname = $f['tmp_name'];
if (!is_uploaded_file($tmpname))
  bark('eek');
if (!filesize($tmpname))
  bark('Empty file!');


$dict = bencdec::decode_file($tmpname, bt_config::$conf['max_torrent_size'], bencdec::OPTION_EXTENDED_VALIDATION);
if ($dict === false)
  bark('What the hell did you upload? This is not a bencoded file!');

if (isset($dict['announce-list']))
  unset($dict['announce-list']);

$dict['info']['private'] = 1;

if (!isset($dict['info']))
	bark('invalid torrent, info dictionary does not exist');

$info = &$dict['info'];
$infohash = sha1(bencdec::encode($info), true);
$hinfohash = bt_string::str2hex($infohash);

if (bencdec::get_type($info) != 'dictionary')
	bark('invalid torrent, info is not a dictionary');

if (!isset($info['name']) || !isset($info['piece length']) || !isset($info['pieces']))
	bark('invalid torrent, missing parts of the info dictionary');

if (bencdec::get_type($info['name']) != 'string' || bencdec::get_type($info['piece length']) != 'integer' || bencdec::get_type($info['pieces']) != 'string')
	bark('invalid torrent, invlaid types in info dictionary');

$dname = $info['name'];
$plen = $info['piece length'];
$pieces_len = strlen($info['pieces']);

if ($pieces_len % 20 != 0)
  bark('invalid pieces');

if ($plen % 4096)
	brak('piece size is not mod(4096), wtf kind of torrent is that?');

$filelist = array();
if (isset($info['length'])) {
	if (bencdec::get_type($info['length']) != 'integer')
		bark('length must be an integer');

	$totallen = $info['length'];
	$filelist[] = array($dname, $totallen);
	$type = 'single';
}
else {
	if (!isset($info['files']))
		bark('missing both length and files');

	if (bencdec::get_type($info['files']) != 'list')
		bark('invalid files, not a list');

	$flist = &$info['files'];

	if (!count($flist))
		bark('no files');
	$totallen = 0;
	foreach ($flist as $fn) {
		if (!isset($fn['length']) || !isset($fn['path']))
			bark('file info not found');

		if (bencdec::get_type($fn['length']) != 'integer' || bencdec::get_type($fn['path']) != 'list')
			bark('invalid file info');

		$ll = $fn['length'];
		$ff = $fn['path'];

		$totallen += $ll;
		$ffa = array();
		foreach ($ff as $ffe) {
			if (bencdec::get_type($ffe) != 'string')
				bark('filename type error');
			$ffa[] = $ffe;
		}
		if (!count($ffa))
			bark('filename error');

		$ffe = implode('/', $ffa);
		$filelist[] = array($ffe, $ll);
	}
	$type = 'multi';
}

$num_pieces = $pieces_len / 20;
$expected_pieces = (int)ceil($totallen / $plen);

if ($num_pieces != $expected_pieces)
	bark('total file size and number of peices do not match');


$mp3cat = 8;
$day0cat = 21;
$xxximgcat = 26;

$owner = ($_POST['anon'] ? 0 : bt_user::$current['id']);
//pretime
$precheck = bt_pretime::get_pretime($torrent);
$pretime = 0 + $precheck['pretime'];
$timeofpre = time() - $precheck['pretime'];
if ($pretime > 0)
	$preline = bt_time::format_elapsed_time($timeofpre, time());

if ($catid == $mp3cat && isset($precheck['genre']))
	$preg = $precheck['genre'];
else
	$preg = '';

$search_text = searchfield("$shortfname $dname $torrent");

$q = 'INSERT INTO torrents (info_hash, nfo, added, last_action, pretime, category, owner, size, numfiles, piece_length, name, filename, search_text, descr, type, visible, genre) '.
	'VALUES ('.implode(', ', array_map('bt_sql::binary_esc', array($infohash, $nfo))).', 'implode(', ', array(bt_vars::$timestamp, bt_vars::$timestamp, $pretime, $catid, $owner,
	$totallen, count($filelist), $plen)).', '.implode(', ', array_map('bt_sql::esc', array($torrent, $fname, $search_text, $descr, $type, 'no', $preg))).')';

$ret = mysql_query($q);
if (!$ret) {
	if (mysql_errno() == 1062)
		bark('torrent already uploaded!');
	bark("mysql puked: ".mysql_error());
}
else {
	bt_memcache::connect();
	bt_mem_caching::remove_torrent($hinfohash);
	$id = mysql_insert_id();
	bt_mem_caching::remove_last_torrents();
	$cat = mysql_query("SELECT name FROM categories WHERE id = \"" . (0 + $_POST["type"]) . "\"");
	$cat = mysql_fetch_assoc($cat);
	$catname = mysql_real_escape_string($cat["name"]);
	$username = (($owner == 0) ? "Anonymous" : bt_user::$current["username"]);
	$url = mysql_real_escape_string(bt_vars::$base_url."/details.php?id={$id}&hit=1");
	$torrent = mysql_real_escape_string($torrent);
	$username = mysql_real_escape_string($username);

	$message = "\x02New Torrent Uploaded\x02:\n".
		"Name....: $torrent\n".
		"Category: $catname".($catid == $mp3cat && $preg != '' ? ' - '.$preg : '')."\n".
		"Size....: ".bt_theme::mksize($totallen)."\n".
		(isset($preline) ? 'Pretime.: '.$preline."\n" : '').
		"URL.....: $url";

	$tracers = '!upload ScT '.$torrent;
	$chan = in_array($catid, array($day0cat, $mp3cat, $xxximgcat)) ? '#sct.spam' : '#scenetorrents';

	mysql_query('INSERT INTO `botannounces` (`target`,`text`) VALUES ("#sct.tracers.spam", '.sqlesc($tracers).')');
	mysql_query('INSERT INTO `botannounces` (`target`,`text`) VALUES ("'.$chan.'", '.sqlesc($message).')');
}

@mysql_query("DELETE FROM files WHERE torrent = $id");
foreach ($filelist as $file) {
	@mysql_query("INSERT INTO files (torrent, filename, size) VALUES ($id, ".sqlesc($file[0]).",".$file[1].")");
}


if ($owner == 0)
  @mysql_query('INSERT INTO `torrents_anon` (`id`, `owner`) VALUES("'.$id.'", "'.bt_user::$current['id'].'")');


$dest = bt_config::$conf['torrent_dir'].'/'.$id.'.torrent';
if (!bencdec::encode_file($dest, $dict))
  bark('Could not properly encode file');
@unlink($tmpname);
chmod($dest, 0664);
write_log('Torrent '.$id.' ('.$torrent.') was uploaded by '.(($owner == 0) ? '[anon]'.bt_user::$current['username'].'[/anon]' : bt_user::$current['username']), 'UPLD');

header('Location: '.bt_vars::$base_url.'/details.php?id='.$id.'&uploaded=1');
die();
?>
