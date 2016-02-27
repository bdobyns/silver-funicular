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

function F_drawSearch($query="") {
	global	$CONF,$G_URL;
	$VAR["Heading"] = ucfirst(_SEARCH) . " " . $CONF["SiteName"];
	$VAR["Content"] = "<form action = \"" . $G_URL . "/search.php\" method= GET>\n";
	$VAR["Content"] .= "<table border=0>\n";
	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td>" . _SEARCHFOR . ":";
	$VAR["Content"]	.= "</td>\n<td>\n";
	$VAR["Content"] .= "<input \ttype\t= text\n";
	$VAR["Content"] .= "\tname\t= \"query\"\n";
	$VAR["Content"] .= "\tvalue\t= \"" . $query . "\"\n";
	$VAR["Content"] .= "\tsize\t= 35\n";
	$VAR["Content"] .= "\tmaxlength\t= 35>\n";
	$VAR["Content"] .= "</td>\n";
	$VAR["Content"] .= "</tr>\n";

	if ($CONF["Topics"]>0) {
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td>" . _TOPIC . ":";
		$VAR["Content"]	.= "</td>\n<td>\n";
		$VAR["Content"] .= "<select name = \"topic\">\n";
		$VAR["Content"] .= "<option selected value=\"0\">" . _ALLTOPICS . "</option>\n";
		$VAR["Content"]	.= F_topicsel(0);
		$VAR["Content"] .= "</select></td>\n";
		$VAR["Content"] .= "</tr>\n";
	}

	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td>" . _QUERY . ": \n";
	$VAR["Content"]	.= "</td>\n<td>\n";
	$VAR["Content"] .= "<input type = \"radio\"\n";
	$VAR["Content"] .= "\tname \t= \"what\"\n";
	$VAR["Content"] .= "\tvalue \t= \"stories\" checked> " . _STORIES . "\n";
	if ($CONF["Comments"]>0) {
		$VAR["Content"] .= "<input type = \"radio\"\n";
		$VAR["Content"] .= "\tname \t= \"what\"\n";
		$VAR["Content"] .= "\tvalue \t= \"comments\"> " . _COMMENTS . "\n";
	}
	$VAR["Content"] .= "<input type = \"radio\"\n";
	$VAR["Content"] .= "\tname \t= \"what\"\n";
	$VAR["Content"] .= "\tvalue \t= \"authors\"> " . _AUTHORS . "\n";
	$VAR["Content"]	.= "</td></tr><tr><td>\n";
	$VAR["Content"] .= ucwords(_LIMIT) . ":";
	$VAR["Content"]	.= "</td>\n<td>\n";
	$VAR["Content"] .= "<select name = \"limit\">\n";
	$VAR["Content"] .= "<option value=\"10\">10</option>\n";
	$VAR["Content"] .= "<option value=\"20\">20</option>\n";
	$VAR["Content"] .= "<option selected value=\"30\">30</option>\n";
	$VAR["Content"] .= "<option value=\"40\">40</option>\n";
	$VAR["Content"] .= "<option value=\"50\">50</option>\n";
	$VAR["Content"] .= "</select>\n";
	$VAR["Content"] .= "</tr>\n";
	$VAR["Content"] .= "<tr>\n";
	$VAR["Content"] .= "<td colspan=2><input type = \"submit\" value =\"" . ucwords(_SEARCH) . "\"></td>\n";
	$VAR["Content"] .= "</tr>\n";
	$VAR["Content"] .= "</table>\n";
	$VAR["Content"] .= "</form>\n";
	F_drawMain($VAR);
}

function F_drawResults($query,$topic,$what,$limit="30",$month="",$year="") {
	global $db,$G_URL;
	switch ($what) {
		case "comments";
			/*== build query statement ==*/
			$sql    = "SELECT * FROM T_Comments WHERE ";
			$sql	.= "(Content like '%" . $query . "%' ";
			$sql	.= "OR Content like '" . $query . "%' ";
			$sql	.= "OR Content like '%" . $query . "') ";
			$sql    .= "ORDER BY Timestamp desc";
			$sql	.= " LIMIT $limit";
			$result = @mysql_query($sql,$db);
			$nrows  = mysql_num_rows($result);
			if ($nrows>0) {

				/*== show items ==*/
				$VAR["Heading"] = _SEARCHRESULTS . ": " . $nrows . " " . _MATCHES . " \"" . strip_tags($query) . "\"\n";
				$VAR["Content"] = "<table cellpadding=1 cellspacing=5 border=0 width=\"99%\">\n";
				for ($i=0;$i<$nrows;$i++) {
					$A      = mysql_fetch_array($result);
					$no	= $i+1;
					$topic	= F_getTopic($A["Topic"]);
				$VAR["Content"] .= "<tr>\n";
				$VAR["Content"] .= "\t<td align=left>";
				$VAR["Content"] .= "<b>$no.</b> ";
				/*== determine if comment is from stories or polls ==*/
				if (F_count("T_Stories","Rid",$A["TopRid"])>0) {
					$VAR["Content"] .= "<a href=\"$G_URL/stories.php?story=" . $A["TopRid"] . "\"><b>" . substr($A["Content"],0,50) . "...</b></a>";
				} else {
					$VAR["Content"] .= "<a href=\"$G_URL/pollbooth.php?poll=" . $A["TopRid"] . "&aid=-1\"><b>" . substr($A["Content"],0,50) . "...</b></a>";
				}
				$VAR["Content"] .= "<br>\n<small>" . _POSTEDBY . " " . F_author($A) . "\n";
				$VAR["Content"] .= " " . _ON . " " . F_dateFormat($A["Birthstamp"]) . "</small>\n";
				$VAR["Content"] .= "</td></tr>\n";

			}
        	        $VAR["Content"] .= "</table>\n";
        	        F_drawMain($VAR);

		} else {
			$VAR["Heading"] = _SEARCHRESULTS;
			$VAR["Content"] = "<br>" . _NOMATCHES . "<br><br>";
			F_drawMain($VAR);
		}
		break;
		case "authors":
			/*== build query statement ==*/
			$sql    = "SELECT * FROM T_Stories WHERE ";
			$sql	.= "(Author like '%" . $query . "%' ";
			$sql	.= "OR Author like '" . $query . "%' ";
			$sql	.= "OR Author like '%" . $query . "') ";
			$sql    .= "AND Verified = 'Y' ";
			if ($topic["Topic"]>0) {
				$sql    .= "AND Topic = " . $topic["Topic"];
			}
			$sql    .= " ORDER BY Repostamp desc";
			$sql	.= " LIMIT $limit";
			$result = @mysql_query($sql,$db);
			$nrows  = mysql_num_rows($result);
			if ($nrows>0) {

				/*== show items ==*/
				if ($limit=="5") {
					$VAR["Heading"] = _LAST5 . " " . $query;
				} else {
					$VAR["Heading"] = _SEARCHRESULTS . ": " . $nrows . " " . _MATCHES . " \"" . strip_tags($query) . "\"\n";
				}
				$VAR["Content"] = "<table cellpadding=1 cellspacing=5 border=0 width=\"99%\">\n";
				for ($i=0;$i<$nrows;$i++) {
				$A      = mysql_fetch_array($result);
				$no	= $i+1;
				$topic	= F_getTopic($A["Topic"]);
				$VAR["Content"] .= "<tr>\n";
				$VAR["Content"] .= "\t<td align=left>";
				$VAR["Content"] .= "<b>$no.</b> ";
				$VAR["Content"] .= "<a class=none href=\"$G_URL/stories.php?story=" . $A["Rid"] . "\"><b>" . $A["Heading"] . "</b></a>";
				$VAR["Content"] .= " (<a class=none href=\"$G_URL/stories.php?topic=" . $A["Topic"] . "\">" . $topic["Topic"] . "</a>) ";
				$VAR["Content"] .= _BY . " " . F_author($A) . "\n";
				$VAR["Content"] .= "<br><small>" . F_count("T_Comments","ParentRid",$A["Rid"])  . " " . _COMMENTS . ",";
				$VAR["Content"] .= " " . _POSTEDON . " " . F_dateFormat($A["Repostamp"]) . "</small>\n";
				$VAR["Content"] .= "</td></tr>\n";
			}
        	        $VAR["Content"] .= "</table>\n";
        	        F_drawMain($VAR);

		} else {
			if ($limit=="5") {
				$VAR["Heading"] = _LAST5 . " " . $query;
			} else {
				$VAR["Heading"] = _SEARCHRESULTS;
			}
			$VAR["Content"] = "<br>" . _NOMATCHES . "<br><br>";
			F_drawMain($VAR);
		}
		break;
		default:
			/*== build query statement ==*/
			$sql    = "SELECT * FROM T_Stories WHERE ";
			$sql	.= "(Content like '%" . $query . "%' ";
			$sql	.= "OR Content like '" . $query . "%' ";
			$sql	.= "OR Content like '%" . $query . "') ";
			$sql	.= "OR (Heading like '%" . $query . "%' ";
			$sql	.= "OR Heading like '" . $query . "%' ";
			$sql	.= "OR Heading like '%" . $query . "') ";
			$sql	.= "OR (Summary like '%" . $query . "%' ";
			$sql	.= "OR Summary like '" . $query . "%' ";
			$sql	.= "OR Summary like '%" . $query . "') ";
			/*== archiving routine ==*/
			if (!empty($month)) {
				$sql    = "SELECT * FROM T_Stories ";
				$sql	.= "WHERE (month(Repostamp) = '$month' ";
				$sql	.= "AND year(Repostamp) = '$year') ";
			}
			$sql    .= "AND Verified = 'Y' ";
			if ($topic["Topic"]>1) {
				$sql    .= "AND Topic = " . $topic["Topic"];
			}
			if (0 || !empty($month))
				$sql    .= " ORDER BY Repostamp desc";
			if ($limit>0) {
				$sql	.= " LIMIT $limit";
			}
			$result = @mysql_query($sql,$db);
			$nrows  = @mysql_num_rows($result);
			if ($nrows>0) {

				/*== show items ==*/
				if (!empty($month)) {
					$VAR["Heading"]	= ucwords(_ARCHIVE);
				} else {
					$VAR["Heading"] = _SEARCHRESULTS . ": " . $nrows . " " . _MATCHES . " \"" . strip_tags($query) . "\"\n";
				}
				$VAR["Content"] = "<table cellpadding=1 cellspacing=5 border=0 width=\"99%\">\n";
				for ($i=0;$i<$nrows;$i++) {
				$A      = mysql_fetch_array($result);
				$no	= $i+1;
				$topic	= F_getTopic($A["Topic"]);
				$VAR["Content"] .= "<tr>\n";
				$VAR["Content"] .= "\t<td align=left>";
				$VAR["Content"] .= "<b>$no.</b> ";
				$VAR["Content"] .= "<a class=none href=\"$G_URL/stories.php?story=" . $A["Rid"] . "\"><b>" . $A["Heading"] . "</b></a>";
				$VAR["Content"] .= " (<a class=none href=\"$G_URL/stories.php?topic=" . $A["Topic"] . "\">" . $topic["Topic"] . "</a>) ";
				$VAR["Content"] .= _BY . " " . F_author($A) . "\n";
				$VAR["Content"] .= "<br><small>" . F_count("T_Comments","ParentRid",$A["Rid"])  . " " . _COMMENTS . ",";
				$VAR["Content"] .= " " . _POSTEDON . " " . F_dateFormat($A["Repostamp"]) . "</small>\n";
				$VAR["Content"] .= "</td></tr>\n";
			}
			$VAR["Content"] .= "</table>\n";
			F_drawMain($VAR);

		} else {
			$VAR["Heading"] = _SEARCHRESULTS;
			$VAR["Content"] = "<br>" . _NOMATCHES . "<br><br>";
			F_drawMain($VAR);
		}
		break;
	}
}

?>
