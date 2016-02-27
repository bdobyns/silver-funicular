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

# Original send-to-friend code by Fred St-Amand

include("./include/common.inc.php");
include("./include/header.inc.php");

$VAR["Heading"] = _MAILTOFRIEND;
$tmp	= $HTTP_COOKIE_VARS[$C_USER];
$USER	= explode("|",rot13($tmp));
$yname	= $USER[0];
$yemail	= $USER[1];

?>
<form	action	= "<? echo $G_URL; ?>/submit.php"
        name	= "Friend"
        method	= post
        onsubmit= "return validateFriend()">

<?
        $VAR["Content"]        = "
<table
      border = \"0\"
      cellspacing   =  \"0\"
      cellspading   =  \"3\">

<tr><td colspan=2><h3>". _YOURINFO . "</h3></td></tr>
<tr>
<td>" . _NAME .":</td>
<td>
<INPUT type=text name=Author   value = \"$yname\" size = 30 maxlength = 30></td>
</tr>
<tr>
<td>" . _EMAIL .":</td>
<td>
<INPUT type=text name=AuthorEmail  value  = \"$yemail\"  size = 30 maxlength = 30></td>
</tr>
<tr><td colspan=2><br><h3>". _FRIENDINFO . "</h3></td></tr>
<tr>
<td>" . _FRIENDNAME .":</td>
<td>
<INPUT type=text name=MailTo value  = \"$fname\"  size = 30 maxlength = 30></td>
</tr>
<tr>
<td>" . _FRIENDEMAIL .":</td>
<td>
<INPUT type=text name=MailToEmail value  = \"$femail\" size = 30 maxlength = 30></td>
</tr>
";


$VAR["Content"] .= "
<tr>
<td        colspan        = 2>" . _MESSAGE . ": (" . _OPTIONAL . ")<br>
<textarea
	name        = Message
	wrap        = virtual
	rows        = 5
	cols        = 40></textarea></td>
</tr>
<tr>
<td        colspan        = 2>
<input
	type        = hidden
	name        = Story
	value        = \"$story\">
<input
	type        = hidden
	name        = what
	value        = \"mailfriend\">
<input
	type        = submit
	value        = \"" . F_submit() . "\"></td>
</tr>
</table>
</form>
";

F_drawMain($VAR);

include("./include/footer.inc.php");

?>
