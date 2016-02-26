<?
/*=========================================================================
:: phpWebLog -- web news management with tits.
:: Copyright (C) 2000 Jason Hines
:: see http://phpweblog.org for more

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

=*=======================================================================*/

include("../include/common.inc.php");
include("./auth.inc.php");

include("../include/header.inc.php");

$VAR["Heading"] = "Extended Site Configuration";

$VAR["Content"] = "

<form	action	= \"$G_URL/admin/submit.php\"
	name	= \"config\"
	method	= \"POST\">
<table
	cellpadding	= 2
	cellspacing	= 1
	width	= 100%
	border	= 0>

<tr>
<td>Older Stories Block</td>
<td>
<input type=radio name=Older value=\"0\" " . ($CONF["Older"]==0 ? "checked" : "") . "> No
<input type=radio name=Older value=\"1\" " . ($CONF["Older"]==1 ? "checked" : "") . "> Yes</td>
</tr>

<tr>
<td>Top 5 Stories Block</td>
<td>
<input type=radio name=Top5 value=\"0\" " . ($CONF["Top5"]==0 ? "checked" : "") . "> No
<input type=radio name=Top5 value=\"1\" " . ($CONF["Top5"]==1 ? "checked" : "") . "> Yes
<small>(Top 5 stories most viewed)</small></td>
</tr>

<tr>
<td>Hot 5 Stories Block</td>
<td>
<input type=radio name=Hot5 value=\"0\" " . ($CONF["Hot5"]==0 ? "checked" : "") . "> No
<input type=radio name=Hot5 value=\"1\" " . ($CONF["Hot5"]==1 ? "checked" : "") . "> Yes
<small>(Top 5 stories with most comments)</small></td>
</tr>

<tr>
<td>Enable Mail-Story-To-Friend</td>
<td>
<input type=radio name=MailFriend value=\"0\" " . ($CONF["MailFriend"]==0 ? "checked" : "") . "> No
<input type=radio name=MailFriend value=\"1\" " . ($CONF["MailFriend"]==1 ? "checked" : "") . "> Yes</td>
</tr>

<tr>
<td>Enable Print-Friendly Page</td>
<td>
<input type=radio name=PrintStory value=\"0\" " . ($CONF["PrintStory"]==0 ? "checked" : "") . "> No
<input type=radio name=PrintStory value=\"1\" " . ($CONF["PrintStory"]==1 ? "checked" : "") . "> Yes</td>
</tr>

<tr>
<td>Enable Free-Links</td>
<td>
<input type=radio name=Links value=\"0\" " . ($CONF["Links"]==0 ? "checked" : "") . "> No
<input type=radio name=Links value=\"1\" " . ($CONF["Links"]==1 ? "checked" : "") . "> Yes, Anyone can add
<input type=radio name=Links value=\"2\" " . ($CONF["Links"]==2 ? "checked" : "") . "> Yes, Only admin can add</td>
</tr>

<tr>
<td>Enable Statistics</td>
<td>
<input type=radio name=SiteStats value=\"0\" " . ($CONF["SiteStats"]==0 ? "checked" : "") . "> No
<input type=radio name=SiteStats value=\"1\" " . ($CONF["SiteStats"]==1 ? "checked" : "") . "> Yes
</tr>

<tr>
<td>Enable Story Archive</td>
<td>
<input type=radio name=Archive value=\"0\" " . ($CONF["Archive"]==0 ? "checked" : "") . "> No
<input type=radio name=Archive value=\"1\" " . ($CONF["Archive"]==1 ? "checked" : "") . "> Yes
</tr>

<tr>
<td 	colspan	= 2>
<input	type	= hidden
	name	= what
	value	= config-extend>
<input	type	= submit
	value	= \"Save Changes\">
</td>
</tr>


</table>
";

F_drawMain($VAR);


$VAR["Heading"] = "Extended Story Configuration";


$VAR["Content"] = "
<table 	cellpadding=2 
		cellspacing=1
		width=\"100%\" 
		border=0>
";

# index links
$VAR["Content"] .= "<tr><td colspan=2><b>Story Links</b></td></tr>";
$VAR["Content"]	.= "<tr><td>Index Names<br>(story links)</td><td>";

$VAR["Content"]	.= "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\"><tr>";
$VAR["Content"]	.= "<td><select name=\"IndexKills[]\" size=5 multiple width=150 style=\"width: 150px\">";

# list index names
$sql	= "SELECT * FROM T_IndexNames";
$result	= mysql_query($sql,$db);
$nrows	= mysql_num_rows($result);
for ($i=0;$i<$nrows;$i++) {
	$A	= mysql_fetch_array($result);
	$VAR["Content"] .= sprintf("<option>%s</option>\n",$A["Name"]);
}

$VAR["Content"]	.= "</select></td>";
$VAR["Content"]	.= "<td><input type=submit name=\"action\" value=\"kill\" onclick= \"return getconfirm();\">";
$VAR["Content"]	.= " <small>(Killing index names will kill all<br> associated";
$VAR["Content"] .= " story links as well)</small>";
$VAR["Content"]	.= "</td></tr></table>\n";

$VAR["Content"] .= "</td></tr>";
$VAR["Content"] .= "
<tr>
<td>Add new Link name</td>
<td>
<input	type	= text
	name	= IndexName
	size	= 40
	maxlength	= 48></td>
</tr>";


$VAR["Content"] .= "
<tr>
<td 	colspan	= 2>
<input	type	= hidden
	name	= what
	value	= config-extend>
<input	type	= submit
	value	= \"Add\">
</td>
</tr>

</table>
</form>
";

F_drawMain($VAR);
include("../include/footer.inc.php");
?>
