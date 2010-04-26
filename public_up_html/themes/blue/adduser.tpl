				<div class="main_sm_section_top">Add User</div>
				<form action="/adduser.php" method="post">
					<div class="main_sm_section_body">
						<input type="hidden" name="hash" value="{FORM_HASH}" />
						<table class="upload_main_table g_lttable">
							<tr>
								<td align="right"><b>Username</b></td>
								<td align="left"><input type="text" name="new_username" size="62" /></td>
							</tr>
							<tr>
								<td align="right"><b>Password</b></td>
								<td align="left"><input type="password" name="new_password" size="62" /></td>
							</tr>
							<tr>
								<td align="right"><b>Re-type password</b></td>
								<td align="left"><input type="password" name="new_password2" size="62" /></td>
							</tr>
							<tr>
								<td align="right"><b>E-mail</b></td>
								<td align="left"><input type="text" name="new_email" size="62" /></td>
							</tr>
							<tr>
								<td colspan="2">
									<input type="submit" value="Okay" />
								</td>
							</tr>
						</table>
					</div>
				</form>
				<div class="main_sm_section_bottom"></div>
