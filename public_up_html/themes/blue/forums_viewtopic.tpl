				<a name="top"></a>
				<div class="g_fadetitle_top"></div>
				<div class="forums_p_ttitle">
					<a href="/forums_viewforum.php?id={FORUM_ID}" class="g_bblink">{FORUM_NAME}</a> &gt; {NAME} <a href="#bottom"><img src="{THEME_PIC_DIR}forums_p_bottom.png" alt="bottom" /></a>
				</div>
				<div class="g_fadetitle_bottom">
					{PAGER}     	
				</div>

{POSTS}

				<div class="g_space_line"></div>
				<div class="g_aligncenter">
					{PAGER}
				</div>
				<a name="bottom"></a>

				<div class="main_sm_section_top">Topic Tools</div>
				<div class="main_sm_section_body">
					{MESSAGE}
					<span style="font-size:13px; font-weight:bold;">Quick Reply</span><br />
					<form method="post" action="/forums_post.php" style="margin:0;">
						<div>
							<input type="hidden" name="topicid" value="{TOPIC_ID}" />
							<textarea cols="55" rows="5" name="body" style="background: #5d8294; border: 1px solid #000000; margin: 7px 0px 7px 0px;" id="ajax_comment" {REPLY_EN}></textarea><br />
							<input type="hidden" name="hash" value="{FORM_HASH}" />
							<input type="submit" value="Reply" id="ajax_submitcomment" {REPLY_EN}/>
						</div>
                   	</form>
					<br /><br /><br />
					<table class="g_tcenter">
						<tr>
							<td style="padding-right:5px;">
								<form method="get" action="/forums_post.php">
									<div>
										<input type="hidden" name="topicid" value="{TOPIC_ID}" />
										<input type="submit" value="Full Reply" class="btn" style="margin-left: 10px" {REPLY_EN}/>
									</div>
								</form>
							</td>
							<td style="padding-left: 5px;">
								<form method="get" action="/forums_viewunread.php">
									<div><input type="submit" value="View Unread" class="btn" style="margin-left: 10px" /></div>
								</form>
							</td>
						</tr>
					</table>
{QUICK_JUMP}{MOD_TOOLS}
				</div>
				<div class="main_sm_section_bottom"></div>
