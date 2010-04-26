				<div class="main_section_top">Report user: {USER_LINK}</div>
				<div class="main_section_body g_alignleft">
					Please report users only if:
					<ul>
						<li>You suspect them of cheating (please provide some proof).</li>
						<li>They have broken the rules in any way, and you think the staff are not aware of it.</li>
						<li>You suspect them of uploading torrents downloaded from ScT elsewhere, not to be confused with seeding (Please provide screenshots
							or some form of proof).</li>
					</ul>
					
					Please note, this is <b>NOT</b> to be used to report leechers. Our system takes care of them automatically. 
					Please do <b>NOT</b> abuse this page, as it will result in a warning.<br /><br />

					Reason (required, <a href="tags.php" class="g_bllink">BBcode</a> is supported):
					
					<form action="/takereport.php" method="post">
						<div>
							<textarea name="reason" cols="60" rows="5" class="g_s_input"></textarea><br /><br />
							<input type="hidden" name="user" value="{ID}" />
							<input type="submit" value="Send Report" />
						</div>
					</form>
				</div>
				<div class="main_section_bottom"></div>
