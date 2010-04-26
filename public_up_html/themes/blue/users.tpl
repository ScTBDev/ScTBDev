				<div class="main_section_top">Users</div>
				<form method="get" action="/users.php">
					<div class="main_section_body">
						Search: <input type="text" name="search" size="30" class="g_s_input"/>&nbsp;
						<select name="class" class="g_selectlist">
							<option value="-1">(any class)</option>
{CLASS_LIST}
						</select>&nbsp;
						<select name="country" class="g_selectlist">
							<option value="0">(any country)</option>
{COUNTRY_LIST}
						</select>&nbsp;
						<input type="submit" class="g_s_input" value="Search" />
						<p>
{PAGE_LINKS}
						</p>
						{PAGER}
						<br />
{USERS_LIST}
						<br />
					{PAGER}
					</div>
				</form>
				<div class="main_section_bottom"></div>
