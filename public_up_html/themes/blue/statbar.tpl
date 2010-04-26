
				<div class="userbar_userbar_top"></div>
				<div class="userbar_userbar_body">
					<div class="userbar_userbar_left">
						Welcome, <a href="/userdetails.php?id={USER_ID}" class="{CLASSC} g_noclink">{USER_NAME}</a> {STARS}&nbsp;[<a href="/logout.php" class="g_bllink">logout</a>]&nbsp;&nbsp;
						<span style="color: green;">Connectable: </span>{CONNECTABLE}<br />
						<span style="color: blue;">Ratio: </span>{RATIO}&nbsp;&nbsp;
						<span style="color: green;">Uploaded: </span><span style="color: black;">{UPED}</span> &nbsp;
						<span style="color: red;">Downloaded: </span><span style="color: black;">{DOWNED}</span> &nbsp;
					</div>
					<div class="userbar_userbar_right">
						{CUR_TIME}<br />
						<a href="/inbox.php"><img alt="inbox" title="Inbox {INBOX_TITLE}" src="{THEME_PIC_DIR}inbox{INBOX_PIC}.png" /></a>{INBOX} ({INBOX_NEW} New)&nbsp;&nbsp;
						<a href="/inbox.php?out=1"><img alt="sentbox" title="Sentbox" src="{THEME_PIC_DIR}outbox.png" /></a>&nbsp;{SENTBOX}&nbsp;
						<a href="/friends.php"><img alt="Buddylist" title="Buddylist" src="{THEME_PIC_DIR}buddies.png" /></a>
					</div>
				</div>
				<div class="userbar_userbar_bottom"></div>
