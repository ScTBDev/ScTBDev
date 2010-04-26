
					<br /><br />
					<form method="post" action="/forums_set.php">
						<div class="g_aligncenter">
							<input type="hidden" name="id" value="{TOPIC_ID}" />
							<input type="hidden" name="page" value="{PAGE}" />
							<table class="forums_p_topictools">
								<tr>
									<td>
										<b>Sticky:</b> <input type="radio" name="sticky" value="1" {STICKY_ON}/> Yes
										<input type="radio" name="sticky" value="0" {STICKY_OFF}/> No
									</td>
									<td>
										<b>Locked:</b> <input type="radio" name="locked" value="1" {LOCKED_ON}/> Yes
										<input type="radio" name="locked" value="0" {LOCKED_OFF}/> No
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<b>Topic name:</b> <input type="text" name="subject" value="{SUBJECT}" size="40" />
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<b>Location:</b>
										<select name="location">
											{LOC_LIST}
										</select>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<b>Delete topic:</b>
										<input type="checkbox" name="delete" value="1" />
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<input type="hidden" name="hash" value="{FORM_HASH}" />
										<input type="submit" value="Update" style="width: 120px;" />
									</td>
								</tr>
							</table>
						</div>
					</form>
