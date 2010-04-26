{PREVIEW}
				<div class="main_section_top">{ACTION}</div>
				<form method="post" action="/forums_post.php">
					<div class="main_section_body">
						<input type="hidden" name="{TYPE}" value="{ID}" />
						<table class="g_lttable signupbans_maintable">
{SUBJECT}							<tr>
								<td align="right" style="vertical-align: top; width: 50px;"><b>Body</b></td>
								<td style="width: 666px">
									<textarea name="body" class="g_s_input" rows="12" cols="105">{BODY}</textarea>
								</td>
							</tr>
							<tr>
								<td style="vertical-align: middle; text-align: center;">
									<a href="/tags.php" class="g_bblink">Tags</a><br />
									<a href="/smilies.php" class="g_bblink">Smilies</a>
								</td>
								<td>
									<input type="hidden" name="hash" value="{FORM_HASH}" />
									<input type="submit" name="preview" value="Preview" style="width: 70px; height: 30px;" />
									<input type="submit" value="Post" style="width: 70px; height: 30px;" />
								</td>
							</tr>
						</table>
					</div>
				</form>
				<div class="main_section_bottom"></div>
				<div class="g_space_line"></div>
{LAST}
