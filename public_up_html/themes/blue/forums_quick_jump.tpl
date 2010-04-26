					<form method="get" action="/forums_viewforum.php" id="jump">
						<p style="text-align: center">Quick jump:
							<select name="id" onchange="if(this.options[this.selectedIndex].value != -1) { forms['jump'].submit() }">
{LIST}
							</select>
							<input type="submit" value="Go!" />
						</p>
					</form>
