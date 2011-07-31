<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2011 ScTBDev.ca
 *
 *	This file is part of ScTBDev.
 *
 *	ScTBDev is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	ScTBDev is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');

if (!mkglobal('id'))
        die();

$id = 0 + $id;
if (!$id)
        die();

bt_loginout::db_connect(true);

$res = mysql_query("SELECT * FROM torrents WHERE id = $id");
$row = mysql_fetch_assoc($res);
if (!$row)
        die();
if ($row["owner"] == 0)
  {
   $res2 = mysql_query("SELECT owner FROM torrents_anon WHERE id = '$id'");
   if (mysql_num_rows($res2))
     $row2 = mysql_fetch_assoc($res2);
  }

bt_theme::head("Edit torrent \"" . $row["name"] . "\"");

if (bt_user::$current['id'] != $row['owner'] && bt_user::$current['id'] != $row2['owner'] && !bt_user::required_class(UC_MODERATOR)) {
        print("<h1>Can't edit this torrent</h1>\n");
        print("<p>You're not the rightful owner, or you're not <a href=\"login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">logged in</a> properly.</p>\n");
}
else {
        print("<form method=post action=takeedit.php enctype=multipart/form-data>\n");
        print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
        if (isset($_GET["returnto"]))
                print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlentities($_GET["returnto"]) . "\" />\n");
        print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n");
        tr("Torrent name", "<input type=\"text\" name=\"name\" value=\"" . htmlentities($row["name"]) . "\" size=\"80\" />", 1);
        tr("NFO file", "<input type=radio name=nfoaction value='keep' checked>Keep current<br>".
        "<input type=radio name=nfoaction value='update'>Update:<br><input type=file name=nfo size=80>", 1);
        tr("URL", "<input type=\"text\" name=\"url\" value=\"" . htmlentities($row["url"]) . "\" size=\"80\" />", 1);
        tr('Description','<textarea name="descr" rows="10" cols="80">'.htmlentities($row['descr']).'</textarea><br />'.
      '(HTML is not allowed. <a href="/tags.php">Click here</a> for information on available tags.)<br />'.
      '<input type="checkbox" name="stripnfo" value="1" /> Strip NFO Garbage FROM description', 1);

        $s = "<select name=\"type\">\n";

        $cats = bt_mem_caching::get_cat_list();
        foreach ($cats as $subid => $subrow) {
                $s .= "<option value=\"" . $subid . "\"";
                if ($subid == $row["category"])
                        $s .= " selected=\"selected\"";
                $s .= ">" .$subrow["ename"] . "</option>\n";
        }

        $s .= "</select>\n";
        tr("Type", $s, 1);
        tr("Visible", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Visible on main page<br /><table border=0 cellspacing=0 cellpadding=0 width=420><tr><td class=embedded>Note that the torrent will automatically become visible when there's a seeder, and will become automatically invisible (dead) when there has been no seeder for a while. Use this switch to speed the process up manually. Also note that invisible (dead) torrents can still be viewed or searched for, it's just not the default.</td></tr></table>", 1);

        print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value='Edit it!' style='height: 25px; width: 100px'> <input type=reset value='Revert changes' style='height: 25px; width: 100px'></td></tr>\n");
        print("</table>\n");
        print("</form>\n");
        print('<p>
<form method="post" action="/delete.php">'."\n");
  print('<table border="1" cellspacing="0" cellpadding="5">
  <tr>
    <td class="embedded" style="background-color: #f5f4ea; padding-bottom: 5px" colspan="2"><b>Delete torrent.</b> Reason:</td>
  </tr>
  <tr>
    <td>
      <input name="reasontype" type="radio" value="1" />&nbsp;Dead
    </td>
    <td> 0 seeders, 0 leechers = 0 peers total</td>
  </tr>
  <tr>
    <td>
      <input name="reasontype" type="radio" value="2" />&nbsp;Dupe
    </td>
    <td>
      <input type="text" size="40" name="reason[]" />
    </td>
  </tr>
  <tr>
    <td>
      <input name="reasontype" type="radio" value="3" />&nbsp;Nuked
    </td>
    <td>
      <input type="text" size="40" name="reason[]" />
    </td>
  </tr>
  <tr>
    <td>
      <input name="reasontype" type="radio" value="4" />&nbsp;ScT rules
    </td>
    <td>
      <input type="text" size="40" name="reason[]" />(req)
    </td>
  </tr>
  <tr>
    <td>
      <input name="reasontype" type="radio" value="5" checked="checked" />&nbsp;Other:
    </td>
    <td>
      <input type="text" size="40" name="reason[]" />(req)
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="Delete it!" style="height: 25px" />
    </td>
  </tr>
</table>
<input type="hidden" name="id" value="'.$id.'" />'."\n");
        if (isset($_GET['returnto']))
                print('<input type="hidden" name="returnto" value="'.htmlentities($_GET['returnto']).'" />'."\n");
        print('</form>
</p>'."\n");
}

bt_theme::foot();
?>
