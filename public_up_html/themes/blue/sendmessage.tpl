				<div class="main_sm_section_top">
					Message to {USER_NAME}
				</div>
				<div class="g_space_line"></div>
				<form method="post" action="/takemessage.php">
					<div class="main_sm_section_body">
						{RETURN_TO}
						<input type="text" name="subject" style="width: 530px;" value="{SUBJECT}" class="g_s_input" /><br />
						<textarea name="msg" rows="12" cols="1" style="width: 530px; margin: 5px auto;" class="g_s_input">{MSG}</textarea><br />

						<div class="sendmessage_bottom_options nobr">
							<input type="checkbox" name="save" value="1"{SAVE_CHECKED} /> Save message to sentbox &nbsp;
							{DELETE} &nbsp; &nbsp; &nbsp;
							<a href="/tags.php" class="g_bblink">Tags</a> - <a href="/smilies.php" class="g_bblink">Smilies</a>
						</div>
					</div>
					<div class="sendmessage_box_bottom">
						<input type="hidden" name="receiver" value="{RECEIVER}" />
						<input type="hidden" name="hash" value="{FORM_HASH}" />
						<input type="submit" value="Send" style="height: 25px; width: 100px;" />
					</div>
				</form>
