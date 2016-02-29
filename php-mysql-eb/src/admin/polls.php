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

	/*== list polls ==*/
	$VAR["Heading"] = "Polls Manager";

	$sql	= "SELECT * FROM T_PollQuestions ";
	$sql	.= "ORDER BY Rid";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);

	$VAR["Content"] = "<table \tborder\t= 0\n";
	$VAR["Content"] .= "\twidth\t= 100%\n";
	$VAR["Content"] .= "\tcellspacing\t=1\n";
	$VAR["Content"] .= "\tcellpadding\t=2>\n";

	if ($nrows==0) {
		$VAR["Content"] .= "<tr><td>You have no defined polls.</td></tr>\n";
	} else {
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<th>Question</th>\n";
		$VAR["Content"] .= "<th>Display</th>\n";
		$VAR["Content"] .= "<th>Votes</th>\n";
		$VAR["Content"] .= "<th>&nbsp;</th>\n";
		$VAR["Content"] .= "</tr>\n";

		$BG   = array($LAYOUT["ListBgColor1"],$LAYOUT["ListBgColor2"]);
		for ($i=0;$i<$nrows;$i++) {
			$A	= mysql_fetch_array($result);
			$bg	= $BG[$i%2];
			switch ($A["Display"]) {
				case "0": $tmp = "Off"; break;
				case "l": $tmp = "Left"; break;
				case "r": $tmp = "Right"; break;
			}
			$VAR["Content"] .= "<tr bgcolor=\"$bg\">\n";
			$VAR["Content"] .= "<td>" . stripslashes($A["Question"]) . "</td>\n";
			$VAR["Content"] .= "<td>" . $tmp . "</td>\n";
			$VAR["Content"] .= "<td>" . $A["Voters"] . "</td>";
			$VAR["Content"] .= "<td align=center>";
			$VAR["Content"] .= F_admin("T_PollQuestions",$A["Rid"],"admin/polls.php");
			$VAR["Content"] .= "</td>\n";
			$VAR["Content"] .= "</tr>\n";
		}
	}

	$VAR["Content"] .= "</table>\n";
	F_drawMain($VAR);

	print "<p>\n";

	/*== add poll ==*/
	$VAR["Heading"] = "Add a New Poll";
	$VAR["Content"] = "<form \taction\t= \"$G_URL/admin/submit.php\"\n";
	$VAR["Content"] .= "\tmethod\t= post>\n";
	$VAR["Content"] .= "<table cellspacing=1 cellpadding=2 width=\"100%\" border=0>\n\n";

	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td>Question:</td>\n";
	$VAR["Content"] .= "<td><input \ttype\t= text\n";
	$VAR["Content"] .= "\tname \t= Question\n";
	$VAR["Content"] .= "\tsize\t=32\n";
	$VAR["Content"] .= "maxlength\t=255></td>\n";
	$VAR["Content"] .= "</tr>\n";

	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td>\n";
	$VAR["Content"] .= " Display as Block\n";
	$VAR["Content"]	.= "</td><td>\n";
	$VAR["Content"] .= "<select	name\t= Display>\n";
	$VAR["Content"]	.= "<option value\t=\"0\">Off</option>\n";
	$VAR["Content"]	.= "<option value\t=\"l\" selected>Left</option>\n";
	$VAR["Content"]	.= "<option value\t=\"r\">Right</option>\n";
	$VAR["Content"]	.= "</select>\n";
	$VAR["Content"]	.= "</td>\n";
	$VAR["Content"] .= "</tr>\n";

	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td>Days to Expire:</td>\n";
	$VAR["Content"] .= "<td><input \ttype\t= text\n";
	$VAR["Content"] .= "\tname \t= Days\n";
	$VAR["Content"] .= "\tsize\t=3\n";
	$VAR["Content"] .= "\tvalue\t=7\n";
	$VAR["Content"] .= "maxlength\t=3> <small>(cookie expiration)</small></td>\n";
	$VAR["Content"] .= "</tr>\n";

	for ($i=1; $i<=10; $i++) {
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td>Answer #" . $i . "</td>";
		$VAR["Content"] .= "<td><input type\t=text\n";
		$VAR["Content"] .= "name\t= Aid" . $i . "\n";
		$VAR["Content"] .= "\tsize\t= 24\n";
		$VAR["Content"] .= "\tmaxlength\t= 255>\n";
		$VAR["Content"] .= "Votes:<input \ttype\t=text\n";
		$VAR["Content"] .= "\tname \t= Votes" . $i . "\n";
		$VAR["Content"] .= "\tvalue \t= \"0\"\n";
		$VAR["Content"] .= "\tsize\t= 5>\n</td>\n";
		$VAR["Content"] .= "</tr>\n";
	}

	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td colspan=2>";
	$VAR["Content"] .= "<input \ttype\t= hidden";
	$VAR["Content"] .= "\n\tname\t= what\n";
	$VAR["Content"] .= "\tvalue\t= poll>\n";
	$VAR["Content"] .= "<input \ttype\t= hidden";
	$VAR["Content"] .= "\n\tname\t= where\n";
	$VAR["Content"] .= "\tvalue\t= \"admin/polls.php\">\n";
	$VAR["Content"] .= "<input \ttype\t= hidden";
	$VAR["Content"] .= "\n\tname\t= Rid\n";
	$VAR["Content"] .= "\tvalue\t= \"" . F_getRid() . "\">\n";
	$VAR["Content"] .= "<input \ttype\t= submit";
	$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
	$VAR["Content"] .= "</tr>\n";

	$VAR["Content"] .= "</table>\n";
	$VAR["Content"] .= "</form>\n";
	F_drawMain($VAR);

include("../include/footer.inc.php");

?>
