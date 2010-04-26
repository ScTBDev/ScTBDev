				<div class="main_section_top">
					Site Log
				</div>
				<form action="/log.php" method="get">
	                <div class="main_section_body">
						Search Log: <input type="text" name="search" size="40" />&nbsp; <input type="submit" value="Search" /><br /><br />
						{PAGER}
						<div class="g_space_line"></div>
{LOG_TABLE}
						<div class="g_space_line"></div>
						{PAGER}
					</div>
				</form>
				<div class="main_section_bottom"></div>
