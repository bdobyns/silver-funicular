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

$VAR["Heading"] = "Topic Manager";
$VAR["Content"] = "
<table	border	= 0
	cellspacing=1
	cellpadding=2
	width	= 100%>
	";

	$sql	= "SELECT * FROM T_Topics";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	$VAR["Content"] .= "<tr><th>Image</th><th>Topic Name</th>";
	$VAR["Content"] .= "<th>Alt Tag</th><th>Rules</th>";
	$VAR["Content"] .= "<th>&nbsp;</th></tr>";
	for ($i=0;$i<$nrows;$i++) {
		$A	= mysql_fetch_array($result);
		$VAR["Content"] .= "<tr>\n";
		if (!empty($A["ImgURL"])) {
			$tmp	= sprintf("<img src=\"%s\">",$A["ImgURL"]);
		} else {
			$tmp	= "n/a";
		}
		$foo	= "";
		if ($A["NoPosting"]=="1") {
			$foo	.= "<li>No Posting</li>";
		}
		if ($A["NoComments"]=="1") {
			$foo	.= "<li>No Comments</li>";
		} elseif ($A["NoComments"]=="2") {
			$foo	.= "<li>Comments Display Only</li>";
		}
		$foo	= empty($foo) ? "None" : $foo;
		$blah	= "<small>" . $foo . "</small>\n";
		$VAR["Content"] .= sprintf("<td>%s</td>\n",$tmp);
		$VAR["Content"] .= sprintf("<td>%s</td>\n",$A["Topic"]);
		$VAR["Content"] .= sprintf("<td>%s&nbsp;</td>\n",$A["AltTag"]);
		$VAR["Content"] .= sprintf("<td>%s&nbsp;</td>\n",$blah);
		$VAR["Content"] .= "<td align=center>";
		$VAR["Content"] .= F_admin("T_Topics",$A["Rid"]);
		$VAR["Content"] .= "</td></tr>\n";
	}

$VAR["Content"] .= "
</table>
<center>
Killing a topic will also kill all associated stories and comments.
</center>";

F_drawMain($VAR);

print "<p>\n";

$VAR["Heading"] = "Add New Topic";
$VAR["Content"] = "
<form	action	= \"$G_URL/admin/submit.php\"
	method	= post>
<table
	border	= 0
	cellspacing	= 1
	cellpadding	= 2
	width	= \"100%\">
<tr>
<td>Topic</td>
<td>
<input	type	= text
	name	= Topic
	size	= 40
	maxlength	= 48></td>
</tr>

<tr>
<td>Image URL</td>
<td>
<input	type	= text
	name	= URL
	size	= 40
	value	= \"\"
	maxlength	= 96>
<small>(blank=none)</small>
</td>
</tr>

<tr>
<td>Alt Tag</td>
<td>
<input	type	= text
	name	= AltTag
	size	= 40
	maxlength	= 64></td>
</tr>

<tr>
<td>Rules</td>
<td>";

if ($CONF["AllowContrib"]>0) {
	$VAR["Content"]	.= "
	<input	type=\"checkbox\"
		name=\"NoPosting\"> No Posting";
}
if ($CONF["Comments"]>0) {
	$VAR["Content"]	.= "
	<input	type=\"checkbox\"
		name=\"NoComments\"> No Comments";
}

$VAR["Content"]	.= "
</td>
</tr>

<tr>
<td
	colspan	= 2
	align	= center>
<input	type	= hidden
	name	= what
	value	= \"topic\">
<input	type	= submit
	value	= \"" . F_submit() . "\">
</table>
</form>";

F_drawMain($VAR);
include("../include/footer.inc.php");

?>
