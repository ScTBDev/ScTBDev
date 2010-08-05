<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2010 ScTBDev.ca
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(INCL_PATH.'define_bits.php');

class bt_options {
	const FLAGS_CONFIRMED			= BIT_1;	// status				confirmed account?
	const FLAGS_ANON				= BIT_2;	// privacy				hide seeding/leeching torrents (anonymous)
	const FLAGS_ACCEPT_PMS			= BIT_3;	// acceptpms			allow pms from everybody?
	const FLAGS_ACCEPT_FRIEND_PMS	= BIT_4;	// acceptfriendpms		allow pms from friends?
	const FLAGS_AVATAR_PO			= BIT_5;	// avatar_po			my avatar is potentially offensive
//	const FLAGS_DST					= BIT_6;	// dst					daylight savings
	const FLAGS_PM_NOTIFICATION		= BIT_7;	// pmnotif				email notification of pms
	const FLAGS_ENABLED				= BIT_8;	// enabled				enabled account?
	const FLAGS_SHOW_AVATARS		= BIT_9;	// avatars				show avatars
	const FLAGS_SHOW_PO_AVATARS		= BIT_10;	// avatars_po			show potentially offensive avatars
	const FLAGS_DONOR				= BIT_11;	// donor				user has donated
	const FLAGS_WARNED				= BIT_12;	// warned				user is warned
	const FLAGS_DELETE_PMS			= BIT_13;	// deletepms			default to deleting pms from inbox on reply
	const FLAGS_SAVE_PMS			= BIT_14;	// savepms				default to saving sent pms to sentbox
	const FLAGS_SSL_SITE			= BIT_15;	// ssl_site				force use of ssl, redirect from http to https
	const FLAGS_PROTECT				= BIT_16;	// protect				protect users ip, don't store it in the database
	const FLAGS_HIDE_STATS			= BIT_17;	// hide_stats			hide upload/download/class/ratio
	const FLAGS_LOG_USER			= BIT_18;	// log					log everything the user does (really messy atm)
	const FLAGS_UPLOADER			= BIT_19;	// uploader				user is an uploader on the uploader list
	const FLAGS_FIRST_LINE_SUPPORT	= BIT_20;	// fls					user is first line support
	const FLAGS_STATBAR				= BIT_21;	// statbar				show statbar
	const FLAGS_POST_ENABLE			= BIT_22;	// post_enable			enable forum posting
	const FLAGS_IRC_ENABLE			= BIT_23;	// irc_enable			allow in irc channels
	const FLAGS_PROXY_TRACKER		= BIT_24;	// proxy				enable proxy option for tracker
	const FLAGS_SSL_TRACKER			= BIT_25;	// ssl_tracker			enable ssl tracker
	const FLAGS_BYPASS_BANS			= BIT_26;	// bypass_ban			allows a user to bypass bans
	const FLAGS_HIDE_LAST_SEEN		= BIT_27;	// hide_last_seen		hide last seen time from other users
	const FLAGS_DISABLE_INVITE_BUY	= BIT_28;	// disable_invites		disable buying of invites
	const FLAGS_PROBED				= BIT_29;	//
	const FLAGS_CONNECTABLE			= BIT_30;	//
//	const FLAGS_UNALLOCATED31		= BIT_31;	//
//	const FLAGS_UNALLOCATED32		= BIT_32;	//

//	const FLAGS_UNALLOCATED33		= BIT_33;	//
//	const FLAGS_UNALLOCATED34		= BIT_34;	//
//	const FLAGS_UNALLOCATED35		= BIT_35;	//
//	const FLAGS_UNALLOCATED36		= BIT_36;	//
//	const FLAGS_UNALLOCATED37		= BIT_37;	//
//	const FLAGS_UNALLOCATED38		= BIT_38;	//
//	const FLAGS_UNALLOCATED39		= BIT_39;	//
//	const FLAGS_UNALLOCATED40		= BIT_40;	//
//	const FLAGS_UNALLOCATED41		= BIT_41;	//
//	const FLAGS_UNALLOCATED42		= BIT_42;	//
//	const FLAGS_UNALLOCATED43		= BIT_43;	//
//	const FLAGS_UNALLOCATED44		= BIT_44;	//
//	const FLAGS_UNALLOCATED45		= BIT_45;	//
//	const FLAGS_UNALLOCATED46		= BIT_46;	//
//	const FLAGS_UNALLOCATED47		= BIT_47;	//
//	const FLAGS_UNALLOCATED48		= BIT_48;	//
//	const FLAGS_UNALLOCATED49		= BIT_49;	//
//	const FLAGS_UNALLOCATED50		= BIT_50;	//
//	const FLAGS_UNALLOCATED51		= BIT_51;	//
//	const FLAGS_UNALLOCATED52		= BIT_52;	//
	const FLAGS_FORUM_ICONS_1		= BIT_53;	// forum_1				forum_1-4 used for button selection in forums
	const FLAGS_FORUM_ICONS_2		= BIT_54;	// forum_2				"
	const FLAGS_FORUM_ICONS_3		= BIT_55;	// forum_3				"
	const FLAGS_FORUM_ICONS_4		= BIT_56;	// forum_4				"
//	const FLAGS_UNALLOCATED57		= BIT_57;	//
//	const FLAGS_UNALLOCATED58		= BIT_58;	//
//	const FLAGS_UNALLOCATED59		= BIT_59;	//
//	const FLAGS_UNALLOCATED60		= BIT_60;	//
//	const FLAGS_UNALLOCATED61		= BIT_61;	//
//	const FLAGS_UNALLOCATED62		= BIT_62;	//
//	const FLAGS_UNALLOCATED63		= BIT_63;	//
//	const FLAGS_UNALLOCATED64		= BIT_64;	//
}
?>
