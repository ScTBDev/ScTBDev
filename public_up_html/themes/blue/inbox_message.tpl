					<div class="inbox_section_top">
						<div class="inbox_top_checkbox">
							<input type="checkbox" name="delete[]" value="{ID}" />
						</div>
						<div class="inbox_top_left nobr">
							{TO_FROM} {USER_NAME}{STARS} ({USER_TITLE}) - Subject: <span style="text-decoration: underline; font-weight: bold;"{SUBJECT_TITLE}>{SUBJECT}</span>
						</div>
						<div class="inbox_top_right nobr">
							{NEW}at {DATE} {TIME} ({AGO} ago)
						</div>
						<div class="g_space_line"></div>
					</div>
					<div class="inbox_section_body g_alignleft">
						<div class="inbox_pm_avatar">
							<img src="{AVATAR}" alt="avatar"{AVATAR_TXT} />
						</div>
						<div class="inbox_pm_content">
{MESSAGE}
						</div>
						<div class="g_space_line"></div>
					</div>
					<div class="inbox_section_bottom">
						<div class="inbox_section_bottom_inner">
							<div class="g_alignright nobr">
								{REPLY}<a href="/deletemessage.php?id={ID}&amp;type={LOC}" class="g_bbnulink">Delete</a>
							</div>
						</div>
					</div>

