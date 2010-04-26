				<div class="main_section_top">
					Edit User ({USER_LINK})
				</div>
				<form action="/mod_edituser.php" method="post">
					<div class="main_section_body">
						<input type="hidden" name="hash" value="{HASH}" />
						<input type="hidden" name="userid" value="{ID}" />
						<table class="g_noctable">
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
								<td class="edituser_seccol" style="text-align: center" colspan="3">
									<input type="text" name="reason" size="120" maxlength="512" class="g_s_input"/><br /><br />
									<span style="color: #000; position:relative; left: 12px; font-weight: bold">
										Required for Warning, Disabling, Banning of account and Revoking of Post / IRC Access
									</span>
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
