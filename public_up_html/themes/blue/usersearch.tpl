				<div class="main_section_top">Administrative User Search</div>
				<div class="main_section_body">
					<form>
						<table class="g_lttable upload_main_table">
							<tr>
								<td class="g_alignright"><b>Name</b></td>
								<td><input type="text" name="username" class="g_s_input" size="30" /></td>
                                <td align="right"><b>Ratio</b></td>
                                <td>
                                	<select name="ratio" class="g_selectlist">
                                    	<option selected="selected">equal</option>

                                        <option>above</option>
                                        <option>below</option>
                                        <option>between</option>
                                   	</select>
                                    <input type="text" name="ratio1" size="5" class="g_s_input" />
                                    <input type="text" name="ratio2" size="5" class="g_s_input" />
                                </td>

                                <td align="right"><b>Member status</b></td>
                                <td align="left">
                                	<select name="memberstatus" class="g_selectlist">
                                    	<option selected="selected">(any)</option>
                                        <option>confirmed</option>
                                        <option>pending</option>
                                    </select>

                               	</td>
                            </tr>
                            <tr>
                            	<td align="right"><b>Email</b></td>
                                <td><input type="text" name="email" class="g_s_input" size="30" /></td>
                                <td align="right"><b>IP</b></td>
                                <td align="left"><input type="text" name="ip" size="30" class="g_s_input" /></td>
                                <td align="right"><b>Account status</b></td>

                                <td align="left">
                                	<select name="accountstatus" class="g_selectlist">
                                    	<option selected="selected">(any)</option>
                                        <option>enabled</option>
                                        <option>disable</option>
                                    </select>
                               	</td>

                            </tr>
                            <tr>
                            	<td align="right"><b>Comment</b></td>
                                <td><input type="text" name="comment" class="g_s_input" size="30" /></td>
                                <td align="right"><b>Mask</b></td>
                                <td align="left"><input type="text" name="mask" size="30" class="g_s_input" /></td></td>
                                <td align="right"><b>Class</b></td>

                                <td>
                                	<select name="class" class="g_selectlist">	
                                    	<option selected="selected">(any)</option>
                                    	<option>User</option>
                                        <option>Power User</option>
                                        <option>Xtreme User</option>
                                        <option>ScT Whore</option>

                                        <option>ScT Super Whore</option>
                                        <option>ScT Seed Whore</option>
                                        <option>VIP</option>
                                        <option>Uploader</option>
                                        <option>Forum Moderator</option>
                                        <option>Global Moderator</option>

                                        <option>Administrator</option>
                                        <option>Staff Leader</option>
                                    </select>
                               	</td>
                            </tr>
                            <tr>
                            	<td align="right"><b>Joined</b></td>

                                <td>
                                	<select name="joined" class="g_selectlist">
                                    	<option selected="selected">on</option>
                                        <option>before</option>
                                        <option>after</option>
                                        <option>between</option>
                                   	</select>

                                    <input type="text" name="joined1" size="5" class="g_s_input" />
                                    <input type="text" name="joined" size="5" class="g_s_input" />
                                </td>
                                <td align="right"><img src="img/upload-arrow.png" alt="downl." /></td>
                                <td>
                                	<select name="uploaded" class="g_selectlist">
                                    	<option selected="selected">equal</option>
                                        <option>above</option>

                                        <option>below</option>
                                        <option>between</option>
                                   	</select>
                                    <input type="text" name="uploaded" size="5" class="g_s_input" />
                                    <input type="text" name="uploaded" size="5" class="g_s_input" />
                                </td>
                                <td align="right"><b>Donor</b></td>

                                <td align="left">
                                	<select name="donor" class="g_selectlist">
                                    	<option selected="selected">(any)</option>
                                        <option>yes</option>
                                        <option>no</option>
                                    </select>
                               	</td>

                            </tr>
                            <tr>
                            	<td align="right"><b>Last seen</b></td>
                                <td>
                                	<select name="lastseen" class="g_selectlist">
                                    	<option selected="selected">on</option>
                                        <option>before</option>

                                        <option>after</option>
                                        <option>between</option>
                                   	</select>
                                    <input type="text" name="lastseen1" size="5" class="g_s_input" />
                                    <input type="text" name="lastseen2" size="5" class="g_s_input" />
                                </td>
                                <td align="right"><img src="img/download-arrow.png" alt="downl." /></td>
                                <td>

                                	<select name="downloaded" class="g_selectlist">
                                    	<option selected="selected">equal</option>
                                        <option>above</option>
                                        <option>below</option>
                                        <option>between</option>
                                   	</select>
                                    <input type="text" name="downloaded" size="5" class="g_s_input" />

                                    <input type="text" name="downloaded" size="5" class="g_s_input" />
                                </td>
                                <td align="right"><b>Warned</b></td>
                                <td align="left">
                                	<select name="warned" class="g_selectlist">
                                    	<option selected="selected">(any)</option>
                                        <option>yes</option>

                                        <option>no</option>
                                    </select>
                               	</td>
                            </tr>
                            <tr>
                            	<td align="right"><b>IP Ban</b></td>
                                <td align="left">
                                	<input type="checkbox" name="disabledip" value="1" />

                                </td>
                                <td align="right"><b>Active</b></td>
                                <td align="left">
                                	<input type="checkbox" name="activeonly" value="1" />
                                </td>
                                <td align="right"><b>Connectable</b></td>
                                <td align="left">
                                	<select name="connectable" class="g_selectlist">

                                    	<option selected="selected">(any)</option>
                                        <option>yes</option>
                                        <option>no</option>
                                    </select>
                               	</td>
                            </tr>
                            <tr>

                            	<td align="right"><b>Passkey</b></td>
                                <td><input type="text" name="passkey" class="g_s_input" size="30" /></td>
                                <td align="right"><b>IRC</b></td>
                                <td align="left">
                                	<select name="ircaccess" class="g_selectlist">
                                    	<option selected="selected">(any)</option>
                                        <option>access allowed</option>

                                        <option>access revoked</option>
                                    </select>
                                </td>
                                <td align="right"><b>Post Access</b></td>
                                <td align="left">
                                	<select name="postaccess" class="g_selectlist">
                                    	<option selected="selected">(any)</option>

                                        <option>allowed</option>
                                        <option>revoked</option>
                                    </select>
                               	</td>
                            </tr>
                            <tr>
                            	<td colspan="6">
                                	<input type="submit" value="Search" />

                                </td>
                            </tr>
                    	</table>
                    </form>
                </div>
                <div class="main_section_bottom"></div>
                
                <div class="main_section_top">
                	Search Results
				</div>

                <div class="main_section_body">
                	<table class="g_deftable">
                    	<tr class="g_deftable_toprow g_wide_tablerow">
                        	<td align="left" width="100">Name</td>
                            <td align="left" width="40">Ratio</td>
                            <td align="left" width="120">Email</td>
                            <td align="left" width="110">Joined</td>

                            <td align="left" width="110">Last seen</td>
                            <td align="left" width="50">Stauts</td>
                            <td align="left" width="40">Enabled</td>
                            <td align="left" width="50">History</td>
                            <td align="left" width="18">PM</td>
                        </tr>

                        <tr>
                        	<td align="left" style="padding-left:5px;"><a href="userdetails.php?id=microphonechecka12" class="g_noclink g_c_xuser">10bottlesOfbeer</a></td>
                            <td>31.87</td>
                            <td align="left" style="padding-left: 4px;">10bottles@beer.com</td>
                            <td>2006-11-02 14:57:20</td>
                            <td>2008-06-24 14:41:43</td>

                            <td>confirmed</td>
                            <td>Yes</td>
                            <td><a href="userhistory.php?id=123&amp;action=blahwhatever" class="g_bllink">56</a>&nbsp;<a href="userhistory.php?id=123&amp;action=blahwhatddever" class="g_bllink">171</a></td>
                            <td><img src="img/staff_pm.png" alt="PM" style="margin: 2px 0px 0px;" /></td>
                        </tr>
                    </table>
                </div>

                <div class="main_section_bottom"></div>

