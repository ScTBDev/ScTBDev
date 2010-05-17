				<div class="main_section_top">Feed URL</div>
				<div class="main_section_body g_alignleft">
					<a href="{FEED_URL}" class="g_bllink">{FEED_URL}</a>
				</div>
				<div class="main_section_bottom"></div>
				<div class="main_section_top">RSS Feed Configurator</div>
				<form action="/rsser.php" method="post">
					<div class="main_section_body g_alignleft">
						<b>Feed:</b> <input type="radio" name="feed" value="1"{FEED_1} /> Download &nbsp; <input type="radio" name="feed" value="2"{FEED_2} /> Standard<br />
						<b>Number of releases:</b> <input type="text" name="num" value="{NUM}" size="2" class="g_s_input" /> (min 10, max 100)<br />
						<b>SSL:</b> <input type="checkbox" name="ssl" value="1"{SSL_ON} /> Use a SSL encrypted feed (HTTPS)<br /><br />
						<b>Categories:</b> (selecting none means all categories will be listed)<br />
						<table class="my_maintable">
							<tr>
{CAT_TABLE}
							</tr>
						</table>
						<br /><br />
						<input type="submit" value="Update URL" />
					</div>
				</form>
				<div class="main_section_bottom"></div>
