				<div class="main_sm_section_top">Apply to become an uploader</div>
				<form method="post" action="/takeupapp.php">
					<div class="main_sm_section_body">
						<table>
							<tr>
								<td class="upapp_leftcol">Your ratio</td>
								<td class="upapp_rightcol">{RATIO}</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Your current upload amount</td>
								<td class="upapp_rightcol">{UPLD} ( {DAY} / day)</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Your upload speed</td>
								<td class="upapp_rightcol">
									<input type="radio" name="upspeed" value="1" /> Don't know<br />
									<input type="radio" name="upspeed" value="2" /> &lt;= 5 Mbps<br />
									<input type="radio" name="upspeed" value="3" /> 5+ Mbps - 10 Mbps<br />
									<input type="radio" name="upspeed" value="4" /> 10+ Mbps - 20 Mbps<br />
									<input type="radio" name="upspeed" value="5" /> 20+ Mbps - 30 Mbps<br />
									<input type="radio" name="upspeed" value="6" /> 30+ Mbps - 50 Mbps<br />
									<input type="radio" name="upspeed" value="7" /> 50+ Mbps - 100 Mbps<br />
									<input type="radio" name="upspeed" value="8" /> &gt; 100 Mbps<br />
								</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Your upload line location</td>
								<td class="upapp_rightcol">
									<input type="radio" name="uploc" value="1" /> USA or Canada<br />
									<input type="radio" name="uploc" value="2" /> Europe<br />
									<input type="radio" name="uploc" value="3" /> Other<br />
								</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Your sources</td>
								<td class="upapp_rightcol">
									<input type="radio" name="sources" value="1" /> Kazaa<br />
									<input type="radio" name="sources" value="2" /> Other torrent sites<br />
									<input type="radio" name="sources" value="3" /> Other P2P networks<br />
									<input type="radio" name="sources" value="4" /> Top sites<br />
									<input type="radio" name="sources" value="5" /> Need supplying <br />
								</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Your upload average pre times</td>
								<td class="upapp_rightcol">
									<input type="radio" name="pretime" value="1" /> &lt; 20 mins<br />
									<input type="radio" name="pretime" value="2" /> ~ 1 hour<br />
									<input type="radio" name="pretime" value="3" /> ~ 6 hours<br />
									<input type="radio" name="pretime" value="4" /> ~ 1 day<br />
									<input type="radio" name="pretime" value="5" /> ~ 5 days<br />
									<input type="radio" name="pretime" value="6" /> What's a &quot;pre time&quot;?<br />
								</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Stuff you plan on uploading</td>
								<td class="upapp_rightcol">
									<table>
										<tr>
{CATS}
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Check all that apply</td>
								<td class="upapp_rightcol">
									<input type="checkbox" name="chk1" value="1" /> I have read the FAQ<br />
									<input type="checkbox" name="chk2" value="1" /> I agree to the rules<br />
									<input type="checkbox" name="chk3" value="1" /> I know how to make a torrent<br />
									<input type="checkbox" name="chk4" value="1" /> I have uploaded torrents before<br />
									<input type="checkbox" name="chk5" value="1" /> I agree to seed my torrents until there are enough seeders to keep it alive<br />
									<input type="checkbox" name="chk6" value="1" /> I know what it means to be connectable<br />
									<input type="checkbox" name="chk7" value="1" /> I am connectable<br />
									<input type="checkbox" name="chk8" value="1" /> I am not currently uploading on any other torrent site(s)<br />
									<input type="checkbox" name="chk9" value="1" /> I can be on IRC at most or all of the time<br />
								</td>
							</tr>
							<tr>
								<td class="upapp_leftcol">Any additional comments?</td>
								<td class="upapp_rightcol" align="center">
									<textarea name="comments" cols="46" rows="5" style="margin: 0;"></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="upapp_endcol">
									<input type="submit" value="Apply" style="width: 100px;" /> &nbsp;
									<input type="reset" value="Cancel" style="width: 100px;" />
								</td>
							</tr>
						</table>
					</div>
				</form>
				<div class="main_sm_section_bottom"></div>
