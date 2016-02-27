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
include("./include/header.inc.php");


if (empty($Author)) {
	$VAR["Heading"] = _NOUSER;
	$VAR["Content"] = "<br>" . _NOUSER . "<br><br>";
	F_drawMain($VAR);
} else {
	$usr	= stripslashes($Author);
	$VAR["Heading"] = _PROFILING . " " . $usr;

	$VAR["Content"] = "<table>\n";
	$VAR["Content"] .= "<tr><td>" . _NAME . ":</td>\n";
	$VAR["Content"] .= "<td>" . urldecode(stripslashes(stripslashes($Author))) . "</td></tr>\n";
	$VAR["Content"] .= "<tr><td>" . _URL . ":</td>\n";
	$VAR["Content"] .= "<td>";
	if (!empty($AuthorURL)) {
		$VAR["Content"] .= "<a target=_new href=\"" . stripslashes(stripslashes($AuthorURL)) . "\">" . stripslashes(stripslashes($AuthorURL)) . "</a>";
	} else {
		$VAR["Content"] .= _NA;
	}
	$VAR["Content"]	.= "</td></tr>\n";
	$VAR["Content"] .= "</table>\n";
	F_drawMain($VAR);

	print "<p>\n";

	F_drawResults($Author,"0","authors","5");

print "<p>\n";

?>
<form	action	= "submit.php"
	method	= POST
	name	= "Contact"
	onsubmit= "return validateContact()">
<?php
	$tmp	= $HTTP_COOKIE_VARS[$C_USER];
	$USER	= explode("|",rot13($tmp));
	$x	= stripslashes($Author);
	$VAR["Heading"]	= _MAILTO . " " . $x;
	$VAR["Content"] = "
<table
	cellspacing	= 0
	cellpadding	= 0
	border	= 0>
<tr>
<td>" . _NAME . ":</td>
<td>
<input	type	= text
	name	= Author
	size	= 32
	value	= \"$USER[0]\"
	maxlength	= 32></td>
</tr>

<tr>
<td>" . _EMAIL . ":</td>
<td>
<input	type	= text
	name	= AuthorEmail
	size	= 32
	value	= \"$USER[1]\"
	maxlength	= 96>
</td>
</tr>

<tr>
<td>" . _SUBJECT . ":</td>
<td>
<input	type	= text
	name	= Subject
	size	= 32
	maxlength	= 96>
</td>
</tr>

<tr>
<td>" . _MESSAGE . ":</td>
<td>
<textarea
	name	= Message
	wrap	= virtual
	rows	= 10
	cols	= 40></textarea>
<br>" . _NOHTML . "</td>
</tr>
<tr>
<td	colspan	= 2
	align	= center>
<input	type	= hidden
	name	= what
	value	= contact>
<input	type	= hidden
	name	= MailTo
	value	= \"" . stripslashes($Author) . "\">
<input	type	= hidden
	name	= MailToEmail
	value	= \"" . stripslashes($AuthorEmail) . "\">
<input	type	= submit
	value	= \"" . F_submit() . "\">
</table>
</form>";
F_drawMain($VAR);
}

	include("./include/footer.inc.php");
?>
