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

class bt_options {
	const OPTS_CONFIRMED			= 0x1;			// status
	const OPTS_ANON					= 0x2;			// privacy
	const OPTS_ACCEPT_PMS			= 0x4;			// acceptpms
	const OPTS_ACCEPT_FRIEND_PMS	= 0x8;			// acceptfriendpms
	const OPTS_AVATAR_PO			= 0x10;			// avatar_po
	const OPTS_DST					= 0x20;			// dst
	const OPTS_PM_NOTIFICATION		= 0x40;			// pmnotif
	const OPTS_ENABLED				= 0x80;			// enabled
	const OPTS_SHOW_AVATARS			= 0x100;		// avatars
	const OPTS_SHOW_PO_AVATARS		= 0x200;		// avatars_po
}
?>
