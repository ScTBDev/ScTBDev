				<div class="main_section_top">Anatomy of a torrent session</div>
				<div class="main_section_body g_alignleft">
					<em>(Updated to reflect the tracker changes. 2008-09-14)</em><br /><br />

					There seems to be a lot of confusion about how the statistics updates work. The following is a capture of a full session to see what's going on behind the scenes. The client communicates
						with the tracker via simple http GET commands. The very first in this case was:<br /><br />

					<code>GET/announce.php?passkey=abcdef1234567890&amp;info_hash=c%97%91%C5jG%951%BE%C7M%F9%BFa%03%F2%2C%ED%EE%0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;
						port=6882&amp;uploaded=0&amp;downloaded=0&amp;left=753690875&amp;event=started</code><br /><br />

					Let's dissect this:<br />
					<b>passkey</b> is your unique passkey which identifys your account, and associates the torrent session with your user id<br />
					<b>info_hash</b> is just the hash identifying the torrent in question;<br />
					<b>peer_id</b>, as the name suggests, identifies the client (the s588 part identifies Shad0w's 5.8.8, the rest is random);<br />
					<b>port</b> just tells the tracker which port the client will listen to for incoming connections;<br />
					<b>uploaded=0</b> (this and the following are the relevant ones, and are self-explanatory)<br />
					<b>downloaded=0</b><br />
					<b>left=753690875</b> (how much left);<br />
					<b>event=started</b> (telling the tracker that the client has just started).<br /><br />

					At this stage the user's profile will be listing this torrent as being leeched.<br /><br />

					From now on the client will keep sending GETs to the tracker. We show only the first one as an example,<br /><br />

					<code>GET/announce.php?passkey=abcdef1234567890&amp;info_hash=c%97%91%C5jG%951%BE%C7M%F9%BFa%03%F2%2C%ED%EE%0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;
					port=6882&amp;uploaded=67960832&amp;downloaded=40828928&amp;left=715417851&amp;numwant=0</code><br /><br />

					<b>numwant</b> is how the client tells the tracker how many new peers it wants, in this case 0 (none).<br /><br />

					As you can see at this stage the user had uploaded approx. 68MB and downloaded approx. 40MB. Whenever the tracker receives these GETs it updates both the stats relative to the
						&quot;currently leeching/seeding&quot; boxes and the total user upload/download stats. These intermediate GETs will be sent either periodically (every 15 min or so, depends
						on the client and tracker) or when you force a manual announce in the client.<br /><br />

					Finally, when the client was closed it sent<br /><br />
					<code>GET/announce.php?passkey=abcdef1234567890&amp;info_hash=c%97%91%C5jG%951%BE%C7M%F9%BFa%03%F2%2C%ED%EE%0F&amp;peer_id=S588-----gqQ8TqDeqaY&amp;
                    port=6882&amp;uploaded=754384896&amp;downloaded=754215163&amp;left=0&amp;numwant=0&amp;event=completed</code><br /><br />

					<b>event=completed</b> indicates to the tracker that the torrents has completed the download, and the tracker will then increment the number of times downloaded (snatches)<br /><br />

					Finaly, <b>event=stopped</b> will be sent to the tracker when the client has stoped leeching/seeding or the user has canceled the download. If for some reason (tracker down, lost
						connection, bad client, crash, ...) this last GET doesn't reach the tracker this torrent will still be seen in the user profile until some tracker timeout occurs.
						It should be stressed that this message will be sent only when closing the client properly, not when the download is finished. (The tracker will start listing a
						torrent as 'currently seeding' after it receives a GET with left=0).<br /><br />

					One last note: some clients have a pause/resume option. This will not send any message to the server. Do not use it as a way of updating stats more often, it just doesn't work. 
				</div>
				<div class="main_section_bottom"></div>
