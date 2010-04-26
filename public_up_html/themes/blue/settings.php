<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2010 LinuxHosted.ca
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

$THEME['donbar'] = array(
	'max_length'	=> 33,
	'empty'			=> true,
	'pic_names'		=> array('red','yellow','green'),
);

$THEME['statbar'] = array(
	'unconnl'	=> ' class="userbar_unconn"',
);

$THEME['browse'] = array(
	'midl'			=> ' class="g_bblink"',
	'newt'			=> ' &nbsp; (<span class="browse_new_torrent">NEW!</span>)',
	'catlist'		=> array(
		'per_row'		=> 7,
		'row_start'		=> "\t\t\t\t\t\t\t".'<tr>'."\n",
		'row_end'		=> "\t\t\t\t\t\t\t".'</tr>'."\n",
		'cat_start'		=> "\t\t\t\t\t\t\t\t".'<td class="browse_catlink">',
		'link'			=> ' class="g_bnulink"',
		'cat_end'		=> '</td>',
	),
);

$THEME['pager'] = array(
	'prev'		=> '&lt;&lt;&nbsp;Prev',
	'next'		=> 'Next&nbsp;&gt;&gt;',
	'startp'	=> '<p class="browse_changepage">',
	'endp'		=> '</p>',
	'midl'		=> ' class="g_bblink"',
	'midl_only'	=> ' class="g_bllink"',
	'spacep'	=> '&nbsp;|&nbsp;',
	'nospacep'	=> '&nbsp;',
	'dotp'		=> '...',
	'sepp'		=> '&nbsp;-&nbsp;',
	'dotspace'	=> 2,
	'dotpages'	=> 5,
);

$THEME['classes'] = array(
	'colors'	=> array('g_c_user','g_c_puser','g_c_xuser','g_c_lover','g_c_whore','g_c_whor2','g_c_whor3','g_c_oversd',
		'g_c_vip','g_c_upldr','g_c_fmod','g_c_gmod','g_c_admin','g_c_leader'),
	'link'		=> 'g_noclink',
);

$THEME['stdmsg'] = array(
	'small'	=> '_sm',
);

$THEME['log'] = array(
	'types'	=> array(
		0	=> 'i',
		1	=> 'e',
		2	=> 'd',
		3	=> 'u',
	),
);

$THEME['bbcode'] = array(
	'link'		=> ' class="g_bllink"',
	'*'			=> array('<ul><li>','</li></ul>',''),
	'b'			=> array('<b>','</b>'),
	'i'			=> array('<em>','</em>'),
	'u'			=> array('<span class="u">','</span>'),
	'center'	=> array('<span class="g_centertag">','</span>'),
	'pre'		=> array('<span class="preformat">','</span>'),
	'spoiler'	=> array('<div class="g_spoiler">','</div>'),
	'quote'		=> array('<p class="g_quote">','</p>','<div class="g_quote">','</div>'."\n"),
);

$THEME['foot'] = array(
	'setp'				=> ' class="script_execution_time"',
	'powered_by_link'	=> ' class="g_wlink"',
);

$THEME['forums'] = array(
	'index'			=> array(
		'link'			=> ' class="g_bbnulink"',
		'staff_tools'	=> ' class="forums_f_stafftools"',
		'descr'			=> 'forums_f_finfo',
		'no_descr'		=> 'forums_f_flinfo',
		'pre_descr'		=> '<br />',
	),
	'quick_jump'	=> array(
		'list_prefix'	=> "\t\t\t\t\t\t\t\t",
	),
);

$THEME['inbox'] = array(
	'inbox_link'	=> ' class="g_bblink"',
	'sentbox_link'	=> ' class="g_bblink"',
	'link'			=> ' class="g_bbnulink"',
	'new_message'	=> '(<span class="browse_new_torrent">NEW!</span>) ',
	'reply_sep'		=> ' - ',
	'uclass'		=> 'g_noclink',
	'table_start'	=> '',
	'table_end'		=> '',
//	'massdel'		=> "\n\t\t\t\t\t".'<div class="main_sm_section_top">Delete?</div>'."\n\t\t\t\t\t".'<div class="main_sm_section_body">'."\n\t\t\t\t\t\t".
//		'<input type="hidden" name="loc" value="%s" />'."\n\t\t\t\t\t\t".'<input type="submit" value="Delete Checked PMs" />'."\n\t\t\t\t\t".'</div>'."\n\t\t\t\t\t".
//		'<div class="main_sm_section_bottom"></div>',
	'massdel'		=> "\n\t\t\t\t\t".'<p style="text-align: center">'."\n\t\t\t\t\t\t".'<input type="hidden" name="type" value="%s" />'."\n\t\t\t\t\t\t".
		'<input type="submit" value="Delete Checked PMs" />'."\n\t\t\t\t\t".'</p>',
);

$THEME['upload'] = array(
	'list_prefix'	=> "\t\t\t\t\t\t\t\t\t\t",
);

$THEME['signup'] = array(
	'list_prefix'	=> "\t\t\t\t\t\t\t\t\t\t",
);

$THEME['tags'] = array(
	'remarks_start'	=> "\n\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t".'<td><b>Remarks</b></td>'."\n\t\t\t\t\t\t\t".'<td>',
	'remarks_end'	=> '</td>'."\n\t\t\t\t\t\t".'</tr>',
);

$THEME['invite'] = array(
	'pending_row'	=> "\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t".'<td align="left" style="padding-left: 10px; height: 25px; text-align: left">%s</td>'."\n\t\t\t\t\t\t\t".
		'<td>%s %s</td>'."\n\t\t\t\t\t\t\t".'<td><a href="/invite.php?cancel=%u"><img src="%scancel_invite.png" alt="Canel Invite" /></a></td>'."\n\t\t\t\t\t\t".'</tr>',
);

$THEME['users'] = array(
	'class_list'	=> array(
		'join'		=> "\n",
		'selected'	=> ' selected="selected"',
		'option'	=> "\t\t\t\t\t\t\t".'<option value="%u"%s>%s</option>',
	),
	'country_list'	=> array(
		'join'		=> "\n",
		'selected'	=> ' selected="selected"',
		'option'	=> "\t\t\t\t\t\t\t".'<option value="%u"%s>%s</option>',
	),
	'page_links'	=> array(
		'join'		=> "\n",
		'no_link'	=> "\t\t\t\t\t\t\t".'<b>%s</b>',
		'link'		=> "\t\t\t\t\t\t\t".'<a href="?letter=%s" class="g_bblink">%s</a>',
	),
	'users_list'	=> array(
		'table'		=> "\t\t\t\t\t\t".'<table class="g_deftable">'."\n\t\t\t\t\t\t\t".'<tr class="g_deftable_toprow g_wide_tablerow">'."\n\t\t\t\t\t\t\t\t".
			'<td align="left" style="width: 100px">Username</td>'."\n\t\t\t\t\t\t\t\t".'<td align="left" style="width: 110px">Registered</td>'."\n\t\t\t\t\t\t\t\t".
			'<td align="left" style="width: 200px">Last Access</td>'."\n\t\t\t\t\t\t\t\t".'<td align="left" style="width: 85px">Class</td>'."\n\t\t\t\t\t\t\t\t".
			'<td align="center" style="width: 50px">Country</td>'."\n\t\t\t\t\t\t\t".'</tr>'."\n".'%s'."\n\t\t\t\t\t\t".'</table>',

		'join'		=> "\n",
		'row'		=> "\t\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t\t".'<td align="left" style="padding-left: 5px">%s%s</td>'."\n\t\t\t\t\t\t\t\t".'<td>%s %s</td>'."\n\t\t\t\t\t\t\t\t".
			'<td>%s %s (%s ago)</td>'."\n\t\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t".'</tr>',
		'flag'		=> '<img src="%s" style="margin: 1px 0px" alt="%s" title="%2$s" />',
	),
);

$THEME['donate'] = array(
	'inv_20'	=> "\n\t\t\t\t\t\t\t\t".'<option value="2">1 Invite</option>',
	'inv_30'	=> "\n\t\t\t\t\t\t\t\t".'<option value="2">2 Invites</option>',
	'inv_50'	=> "\n\t\t\t\t\t\t\t\t".'<option value="2">4 Invites</option>',
	'inv_cus'	=> "\n\t\t\t\t\t\t\t\t".'<option value="2">Invites</option>',
);

$THEME['index'] = array(
	'stats'	=> array(
		'recentu_join'	=> ','."\n",
	),
);

$THEME['my'] = array(
	'radio_checked'		=> ' checked="checked"',
	'check_checked'		=> ' checked="checked"',
	'list_selected'		=> ' selected="selected"',
	'country_entry'		=> "\t\t\t\t\t\t\t\t\t\t".'<option value="%u"%s>%s</option>',
	'country_join'		=> "\n",
	'timezone_entry'	=> "\t\t\t\t\t\t\t\t\t\t".'<option value="%s"%s>%s</option>',
	'timezone_join'		=> "\n",
	'dst_offset_entry'	=> "\t\t\t\t\t\t\t\t\t\t".'<option value="%d"%s>%s</option>',
	'dst_offset_join'	=> "\n",
	'catlist'			=> array(
		'per_row'		=> 5,
		'entry'			=> "\t\t\t\t\t\t\t\t\t\t\t".'<td><input name="c[]" type="checkbox" value="%u"%s /><a href="/browse.php?cat=%1$u" class="g_bnulink">%s</a></td>',
		'join'			=> "\n",
		'row_join'		=> "\n\t\t\t\t\t\t\t\t\t\t".'</tr>'."\n\t\t\t\t\t\t\t\t\t\t".'<tr>'."\n",
	),
	'irclist'			=> array(
		'per_row'		=> 4,
		'entry'			=> "\t\t\t\t\t\t\t\t\t\t\t".'<td><input name="chan_%s" type="checkbox" value="1"%s /> %s</td>',
		'join'			=> "\n",
		'row_join'		=> "\n\t\t\t\t\t\t\t\t\t\t".'</tr>'."\n\t\t\t\t\t\t\t\t\t\t".'<tr>'."\n",
	),
	'themes_entry'		=> "\t\t\t\t\t\t\t\t\t\t".'<option value="%u"%s>%s</option>',
	'themes_join'		=> "\n",
	'ficons_entry'		=> "\t\t\t\t\t\t\t\t\t".'<input type="radio" name="forum_buttons" value="%u"%s />%s',
	'ficons_img'		=> ' <img src="%s%s%s.png" alt="%2$s - %3$s" title="%2$s - %3$s" />',
	'ficons_join'		=> '<br /><br />'."\n",
	'link'				=> ' class="g_bbnulink"',
);

$THEME['snatches'] = array(
	'infin'				=> '&#8734;',
	'ratio'				=> '<span style="; color: %s">%s</span>',
	'blank'				=> '---',
	'na'				=> 'N/A',
	'join'				=> "\n",
);

$THEME['staff'] = array(
	'staff_join'		=> "\n",
	'staff_list_join'	=> "\n".'<br /><br />'."\n",
	'fls_join'			=> "\n",
	'bitbucket'			=> 'Use the following URL to access the file: <b><a href="%s" class="g_bblink">%1$s</a></b><br /><br />'."\n".
		'<a href="/staff.php#upload" class="g_bllink">Upload another file</a>.',
);

$THEME['theme'] = array(
	'ratio'	=> array(
		'infin'		=> '&#8734;',
		'ratio'		=> '<span style="color: %s">%s</span>',
		'blank'		=> '---',
		'ratios'	=> array(0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1),
		'colors'	=> array('#ff0000','#ee0000','#dd0000','#cc0000','#bb0000','#aa0000','#990000','#880000','#770000','#660000'),
		'def_color'	=> '#000000',
	),
);

$THEME['upapp'] = array(
	'catlist'		=> array(
		'per_row'	=> 3,
		'entry'		=> "\t\t\t\t\t\t\t\t\t\t\t".'<td><input name="cats[]" type="checkbox" value="%u" /><a href="/browse.php?cat=%1$u" class="g_bnulink">%s</a></td>',
		'join'		=> "\n",
		'row_join'	=> "\n\t\t\t\t\t\t\t\t\t\t".'</tr>'."\n\t\t\t\t\t\t\t\t\t\t".'<tr>'."\n",
	),
);

$THEME['edituser'] = array(
	'check_on'		=> ' checked="checked"',
	'radio_on'		=> ' checked="checked"',
	'list_on'		=> ' selected="selected"',
	'class_list'	=> array(
		'list'		=> "\t\t\t\t\t\t\t\t\t".'<select name="class" class="g_selectlist">'."\n".'%s'."\n\t\t\t\t\t\t\t\t\t".'</select>',
		'no_list'	=> "\t\t\t\t\t\t\t\t\t".'%s',
		'entry'		=> "\t\t\t\t\t\t\t\t\t\t".'<option value="%u"%s>%s</option>',
		'join'		=> "\n",
	),
	'warned_no'		=> 'No'."\n\t\t\t\t\t\t\t\t\t".'<span style="position: relative; left: 100px;">'."\n\t\t\t\t\t\t\t\t\t\t".'Warn for'.
		"\n\t\t\t\t\t\t\t\t\t\t".'<select name="warnlength" class="g_selectlist">'."\n\t\t\t\t\t\t\t\t\t\t\t".'<option value="0">-----</option>'."\n\t\t\t\t\t\t\t\t\t\t\t".
		'<option value="1">1 week</option>'."\n\t\t\t\t\t\t\t\t\t\t\t".'<option value="2">2 weeks</option>'."\n\t\t\t\t\t\t\t\t\t\t\t".'<option value="4">4 weeks</option>'.
		"\n\t\t\t\t\t\t\t\t\t\t\t".'<option value="8">8 weeks</option>'."\n\t\t\t\t\t\t\t\t\t\t\t".'<option value="255">Indefinitely</option>'."\n\t\t\t\t\t\t\t\t\t\t".
		'</select>'."\n\t\t\t\t\t\t\t\t\t".'</span>',
	'warned_yes'	=> '<input name="warned" value="1" type="radio" checked="checked" />Yes'."\n\t\t\t\t\t\t\t\t\t".
		'<input name="warned" value="0" type="radio" /> No'."\n\t\t\t\t\t\t\t\t\t".'&nbsp; &nbsp; (%s)',

	'irc_list'		=> array(
		'per_row'	=> 4,
		'entry'		=> "\t\t\t\t\t\t\t\t\t\t\t".'<td><input type="checkbox" name="chan_%s" value="1"%s /> %s</td>',
		'join'		=> "\n",
		'join_row'	=> "\n\t\t\t\t\t\t\t\t\t\t".'</tr>'."\n\t\t\t\t\t\t\t\t\t\t".'<tr>'."\n",
	),
);

$THEME['smilies'] = array(
	'per_row'		=> 5,
	'img_row'		=> "\t\t\t\t\t\t".'<tr class="smilies_primrow">'."\n".'%s'."\n\t\t\t\t\t\t".'</tr>',
	'img_col'		=> "\t\t\t\t\t\t\t".'<td><img src="%ssmilies/%s" alt="%s" title="%3$s" /></td>',
	'img_join'		=> "\n",
	'txt_row'		=> "\t\t\t\t\t\t".'<tr class="smilies_secrow">'."\n".'%s'."\n\t\t\t\t\t\t".'</tr>',
	'txt_col'		=> "\t\t\t\t\t\t\t".'<td>%s</td>',
	'txt_join'		=> "\n",
	'img_txt_join'	=> "\n",
	'join'			=> "\n",
);

$THEME['makepoll'] = array(
	'radio_on'		=> ' checked="checked"',
);

$THEME['svn'] = array(
	'row'			=> "\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t".'<td><b>%u</b></td>'."\n\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t".
		'<td>%s</td>'."\n\t\t\t\t\t\t".'</tr>',
	'join'			=> "\n",
);

$THEME['bans'] = array(
	'table'			=> "\t\t\t\t\t".'<table class="g_deftable signupbans_maintable">'."\n\t\t\t\t\t\t".'<tr class="g_deftable_toprow g_wide_tablerow">'."\n\t\t\t\t\t\t\t".
		'<td style="width: 105px">Added</td>'."\n\t\t\t\t\t\t\t".'<td style="width: 80px">First IP</td>'."\n\t\t\t\t\t\t\t".'<td style="width: 80px">Last IP</td>'.
		"\n\t\t\t\t\t\t\t".'<td style="width: 50px">By</td>'."\n\t\t\t\t\t\t\t".'<td style="width: 300px">Comment</td>'."\n\t\t\t\t\t\t\t".
		'<td style="width: 40px">Remove</td>'."\n\t\t\t\t\t\t".'</tr>'."\n".'%s'."\n\t\t\t\t\t".'</table>',
	'row'			=> "\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t".
		'<td>%s</td>'."\n\t\t\t\t\t\t\t".'<td>%s</td>'."\n\t\t\t\t\t\t\t".'<td><a href="/bans.php?remove=%u" class="g_bllink">Remove</a></td>'."\n\t\t\t\t\t\t".'</tr>',
	'join'			=> "\n",
);

$THEME['rsser'] = array(
	'radio_on'		=> ' checked="checked"',
	'check_on'		=> ' checked="checked"',
	'per_row'		=> 6,
	'entry'			=> "\t\t\t\t\t\t\t\t\t".'<td style="width: 15%%"><input type="checkbox" name="c[]" value="%u"%s /> %s</td>',
	'join'			=> "\n",
	'join_row'		=> "\n\t\t\t\t\t\t\t\t".'</tr>'."\n\t\t\t\t\t\t\t\t".'<tr>'."\n",
);

$THEME['viewnfo'] = array(
//	'colour'		=> array(61, 31, 12),
	'colour'		=> array(0, 0, 0),
	'bg_colour'		=> array(255, 0, 255),
);

$THEME['forum_post'] = array(
	'post_link'		=> "\n\t\t\t\t\t\t".'<a href="/forums_viewtopic.php?id=%u&amp;page=p%u" name="p%2$u" class="g_wblink">#%2$u</a>',
	'to_top'		=> "\n\t\t\t\t\t\t".'<a href="#top"><img src="%sforums_p_top.png" alt="top" title="Go to top" /></a>',
	'post_prev'		=> ' <span style="color: #ffffff">#xxxxx</span>',
	'last_edit'		=> '<div class="g_alignleft" style="float: left;">Last edited by %s at %s</div>',
	'last_post'		=> '<a name="last"></a>',
	'new'			=> '(<span class="browse_new_torrent">NEW!</span>) ',

	'tools'			=> array(
		'join'		=> ' -'."\n\t\t\t\t\t\t",
		'edit'		=> '<a href="/forums_post.php?editid=%u" class="g_bbnulink">Edit</a>',
		'delete'	=> '<a href="/forums_deletepost.php?id=%u" class="g_bbnulink">Remove</a>',
		'quote'		=> '<a href="/forums_post.php?postid=%u" class="g_bbnulink">Quote</a>',
		'viewe'		=> '<a href="/forums_viewedits.php?id=%u" class="g_bbnulink">View Edits</a>',
	),
);

$THEME['forums_post'] = array(
	'subject'		=> "\t\t\t\t\t\t\t".'<tr>'."\n\t\t\t\t\t\t\t\t".'<td align="right"><b>Subject</b></td>'."\n\t\t\t\t\t\t\t\t".
		'<td><input type="text" name="subject" class="g_s_input" size="129" maxlength="%u" value="%s" /></td>'."\n\t\t\t\t\t\t\t".'</tr>'."\n",

	'last_join'		=> "\n",						
);

$THEME['forums_viewforum'] = array(
	'sticky'		=> 'Sticky: ',
	'pages'			=> 'forums_f_finfo',
	'no_pages'		=> 'forums_f_flinfo',
	'multipage'		=> '<br /> <img src="%sforums_t_multipage.png" alt="mp" /> ',
	'no_posts'		=> '<p class="g_aligncenter i">You are not permitted to start new topics in this forum.</p>',

	'newtopic'      => "\t\t\t\t\t".'<form method="get" action="/forums_post.php">'."\n\t\t\t\t\t\t".'<p style="text-align: center">'."\n\t\t\t\t\t\t\t".
        '<input type="hidden" name="forumid" value="%u" />'."\n\t\t\t\t\t\t\t".'<input type="submit" value="New topic" class="btn" style="margin-left: 10px" />'.
        "\n\t\t\t\t\t\t".'</p>'."\n\t\t\t\t\t".'</form>',
);

$THEME['forums_viewtopic'] = array(
	'post_join'		=> "\n\n",
	'location_join'	=> "\n\t\t\t\t\t\t\t\t\t\t\t",
	'locations'		=> '<option value="%u"%s>%s</option>',

	'reply_disable'	=> 'disabled="disabled" ',

	'locked'		=> 'This topic is locked; no new posts are allowed.<br /><br />'."\n",
	'double_post'	=> '<em>Double Posting is not allowed, please edit your previous post.</em><br /><br />'."\n",
	'no_fpost'		=> '<em>You are not permitted to post in this forum.</em><br /><br />'."\n",
	'no_post'		=> '<em>Your posting rights have been disabled.</em><br /><br />'."\n",

	'radio_on'		=> ' checked="checked"',
	'check_on'		=> ' checked="checked"',
	'list_on'		=> ' selected="selected"',
);

$THEME['forums_deletepost'] = array(
	'sanity'		=> 'Sanity check: You are about to delete a post. Click <a href="/forums_deletepost.php?id=%u&amp;sure=1" class="g_bllink">here</a> if you are sure.',
	'error'			=> 'Can\'t delete post; it is the only post of the topic. You should <a href="/forums_viewtopic.php?id=%u#bottom" class="g_bllink">delete the topic</a> instead.',
);
?>
