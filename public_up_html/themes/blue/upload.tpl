				<div class="main_sm_section_top">
					Tracker URL
				</div>
				<div class="main_sm_section_body">
					{TRACKER_URL}
				</div>
				<div class="main_sm_section_bottom"></div>
				<div class="main_sm_section_top">
					Upload
				</div>
				<form enctype="multipart/form-data" action="/takeupload.php" method="post">
					<div class="main_sm_section_body">
						<input type="hidden" name="MAX_FILE_SIZE" value="{MAX_FILE_SIZE}" />
						<table class="upload_main_table g_lttable">
							<tr>
								<td align="right"><b>Torrent File</b></td>
								<td align="left"><input type="file" name="file" size="50" /></td>
							</tr>
							<tr>
								<td align="right"><b>Torrent Name</b></td>
								<td align="left"><input type="text" name="name" size="63" /></td>
							</tr>
							<tr>
								<td align="right"><b>NFO</b></td>
								<td align="left"><input type="file" name="nfo" size="50" /></td>
							</tr>
							<tr>
								<td align="right"><b>URL</b></td>
								<td align="left"><input type="text" name="url" size="63" /></td>
							</tr>
							<tr>
								<td align="right"><b>Description</b></td>
								<td align="left">
									<textarea name="descr" cols="47" rows="11"></textarea><br />
									<input type="checkbox" name="strip" value="1" checked="checked" /> Strip nfo garbage from description<br />
									<input type="checkbox" name="desc_nfo" value="1" /> Get description from NFO (must include nfo file)
								</td>
							</tr>
							<tr>
								<td align="right"><b>Type</b></td>
								<td align="left">
									<select name="type">
										<option value="0">(choose one)</option>
{TYPE_LIST}
									</select>
								</td>
							</tr>
							<tr>
								<td align="right"><b>Upload As</b></td>
								<td align="left"><input type="radio" name="anon" value="0"{ANON_UNCHECKED} /> {USER_NAME} <input type="radio" name="anon" value="1"{ANON_CHECKED} /> <em>Anonymous</em></td>
							</tr>
							<tr>
								<td colspan="2">
									<input type="submit" value="UPLOAD" />
								</td>
							</tr>
						</table>
					</div>
				</form>
				<div class="main_sm_section_bottom"></div>
