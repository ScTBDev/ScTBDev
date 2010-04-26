				<div class="signup_proxycheck">
					<div class="g_aligncenter" style="font-size: 18px; font-weight: bolder;">Proxy check</div>
					<span style="color:red; font-weight: bold;">Important - please read:</span> We do not accept users connecting through open proxies (this includes Tor proxies).
						When you submit the form below, we will check your IP against a blacklist of known open proxies. The IP address we will test is <b>127.0.0.1</b>. By submitting
						the form below, you agree that you are not connecting through an open or abused proxy.
				</div>
                
				<div class="main_sm_section_top">Signup</div>
				<form action="/takesignup.php" method="post">
					<div class="main_sm_section_body">
						<input type="hidden" name="id" value="{ID}" />
						<input type="hidden" name="invite" value="{INVITE}" />
						<table class="upload_main_table g_lttable">
							<tr>
								<td align="right"><b>Username</b></td>
								<td align="left"><input type="text" name="wantusername" size="40" maxlength="12" class="g_s_input" /></td>
							</tr>
							<tr>
								<td align="right"><b>Password</b></td>
								<td align="left"><input type="password" name="wantpassword" size="40" class="g_s_input" /></td>
							</tr>
							<tr>
								<td align="right"><b>Repeat Password</b></td>
								<td align="left"><input type="password" name="passagain" size="40" class="g_s_input" /></td>
							</tr>
							<tr>
								<td align="right"><b>E-mail</b></td>
								<td align="left">
									<input type="text" name="email" size="40" maxlength="80" class="g_s_input" style="margin-bottom: 3px;" /><br />
									The e-mail address must be valid. You will receive a<br />confirmation e-mail which you need to respond to.<br />
									The e-mail address won't be publicly shown<br />anywhere.
								</td>
							</tr>
							<tr>
								<td align="right"><b>Country</b></td>
								<td align="left">
									<select name="country" class="g_selectlist">
										<option value="0">---- None selected ----</option>
{COUNTRY_LIST}
									</select>
								</td>
							</tr>
							<tr>
								<td align="right"></td>
								<td align="left">
									<input type="checkbox" name="rulesverify" value="1" />I have read the <a href="/rules.php" class="g_dblink">site rules</a>.<br />
									<input type="checkbox" name="faqverify" value="1" />I agree to read the <a href="/faq.php" class="g_dblink">FAQ</a> before asking questions.<br />
									<input type="checkbox" name="ageverify" value="1" />I am at least 18 years old.
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<input type="submit" value="Register" />
								</td>
							</tr>
						</table>
					</div>
				</form>
				<div class="main_sm_section_bottom"></div>
