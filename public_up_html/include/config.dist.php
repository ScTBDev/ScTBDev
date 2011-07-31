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

// New Configureation settings script :)
$CONFIG['SITE_ONLINE'] = true;						// Site Up?
$CONFIG['TRACKER_ONLINE'] = true;					// Tracker Up?

$CONFIG['allow_signups'] = false;					// Weather to allow signups or not, set to false for invite only
$CONFIG['max_num_slots'] = 250;						// Mamimum number of seeding/leeching torrents per user
$CONFIG['new_user_delay_days'] = 2;					//
$CONFIG['announce_interval'] = 1800;				// 30 minutes - standard torrent client announce interval
$CONFIG['min_announce_interval'] = 1440;			// 24 minutes - minimum time that a torrent client must wait before reannouncing
$CONFIG['announce_unconn_interval'] = 600;			// 10 mins - announce interval for unconnectable users
$CONFIG['min_announce_unconn_interval'] = 480;		// 6 mins - minimum announce interval for unconnectable users
$CONFIG['announce_interval_fuzz'] = 300;			// 5 mins - max ammount of randomization in either direction of announce invterval
$CONFIG['announce_unconn_interval_fuzz'] = 60;		// 1 min - max ammount of randomization in either direction of announce invterval for unconnectable users
$CONFIG['maxips'] = 5;								// Maxmimum (peer) IPs allowed per account
$CONFIG['max_torrent_size'] = 1048576;				// 1MB - maximum torrent file size
$CONFIG['signup_timeout'] = 259200;					// 3 days - length of time that a user has to verify his email
$CONFIG['max_dead_torrent_time'] = 172800;			// 2 days - amount of time before a torrent goes invisible
$CONFIG['maxusers'] = 15000;						// Max users on site
$CONFIG['torrent_dir'] = '/home/sct/torrents';		// Dir for torrents, without trailing slash (must be writable for web server user)

// Donation related settings
$CONFIG['donate_day'] = 16;							// Day of month that bill is usually paid
$CONFIG['require_donations'] = 1000;				// # of euros needed a month for bills

$CONFIG['nfo_dir'] = '/home/sct/nfos';
$CONFIG['nfo_url'] = '/nfos/';

// Array of memcached servers
$CONFIG['memcache_servers'] = array();
$CONFIG['memcache_servers'][] = array(
	'ip'				=> '10.17.52.16',
	'port'				=> 53760,
	'persistent'		=> true,
	'weight'			=> 1,
	'timeout'			=> 1,
	'retry_interval'	=> 1,
	'status'			=> true
);
// set a prefix on all keys for memcache (allows for multiple sites on the same memcache server)
$CONFIG['memcache_prefix'] = '';


# the first one will be displayed on the pages
$announce_urls = $announce_urls_proxy = $announce_urls_ssl = array();
$announce_urls[] = 'http://tracker.scenetorrents.org:80/announce.php';
$announce_urls_proxy[] = 'http://tracker.scenetorrents.org:25600/announce.php';
$announce_urls_ssl[] = 'https://tracker.scenetorrents.org:443/announce.php';


if (trim($_SERVER['HTTP_HOST']) == "")
  $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];


// Set this to your site URL... No ending slash!
$CONFIG['default_base_url'] = 'http'.($_SERVER['HTTPS'] == 'on' ? 's' : '').'://www.scenetorrents.test';
$CONFIG['default_plain_url'] = 'http://www.scenetorrents.test';
$CONFIG['default_ssl_url'] = 'https://www.scenetorrents.test';

$CONFIG['ssl_only_ccs'] = array('A1','A2','SE');

// set this to true to enable delays
$CONFIG['delays'] = false;

// set this to true to enable limits on unconnectable peers
$CONFIG['limitunconn'] = false;
$CONFIG['unconnlimit'] = 5;

// Email for sender/return path.
$CONFIG['site_name'] = 'SceneTorrents';
$CONFIG['site_email'] = 'SceneTorrents Tracker <noreply@scenetorrents.org>';
$CONFIG['pic_base_url'] = '/pic/';

$CONFIG['default_user_flags'] = 7345036;

$CONFIG['mem_host'] = '127.0.0.1';
$CONFIG['mem_port'] = 11211;

$CONFIG['probe_ip'] = ''; // Tracker IP Port Probe

$CONFIG['hash_types'] = array('sha512','ripemd320','tiger192,4','snefru','gost','haval256,5','whirlpool','salsa20');
?>
