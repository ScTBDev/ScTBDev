{MSG}
				<div class="main_section_top">Settings</div>
				<div class="main_section_body">
					<form action="/takeprofedit.php" method="post">
						<table class="g_my_maintable">
							<tr>
								<td colspan="2" class="my_col1" style="padding: 0px;">
									<table style="margin: 0; width: 735px;">
										<tr>
											<td style="width: 312px; text-align: center; padding: 5px 0px 5px 0px;">
												<a href="/snatches.php" class="g_bbnulink" style="font-size:14px;">Snatches</a>	
											</td>
											<td style="width: 312px; text-align: center; border-left: 1px solid #425c6b; padding: 5px 0px 5px 0px;">
												<a href="/friends.php" class="g_bbnulink" style="font-size:14px;">Friends</a>
											</td>
										</tr>	
									</table>
								</td>
							</tr>
							<!--			End of Header			-->
							<tr>
								<td class="my_col1" style="width: 110px">Accept PMs</td>
								<td class="my_col2" style="width: 625px">
									<input type="radio" name="acceptpms" value="2"{ACCEPT_PM_ALL} /> All (except blocks)
									<input type="radio" name="acceptpms" value="1"{ACCEPT_PM_FRIENDS} /> Friends only
									<input type="radio" name="acceptpms" value="0"{ACCEPT_PM_STAFF} /> Staff only
								</td>
							</tr>
							<tr>
								<td class="my_col1">Delete PMs</td>
								<td class="my_col2"><input type="checkbox" name="deletepms" value="1"{DELETE_PM} /> Delete received PMs on Reply (by default)</td>
							</tr>
							<tr>
								<td class="my_col1">Save PMs</td>
								<td class="my_col2"><input type="checkbox" name="savepms" value="1"{SAVE_PM} /> Save sent PMs to sentbox</td>
							</tr>
							<tr>
								<td class="my_col1">E-mail notification</td>
								<td class="my_col2"><input type="checkbox" name="pmnotif" value="1"{PM_NOTIF} /> Notify by e-mail, when a new PM has been received</td>
							</tr>

							<tr>
								<td class="my_col1">Proxy</td>
								<td class="my_col2">
									<input type="checkbox" name="proxy" value="1"{PROXY} /> Use alternate tracker port<br />
									<span class="g_small">
										Check this if you are having problems with a transparent proxy<br />
										server that is causing problems with your torrent client.
									</span>
								</td>
							</tr>
							<tr>
								<td class="my_col1">SSL Tracker</td>
								<td class="my_col2">
									<input type="checkbox" name="ssl_tracker" value="1"{SSL_TRACKER} /> Use SSL tracker port<br />
									<span class="g_small">
										Please check this only if you are having trouble with your ISP blocking<br />
										BitTorrent Tracker Connections or if you are very paranoid :P<br />
										Note: Only a few BT programs support SSL Trackers (uTorrent, Azureus, etc.)
									</span>
								</td>
							</tr>
							<tr>
								<td class="my_col1">SSL Site</td>
								<td class="my_col2">
									<input type="checkbox" name="ssl_site" value="1"{SSL_SITE} /> Force SSL on website<br />
									<span class="g_small">
										If you prefer to browse the website over a secure connection, and would like<br />
										to be automatically redirected to HTTPS when clicking on an HTTP link,<br />
										enable this option.
									</span>
								</td>
							</tr>
							<tr>
								<td class="my_col1">Avatar</td>
								<td class="my_col2">
									<input type="text" name="avatar" value="{AVATAR_URL}" size="50" class="g_s_input"/><br />
									<span class="g_small">
										Width should be 150 pixels. (will be resized if necessary)<br />
										If you need a host, try <a href="/out.php?url=http://www.tinypic.com" class="g_bbnulink">tinypic</a> or 
											<a href="/out.php?url=http://www.imageshack.us" class="g_bbnulink">imageshack</a>.<br /><br />
									</span>

									<input type="checkbox" name="avatar_po" value="1"{AVATAR_PO} /> <b>This avatar may be offensive to some people</b><br />
									<span class="g_small">
										Please check this box if your avatar depicts nudity, or may<br />
										otherwise be potentially offensive or unsuitable for minors.
									</span>
								</td>
							</tr>
							<tr>
								<td class="my_col1">Show Avatars</td>
								<td class="my_col2">
									<input type="radio" name="avatars" value="2"{AVATARS_ALL} /> All<br />
									<input type="radio" name="avatars" value="1"{AVATARS_SOME} /> All except potentially offensive ones<br />
									<input type="radio" name="avatars" value="0"{AVATARS_NONE} /> None (Good for low bandwidth users)<br />
								</td>
							</tr>
							<tr>
								<td class="my_col1">Show Statbar</td>
								<td class="my_col2">
									<input type="checkbox" name="statbar" value="1"{STATBAR} /> Turn on statbar at top of page<br />
								</td>
							</tr>
							<tr>
								<td class="my_col1">Torrents per page</td>
								<td class="my_col2"><input type="text" name="torrentsperpage" size="10" class="g_s_input" value="{TORRENTS_PP}" /> (0 - use default)</td>
							</tr>
							<tr>
								<td class="my_col1">Topics per page</td>
								<td class="my_col2"><input type="text" name="topicsperpage" size="10" class="g_s_input" value="{TOPICS_PP}" /> (0 - use default)</td>
							</tr>
							<tr>
								<td class="my_col1">Posts per page</td>
								<td class="my_col2"><input type="text" name="postsperpage" size="10" class="g_s_input" value="{POSTS_PP}" /> (0 - use default)</td>
							</tr>
							<tr>
								<td class="my_col1">Theme</td>
								<td class="my_col2">
									<select name="theme" class="g_selectlist">
{THEME_LIST}
									</select>
								</td>
							</tr>
							<tr>
								<td class="my_col1">Forum Buttons</td>
								<td class="my_col2">
{FORUM_ICONS}
								</td>
							</tr>
							<tr>
								<td class="my_col1">Country</td>
								<td class="my_col2">
									<select name="country" class="g_selectlist">
										<option value="0">---- None selected ----</option>
{COUNTRY_LIST}
									</select>
								</td>
							</tr>
							<tr>
								<td class="my_col1">Timezone</td>
								<td class="my_col2">
									<select name="timezone" class="g_selectlist">
{TIMEZONE_LIST}
									</select>
								</td>
							</tr>
							<tr>
								<td class="my_col1">DST</td>
								<td class="my_col2">
									<input name="dst" type="checkbox" value="1"{DST} />Currently observing Daylight Savings Time with an offset of 
									<select name="dst_offset" class="g_selectlist">
{DST_OFFSET_LIST}
									</select>
								</td>
							</tr>

							<tr>
								<td class="my_col1">Default browse categories</td>
								<td class="my_col2">
									<table class="my_maintable">
										<tr>
{CAT_TABLE}
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="my_col1">IRC Channels</td>
								<td class="my_col2">
									<table class="my_ircchan_table">
										<tr>
{IRC_TABLE}
										</tr>
									</table>
									<span class="g_small">Select the channels you would like to be invited to when issuing the IRC invite command.</span>
								</td>
							</tr>
{WHORE}
{DONATE}
{STAFF}
							<!--			Bottom of Page			-->
							<tr>
								<td class="my_col1">Profile Info</td>
								<td class="my_col2">
									<textarea name="info" rows="12" cols="60" class="g_s_input">{PROFILE_INFO}</textarea><br />
									<span class="g_small">Displayed on your public page. May contain <a href="/tags.php" class="g_bbnulink">BB codes</a>.</span>
								</td>
							</tr>
							<tr>
								<td class="my_col1">E-mail Address</td>
								<td class="my_col2">
									<input type="text" size="50" name="email" class="g_s_input" value="{EMAIL}" /><br />
									<span class="g_small">In order to change your email address, you will receive another confirmation email to your new address.</span>
								</td>
							</tr>
							<tr>
								<td class="my_col1">Old Password</td>
								<td class="my_col2"><input type="password" size="50" name="oldpassword" class="g_s_input" /></td>
							</tr>
							<tr>
								<td class="my_col1">Change Password</td>
								<td class="my_col2"><input type="password" size="50" name="chpassword" class="g_s_input" /></td>
							</tr>
							<tr>
								<td class="my_col1">Password again</td>
								<td class="my_col2"><input type="password" size="50" name="passagain" class="g_s_input" /></td>
							</tr>
							<!--				Submit				-->
							<tr>
								<td colspan="2" class="my_col3">
									<input type="hidden" name="hash" value="{FORM_HASH}" />
									<input type="submit" value="Update Profile" style="width: 150px;" /> &nbsp; <input type="reset" value="Cancel" style="width: 150px;" />
								</td>
							</tr>
						</table>
					</form>
				</div>
				<div class="main_section_bottom"></div>
