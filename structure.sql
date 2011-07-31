/*
 *  ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *  Copyright (C) 2005-2011 ScTBDev.ca
 *
 *  This file is part of ScTBDev.
 *
 *  ScTBDev is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  ScTBDev is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO,NO_UNSIGNED_SUBTRACTION";

--
-- Database: `sctbdev`
--

-- --------------------------------------------------------

--
-- Table structure for table `bans`
--

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `who` int(10) unsigned NOT NULL,
  `time` bigint(20) NOT NULL,
  `first` varbinary(16) NOT NULL,
  `last` varbinary(16) NOT NULL,
  `expires` bigint(20) DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_range` (`first`,`last`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bans_signups`
--

CREATE TABLE IF NOT EXISTS `bans_signups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `who` int(10) unsigned DEFAULT NULL,
  `time` bigint(20) NOT NULL,
  `first` varbinary(16) NOT NULL,
  `last` varbinary(16) NOT NULL,
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
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`) VALUES
(NULL, '0DAY', '0day.png'),
(NULL, 'Anime', 'anime.png'),
(NULL, 'Applications', 'appz.png'),
(NULL, 'DOX', 'dox.png'),
(NULL, 'Games/Other', 'games_other.png'),
(NULL, 'Games/PC', 'games_pc.png'),
(NULL, 'Games/PSP/PS2', 'games_psp_ps2.png'),
(NULL, 'Games/Wii', 'games_wii.png'),
(NULL, 'Games/Xbox 360', 'games_xbox360.png'),
(NULL, 'MiSC', 'misc.png'),
(NULL, 'Movies/DVDR', 'movies_dvdr.png'),
(NULL, 'Movies/Other', 'movies_other.png'),
(NULL, 'Movies/Packs', 'movies_packs.png'),
(NULL, 'Movies/WMV', 'movies_wmv.png'),
(NULL, 'Movies/x264', 'movies_x264.png'),
(NULL, 'Movies/XViD', 'movies_xvid.png'),
(NULL, 'Music/MP3', 'music_mp3.png'),
(NULL, 'Music/Packs', 'music_packs.png'),
(NULL, 'Music/Videos', 'music_videos.png'),
(NULL, 'TV/DVDR', 'tv_dvdr.png'),
(NULL, 'TV/DVDRip', 'tv_dvdrip.png'),
(NULL, 'TV/HR', 'tv_hr.png'),
(NULL, 'TV/Packs', 'tv_packs.png'),
(NULL, 'TV/x264', 'tv_x264.png'),
(NULL, 'TV/XViD', 'tv_xvid.png'),
(NULL, 'XXX', 'xxx_xvid.png'),
(NULL, 'XXX/HD', 'xxx_hd.png'),
(NULL, 'XXX/IMGSETS', 'xxx_img_sets.png');

-- --------------------------------------------------------

--
-- Table structure for table `cheaters`
--

CREATE TABLE IF NOT EXISTS `cheaters` (
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
  `flags` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `editedby` int(10) unsigned DEFAULT NULL,
  `editedat` bigint(20) DEFAULT NULL,
  `edits` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
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
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `commentid` (`commentid`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cc` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ccc` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `flagpic` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `cc`, `ccc`, `name`, `flagpic`) VALUES
(NULL, 'O1', 'O1', 'Isla de Muerte', 'islademuerte.png'),
(NULL, 'AF', 'AFG', 'Afghanistan', 'afghanistan.png'),
(NULL, 'AL', 'ALB', 'Albania', 'albania.png'),
(NULL, 'DZ', 'DZA', 'Algeria', 'algeria.png'),
(NULL, 'AD', 'AND', 'Andorra', 'andorra.png'),
(NULL, 'AO', 'AGO', 'Angola', 'angola.png'),
(NULL, 'AG', 'ATG', 'Antigua Barbuda', 'antiguabarbuda.png'),
(NULL, 'AR', 'ARG', 'Argentina', 'argentina.png'),
(NULL, 'AM', 'ARM', 'Armenia', 'armenia.png'),
(NULL, 'AU', 'AUS', 'Australia', 'australia.png'),
(NULL, 'AT', 'AUT', 'Austria', 'austria.png'),
(NULL, 'AZ', 'AZE', 'Azerbaijan', 'azerbaijan.png'),
(NULL, NULL, 'PRT', 'Azores', 'azores.png'),
(NULL, 'BS', 'BHS', 'Bahamas', 'bahamas.png'),
(NULL, 'BH', 'BHR', 'Bahrain', 'bahrain.png'),
(NULL, 'BD', 'BGD', 'Bangladesh', 'bangladesh.png'),
(NULL, 'BB', 'BRB', 'Barbados', 'barbados.png'),
(NULL, 'BY', 'BLR', 'Belarus', 'belarus.png'),
(NULL, 'BE', 'BEL', 'Belgium', 'belgium.png'),
(NULL, 'BZ', 'BLZ', 'Belize', 'belize.png'),
(NULL, 'BJ', 'BEN', 'Benin', 'benin.png'),
(NULL, 'BT', 'BTN', 'Bhutan', 'bhutan.png'),
(NULL, 'BO', 'BOL', 'Bolivia', 'bolivia.png'),
(NULL, 'BA', 'BIH', 'Bosnia Herzegovina', 'bosniaherzegovina.png'),
(NULL, 'BW', 'BWA', 'Botswana', 'bostwana.png'),
(NULL, 'BR', 'BRA', 'Brazil', 'brazil.png'),
(NULL, 'BN', 'BRN', 'Brunei', 'brunei.png'),
(NULL, 'BG', 'BGR', 'Bulgaria', 'bulgaria.png'),
(NULL, 'BF', 'BFA', 'Burkina Faso', 'burkinafaso.png'),
(NULL, 'MM', 'MMR', 'Burma/Myanmar', 'burma-myanmar.png'),
(NULL, 'BI', 'BDI', 'Burundi', 'burundi.png'),
(NULL, 'KH', 'KHM', 'Cambodia', 'cambodia.png'),
(NULL, 'CM', 'CMR', 'Cameroon', 'cameroon.png'),
(NULL, 'CA', 'CAN', 'Canada', 'canada.png'),
(NULL, 'CV', 'CPV', 'Cape Verde', 'capeverde.png'),
(NULL, 'CF', 'CAF', 'Central African Republic', 'centralafrica.png'),
(NULL, 'TD', 'TCD', 'Chad', 'chad.png'),
(NULL, 'CL', 'CHL', 'Chile', 'chile.png'),
(NULL, 'CN', 'CHN', 'China', 'china.png'),
(NULL, 'CO', 'COL', 'Colombia', 'colombia.png'),
(NULL, 'KM', 'COM', 'Comoros', 'comoros.png'),
(NULL, 'CG', 'COG', 'Congo', 'congo.png'),
(NULL, 'CR', 'CRI', 'Costa Rica', 'costarica.png'),
(NULL, 'CI', 'CIV', 'Côte d''Ivoire', 'cotedivoire.png'),
(NULL, 'HR', 'HRV', 'Croatia', 'croatia.png'),
(NULL, 'CU', 'CUB', 'Cuba', 'cuba.png'),
(NULL, 'CY', 'CYP', 'Cyprus', 'cyprus.png'),
(NULL, 'CZ', 'CZE', 'Czech Republic', 'czechrepublic.png'),
(NULL, 'DK', 'DNK', 'Denmark', 'denmark.png'),
(NULL, 'DJ', 'DJI', 'Djibouti', 'djibouti.png'),
(NULL, 'DM', 'DMA', 'Dominica', 'dominica.png'),
(NULL, 'DO', 'DOM', 'Dominican Republic', 'dominicanrep.png'),
(NULL, 'EC', 'ECU', 'Ecuador', 'ecuador.png'),
(NULL, 'EG', 'EGY', 'Egypt', 'egypt.png'),
(NULL, 'SV', 'SLV', 'El Salvador', 'elsalvador.png'),
(NULL, NULL, 'GBR', 'England', 'england.png'),
(NULL, 'GQ', 'GNQ', 'Equatorial Guinea', 'equatorialguinea.png'),
(NULL, 'ER', 'ERI', 'Eritrea', 'eritrea.png'),
(NULL, 'EE', 'EST', 'Estonia', 'estonia.png'),
(NULL, 'ET', 'ETH', 'Ethiopia', 'ethiopia.png'),
(NULL, 'EU', 'EUN', 'European Union', 'europeanunion.png'),
(NULL, 'FJ', 'FJI', 'Fiji', 'fiji.png'),
(NULL, 'FI', 'FIN', 'Finland', 'finland.png'),
(NULL, 'FR', 'FRA', 'France', 'france.png'),
(NULL, 'GA', 'GAB', 'Gabon', 'gabon.png'),
(NULL, 'GM', 'GMB', 'Gambia', 'gambia.png'),
(NULL, 'GE', 'GEO', 'Georgia', 'georgia.png'),
(NULL, 'DE', 'DEU', 'Germany', 'germany.png'),
(NULL, 'GH', 'GHA', 'Ghana', 'ghana.png'),
(NULL, 'GR', 'GRC', 'Greece', 'greece.png'),
(NULL, 'GD', 'GRD', 'Grenada', 'grenada.png'),
(NULL, 'GT', 'GTM', 'Guatemala', 'guatemala.png'),
(NULL, 'GN', 'GIN', 'Guinea', 'guinea.png'),
(NULL, 'GW', 'GNB', 'Guinea-Bissau', 'guineabissau.png'),
(NULL, 'GY', 'GUY', 'Guyana', 'guyana.png'),
(NULL, 'HT', 'HTI', 'Haiti', 'haiti.png'),
(NULL, 'VA', 'VAT', 'Holy See (Vatican City State)', 'holysee.png'),
(NULL, 'HN', 'HND', 'Honduras', 'honduras.png'),
(NULL, 'HK', 'HKG', 'Hong Kong', 'hongkong.png'),
(NULL, 'HU', 'HUN', 'Hungary', 'hungary.png'),
(NULL, 'IS', 'ISL', 'Iceland', 'iceland.png'),
(NULL, 'IN', 'IND', 'India', 'india.png'),
(NULL, 'ID', 'IDN', 'Indonesia', 'indonesia.png'),
(NULL, 'IR', 'IRN', 'Iran', 'iran.png'),
(NULL, 'IQ', 'IRQ', 'Iraq', 'iraq.png'),
(NULL, 'IE', 'IRL', 'Ireland', 'ireland.png'),
(NULL, 'IL', 'ISR', 'Israel', 'israel.png'),
(NULL, 'IT', 'ITA', 'Italy', 'italy.png'),
(NULL, 'JM', 'JAM', 'Jamaica', 'jamaica.png'),
(NULL, 'JP', 'JPN', 'Japan', 'japan.png'),
(NULL, 'JO', 'JOR', 'Jordan', 'jordan.png'),
(NULL, 'KZ', 'KAZ', 'Kazakhstan', 'kazakhstan.png'),
(NULL, 'KE', 'KEN', 'Kenya', 'kenya.png'),
(NULL, 'KI', 'KIR', 'Kiribati', 'kiribati.png'),
(NULL, 'KW', 'KWT', 'Kuwait', 'kuwait.png'),
(NULL, 'KG', 'KGZ', 'Kyrgyzstan', 'kyrgyzstan.png'),
(NULL, 'LA', 'LAO', 'Laos', 'laos.png'),
(NULL, 'LV', 'LVA', 'Latvia', 'latvia.png'),
(NULL, 'LB', 'LBN', 'Lebanon', 'lebanon.png'),
(NULL, 'LS', 'LSO', 'Lesotho', 'lesotho.png'),
(NULL, 'LR', 'LBR', 'Liberia', 'liberia.png'),
(NULL, 'LY', 'LBY', 'Libyan', 'libya.png'),
(NULL, 'LI', 'LIE', 'Liechtenstein', 'liechtenstein.png'),
(NULL, 'LT', 'LTU', 'Lithuania', 'lithuania.png'),
(NULL, 'LU', 'LUX', 'Luxembourg', 'luxembourg.png'),
(NULL, 'MK', 'MKD', 'Macedonia', 'macedonia.png'),
(NULL, 'MG', 'MDG', 'Madagascar', 'madagascar.png'),
(NULL, 'MW', 'MWI', 'Malawi', 'malawi.png'),
(NULL, 'MY', 'MYS', 'Malaysia', 'malaysia.png'),
(NULL, 'MV', 'MDV', 'Maldives', 'maldives.png'),
(NULL, 'ML', 'MLI', 'Mali', 'mali.png'),
(NULL, 'MT', 'MLT', 'Malta', 'malta.png'),
(NULL, 'MH', 'MHL', 'Marshall Islands', 'marshallislands.png'),
(NULL, 'MR', 'MRT', 'Mauritania', 'mauritania.png'),
(NULL, 'MU', 'MUS', 'Mauritius', 'mauritius.png'),
(NULL, 'MX', 'MEX', 'Mexico', 'mexico.png'),
(NULL, 'FM', 'FSM', 'Micronesia', 'micronesia.png'),
(NULL, 'MD', 'MDA', 'Moldova', 'moldova.png'),
(NULL, 'MC', 'MCO', 'Monaco', 'monaco.png'),
(NULL, 'MN', 'MNG', 'Mongolia', 'mongolia.png'),
(NULL, 'ME', 'MNE', 'Montenegro', 'montenegro.png'),
(NULL, 'MA', 'MAR', 'Morocco', 'morocco.png'),
(NULL, 'MZ', 'MOZ', 'Mozambique', 'mozambique.png'),
(NULL, 'NA', 'NAM', 'Namibia', 'namibia.png'),
(NULL, 'NR', 'NRU', 'Nauru', 'nauru.png'),
(NULL, 'NP', 'NPL', 'Nepal', 'nepal.png'),
(NULL, 'NL', 'NLD', 'Netherlands', 'netherlands.png'),
(NULL, 'AN', 'ANT', 'Netherlands Antilles', 'netherlandsantilles.png'),
(NULL, 'NZ', 'NZL', 'New Zealand', 'newzealand.png'),
(NULL, 'NI', 'NIC', 'Nicaragua', 'nicaragua.png'),
(NULL, 'NE', 'NER', 'Niger', 'niger.png'),
(NULL, 'NG', 'NGA', 'Nigeria', 'nigeria.png'),
(NULL, 'KP', 'PRK', 'North Korea', 'northkorea.png'),
(NULL, NULL, 'GBR', 'Northern Ireland', 'unitedkingdom.png'),
(NULL, 'NO', 'NOR', 'Norway', 'norway.png'),
(NULL, 'OM', 'OMN', 'Oman', 'oman.png'),
(NULL, 'PK', 'PAK', 'Pakistan', 'pakistan.png'),
(NULL, 'PW', 'PLW', 'Palau', 'palau.png'),
(NULL, 'PA', 'PAN', 'Panama', 'panama.png'),
(NULL, 'PG', 'PNG', 'Papua New Guinea', 'papuanewguinea.png'),
(NULL, 'PY', 'PRY', 'Paraguay', 'paraguay.png'),
(NULL, 'PE', 'PER', 'Peru', 'peru.png'),
(NULL, 'PH', 'PHL', 'Philippines', 'philippines.png'),
(NULL, 'PL', 'POL', 'Poland', 'poland.png'),
(NULL, 'PT', 'PRT', 'Portugal', 'portugal.png'),
(NULL, 'PR', 'PRI', 'Puerto Rico', 'puertorico.png'),
(NULL, 'QA', 'QAT', 'Qatar', 'qatar.png'),
(NULL, 'RO', 'ROU', 'Romania', 'romania.png'),
(NULL, 'RU', 'RUS', 'Russia', 'russia.png'),
(NULL, 'RW', 'RWA', 'Rwanda', 'rwanda.png'),
(NULL, 'KN', 'KNA', 'Saint Kitts and Nevis', 'saintkittsandnevis.png'),
(NULL, 'LC', 'LCA', 'Saint Lucia', 'saintlucia.png'),
(NULL, 'VC', 'VCT', 'Saint Vincent and the Grenadines', 'saintvincentandthegrenadine.png'),
(NULL, 'SM', 'SMR', 'San Marino', 'sanmarino.png'),
(NULL, 'ST', 'STP', 'Sao Tome and Principe', 'saotomeandprincipe.png'),
(NULL, 'SA', 'SAU', 'Saudi Arabia', 'saudiarabia.png'),
(NULL, NULL, 'GBR', 'Scotland', 'scotland.png'),
(NULL, 'SN', 'SEN', 'Senegal', 'senegal.png'),
(NULL, 'RS', 'SRB', 'Serbia', 'serbia.png'),
(NULL, 'SC', 'SYC', 'Seychelles', 'seychelles.png'),
(NULL, 'SL', 'SLE', 'Sierra Leone', 'sierraleone.png'),
(NULL, 'SG', 'SGP', 'Singapore', 'singapore.png'),
(NULL, 'SK', 'SVK', 'Slovakia', 'slovakia.png'),
(NULL, 'SI', 'SVN', 'Slovenia', 'slovenia.png'),
(NULL, 'SB', 'SLB', 'Solomon Islands', 'solomonislands.png'),
(NULL, 'SO', 'SOM', 'Somalia', 'somalia.png'),
(NULL, 'ZA', 'ZAF', 'South Africa', 'southafrica.png'),
(NULL, 'KR', 'KOR', 'South Korea', 'southkorea.png'),
(NULL, 'ES', 'ESP', 'Spain', 'spain.png'),
(NULL, 'LK', 'LKA', 'Sri Lanka', 'srilanka.png'),
(NULL, 'SD', 'SDN', 'Sudan', 'sudan.png'),
(NULL, 'SR', 'SUR', 'Suriname', 'suriname.png'),
(NULL, 'SE', 'SWE', 'Sweden', 'sweden.png'),
(NULL, 'CH', 'CHE', 'Switzerland', 'switzerland.png'),
(NULL, 'SY', 'SRY', 'Syria', 'syria.png'),
(NULL, 'TW', 'TWN', 'Taiwan', 'taiwan.png'),
(NULL, 'TJ', 'TJK', 'Tajikistan', 'tajikistan.png'),
(NULL, 'TZ', 'TZA', 'Tanzania', 'tanzania.png'),
(NULL, 'TH', 'THA', 'Thailand', 'thailand.png'),
(NULL, 'TL', 'TLS', 'Timor-Leste', 'timor-leste.png'),
(NULL, 'TG', 'TGO', 'Togo', 'togo.png'),
(NULL, 'TO', 'TON', 'Tonga', 'tonga.png'),
(NULL, 'TT', 'TTO', 'Trinidad & Tobago', 'trinidadandtobago.png'),
(NULL, 'TN', 'TUN', 'Tunisia', 'tunisia.png'),
(NULL, 'TR', 'TUR', 'Turkey', 'turkey.png'),
(NULL, 'TM', 'TKM', 'Turkmenistan', 'turkmenistan.png'),
(NULL, 'TV', 'TUV', 'Tuvalu', 'tuvalu.png'),
(NULL, 'UG', 'UGA', 'Uganda', 'uganda.png'),
(NULL, 'UA', 'UKR', 'Ukraine', 'ukraine.png'),
(NULL, 'AE', 'ARE', 'United Arab Emirates', 'unitedarabemirates.png'),
(NULL, 'GB', 'GBR', 'United Kingdom', 'unitedkingdom.png'),
(NULL, 'US', 'USA', 'United States', 'unitedstates.png'),
(NULL, 'UY', 'URY', 'Uruguay', 'uruguay.png'),
(NULL, 'UZ', 'UZB', 'Uzbekistan', 'uzbekistan.png'),
(NULL, 'VU', 'VUT', 'Vanuatu', 'vanatu.png'),
(NULL, 'VE', 'VEN', 'Venezuela', 'venezuela.png'),
(NULL, 'VN', 'VNM', 'Vietnam', 'vietnam.png'),
(NULL, NULL, 'GBR', 'Wales', 'wales.png'),
(NULL, 'WS', 'WSM', 'Western Samoa', 'westernsaoma.png'),
(NULL, 'YE', 'YEM', 'Yemen', 'yemen.png'),
(NULL, 'YU', 'YUG', 'Yugoslavia', 'yugoslavia.png'),
(NULL, 'ZM', 'ZMB', 'Zambia', 'zambia.png'),
(NULL, 'ZW', 'ZWE', 'Zimbabwe', 'zimbabwe.png');

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
  `ip` varbinary(16) NOT NULL,
  `realip` varbinary(16) NOT NULL,
  PRIMARY KEY (`transid`),
  KEY `userid` (`userid`),
  KEY `status` (`status`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `email_changes`
--

CREATE TABLE IF NOT EXISTS `email_changes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` binary(20) NOT NULL,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `newemail` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `realip` varbinary(16) NOT NULL,
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
  `lang` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `helpwith` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
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
-- Table structure for table `genres`
--

CREATE TABLE IF NOT EXISTS `genres` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=256;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(0, 'Blues'),
(1, 'Classic Rock'),
(2, 'Country'),
(3, 'Dance'),
(4, 'Disco'),
(5, 'Funk'),
(6, 'Grunge'),
(7, 'Hip-Hop'),
(8, 'Jazz'),
(9, 'Metal'),
(10, 'New Age'),
(11, 'Oldies'),
(12, 'Other'),
(13, 'Pop'),
(14, 'R&B'),
(15, 'Rap'),
(16, 'Reggae'),
(17, 'Rock'),
(18, 'Techno'),
(19, 'Industrial'),
(20, 'Alternative'),
(21, 'Ska'),
(22, 'Death Metal'),
(23, 'Pranks'),
(24, 'Soundtrack'),
(25, 'Euro-Techno'),
(26, 'Ambient'),
(27, 'Trip-Hop'),
(28, 'Vocal'),
(29, 'Jazz+Funk'),
(30, 'Fusion'),
(31, 'Trance'),
(32, 'Classical'),
(33, 'Instrumental'),
(34, 'Acid'),
(35, 'House'),
(36, 'Game'),
(37, 'Sound Clip'),
(38, 'Gospel'),
(39, 'Noise'),
(40, 'Alternative Rock'),
(41, 'Bass'),
(42, 'Soul'),
(43, 'Punk'),
(44, 'Space'),
(45, 'Meditative'),
(46, 'Instrumental Pop'),
(47, 'Instrumental Rock'),
(48, 'Ethnic'),
(49, 'Gothic'),
(50, 'Darkwave'),
(51, 'Techno-Industrial'),
(52, 'Electronic'),
(53, 'Pop-Folk'),
(54, 'Eurodance'),
(55, 'Dream'),
(56, 'Southern Rock'),
(57, 'Comedy'),
(58, 'Cult'),
(59, 'Gangsta Rap'),
(60, 'Top 40'),
(61, 'Christian Rap'),
(62, 'Pop/Funk'),
(63, 'Jungle'),
(64, 'Native American'),
(65, 'Cabaret'),
(66, 'New Wave'),
(67, 'Psychedelic'),
(68, 'Rave'),
(69, 'Showtunes'),
(70, 'Trailer'),
(71, 'Lo-Fi'),
(72, 'Tribal'),
(73, 'Acid Punk'),
(74, 'Acid Jazz'),
(75, 'Polka'),
(76, 'Retro'),
(77, 'Musical'),
(78, 'Rock & Roll'),
(79, 'Hard Rock'),
(80, 'Folk'),
(81, 'Folk/Rock'),
(82, 'National Folk'),
(83, 'Swing'),
(84, 'Fast-Fusion'),
(85, 'Bebop'),
(86, 'Latin'),
(87, 'Revival'),
(88, 'Celtic'),
(89, 'Bluegrass'),
(90, 'Avantgarde'),
(91, 'Gothic Rock'),
(92, 'Progressive Rock'),
(93, 'Psychedelic Rock'),
(94, 'Symphonic Rock'),
(95, 'Slow Rock'),
(96, 'Big Band'),
(97, 'Chorus'),
(98, 'Easy Listening'),
(99, 'Acoustic'),
(100, 'Humour'),
(101, 'Speech'),
(102, 'Chanson'),
(103, 'Opera'),
(104, 'Chamber Music'),
(105, 'Sonata'),
(106, 'Symphony'),
(107, 'Booty Bass'),
(108, 'Primus'),
(109, 'Porn Groove'),
(110, 'Satire'),
(111, 'Slow Jam'),
(112, 'Club'),
(113, 'Tango'),
(114, 'Samba'),
(115, 'Folklore'),
(116, 'Ballad'),
(117, 'Power Ballad'),
(118, 'Rhythmic Soul'),
(119, 'Freestyle'),
(120, 'Duet'),
(121, 'Punk Rock'),
(122, 'Drum Solo'),
(123, 'A Cappella'),
(124, 'Euro-House'),
(125, 'Dance Hall'),
(126, 'Goa'),
(127, 'Drum & Bass'),
(128, 'Club-House'),
(129, 'Hardcore'),
(130, 'Terror'),
(131, 'Indie'),
(132, 'BritPop'),
(133, 'Afro-Punk'),
(134, 'Polsk Punk'),
(135, 'Beat'),
(136, 'Christian Gangsta Rap'),
(137, 'Heavy Metal'),
(138, 'Black Metal'),
(139, 'Crossover'),
(140, 'Contemporary Christian'),
(141, 'Christian Rock'),
(142, 'Merengue'),
(143, 'Salsa'),
(144, 'Thrash Metal'),
(145, 'Anime'),
(146, 'JPop'),
(147, 'Synthpop'),
(148, 'Abstract'),
(149, 'Art Rock'),
(150, 'Baroque'),
(151, 'Bhangra'),
(152, 'Big Beat'),
(153, 'Breakbeat'),
(154, 'Chillout'),
(155, 'Downtempo'),
(156, 'Dub'),
(157, 'EBM'),
(158, 'Eclectic'),
(159, 'Electro'),
(160, 'Electroclash'),
(161, 'Emo'),
(162, 'Experimental'),
(163, 'Garage'),
(164, 'Global'),
(165, 'IDM'),
(166, 'Illbient'),
(167, 'Industro-Goth'),
(168, 'Jam Band'),
(169, 'Krautrock'),
(170, 'Leftfield'),
(171, 'Lounge'),
(172, 'Math Rock'),
(173, 'New Romantic'),
(174, 'Nu-Breakz'),
(175, 'Post-Punk'),
(176, 'Post-Rock'),
(177, 'Psytrance'),
(178, 'Shoegaze'),
(179, 'Space Rock'),
(180, 'Trop Rock'),
(181, 'World Music');

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
  UNIQUE KEY `inviteid` (`inviteid`),
  KEY `userid` (`userid`),
  KEY `added` (`added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ips`
--

CREATE TABLE IF NOT EXISTS `ips` (
  `ip` varbinary(16) NOT NULL,
  `dns` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `backwards` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_update` bigint(20) NOT NULL,
  PRIMARY KEY (`ip`),
  KEY `dns` (`dns`(15)),
  KEY `backwards` (`backwards`(15))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `irc_bot`
--

CREATE TABLE IF NOT EXISTS `irc_bot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('privmsg','notice','invite') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'privmsg',
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- -------------------------------------------------------

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
-- Table structure for table `log_site`
--

CREATE TABLE IF NOT EXISTS `log_site` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `txt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `txt` (`txt`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_staff`
--

CREATE TABLE IF NOT EXISTS `log_staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `txt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `txt` (`txt`(20))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_svn`
--

CREATE TABLE IF NOT EXISTS `log_svn` (
  `revision` int(10) unsigned NOT NULL,
  `time` bigint(20) NOT NULL,
  `comments` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`revision`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned DEFAULT NULL,
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `added` bigint(20) NOT NULL DEFAULT '0',
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `subject` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `msg` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `unread` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
  `location` enum('in','out','both') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'in',
  PRIMARY KEY (`id`),
  KEY `location` (`location`),
  KEY `unread_messages` (`receiver`,`location`,`unread`),
  KEY `sender` (`sender`,`location`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
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
  `body` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`added`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paypal_ipn`
--

CREATE TABLE IF NOT EXISTS `paypal_ipn` (
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
  `post` blob NOT NULL,
  `verified` enum('yes','no','fake') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `realip` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `payment_status` (`payment_status`,`txn_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- -------------------------------------------------------

--
-- Table structure for table `peers`
--

CREATE TABLE IF NOT EXISTS `peers` (
  `torrent` int(10) unsigned NOT NULL DEFAULT '0',
  `peer_id` binary(20) NOT NULL DEFAULT '                    ',
  `ip` varbinary(16) NOT NULL,
  `ip2` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) NOT NULL,
  `port` smallint(5) unsigned DEFAULT NULL,
  `port6` smallint(5) unsigned DEFAULT NULL,
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
  `flags` smallint(5) unsigned NOT NULL DEFAULT '0',
  `compact4` varbinary(6) DEFAULT NULL,
  `compact6` varbinary(18) DEFAULT NULL,
  PRIMARY KEY (`torrent`,`peer_id`) USING HASH,
  KEY `userid` (`userid`) USING HASH,
  KEY `last_action` (`last_action`) USING BTREE,
  KEY `ip` (`ip`(7)) USING BTREE,
  KEY `ip2` (`ip2`(7)) USING BTREE,
  KEY `realip` (`realip`(7)) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `pollanswers`
--

CREATE TABLE IF NOT EXISTS `pollanswers` (
  `pollid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `selection` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pollid`,`userid`),
  KEY `pollid_selection` (`pollid`,`selection`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `flags` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `body` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `editedby` int(10) unsigned DEFAULT NULL,
  `editedat` bigint(20) DEFAULT NULL,
  `edits` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`),
  KEY `added` (`added`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
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
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `body` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `postid` (`postid`),
  KEY `added` (`added`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
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
  KEY `topicid` (`topicid`)
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
  `id` binary(20) NOT NULL,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varbinary(16) NOT NULL,
  `realip` varbinary(16) NOT NULL,
  `time` bigint(20) NOT NULL DEFAULT '0',
  `lastaction` bigint(20) NOT NULL DEFAULT '0',
  `maxage` int(10) unsigned NOT NULL DEFAULT '7776000',
  `maxidle` int(10) NOT NULL DEFAULT '604800',
  `flags` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`user`),
  KEY `lastaction` (`lastaction`,`maxidle`),
  KEY `added` (`time`,`maxage`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

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
  `ip` varbinary(16) DEFAULT NULL,
  `ip2` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `last_action` tinyint(3) unsigned DEFAULT NULL,
  `clientid` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`torrent`,`user`),
  KEY `last_time` (`last_time`),
  KEY `ip` (`ip`(7)),
  KEY `ip2` (`ip2`(7)),
  KEY `realip` (`realip`(7)),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `sphinx_search`
--

CREATE TABLE IF NOT EXISTS `sphinx_search` (
  `id` bigint(20) unsigned NOT NULL,
  `weight` int(11) NOT NULL,
  `query` varchar(3072) COLLATE utf8_unicode_ci NOT NULL,
  KEY `query` (`query`)
) ENGINE=SPHINX DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci CONNECTION='sphinx://localhost:9312/sphinx_search';

-- --------------------------------------------------------

--
-- Table structure for table `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `locked` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `forumid` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `sticky` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'no',
  `posts` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `forumid` (`forumid`),
  KEY `sticky` (`sticky`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `torrents`
--

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` binary(20) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `category` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  `owner` int(10) unsigned DEFAULT NULL,
  `pretime` bigint(20) DEFAULT NULL,
  `genre` smallint(5) unsigned DEFAULT NULL,
  `flags` smallint(5) unsigned NOT NULL DEFAULT '0',
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
  KEY `genre` (`genre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

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
-- Table structure for table `torrents_extra`
--

CREATE TABLE IF NOT EXISTS `torrents_extra` (
  `id` int(10) unsigned NOT NULL,
  `search_text` text COLLATE utf8_unicode_ci NOT NULL,
  `descr` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `nfo` mediumblob,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `last_login` bigint(20) DEFAULT NULL,
  `last_access` bigint(20) NOT NULL DEFAULT '0',
  `editsecret` varbinary(20) NOT NULL,
  `theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `profile` text COLLATE utf8_unicode_ci NOT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `ip_access` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `class` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `payed_uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeding` smallint(3) unsigned NOT NULL DEFAULT '0',
  `leeching` smallint(3) unsigned NOT NULL DEFAULT '0',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country` smallint(5) unsigned DEFAULT '1',
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UTC',
  `notifs` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modcomment` text COLLATE utf8_unicode_ci NOT NULL,
  `warneduntil` bigint(20) DEFAULT NULL,
  `torrentsperpage` tinyint(3) unsigned DEFAULT NULL,
  `topicsperpage` tinyint(3) unsigned DEFAULT NULL,
  `postsperpage` tinyint(3) unsigned DEFAULT NULL,
  `last_browse` bigint(20) DEFAULT NULL,
  `inbox_new` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sentbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `comments` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `posts` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `last_forum_visit` bigint(20) DEFAULT NULL,
  `passkey` binary(16) NOT NULL,
  `invites` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `invitedby` int(10) unsigned DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '7345036',
  `chans` bigint(20) unsigned NOT NULL DEFAULT '32132355443392545',
  `donations` double NOT NULL,
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
  KEY `added` (`added`),
  KEY `country` (`country`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `users_deleted`
--

CREATE TABLE IF NOT EXISTS `users_deleted` (
  `id` int(10) unsigned NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `added` bigint(20) NOT NULL DEFAULT '0',
  `last_login` bigint(20) DEFAULT NULL,
  `last_access` bigint(20) NOT NULL DEFAULT '0',
  `editsecret` varbinary(20) NOT NULL,
  `theme` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `info` text COLLATE utf8_unicode_ci NOT NULL,
  `ip` varbinary(16) DEFAULT NULL,
  `realip` varbinary(16) DEFAULT NULL,
  `ip_access` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `class` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `payed_uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeding` smallint(3) unsigned NOT NULL DEFAULT '0',
  `leeching` smallint(3) unsigned NOT NULL DEFAULT '0',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `country` smallint(5) unsigned DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UTC',
  `notifs` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modcomment` text COLLATE utf8_unicode_ci NOT NULL,
  `warneduntil` bigint(20) DEFAULT NULL,
  `torrentsperpage` tinyint(3) unsigned DEFAULT NULL,
  `topicsperpage` tinyint(3) unsigned DEFAULT NULL,
  `postsperpage` tinyint(3) unsigned DEFAULT NULL,
  `last_browse` bigint(20) DEFAULT NULL,
  `inbox_new` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sentbox` smallint(5) unsigned NOT NULL DEFAULT '0',
  `comments` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `posts` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `last_forum_visit` bigint(20) DEFAULT NULL,
  `passkey` binary(16) NOT NULL,
  `invites` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `invitedby` int(10) unsigned DEFAULT NULL,
  `flags` bigint(20) unsigned NOT NULL DEFAULT '0',
  `chans` bigint(20) unsigned NOT NULL DEFAULT '0',
  `donations` float NOT NULL,
  `irc_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `flags` (`flags`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7)),
  KEY `username` (`username`(2)),
  KEY `passkey` (`passkey`(2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `users_history`
--

CREATE TABLE IF NOT EXISTS `users_history` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `time` bigint(20) NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_log`
--

CREATE TABLE IF NOT EXISTS `users_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` bigint(20) NOT NULL DEFAULT '0',
  `ip` varbinary(16) NOT NULL,
  `realip` varbinary(16) NOT NULL,
  `server` blob NOT NULL,
  `get` blob NOT NULL,
  `post` blob NOT NULL,
  `cookie` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `ip` (`ip`(7)),
  KEY `realip` (`realip`(7))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
