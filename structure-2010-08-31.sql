-- phpMyAdmin SQL Dump
-- version 3.3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2010 at 12:36 AM
-- Server version: 5.1.48
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `sct_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `who` int(10) unsigned NOT NULL,
  `time` bigint(20) NOT NULL,
  `first` binary(16) NOT NULL,
  `last` binary(16) NOT NULL,
  `expires` bigint(20) DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_range` (`first`,`last`),
  KEY `who` (`who`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE IF NOT EXISTS `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `blockid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userfriend` (`userid`,`blockid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `botannounces`
--

CREATE TABLE IF NOT EXISTS `botannounces` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('privmsg','notice','invite') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'privmsg',
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `cheat_disables`
--

CREATE TABLE IF NOT EXISTS `cheat_disables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `peer_identity` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_agent` (`peer_identity`,`user_agent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` bigint(20) NOT NULL DEFAULT '0',
  `edits` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments_edits`
--

CREATE TABLE IF NOT EXISTS `comments_edits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `commentid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `commentid` (`commentid`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE IF NOT EXISTS `donations` (
  `transid` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `status` enum('Waiting','Pending','Completed','Refunded','Reversed','Failed','Denied') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Waiting',
  `txn_id` varchar(17) COLLATE utf8_unicode_ci NOT NULL,
  `item` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `amount` float NOT NULL,
  `last_update` bigint(20) NOT NULL,
  `ip` binary(16) NOT NULL,
  `realip` binary(16) NOT NULL,
  PRIMARY KEY (`transid`),
  KEY `userid` (`userid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `don_attempt`
--

CREATE TABLE IF NOT EXISTS `don_attempt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_name` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `item_number` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `payment_status` enum('Canceled-Reversal','Completed','Denied','Expired','Failed','In-Progress','Partially-Refunded','Pending','Processed','Refunded','Reversed','Voided') COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_amount` float NOT NULL,
  `payment_currency` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `payment_fee` float NOT NULL,
  `txn_id` varchar(17) COLLATE utf8_unicode_ci NOT NULL,
  `txn_type` enum('cart','send_money','web_accept') COLLATE utf8_unicode_ci DEFAULT NULL,
  `receiver_email` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `payer_email` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `first_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `payer_status` enum('verified','unverified') COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_type` enum('instant','echeck') COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_date` bigint(20) NOT NULL,
  `post` text COLLATE utf8_unicode_ci NOT NULL,
  `verified` enum('yes','no','fake') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `realip` binary(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `payment_status` (`payment_status`,`txn_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `email_changes`
--

CREATE TABLE IF NOT EXISTS `email_changes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `newemail` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ip` binary(16) NOT NULL,
  `realip` binary(16) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `torrent` (`torrent`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `firstline`
--

CREATE TABLE IF NOT EXISTS `firstline` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `lang` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `helpwith` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forums`
--

CREATE TABLE IF NOT EXISTS `forums` (
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `minclassread` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `minclasswrite` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `postcount` int(10) unsigned NOT NULL DEFAULT '0',
  `topiccount` int(10) unsigned NOT NULL DEFAULT '0',
  `minclasscreate` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `lasttopic` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`,`name`),
  KEY `minclass` (`minclassread`,`minclasswrite`,`minclasscreate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE IF NOT EXISTS `friends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `friendid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userfriend` (`userid`,`friendid`),
  KEY `friendid` (`friendid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invites`
--

CREATE TABLE IF NOT EXISTS `invites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `inviteid` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inviteid` (`inviteid`) USING HASH,
  KEY `userid` (`userid`),
  KEY `added` (`added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ips`
--

CREATE TABLE IF NOT EXISTS `ips` (
  `ip` binary(16) NOT NULL,
  `dns` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_update` bigint(20) NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `irc_seen`
--

CREATE TABLE IF NOT EXISTS `irc_seen` (
  `nick` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `time` bigint(20) NOT NULL,
  `type` enum('msg','action','quit','join','part','kick','topic') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'msg',
  `where` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `data` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`nick`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `subject` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `msg` text COLLATE utf8_unicode_ci NOT NULL,
  `unread` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `location` enum('in','out','both') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'in',
  PRIMARY KEY (`id`),
  KEY `location` (`location`),
  KEY `unread_messages` (`receiver`,`location`,`unread`),
  KEY `sender` (`sender`,`location`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `peers`
--

CREATE TABLE IF NOT EXISTS `peers` (
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `peer_id` binary(20) NOT NULL DEFAULT '                    ',
  `ip` binary(16) NOT NULL,
  `ip2` binary(16) DEFAULT NULL,
  `realip` binary(16) NOT NULL,
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `port6` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `to_go` bigint(20) unsigned NOT NULL DEFAULT '0',
  `started` bigint(20) NOT NULL DEFAULT '0',
  `last_action` bigint(20) NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `clientid` int(10) unsigned NOT NULL DEFAULT '0',
  `finishedat` bigint(20) NOT NULL DEFAULT '0',
  `downloadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `uploadoffset` bigint(20) unsigned NOT NULL DEFAULT '0',
  `flags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `compact4` binary(6) DEFAULT NULL,
  `compact6` binary(18) DEFAULT NULL,
  PRIMARY KEY (`torrent`,`peer_id`) USING HASH,
  UNIQUE KEY `torrent_flags` (`torrent`,`flags`) USING BTREE,
  KEY `userid` (`userid`),
  KEY `last_action` (`last_action`) USING BTREE,
  KEY `flags` (`flags`) USING BTREE,
  KEY `userid_flags` (`userid`,`flags`) USING BTREE,
  KEY `ip` (`ip`) USING BTREE,
  KEY `ip2` (`ip2`) USING BTREE,
  KEY `realip` (`realip`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=binary PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Table structure for table `pollanswers`
--

CREATE TABLE IF NOT EXISTS `pollanswers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `selection` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `selection` (`selection`),
  KEY `userid` (`userid`),
  KEY `pollid_selection` (`pollid`,`selection`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `polls`
--

CREATE TABLE IF NOT EXISTS `polls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `option0` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option1` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option2` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option3` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option4` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option5` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option6` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option7` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option8` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option9` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option10` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option11` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option12` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option13` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option14` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option15` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option16` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option17` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option18` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `option19` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sort` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topicid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `editedby` int(10) unsigned NOT NULL DEFAULT '0',
  `editedat` bigint(20) NOT NULL DEFAULT '0',
  `edits` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`),
  KEY `added` (`added`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`),
  FULLTEXT KEY `body` (`body`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts_edits`
--

CREATE TABLE IF NOT EXISTS `posts_edits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `postid` (`postid`),
  KEY `added` (`added`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `readposts`
--

CREATE TABLE IF NOT EXISTS `readposts` (
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `topicid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpostread` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`,`topicid`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_users`
--

CREATE TABLE IF NOT EXISTS `report_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `byuser` int(10) unsigned NOT NULL DEFAULT '0',
  `handled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `reason` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `handled` (`handled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` binary(16) NOT NULL,
  `realip` binary(16) NOT NULL,
  `time` bigint(20) NOT NULL DEFAULT '0',
  `lastaction` bigint(20) NOT NULL DEFAULT '0',
  `maxage` int(10) unsigned NOT NULL DEFAULT '7776000',
  `maxidle` int(10) NOT NULL DEFAULT '604800',
  `flags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`user`),
  KEY `lastaction` (`lastaction`,`maxidle`),
  KEY `added` (`time`,`maxage`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `signupbans`
--

CREATE TABLE IF NOT EXISTS `signupbans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `who` int(10) unsigned NOT NULL,
  `time` bigint(20) NOT NULL,
  `first` binary(16) NOT NULL,
  `last` binary(16) NOT NULL,
  `expires` bigint(20) DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_range` (`first`,`last`),
  KEY `who` (`who`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sitelog`
--

CREATE TABLE IF NOT EXISTS `sitelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `txt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `txt` (`txt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `snatched`
--

CREATE TABLE IF NOT EXISTS `snatched` (
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `start_time` bigint(20) NOT NULL DEFAULT '0',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `last_time` bigint(20) NOT NULL DEFAULT '0',
  `seed_time` int(10) unsigned NOT NULL DEFAULT '0',
  `total_time` int(10) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ip` binary(16) DEFAULT NULL,
  `ip2` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `last_action` enum('Start','Leech','Seed','Stop','Complete','Ghost') COLLATE utf8_unicode_ci DEFAULT NULL,
  `clientid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`torrent`,`user`),
  KEY `user` (`user`,`last_time`),
  KEY `last_time` (`last_time`),
  KEY `clientid` (`clientid`),
  KEY `ip` (`ip`),
  KEY `ip2` (`ip2`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stafflog`
--

CREATE TABLE IF NOT EXISTS `stafflog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `txt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `txt` (`txt`(20))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `svn_log`
--

CREATE TABLE IF NOT EXISTS `svn_log` (
  `revision` int(10) unsigned NOT NULL,
  `time` bigint(20) NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`revision`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `locked` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `forumid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `posts` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `subject` (`subject`),
  KEY `forumid` (`forumid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topten_countries`
--

CREATE TABLE IF NOT EXISTS `topten_countries` (
  `subtype` enum('avg','r','ul','us') COLLATE utf8_unicode_ci NOT NULL,
  `rank` mediumint(2) unsigned NOT NULL DEFAULT '0',
  `flagpic` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `data` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`subtype`,`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topten_peers`
--

CREATE TABLE IF NOT EXISTS `topten_peers` (
  `subtype` enum('dl','ul','uml','ums','upt','urs') COLLATE utf8_unicode_ci NOT NULL,
  `rank` mediumint(3) NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `anon` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `data1` bigint(20) unsigned NOT NULL DEFAULT '0',
  `data2` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`subtype`,`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topten_torrents`
--

CREATE TABLE IF NOT EXISTS `topten_torrents` (
  `subtype` enum('act','bse','mcm','mdt','sna','wse') COLLATE utf8_unicode_ci NOT NULL,
  `rank` mediumint(2) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `snatched` int(10) unsigned NOT NULL DEFAULT '0',
  `seeders` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0',
  `banned` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `data` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`subtype`,`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `topten_users`
--

CREATE TABLE IF NOT EXISTS `topten_users` (
  `subtype` enum('bsh','dl','dls','ul','uls','wsh') COLLATE utf8_unicode_ci NOT NULL,
  `rank` mediumint(3) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `upspeed` int(10) unsigned NOT NULL DEFAULT '0',
  `downspeed` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`subtype`,`rank`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `torrents`
--

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` binary(20) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `search_text` text COLLATE utf8_unicode_ci NOT NULL,
  `descr` text COLLATE utf8_unicode_ci NOT NULL,
  `category` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `numfiles` int(10) unsigned NOT NULL DEFAULT '0',
  `piece_length` int(10) unsigned NOT NULL DEFAULT '0',
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `times_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` smallint(5) unsigned NOT NULL DEFAULT '0',
  `seeders` smallint(5) unsigned NOT NULL DEFAULT '0',
  `last_action` bigint(20) DEFAULT NULL,
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  `nfo` mediumblob,
  `pretime` bigint(20) DEFAULT NULL,
  `genre` smallint(5) unsigned DEFAULT NULL,
  `flags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `owner` (`owner`),
  KEY `size` (`size`),
  KEY `times_completed` (`times_completed`),
  KEY `category` (`category`),
  KEY `leechers` (`leechers`),
  KEY `added` (`added`),
  KEY `comments` (`comments`),
  KEY `seeders` (`seeders`),
  KEY `numfiles` (`numfiles`),
  KEY `genre` (`genre`),
  FULLTEXT KEY `ft_search` (`search_text`,`descr`),
  FULLTEXT KEY `clean_name` (`search_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `torrents_anon`
--

CREATE TABLE IF NOT EXISTS `torrents_anon` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `owner` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaders`
--

CREATE TABLE IF NOT EXISTS `uploaders` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `contacttype` enum('msn','yahoo','aim','icq','irc','none') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'msn',
  `contact` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `uploadtype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bandwidth` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `last_login` bigint(20) NOT NULL DEFAULT '0',
  `last_access` bigint(20) NOT NULL DEFAULT '0',
  `editsecret` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `ip_access` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `class` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `payed_uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeding` smallint(3) unsigned NOT NULL DEFAULT '0',
  `leeching` smallint(3) unsigned NOT NULL DEFAULT '0',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country` tinyint(3) unsigned NOT NULL DEFAULT '105',
  `timezone` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UTC',
  `notifs` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modcomment` text COLLATE utf8_unicode_ci NOT NULL,
  `warneduntil` bigint(20) NOT NULL DEFAULT '0',
  `torrentsperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `topicsperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `postsperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_browse` bigint(20) NOT NULL DEFAULT '0',
  `inbox_new` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sentbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `comments` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `posts` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `last_forum_visit` bigint(20) NOT NULL DEFAULT '0',
  `passkey` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `invites` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `invitedby` int(10) unsigned NOT NULL DEFAULT '0',
  `flags` bigint(20) unsigned NOT NULL DEFAULT '7345036',
  `chans` bigint(20) unsigned NOT NULL DEFAULT '32132355443392545',
  `donations` float NOT NULL,
  `irc_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `passkey` (`passkey`),
  UNIQUE KEY `email` (`email`),
  KEY `class` (`class`),
  KEY `last_forum_visit` (`last_forum_visit`),
  KEY `ratio` (`uploaded`,`downloaded`),
  KEY `last_access` (`last_access`),
  KEY `deadtime` (`added`,`last_login`),
  KEY `invitedby` (`invitedby`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`),
  KEY `added` (`added`),
  KEY `country` (`country`) USING HASH
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_deleted`
--

CREATE TABLE IF NOT EXISTS `users_deleted` (
  `id` int(10) unsigned NOT NULL,
  `username` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `last_login` bigint(20) NOT NULL DEFAULT '0',
  `last_access` bigint(20) NOT NULL DEFAULT '0',
  `editsecret` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  `ip` binary(16) DEFAULT NULL,
  `realip` binary(16) DEFAULT NULL,
  `ip_access` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `class` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `payed_uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeding` smallint(3) unsigned NOT NULL DEFAULT '0',
  `leeching` smallint(3) unsigned NOT NULL DEFAULT '0',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `timezone` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UTC',
  `notifs` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modcomment` text COLLATE utf8_unicode_ci NOT NULL,
  `warneduntil` bigint(20) NOT NULL DEFAULT '0',
  `torrentsperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `topicsperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `postsperpage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_browse` bigint(20) NOT NULL DEFAULT '0',
  `inbox_new` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sentbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `comments` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `posts` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `last_forum_visit` bigint(20) NOT NULL DEFAULT '0',
  `passkey` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `invites` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `invitedby` int(10) unsigned NOT NULL DEFAULT '0',
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  `chans` bigint(20) unsigned NOT NULL DEFAULT '0',
  `donations` float NOT NULL,
  `irc_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `flags` (`flags`),
  KEY `username` (`username`),
  KEY `ip` (`ip`),
  KEY `realip` (`realip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`) VALUES
(1, 'Applications', 'appz.png'),
(2, 'Movies/XViD', 'movies_xvid.png'),
(3, 'Movies/Other', 'movies_other.png'),
(4, 'Movies/DVDR', 'movies_dvdr.png'),
(5, 'Games/PC', 'games_pc.png'),
(6, 'Games/PSP/PS2', 'games_psp_ps2.png'),
(7, 'Games/Other', 'games_other.png'),
(8, 'Music/MP3', 'music_mp3.png'),
(9, 'Movies/x264', 'movies_x264.png'),
(10, 'DOX', 'dox.png'),
(11, 'TV/XViD', 'tv_xvid.png'),
(12, 'Games/Xbox 360', 'games_xbox360.png'),
(13, 'XXX', 'xxx_xvid.png'),
(14, 'Anime', 'anime.png'),
(15, 'TV/x264', 'tv_x264.png'),
(16, 'MiSC', 'misc.png'),
(17, 'TV/Packs', 'tv_packs.png'),
(18, 'Music/Videos', 'music_videos.png'),
(19, 'Movies/WMV', 'movies_wmv.png'),
(20, 'Games/Wii', 'games_wii.png'),
(21, '0DAY', '0day.png'),
(22, 'TV/DVDRip', 'tv_dvdrip.png'),
(23, 'TV/DVDR', 'tv_dvdr.png'),
(24, 'TV/HR', 'tv_hr.png'),
(25, 'Movies/Packs', 'movies_packs.png'),
(26, 'XXX/IMGSETS', 'xxx_img_sets.png'),
(27, 'XXX/HD', 'xxx_hd.png'),
(28, 'Music/Packs', 'music_packs.png');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `cc` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ccc` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `flagpic` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=209 ;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `cc`, `ccc`, `name`, `flagpic`) VALUES
(54, 'AF', 'AFG', 'Afghanistan', 'afghanistan.png'),
(65, 'AL', 'ALB', 'Albania', 'albania.png'),
(35, 'DZ', 'DZA', 'Algeria', 'algeria.png'),
(68, 'AD', 'AND', 'Andorra', 'andorra.png'),
(36, 'AO', 'AGO', 'Angola', 'angola.png'),
(89, 'AG', 'ATG', 'Antigua Barbuda', 'antiguabarbuda.png'),
(19, 'AR', 'ARG', 'Argentina', 'argentina.png'),
(109, 'AM', 'ARM', 'Armenia', 'armenia.png'),
(20, 'AU', 'AUS', 'Australia', 'australia.png'),
(37, 'AT', 'AUT', 'Austria', 'austria.png'),
(110, 'AZ', 'AZE', 'Azerbaijan', 'azerbaijan.png'),
(108, NULL, 'PRT', 'Azores', 'azores.png'),
(82, 'BS', 'BHS', 'Bahamas', 'bahamas.png'),
(111, 'BH', 'BHR', 'Bahrain', 'bahrain.png'),
(86, 'BD', 'BGD', 'Bangladesh', 'bangladesh.png'),
(85, 'BB', 'BRB', 'Barbados', 'barbados.png'),
(112, 'BY', 'BLR', 'Belarus', 'belarus.png'),
(16, 'BE', 'BEL', 'Belgium', 'belgium.png'),
(34, 'BZ', 'BLZ', 'Belize', 'belize.png'),
(113, 'BJ', 'BEN', 'Benin', 'benin.png'),
(114, 'BT', 'BTN', 'Bhutan', 'bhutan.png'),
(115, 'BO', 'BOL', 'Bolivia', 'bolivia.png'),
(67, 'BA', 'BIH', 'Bosnia Herzegovina', 'bosniaherzegovina.png'),
(116, 'BW', 'BWA', 'Botswana', 'bostwana.png'),
(18, 'BR', 'BRA', 'Brazil', 'brazil.png'),
(117, 'BN', 'BRN', 'Brunei', 'brunei.png'),
(104, 'BG', 'BGR', 'Bulgaria', 'bulgaria.png'),
(60, 'BF', 'BFA', 'Burkina Faso', 'burkinafaso.png'),
(118, 'MM', 'MMR', 'Burma/Myanmar', 'burma-myanmar.png'),
(119, 'BI', 'BDI', 'Burundi', 'burundi.png'),
(84, 'KH', 'KHM', 'Cambodia', 'cambodia.png'),
(120, 'CM', 'CMR', 'Cameroon', 'cameroon.png'),
(5, 'CA', 'CAN', 'Canada', 'canada.png'),
(121, 'CV', 'CPV', 'Cape Verde', 'capeverde.png'),
(122, 'CF', 'CAF', 'Central African Republic', 'centralafrica.png'),
(123, 'TD', 'TCD', 'Chad', 'chad.png'),
(51, 'CL', 'CHL', 'Chile', 'chile.png'),
(8, 'CN', 'CHN', 'China', 'china.png'),
(99, 'CO', 'COL', 'Colombia', 'colombia.png'),
(124, 'KM', 'COM', 'Comoros', 'comoros.png'),
(53, 'CG', 'COG', 'Congo', 'congo.png'),
(102, 'CR', 'CRI', 'Costa Rica', 'costarica.png'),
(125, 'CI', 'CIV', 'Côte d''Ivoire', 'cotedivoire.png'),
(97, 'HR', 'HRV', 'Croatia', 'croatia.png'),
(52, 'CU', 'CUB', 'Cuba', 'cuba.png'),
(126, 'CY', 'CYP', 'Cyprus', 'cyprus.png'),
(46, 'CZ', 'CZE', 'Czech Republic', 'czechrepublic.png'),
(10, 'DK', 'DNK', 'Denmark', 'denmark.png'),
(127, 'DJ', 'DJI', 'Djibouti', 'djibouti.png'),
(128, 'DM', 'DMA', 'Dominica', 'dominica.png'),
(41, 'DO', 'DOM', 'Dominican Republic', 'dominicanrep.png'),
(81, 'EC', 'ECU', 'Ecuador', 'ecuador.png'),
(103, 'EG', 'EGY', 'Egypt', 'egypt.png'),
(129, 'SV', 'SLV', 'El Salvador', 'elsalvador.png'),
(106, NULL, 'GBR', 'England', 'england.png'),
(130, 'GQ', 'GNQ', 'Equatorial Guinea', 'equatorialguinea.png'),
(131, 'ER', 'ERI', 'Eritrea', 'eritrea.png'),
(98, 'EE', 'EST', 'Estonia', 'estonia.png'),
(132, 'ET', 'ETH', 'Ethiopia', 'ethiopia.png'),
(206, 'EU', 'EUN', 'European Union', 'europeanunion.png'),
(133, 'FJ', 'FJI', 'Fiji', 'fiji.png'),
(4, 'FI', 'FIN', 'Finland', 'finland.png'),
(6, 'FR', 'FRA', 'France', 'france.png'),
(134, 'GA', 'GAB', 'Gabon', 'gabon.png'),
(135, 'GM', 'GMB', 'Gambia', 'gambia.png'),
(136, 'GE', 'GEO', 'Georgia', 'georgia.png'),
(7, 'DE', 'DEU', 'Germany', 'germany.png'),
(137, 'GH', 'GHA', 'Ghana', 'ghana.png'),
(42, 'GR', 'GRC', 'Greece', 'greece.png'),
(138, 'GD', 'GRD', 'Grenada', 'grenada.png'),
(43, 'GT', 'GTM', 'Guatemala', 'guatemala.png'),
(139, 'GN', 'GIN', 'Guinea', 'guinea.png'),
(140, 'GW', 'GNB', 'Guinea-Bissau', 'guineabissau.png'),
(141, 'GY', 'GUY', 'Guyana', 'guyana.png'),
(142, 'HT', 'HTI', 'Haiti', 'haiti.png'),
(143, 'VA', 'VAT', 'Holy See (Vatican City State)', 'holysee.png'),
(79, 'HN', 'HND', 'Honduras', 'honduras.png'),
(33, 'HK', 'HKG', 'Hong Kong', 'hongkong.png'),
(74, 'HU', 'HUN', 'Hungary', 'hungary.png'),
(62, 'IS', 'ISL', 'Iceland', 'iceland.png'),
(70, 'IN', 'IND', 'India', 'india.png'),
(144, 'ID', 'IDN', 'Indonesia', 'indonesia.png'),
(145, 'IR', 'IRN', 'Iran', 'iran.png'),
(146, 'IQ', 'IRQ', 'Iraq', 'iraq.png'),
(13, 'IE', 'IRL', 'Ireland', 'ireland.png'),
(105, 'O1', 'O1', 'Isla de Muerte', 'islademuerte.png'),
(44, 'IL', 'ISR', 'Israel', 'israel.png'),
(9, 'IT', 'ITA', 'Italy', 'italy.png'),
(31, 'JM', 'JAM', 'Jamaica', 'jamaica.png'),
(17, 'JP', 'JPN', 'Japan', 'japan.png'),
(147, 'JO', 'JOR', 'Jordan', 'jordan.png'),
(148, 'KZ', 'KAZ', 'Kazakhstan', 'kazakhstan.png'),
(107, 'KE', 'KEN', 'Kenya', 'kenya.png'),
(58, 'KI', 'KIR', 'Kiribati', 'kiribati.png'),
(149, 'KW', 'KWT', 'Kuwait', 'kuwait.png'),
(80, 'KG', 'KGZ', 'Kyrgyzstan', 'kyrgyzstan.png'),
(87, 'LA', 'LAO', 'Laos', 'laos.png'),
(101, 'LV', 'LVA', 'Latvia', 'latvia.png'),
(100, 'LB', 'LBN', 'Lebanon', 'lebanon.png'),
(150, 'LS', 'LSO', 'Lesotho', 'lesotho.png'),
(151, 'LR', 'LBR', 'Liberia', 'liberia.png'),
(152, 'LY', 'LBY', 'Libyan', 'libya.png'),
(153, 'LI', 'LIE', 'Liechtenstein', 'liechtenstein.png'),
(69, 'LT', 'LTU', 'Lithuania', 'lithuania.png'),
(32, 'LU', 'LUX', 'Luxembourg', 'luxembourg.png'),
(154, 'MK', 'MKD', 'Macedonia', 'macedonia.png'),
(155, 'MG', 'MDG', 'Madagascar', 'madagascar.png'),
(156, 'MW', 'MWI', 'Malawi', 'malawi.png'),
(40, 'MY', 'MYS', 'Malaysia', 'malaysia.png'),
(157, 'MV', 'MDV', 'Maldives', 'maldives.png'),
(158, 'ML', 'MLI', 'Mali', 'mali.png'),
(159, 'MT', 'MLT', 'Malta', 'malta.png'),
(160, 'MH', 'MHL', 'Marshall Islands', 'marshallislands.png'),
(161, 'MR', 'MRT', 'Mauritania', 'mauritania.png'),
(162, 'MU', 'MUS', 'Mauritius', 'mauritius.png'),
(25, 'MX', 'MEX', 'Mexico', 'mexico.png'),
(163, 'FM', 'FSM', 'Micronesia', 'micronesia.png'),
(164, 'MD', 'MDA', 'Moldova', 'moldova.png'),
(165, 'MC', 'MCO', 'Monaco', 'monaco.png'),
(166, 'MN', 'MNG', 'Mongolia', 'mongolia.png'),
(167, 'ME', 'MNE', 'Montenegro', 'montenegro.png'),
(168, 'MA', 'MAR', 'Morocco', 'morocco.png'),
(169, 'MZ', 'MOZ', 'Mozambique', 'mozambique.png'),
(170, 'NA', 'NAM', 'Namibia', 'namibia.png'),
(63, 'NR', 'NRU', 'Nauru', 'nauru.png'),
(171, 'NP', 'NPL', 'Nepal', 'nepal.png'),
(15, 'NL', 'NLD', 'Netherlands', 'netherlands.png'),
(71, 'AN', 'ANT', 'Netherlands Antilles', 'netherlandsantilles.png'),
(21, 'NZ', 'NZL', 'New Zealand', 'newzealand.png'),
(172, 'NI', 'NIC', 'Nicaragua', 'nicaragua.png'),
(173, 'NE', 'NER', 'Niger', 'niger.png'),
(61, 'NG', 'NGA', 'Nigeria', 'nigeria.png'),
(96, 'KP', 'PRK', 'North Korea', 'northkorea.png'),
(205, NULL, 'GBR', 'Northern Ireland', 'unitedkingdom.png'),
(11, 'NO', 'NOR', 'Norway', 'norway.png'),
(174, 'OM', 'OMN', 'Oman', 'oman.png'),
(45, 'PK', 'PAK', 'Pakistan', 'pakistan.png'),
(175, 'PW', 'PLW', 'Palau', 'palau.png'),
(176, 'PA', 'PAN', 'Panama', 'panama.png'),
(177, 'PG', 'PNG', 'Papua New Guinea', 'papuanewguinea.png'),
(90, 'PY', 'PRY', 'Paraguay', 'paraguay.png'),
(83, 'PE', 'PER', 'Peru', 'peru.png'),
(59, 'PH', 'PHL', 'Philippines', 'philippines.png'),
(14, 'PL', 'POL', 'Poland', 'poland.png'),
(24, 'PT', 'PRT', 'Portugal', 'portugal.png'),
(50, 'PR', 'PRI', 'Puerto Rico', 'puertorico.png'),
(178, 'QA', 'QAT', 'Qatar', 'qatar.png'),
(75, 'RO', 'ROU', 'Romania', 'romania.png'),
(3, 'RU', 'RUS', 'Russia', 'russia.png'),
(179, 'RW', 'RWA', 'Rwanda', 'rwanda.png'),
(180, 'KN', 'KNA', 'Saint Kitts and Nevis', 'saintkittsandnevis.png'),
(181, 'LC', 'LCA', 'Saint Lucia', 'saintlucia.png'),
(182, 'VC', 'VCT', 'Saint Vincent and the Grenadines', 'saintvincentandthegrenadine.png'),
(183, 'SM', 'SMR', 'San Marino', 'sanmarino.png'),
(184, 'ST', 'STP', 'Sao Tome and Principe', 'saotomeandprincipe.png'),
(185, 'SA', 'SAU', 'Saudi Arabia', 'saudiarabia.png'),
(208, NULL, 'GBR', 'Scotland', 'scotland.png'),
(94, 'SN', 'SEN', 'Senegal', 'senegal.png'),
(47, 'RS', 'SRB', 'Serbia', 'serbia.png'),
(48, 'SC', 'SYC', 'Seychelles', 'seychelles.png'),
(186, 'SL', 'SLE', 'Sierra Leone', 'sierraleone.png'),
(26, 'SG', 'SGP', 'Singapore', 'singapore.png'),
(187, 'SK', 'SVK', 'Slovakia', 'slovakia.png'),
(64, 'SI', 'SVN', 'Slovenia', 'slovenia.png'),
(188, 'SB', 'SLB', 'Solomon Islands', 'solomonislands.png'),
(189, 'SO', 'SOM', 'Somalia', 'somalia.png'),
(29, 'ZA', 'ZAF', 'South Africa', 'southafrica.png'),
(30, 'KR', 'KOR', 'South Korea', 'southkorea.png'),
(23, 'ES', 'ESP', 'Spain', 'spain.png'),
(190, 'LK', 'LKA', 'Sri Lanka', 'srilanka.png'),
(191, 'SD', 'SDN', 'Sudan', 'sudan.png'),
(192, 'SR', 'SUR', 'Suriname', 'suriname.png'),
(1, 'SE', 'SWE', 'Sweden', 'sweden.png'),
(57, 'CH', 'CHE', 'Switzerland', 'switzerland.png'),
(193, 'SY', 'SRY', 'Syria', 'syria.png'),
(49, 'TW', 'TWN', 'Taiwan', 'taiwan.png'),
(194, 'TJ', 'TJK', 'Tajikistan', 'tajikistan.png'),
(195, 'TZ', 'TZA', 'Tanzania', 'tanzania.png'),
(93, 'TH', 'THA', 'Thailand', 'thailand.png'),
(196, 'TL', 'TLS', 'Timor-Leste', 'timor-leste.png'),
(95, 'TG', 'TGO', 'Togo', 'togo.png'),
(197, 'TO', 'TON', 'Tonga', 'tonga.png'),
(78, 'TT', 'TTO', 'Trinidad & Tobago', 'trinidadandtobago.png'),
(198, 'TN', 'TUN', 'Tunisia', 'tunisia.png'),
(55, 'TR', 'TUR', 'Turkey', 'turkey.png'),
(66, 'TM', 'TKM', 'Turkmenistan', 'turkmenistan.png'),
(199, 'TV', 'TUV', 'Tuvalu', 'tuvalu.png'),
(200, 'UG', 'UGA', 'Uganda', 'uganda.png'),
(72, 'UA', 'UKR', 'Ukraine', 'ukraine.png'),
(201, 'AE', 'ARE', 'United Arab Emirates', 'unitedarabemirates.png'),
(12, 'GB', 'GBR', 'United Kingdom', 'unitedkingdom.png'),
(2, 'US', 'USA', 'United States', 'unitedstates.png'),
(88, 'UY', 'URY', 'Uruguay', 'uruguay.png'),
(56, 'UZ', 'UZB', 'Uzbekistan', 'uzbekistan.png'),
(76, 'VU', 'VUT', 'Vanuatu', 'vanatu.png'),
(73, 'VE', 'VEN', 'Venezuela', 'venezuela.png'),
(77, 'VN', 'VNM', 'Vietnam', 'vietnam.png'),
(207, NULL, 'GBR', 'Wales', 'wales.png'),
(39, 'WS', 'WSM', 'Western Samoa', 'westernsaoma.png'),
(202, 'YE', 'YEM', 'Yemen', 'yemen.png'),
(38, 'YU', 'YUG', 'Yugoslavia', 'yugoslavia.png'),
(203, 'ZM', 'ZMB', 'Zambia', 'zambia.png'),
(204, 'ZW', 'ZWE', 'Zimbabwe', 'zimbabwe.png');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE IF NOT EXISTS `genres` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id3` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id3` (`id3`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=183 ;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `id3`, `name`) VALUES
(1, 0, 'Blues'),
(2, 1, 'Classic Rock'),
(3, 2, 'Country'),
(4, 3, 'Dance'),
(5, 4, 'Disco'),
(6, 5, 'Funk'),
(7, 6, 'Grunge'),
(8, 7, 'Hip-Hop'),
(9, 8, 'Jazz'),
(10, 9, 'Metal'),
(11, 10, 'New Age'),
(12, 11, 'Oldies'),
(13, 12, 'Other'),
(14, 13, 'Pop'),
(15, 14, 'R&B'),
(16, 15, 'Rap'),
(17, 16, 'Reggae'),
(18, 17, 'Rock'),
(19, 18, 'Techno'),
(20, 19, 'Industrial'),
(21, 20, 'Alternative'),
(22, 21, 'Ska'),
(23, 22, 'Death Metal'),
(24, 23, 'Pranks'),
(25, 24, 'Soundtrack'),
(26, 25, 'Euro-Techno'),
(27, 26, 'Ambient'),
(28, 27, 'Trip-Hop'),
(29, 28, 'Vocal'),
(30, 29, 'Jazz+Funk'),
(31, 30, 'Fusion'),
(32, 31, 'Trance'),
(33, 32, 'Classical'),
(34, 33, 'Instrumental'),
(35, 34, 'Acid'),
(36, 35, 'House'),
(37, 36, 'Game'),
(38, 37, 'Sound Clip'),
(39, 38, 'Gospel'),
(40, 39, 'Noise'),
(41, 40, 'Alternative Rock'),
(42, 41, 'Bass'),
(43, 42, 'Soul'),
(44, 43, 'Punk'),
(45, 44, 'Space'),
(46, 45, 'Meditative'),
(47, 46, 'Instrumental Pop'),
(48, 47, 'Instrumental Rock'),
(49, 48, 'Ethnic'),
(50, 49, 'Gothic'),
(51, 50, 'Darkwave'),
(52, 51, 'Techno-Industrial'),
(53, 52, 'Electronic'),
(54, 53, 'Pop-Folk'),
(55, 54, 'Eurodance'),
(56, 55, 'Dream'),
(57, 56, 'Southern Rock'),
(58, 57, 'Comedy'),
(59, 58, 'Cult'),
(60, 59, 'Gangsta Rap'),
(61, 60, 'Top 40'),
(62, 61, 'Christian Rap'),
(63, 62, 'Pop/Funk'),
(64, 63, 'Jungle'),
(65, 64, 'Native American'),
(66, 65, 'Cabaret'),
(67, 66, 'New Wave'),
(68, 67, 'Psychedelic'),
(69, 68, 'Rave'),
(70, 69, 'Showtunes'),
(71, 70, 'Trailer'),
(72, 71, 'Lo-Fi'),
(73, 72, 'Tribal'),
(74, 73, 'Acid Punk'),
(75, 74, 'Acid Jazz'),
(76, 75, 'Polka'),
(77, 76, 'Retro'),
(78, 77, 'Musical'),
(79, 78, 'Rock & Roll'),
(80, 79, 'Hard Rock'),
(81, 80, 'Folk'),
(82, 81, 'Folk/Rock'),
(83, 82, 'National Folk'),
(84, 83, 'Swing'),
(85, 84, 'Fast-Fusion'),
(86, 85, 'Bebop'),
(87, 86, 'Latin'),
(88, 87, 'Revival'),
(89, 88, 'Celtic'),
(90, 89, 'Bluegrass'),
(91, 90, 'Avantgarde'),
(92, 91, 'Gothic Rock'),
(93, 92, 'Progressive Rock'),
(94, 93, 'Psychedelic Rock'),
(95, 94, 'Symphonic Rock'),
(96, 95, 'Slow Rock'),
(97, 96, 'Big Band'),
(98, 97, 'Chorus'),
(99, 98, 'Easy Listening'),
(100, 99, 'Acoustic'),
(101, 100, 'Humour'),
(102, 101, 'Speech'),
(103, 102, 'Chanson'),
(104, 103, 'Opera'),
(105, 104, 'Chamber Music'),
(106, 105, 'Sonata'),
(107, 106, 'Symphony'),
(108, 107, 'Booty Bass'),
(109, 108, 'Primus'),
(110, 109, 'Porn Groove'),
(111, 110, 'Satire'),
(112, 111, 'Slow Jam'),
(113, 112, 'Club'),
(114, 113, 'Tango'),
(115, 114, 'Samba'),
(116, 115, 'Folklore'),
(117, 116, 'Ballad'),
(118, 117, 'Power Ballad'),
(119, 118, 'Rhythmic Soul'),
(120, 119, 'Freestyle'),
(121, 120, 'Duet'),
(122, 121, 'Punk Rock'),
(123, 122, 'Drum Solo'),
(124, 123, 'A Cappella'),
(125, 124, 'Euro-House'),
(126, 125, 'Dance Hall'),
(127, 126, 'Goa'),
(128, 127, 'Drum & Bass'),
(129, 128, 'Club-House'),
(130, 129, 'Hardcore'),
(131, 130, 'Terror'),
(132, 131, 'Indie'),
(133, 132, 'BritPop'),
(134, 133, 'Afro-Punk'),
(135, 134, 'Polsk Punk'),
(136, 135, 'Beat'),
(137, 136, 'Christian Gangsta Rap'),
(138, 137, 'Heavy Metal'),
(139, 138, 'Black Metal'),
(140, 139, 'Crossover'),
(141, 140, 'Contemporary Christian'),
(142, 141, 'Christian Rock'),
(143, 142, 'Merengue'),
(144, 143, 'Salsa'),
(145, 144, 'Thrash Metal'),
(146, 145, 'Anime'),
(147, 146, 'JPop'),
(148, 147, 'Synthpop'),
(149, 148, 'Abstract'),
(150, 149, 'Art Rock'),
(151, 150, 'Baroque'),
(152, 151, 'Bhangra'),
(153, 152, 'Big Beat'),
(154, 153, 'Breakbeat'),
(155, 154, 'Chillout'),
(156, 155, 'Downtempo'),
(157, 156, 'Dub'),
(158, 157, 'EBM'),
(159, 158, 'Eclectic'),
(160, 159, 'Electro'),
(161, 160, 'Electroclash'),
(162, 161, 'Emo'),
(163, 162, 'Experimental'),
(164, 163, 'Garage'),
(165, 164, 'Global'),
(166, 165, 'IDM'),
(167, 166, 'Illbient'),
(168, 167, 'Industro-Goth'),
(169, 168, 'Jam Band'),
(170, 169, 'Krautrock'),
(171, 170, 'Leftfield'),
(172, 171, 'Lounge'),
(173, 172, 'Math Rock'),
(174, 173, 'New Romantic'),
(175, 174, 'Nu-Breakz'),
(176, 175, 'Post-Punk'),
(177, 176, 'Post-Rock'),
(178, 177, 'Psytrance'),
(179, 178, 'Shoegaze'),
(180, 179, 'Space Rock'),
(181, 180, 'Trop Rock'),
(182, 181, 'World Music');

