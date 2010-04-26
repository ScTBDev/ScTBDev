{ERROR_MSG}				<div class="main_sm_section_top">
					Login
				</div>
				<form action="takelogin.php" method="post">
					<div class="main_sm_section_body">
						<input type="hidden" name="hash" value="{FORM_HASH}" />
						{RETURN_TO}
						<br />
						<table class="login_table">
							<tr>
								<td>Username:</td><td><input type="text" size="30" name="username" /></td>
							</tr>
							<tr>
								<td>Password:</td><td><input type="password" size="30" name="password" /></td>
							</tr>
							<tr>
								<td align="right">SSL:</td><td><input type="checkbox" name="ssl" value="1" {SSL_CHECKED}/>&nbsp;Browse site with SSL Encryption (HTTPS)</td>
							</tr>
						</table>
						<span class="g_alignright" style="position:relative; left: 230px; top: 10px;"><a href="recover.php" class="g_bbnulink">Reset Password</a></span>
					</div>
					<div class="login_box_bottom g_aligncenter">
						<input type="submit" value="Login" style="width: 75px; height: 25px;" />
						<div style="font-size: 10px; position: relative; top: 17px;" class="g_aligncenter">
							Note: You need cookies enabled to log in.<br />
							Also Note: Some Antivirus programs / Firewalls with certain privacy options enabled will make it impossible to login.
						</div>
					</div>
				</form>
