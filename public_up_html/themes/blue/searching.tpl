				<div class="main_section_top">
					General
				</div>
				<div class="main_section_body g_alignleft">
					Many people are confused about how the searching of torrents or forum posts work, and therefore do not know how to get good results.<br />
					This page will explain how to get good results when the various searches on this site.<br />
					Both the torrent search and forum search use a boolean style search which is explained below.<br />
					Forum searches search only the text in the body of the posts, whereas torrent searches use the title,
					torrent filename and file/folder name.<br />
					The torrent search has an optional custom search which only uses the titles of torrents to search, this is also explained below.
				</div>
				<div class="main_section_bottom"></div>

				<div class="main_section_top">
					Boolean searching (torrents and forums)
				</div>
				<div class="main_section_body g_alignleft">
					Most search engines use a type of search called boolean; it is fairly simple; here are some examples:<br />
					<ul>
						<li><b>prison break special</b> - Find results that contain at least one of the all the given words.</li>
						<li><b>+prison +break +special</b> - Find results that contain all the given words.</li>
						<li><b>+prison special</b> - Find results that contain the word &quot;prison&quot;, but rank rows higher if they also contain 
							the word &quot;special&quot;. (the ranking does not work for torrent searching though, since they are resorted by the upload date)</li>

						<li><b>+prison -special</b> - Find results that contain the word &quot;prison&quot; but not &quot;special&quot;.</li>
						<li><b>+prison ~special</b> - Find results that contain the word &quot;prison&quot;, but if the result also contains the word &quot;special&quot;,
							rate it lower than if it does not. This is &quot;softer&quot; than a search for &quot;+prison -special&quot;,
							for which the presence of &quot;special&quot; causes the result not to be returned at all.
							(again the ranking does not work for torrent searching though, since they are resorted by the upload date)</li>
						<li><b>+special +(prison bones)</b> - Find results that contain the words &quot;special&quot; and &quot;prison&quot;,
							or &quot;special&quot; and &quot;bones&quot;.</li>
					</ul>
					<br />
					<span class="b u">Please note:</span> Words shorter than 3 characters and very common words are ignored in boolean searches.
				</div>
				<div class="main_section_bottom"></div>

				<div class="main_section_top">
					Torrent specific searching
				</div>
				<div class="main_section_body g_alignleft">
					When you enable the titles only option, there are 4 types of searching:<br />
					<ul>
						<li><b>&quot;Prison Break&quot;</b> - Use quotes to search for an exact phrase.</li>
						<li><b>Pr?son B*</b> - Use wildcards * (any or no characters) or ? (any single character) to find partial words in the title.</li>
						<li><b>Prison.Break.S02E19.720p.HDTV.x264-SAiNTS</b> - This will search for this exact title (must be formated like a proper release name)</li>
						<li><b>+Prison +Break</b> - Standard boolean search against titles. If none of the first 3 methods are used, this is the default.</li>
					</ul>
					<br />
					<span class="b u">Please note:</span> It is possible to get results that do not match the title of the torrent when using boolean search with the titles only option,
						as it also matches against the torrent filename and the file/folder name within the torrent file itself (ie. the downloaded name)
				</div>
				<div class="main_section_bottom"></div>

				<div class="main_section_top">
					Questions?
				</div>
				<div class="main_section_body g_alignleft">
					If you have any questions about the searching, please feel free to post your question on the <a href="/forums_index.php" class="g_bblink">forums</a>.
				</div>
				<div class="main_section_bottom"></div>
