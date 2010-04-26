				<div class="forums_p_qreply_top"></div>
				<div class="forums_p_qreply_body g_aligncenter">
					<div style="font-size:13px; font-weight:bold; margin-bottom: 4px;">Add Ban</div>
					<form action="/bans.php" method="post">
						<table class="g_lttable upload_main_table" style="margin-bottom: 5px;">
							<tr>
								<td align="right"><b>First IP</b></td>
								<td><input type="text" name="first" size="53" class="g_s_input" /></td>
							</tr>
							<tr>
								<td align="right"><b>Last IP</b></td>
								<td><input type="text" name="last" size="53" class="g_s_input" /></td>
							</tr>
							<tr>
								<td align="right"><b>Comment</b></td>
								<td><input type="text" name="comment" size="53" class="g_s_input" /></td>
							</tr>
							<tr>
								<td colspan="2"><input type="submit" class="g_s_input" value="Add" /></td>
							</tr>
						</table>
					</form>
				</div>
				<div class="forums_p_qreply_bottom"></div><br /><br />

				<div class="main_section_top">Signup Bans</div>
                <div class="main_section_body g_aligncenter">
					{PAGER}
{BANS_TABLE}
					{PAGER}
				</div>
				<div class="main_section_bottom"></div>
