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


$VAR["Heading"] = "Blocks Manager";

$sql	= "SELECT * FROM T_Blocks ";
$sql	.= "ORDER BY OrderID,Display";
$result	= mysql_query($sql,$db);
$nrows	= mysql_num_rows($result);

$VAR["Content"] = "<table \tborder\t= 0\n";
$VAR["Content"] .= "\twidth\t= 100%\n";
$VAR["Content"] .= "\tcellspacing\t=1\n";
$VAR["Content"] .= "\tcellpadding\t=2>\n";

if ($nrows==0) {
	$VAR["Content"] .= "<tr><td>You have no defined blocks.</td></tr>\n";
} else {
	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<th>Display</th>\n";
	$VAR["Content"] .= "<th>Heading</th>\n";
	$VAR["Content"] .= "<th>Order</th>\n";
	$VAR["Content"] .= "<th>Type</th>\n";
	$VAR["Content"] .= "<th>Admin</th>\n";
	$VAR["Content"] .= "</tr>\n";
	for ($i=0;$i<$nrows;$i++) {
		$A	= mysql_fetch_array($result);
		switch ($A["Type"]) {
			case "0": $tmp 	= "HTML"; break;
			case "1": $tmp 	= "RDF"; break;
			case "2": $tmp 	= "URL"; break;
		}
		switch ($A["Display"]) {
			case "0": $show = "Off"; break;
			case "l": $show = "Left Block"; break;
			case "r": $show = "Right Block"; break;
			case "p": $show = "Page"; break;
			case "f": $show = "Feature"; break;
		}
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td>" . $show . "</td>\n";
		$VAR["Content"] .= "<td>" . stripslashes($A["Heading"]) . "</td>\n";
		$VAR["Content"] .= "<td>" . $A["OrderID"] . "</td>\n";
		$VAR["Content"] .= "<td>" . $tmp . "</td>\n";
		$VAR["Content"] .= "<td>";
		$VAR["Content"] .= F_admin("T_Blocks",$A["Rid"],"admin/blocks.php");
		$VAR["Content"] .= "</td>\n";
		$VAR["Content"] .= "</tr>\n";
	}
}

$VAR["Content"] .= "</table>\n";

F_drawMain($VAR);

print "<p>\n";

$VAR["Heading"] = "Add a Block / Page";
$VAR["Content"] = "<form\taction\t= \"$G_URL/admin/submit.php\"\n";
$VAR["Content"] .= "\tname\t= Blocks\n";
$VAR["Content"] .= "\tonsubmit\t= \"return validateBlocks()\"\n";
$VAR["Content"] .= "\tmethod\t= post>\n";
	
$VAR["Content"] .= "<table\n";
$VAR["Content"]	.= "\twidth\t= \"100%\"\n";
$VAR["Content"] .= "\tborder\t= \"0\"\n";
$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
$VAR["Content"] .= "\tcellpadding\t= \"2\">\n";

$VAR["Content"] .= "<tr>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "Display:</td>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "<select name\t= \"Display\">\n";
$VAR["Content"] .= "<option value=\"l\">Left Block</option>\n";
$VAR["Content"] .= "<option value=\"r\">Right Block</option>\n";
$VAR["Content"] .= "<option value=\"p\">Page</option>\n";
$VAR["Content"] .= "<option value=\"f\">Feature</option>\n";
$VAR["Content"] .= "</select>\n";
$VAR["Content"] .= "</td>\n";
$VAR["Content"] .= "</tr>\n";

$foo	= F_count("T_Blocks");
$bar	= empty($foo) ? 1 : $foo + 1;

$VAR["Content"] .= "<tr>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "Block Options:</td>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "Sort Order: <input\ttype\t= text\n";
$VAR["Content"] .= "\tname\t= \"OrderID\"\n";
$VAR["Content"] .= "\tsize\t= 3\n";
$VAR["Content"] .= "\tmaxlength\t= 3\n";
$VAR["Content"] .= "\tvalue\t= \"$bar\">\n";
$VAR["Content"] .= " <input\ttype\t= checkbox\n";
$VAR["Content"] .= "\tname\t= \"ShowMain\">";
$VAR["Content"] .= " Display block on story pages only";
$VAR["Content"] .= "</td></tr>\n";

$VAR["Content"] .= "<tr>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "Page/Feature Options:</td>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "<input\ttype\t= checkbox\n";
$VAR["Content"] .= "\tname\t= \"PageComments\">";
$VAR["Content"] .= " Allow user comments";
$VAR["Content"] .= "</td></tr>\n";

$VAR["Content"] .= "<tr>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "Title:</td>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "<input\ttype\t= text\n";
$VAR["Content"] .= "\tname\t= \"Heading\"\n";
$VAR["Content"] .= "\tsize\t= 40\n";
$VAR["Content"] .= "\tmaxlength\t= 48></td>\n";
$VAR["Content"] .= "</tr>\n";

$VAR["Content"] .= "<tr>\n";
$VAR["Content"] .= "<td	colspan	= 2><hr>\n";
$VAR["Content"] .= "<input\ttype\t= radio\n";
$VAR["Content"] .= "\tname\t= Type\n";
$VAR["Content"] .= "\tvalue\t= \"0\" checked> HTML Block\n";
$VAR["Content"] .= "</td>\n";
$VAR["Content"] .= "</tr><tr>\n";
$VAR["Content"] .= "<td	colspan	= 2>\n";
$VAR["Content"] .= "<textarea\n";
$VAR["Content"] .= "\tname\t= \"Content\"\n";
$VAR["Content"] .= "\twrap\t= \"virtual\"\n";
$VAR["Content"] .= "\trows\t= 15\n";
$VAR["Content"] .= "\tcols\t= 50>";
$VAR["Content"] .= "</textarea></td>\n";
$VAR["Content"] .= "</tr>\n";

$VAR["Content"] .= "<tr><td	colspan = 2>\n";
$VAR["Content"] .= "<input\ttype\t= radio\n";
$VAR["Content"] .= "\tname\t= Type\n";
$VAR["Content"] .= "\tvalue\t= \"1\"> RDF Block <small>(Only supports RSS/RDF format)</small>\n";
$VAR["Content"] .= "</td></tr>\n";

$VAR["Content"] .= "<tr><td	colspan = 2>\n";
$VAR["Content"] .= "<input\ttype\t= radio\n";
$VAR["Content"] .= "\tname\t= Type\n";
$VAR["Content"] .= "\tvalue\t= \"2\"> INCLUDE Block <small>(full URL or PATH only)</small>\n";
$VAR["Content"] .= "</td></tr>\n";

$VAR["Content"]	.= "<tr>\n";
$VAR["Content"] .= "<td>URL:</td>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "<input\ttype\t= text\n";
$VAR["Content"]	.= "\tname\t= URL\n";
$VAR["Content"]	.= "\tsize\t= 40>\n";
$VAR["Content"] .= "</td>\n";
$VAR["Content"] .= "</tr>\n";

$VAR["Content"]	.= "<tr>\n";
$VAR["Content"] .= "<td>Cache Time:</td>\n";
$VAR["Content"] .= "<td>\n";
$VAR["Content"] .= "<input\ttype\t= text\n";
$VAR["Content"]	.= "\tname\t= Cache\n";
$VAR["Content"] .= "\tvalue\t= 60\n";
$VAR["Content"]	.= "\tsize\t= 4>\n";
$VAR["Content"] .= "(minutes / 0=no cache)</td>\n";
$VAR["Content"] .= "</tr>\n";

$VAR["Content"] .= "<tr>\n";
$VAR["Content"] .= "<td\tcolspan\t= 2>\n";
$VAR["Content"] .= "<input\ttype\t= hidden\n";
$VAR["Content"] .= "\tname\t= \"what\"\n";
$VAR["Content"] .= "\tvalue\t= \"block\">\n";
$VAR["Content"] .= "<input\ttype\t= \"submit\"\n";
$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
$VAR["Content"] .= "</tr></table></form>\n";
F_drawMain($VAR);

include("../include/footer.inc.php");
?>
