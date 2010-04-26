				<!-- catbox -->
				<form method="get" action="/browse.php">
					<div class="browse_catbox_top">
						<a href="/searching.php"><img src="{THEME_PIC_DIR}browse_help.png" alt="How-to" title="Searching How-to" style="position: relative; top: 2px;" /></a>
						<input type="text" name="search" size="40" value="{SEARCH_STR}" class="browse_inputbox" />
						<select name="incldead" class="browse_selectlist">
							<option value="0"{ACTIVE}>Active</option>
							<option value="1"{INCL_DEAD}>Including Dead</option>
							<option value="2"{ONLY_DEAD}>Only Dead</option>
						</select>&nbsp;
						<input type="checkbox" name="titleonly" value="1" checked="checked" class="browse_checkbox"/> &nbsp;Search titles only &nbsp;
						<input type="hidden" name="all" value="0" />
						<input type="submit" value="Show" class="browse_showbtn" />
					</div>
					<div class="browse_catbox_body">
						<table>
{CAT_LIST}
						</table>
						<br />
						<p class="browse_catbox_showall"><a href="/browse.php?all=1{ALL_INCL_DEAD}" class="g_bblink">Show All</a></p>
					</div>
				</form>
				<div class="g_fadetitle_bottom">
					{PAGES}
				</div>
				<!-- end catbox -->
				<div class="g_space_line"></div>
				<!-- torrent list-->
{TORRENT_LIST}
				<!-- end torrent list -->
				<div class="g_space_line"></div>
				{PAGES}
