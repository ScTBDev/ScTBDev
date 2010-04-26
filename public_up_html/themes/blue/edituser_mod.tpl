				<div class="main_section_top">
					Edit User ({USER_LINK})
				</div>
				<form action="/mod_edituser.php" method="post">
					<div class="main_section_body">
						<input type="hidden" name="hash" value="{HASH}" />
						<input type="hidden" name="userid" value="{ID}" />
						<table class="g_noctable">
							<tr>
								<td class="edituser_primcol">Username</td>
								<td class="edituser_seccol">
									<input type="text" name="username" size="50" value="{USERNAME}" class="g_s_input"/>
								</td>
								<td class="edituser_primcol">Title</td>
								<td class="edituser_seccol">
									<input type="text" name="title" size="50" value="{TITLE}" class="g_s_input" />
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Email</td>
								<td class="edituser_seccol">
									<input type="text" name="email" size="50" value="{EMAIL}" class="g_s_input"/>
								</td>
								<td class="edituser_primcol">Password</td>
								<td class="edituser_seccol">
									<input type="checkbox" name="reset_pw" value="1" /> Reset password
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Avatar</td>
								<td class="edituser_seccol">
									<input type="text" name="avatar" size="50" value="{AVATAR}" class="g_s_input"/>
								</td>
								<td class="edituser_primcol">Offensive</td>
								<td class="edituser_seccol">
									<input type="radio" name="avatar_po" value="1"{OFFENSIVE_ON} /> Yes
									<input type="radio" name="avatar_po" value="0"{OFFENSIVE_OFF} /> No
								</td>
							</tr>
                            <tr>
								<td class="edituser_primcol">Enabled</td>
								<td class="edituser_seccol">
									<input type="radio" name="enabled" value="1"{ENABLED_ON} /> Yes
									<input type="radio" name="enabled" value="0"{ENABLED_OFF} /> No
								</td>
								<td class="edituser_primcol">Passkey</td>
								<td class="edituser_seccol">
									<input type="checkbox" name="reset_pk" value="1" /> Reset passkey
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Class</td>
								<td class="edituser_seccol">
{CLASS_LIST}
								</td>
								<td class="edituser_primcol">S / L</td>
								<td class="edituser_seccol">
									<input type="checkbox" name="reset_sl" value="1" /> Clear Seeds / Leechs
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Warned</td>
								<td class="edituser_seccol">
									{WARNED}
								</td>
								<td class="edituser_primcol">Posting<br /><br />IRC Access</td>
								<td class="edituser_seccol">
									<input type="radio" name="post_en" value="1"{POST_ON} /> Allowed
									<input type="radio" name="post_en" value="0"{POST_OFF} /> Revoked<br /><br />

									<input type="radio" name="irc_en" value="1"{IRC_ON} /> Allowed
									<input type="radio" name="irc_en" value="0"{IRC_OFF} /> Revoked
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Reason</td>
								<td class="edituser_seccol" style="text-align: center">
									<input type="text" name="reason" size="50" maxlength="512" class="g_s_input"/><br /><br />
									<span style="color: #000; position:relative; left: 12px; font-weight: bold">
										Required for Warning, Disabling, Banning of account and Revoking of Post / IRC Access
									</span>
								</td>
								<td class="edituser_primcol">IP Access</td>
								<td class="edituser_seccol">
									<textarea cols="38" rows="4" name="ip_access" class="g_s_input">{IP_ACCESS}</textarea>
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">First Line Support</td>
								<td class="edituser_seccol">
									<input type="radio" name="fls" value="1"{FLS_ON} /> Yes
									<input type="radio" name="fls" value="0"{FLS_OFF} /> No
									<span style="position: relative; left: 120px; font-weight: bold">Languages:</span><br />
									<input type="text" name="flsl" size="50" class="g_s_input" value="{FLS_LANG}" />
								</td>
								<td class="edituser_primcol">Can Help With</td>
								<td class="edituser_seccol">
									<textarea name="flshw" cols="38" rows="2" class="g_s_input">{FLS_HELP}</textarea>
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Ban IP</td>
								<td class="edituser_seccol">
									<input type="radio" name="ban" value="1"{BAN_ON} /> Yes
									<input type="radio" name="ban" value="0"{BAN_OFF} /> No
								</td>
								<td class="edituser_primcol">Protect IP</td>
								<td class="edituser_seccol">
									<input type="radio" name="protect" value="1"{PROTECT_ON} /> Yes
									<input type="radio" name="protect" value="0"{PROTECT_OFF} /> No
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Bypass Bans</td>
								<td class="edituser_seccol">
									<input type="radio" name="bypass_ban" value="1"{BYPASS_ON} /> Yes
									<input type="radio" name="bypass_ban" value="0"{BYPASS_OFF} /> No
								</td>
								<td class="edituser_primcol">Invites</td>
								<td class="edituser_seccol">
									<input type="text" name="invites" size="10" class="g_s_input" value="{INVITES}" />
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Profile</td>
								<td class="edituser_seccol" colspan="3">
									<textarea name="info" class="g_s_input" cols="97" rows="10">{INFO}</textarea>
								</td>
							</tr>
							<tr>
								<td class="edituser_primcol">Comment</td>
								<td class="edituser_seccol" colspan="3">
									Add a comment: <input type="text" name="add_comment" size="104" style="margin-bottom: 3px;" class="g_s_input" />
									<textarea class="g_s_input" name="modcomment" cols="97" rows="7" readonly="readonly">{MOD_COMMENTS}</textarea>
								</td>
							</tr>
							<tr>
								<td colspan="4" class="edituser_midcol">
									<input type="submit" value="Okay" />
									<input type="reset" value="Cancel" />
								</td>
							</tr>
						</table>
					</div>
				</form>
				<div class="main_section_bottom"></div>
