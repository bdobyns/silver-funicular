<?
/*=========================================================================
:: phpWebLog -- web news management with tits.
:: Copyright (C) 2000-2001 Jason Hines
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

include("./include/common.inc.php");

/*== dont allow contributions if they are disabled except by admin ==*/
if ($CONF["AllowContrib"]==0 && !F_isAdmin()) {
	$msg	= urlencode(_NOPOSTING);
	header("location:$G_URL/stories.php?msg=$msg");
	exit();
}

include("./include/header.inc.php");

	$VAR["Heading"] = _CONTRIBUTE;
	$tmp    = $HTTP_COOKIE_VARS[$C_USER];
	$USER   = explode("|",rot13($tmp));

	$VAR["Content"]	= "
<table
	border  = \"0\"
	cellspacing     = \"0\"
	cellpadding     = \"3\">

<form
	action	= \"$G_URL/preview.php\"
	name	= \"News\"
	method	= post
	onsubmit= \"return validateNews()\">

<tr><td colspan=2><h3>" . _YOURINFO . "</h3></td></tr>

<tr>
<td>" . _NAME . ":</td>
<td>
<input	type	= text
		name	= Author
		value	= \"$USER[0]\"
		size	= 40
		maxlength	= 32></td>
</tr>

<tr>
<td>" . _EMAIL . ":</td>
<td>
<input	type	= text
		name	= AuthorEmail
		value	= \"$USER[1]\"
		size	= 40
		maxlength	= 96></td>
</tr>

<tr>
<td>" . _URL . ":</td>
<td>
<input	type	= text
		name	= AuthorURL
		value	= \"$USER[2]\"
		size	= 40
		maxlength	= 96></td>
</tr>";

/*== save user info in cookie? ==*/
if ($CONF["SaveInfo"]>0) {
$VAR["Content"] .= "
	<tr>
	<td	colspan = 2>
	<input	type	= checkbox
		name	= save> " . _SAVEINFO . "</td></tr>";
}

$VAR["Content"]	.= "<tr><td colspan=2><br><h3>" . _YOURSTORY . "</h3></td></tr>

<tr>
<td>" . _TITLE . ":</td>
<td>
<input	type	= text
		name	= Heading
		size	= 40
		maxlength	= 96></td>
</tr>
";

if ($CONF["Topics"]>0) {

$VAR["Content"] .= "
	<tr>
	<td>" . _TOPIC . ":</td>
	<td>
	<select	name	= \"Topic\">";
$VAR["Content"] .= F_topicsel(0,"post");
$VAR["Content"] .= "
	</select>
	</tr>";
}

if ($CONF["SummaryLength"]>0) {
$VAR["Content"] .= "
<tr>
<td	colspan	= 2>" . _SUMMARY . ": (" . _OPTIONAL . ")<br>
<textarea
		name	= Summary
		wrap	= virtual
		rows	= 5
		cols	= 50></textarea></td>
</tr>
";
}

$VAR["Content"] .= "
<tr>
<td	colspan	= 2>" . _STORY . ":<br>
<textarea
		name	= Content
		wrap	= virtual
		rows	= 10
		cols	= 50></textarea></td>
</tr>

<tr>
<td	colspan	= 2>";
$VAR["Content"] .= F_ifHTML();
$VAR["Content"] .= "
</td>
</tr>
";

/*== off-site link / external url ==*/
if (F_count("T_IndexNames")>0) {
	$VAR["Content"]	.= "<tr><td colspan=2><br><h3>" . _RELATED . ":</h3></td></tr>";
	$VAR["Content"]	.= F_editIndexes2();
}

/*== send comments via mail? ==*/
if ($CONF["EmailComments"]>0) {
$VAR["Content"] .= "
	<tr>
	<td	colspan	= 2>
	<input	type	= checkbox
		name	= EmailComments 
		checked> " . _MAILCOMMENTS . "</td></tr>";
}

$VAR["Content"]	.= "
<tr>
<td	colspan	= 2>
<input	type	= hidden
	name	= what
	value	= \"news\">
<input	type	= submit
	name	= mode
	value	= \"" . _SUBMIT . "\">
<input	type	= submit
	name	= mode
	value	= \"" . _PREVIEW . "\"></td>
</tr>
</form>
</table>
";

F_drawMain($VAR);

include("./include/footer.inc.php");
?>
