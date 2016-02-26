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

$VAR["Heading"] = "Story Control";

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
<td>Allow Story Contributions</td>
<td>
<input type=radio name=AllowContrib value=\"0\" " . ($CONF["AllowContrib"]==0 ? "checked" : "") . "> No
<input type=radio name=AllowContrib value=\"1\" " . ($CONF["AllowContrib"]==1 ? "checked" : "") . "> Yes
</td>
</tr>

<tr>
<td>Story Moderation</td>
<td>
<input type=radio name=Moderation value=\"0\" " . ($CONF["Moderation"]==0 ? "checked" : "") . "> None
<input type=radio name=Moderation value=\"1\" " . ($CONF["Moderation"]==1 ? "checked" : "") . "> Yes
<input type=radio name=Moderation value=\"2\" " . ($CONF["Moderation"]==2 ? "checked" : "") . "> Yes / Notify
</td>
</tr>

<tr>
<td>Summary Length</td>
<td>
<input type=text name=SummaryLength size=4 maxlength=4 value=\"" . $CONF["SummaryLength"] . "\">
characters (0=disable)
</td>
</tr>

<tr>
<td>Limit Stories</td>
<td>Display <input type=text name=LimitNews size=2 maxlength=2 value=\"" . $CONF["LimitNews"] . "\">
stories per page
</td>
</tr>

<tr>
<td>Enable User Comments</td>
<td>
<input type=radio name=Comments value=\"0\" " . ($CONF["Comments"]==0 ? "checked" : "") . "> No
<input type=radio name=Comments value=\"1\" " . ($CONF["Comments"]==1 ? "checked" : "") . "> Yes
<input type=radio name=Comments value=\"2\" " . ($CONF["Comments"]==2 ? "checked" : "") . "> Threaded
&nbsp;&nbsp;Sort By
<select	name	= \"CommentSort\">
<option value	= \"asc\"" . ($CONF["CommentSort"]=="asc" ? "selected" : "") . ">Ascending</option>
<option value	= \"desc\"" . ($CONF["CommentSort"]=="desc" ? "selected" : "") . ">Descending</option>
</select>
</td>
</tr>

<tr>
<td>Allow Anonymous Comments</td>
<td>
<input type=radio name=AllowAnon value=\"0\" " . ($CONF["AllowAnon"]==0 ? "checked" : "") . "> No
<input type=radio name=AllowAnon value=\"1\" " . ($CONF["AllowAnon"]==1 ? "checked" : "") . "> Yes</td>
</tr>

<tr>
<td>Allow Comment Mailing</td>
<td>
<input type=radio name=EmailComments value=\"0\" " . ($CONF["EmailComments"]==0 ? "checked" : "") . "> No
<input type=radio name=EmailComments value=\"1\" " . ($CONF["EmailComments"]==1 ? "checked" : "") . "> Yes
<small>(Mails comments to poster)</small></td>
</tr>

<tr>
<td>Enable Topics</td>
<td>
<input type=radio name=Topics value=\"0\" " . ($CONF["Topics"]==0 ? "checked" : "") . "> No
<input type=radio name=Topics value=\"1\" " . ($CONF["Topics"]==1 ? "checked" : "") . "> Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
Sort By
<select	name	= \"TopicSort\">
<option	value	= \"id\" " . ($CONF["TopicSort"]=="id" ? "selected" : "") . ">No Sorting</option>
<option value	= \"asc\" " . ($CONF["TopicSort"]=="asc" ? "selected" : "") . ">Ascending</option>
<option value	= \"desc\" " . ($CONF["TopicSort"]=="desc" ? "selected" : "") . ">Descending</option>
<option value	= \"brief\" " . ($CONF["TopicSort"]=="brief" ? "selected" : "") . ">Brief</option>
</select>
</td>
</tr>

<tr>
<td>Allow User Save</td>
<td>
<input type=radio name=SaveInfo value=\"0\" " . ($CONF["SaveInfo"]==0 ? "checked" : "") . "> No
<input type=radio name=SaveInfo value=\"1\" " . ($CONF["SaveInfo"]==1 ? "checked" : "") . "> Yes
<small>(Sends cookies)</small></td>
</tr>

<tr>
<td>HTML Parse Level</td>
<td>
<select name=ParseLevel>
<option value=\"0\" " . ($CONF["ParseLevel"]==0 ? "selected" : "") . "> No action
<option value=\"1\" " . ($CONF["ParseLevel"]==1 ? "selected" : "") . "> Allow full HTML - in-lining, syntax-parsing
<option value=\"2\" " . ($CONF["ParseLevel"]==2 ? "selected" : "") . "> Reduced HTML - Allow syntax array, line-breaks, in-lining
<option value=\"3\" " . ($CONF["ParseLevel"]==3 ? "selected" : "") . "> Micro HTML - line-breaks, in-lining
<option value=\"4\" " . ($CONF["ParseLevel"]==4 ? "selected" : "") . "> No HTML - all stripped, only line-breaks
<option value=\"5\" " . ($CONF["ParseLevel"]==5 ? "selected" : "") . "> No HTML, no line-breaks
</select>
</td>
</tr>

<tr>
<td>HTML Parse Level (Comments)</td>
<td>
<select name=ParseLevelCmt>
<option value=\"0\" " . ($CONF["ParseLevelCmt"]==0 ? "selected" : "") . "> No action
<option value=\"1\" " . ($CONF["ParseLevelCmt"]==1 ? "selected" : "") . "> Allow full HTML - in-lining, syntax-parsing
<option value=\"2\" " . ($CONF["ParseLevelCmt"]==2 ? "selected" : "") . "> Reduced HTML - Allow syntax array, line-breaks, in-lining
<option value=\"3\" " . ($CONF["ParseLevelCmt"]==3 ? "selected" : "") . "> Micro HTML - line-breaks, in-lining
<option value=\"4\" " . ($CONF["ParseLevelCmt"]==4 ? "selected" : "") . "> No HTML - all stripped, only line-breaks
<option value=\"5\" " . ($CONF["ParseLevelCmt"]==5 ? "selected" : "") . "> No HTML, no line-breaks
</select>
</td>
</tr>

<tr>
<td>Export RDF/RSS Stories</td>
<td>
<input type=radio name=Backend value=\"0\" " . ($CONF["Backend"]==0 ? "checked" : "") . "> No
<input type=radio name=Backend value=\"1\" " . ($CONF["Backend"]==1 ? "checked" : "") . "> Yes
<br>RDF filename:<input 	type=text name=BackendFile size=12 maxlength=128 value=\"" . $CONF["BackendFile"] . "\"></td>
</tr>

<tr>
<td>Story Mailing List</td>
<td>
<input type=radio name=MailingList value=\"0\" " . ($CONF["MailingList"]==0 ? "checked" : "") . "> No
<input type=radio name=MailingList value=\"1\" " . ($CONF["MailingList"]==1 ? "checked" : "") . "> Yes
<br>Email:<input 	type=text name=MailingAddress size=30 maxlength=128 value=\"" . $CONF["MailingAddress"] . "\"></td>
</tr>


<tr>
<td 	colspan	= 2>
<input	type	= hidden
	name	= what
	value	= config-story>
<input	type	= submit
	value	= \"Save Changes\">
</td>
</tr>

</table>
</form>
";

	F_drawMain($VAR);
	include("../include/footer.inc.php");
?>
