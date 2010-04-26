				<div class="main_section_top">Donations Credits</div>
				<div class="main_section_body g_alignleft">
					<!--div class="g_aligncenter">
						<img src="{THEME_PIC_DIR}donpromo.png" alt="Promo" />
						<br /><br />
					</div-->
					<form action="/take_donate.php" method="post">
						<div class="donate_item">
							<input type="hidden" name="id" value="{ID}" />
							<input type="hidden" name="amount" value="10" />
							<input type="image" src="{THEME_PIC_DIR}donate_10e.png" name="submit" alt="Donate" style="border: none" /><br />
							<select name="credits" class="g_selectlist">
								<option value="0">No Credits</option>
								<option value="1">10GB Upload</option>
							</select>
						</div>
					</form>
					<form action="/take_donate.php" method="post">
						<div class="donate_item">
							<input type="hidden" name="id" value="{ID}" />
							<input type="hidden" name="amount" value="20" />
							<input type="image" src="{THEME_PIC_DIR}donate_20e.png" name="submit" alt="Donate" style="border: none" /><br />
							<select name="credits" class="g_selectlist">
								<option value="0">No Credits</option>
								<option value="1">25GB Upload</option>{INV_20}
							</select>
						</div>
					</form>
					<form action="/take_donate.php" method="post">
						<div class="donate_item">
							<input type="hidden" name="id" value="{ID}" />
							<input type="hidden" name="amount" value="30" />
							<input type="image" src="{THEME_PIC_DIR}donate_30e.png" name="submit" alt="Donate" style="border: none" /><br />
							<select name="credits" class="g_selectlist">
								<option value="0">No Credits</option>
								<option value="1">40GB Upload</option>{INV_30}
							</select>
						</div>
					</form>
					<form action="/take_donate.php" method="post">
						<div class="donate_item">
							<input type="hidden" name="id" value="{ID}" />
							<input type="hidden" name="amount" value="50" />
							<input type="image" src="{THEME_PIC_DIR}donate_50e.png" name="submit" alt="Donate" style="border: none" /><br />
							<select name="credits" class="g_selectlist">
								<option value="0">No Credits</option>
								<option value="1">70GB Upload</option>{INV_50}
							</select>
						</div>
					</form>
					<form action="/take_donate.php" method="post">
						<div class="donate_item">
							<input type="hidden" name="id" value="{ID}" />
							<input type="image" src="{THEME_PIC_DIR}donate_custom.png" name="submit" alt="Donate" style="border: none" /><br />
							<select name="credits" class="g_selectlist">
								<option value="0">No Credits</option>
								<option value="1">Upload</option>{INV_CUS}
							</select>
							<input type="text" maxlength="3" name="amount" value="0" size="1" class="g_s_input" />
						</div>
					</form>

					<div class="g_space_line"></div><br />
					<b>In order to get upload credit or invites, you must choose which you want directly under the appropriate donate button before you click it. For the custom donations,
						the ammount of credit you get per euro is based on how much you donate:</b><br />
					<ul>
						<li>10.00 - 19.99&euro; will give you 1GB per euro.</li>
						<li>20.00 - 29.99&euro; will give you 1.25GB per euro <b>OR</b> 1 invite.</li>
						<li>30.00 - 49.99&euro; will give you 1.3125GB per euro <b>OR</b> 2 invites.</li>
						<li>50.00+&euro; will give you 70GB for the first 50.00&euro; plus 1.5GB per euro for the remaining euros <b>OR</b> 4 invites.</li>
					</ul>
					<b>Disclaimer:</b><br />
					<ul>
						<li>Donations <b>DO NOT</b> exempt you from the rules or from being banned.</li>
						<li>Donations for Upload credit will increase your ratio, but normal ratio rules will still apply.</li>
						<li>Upload credits that you get via donations will not allow you to be automatically promoted to higher user classes, this can only be done by actually seeding.</li>
						<li>You cannot get any more than 4 invites per donation.</li>
						<li>All rules still apply to any invites you get, so use them wisely.</li>
					</ul>
				</div>
				<div class="main_section_bottom"></div>

				<div class="main_section_top">All Donations</div>
				<div class="main_section_body g_alignleft">
					All Donations are appreciated. We want to thank everybody who decides to donate to help support SceneTorrents.<br />
					Any Donation 10&euro; and above will get you a star (<img src="{THEME_PIC_DIR}donor_small.png" alt="star" title="Donor" />), a custom title, and a voice on IRC.<br /><br />

					Donation Stars, Upload Credit, and Invites are handled automatically through PayPal IPN.
					Custom titles are set through <a href="/my.php" class="g_bllink">your profile page</a>.
					To get your voice on IRC you must enter the IRC channel and ask a staff member for your auto-voice. 
				</div>
				<div class="main_section_bottom"></div>
                
				<div class="main_section_top">Important Notes</div>
				<div class="main_section_body g_alignleft">
					<ul>
						<li><b>NOTE 1:</b> No stars, credits or invites will be given until the transaction status is &quot;completed&quot;. This means that if you use an eCheck payment or do not
							have sufficent funds in your paypal account, it can take up to an additional 7 days or more to complete.</li>
						<li><b>NOTE 2:</b> <span style="color: red;">Any unauthorized transactions (this includes credit card chargebacks) will just end up getting reversed and will result in your
							account getting disabled along with anyone you may have invited with invites from donations. You have been warned.</span></li>
					</ul>
					<br />
					<b>If you have any problems with your donation like not getting a star or proper credit, <a href="/sendmessage.php?receiver=6" class="g_bblink">send us</a> a pm so we can
						credit your account! (Please allow up to 2 days after the paypal transaction status is &quot;completed&quot; before pming about problems, paypal can take a while to post
						back donation details to us sometimes)</b>
				</div>
				<div class="main_section_bottom"></div>
