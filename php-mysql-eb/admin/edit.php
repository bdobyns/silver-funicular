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

if ($confirm=="yes") {
	switch ($what) {
		case "T_Stories":
			$sql	= "UPDATE T_Stories SET ";
			$sql	.= "Heading = '" . $Heading . "',";
			if ($CONF["Topics"]>0) {
				$sql	.= "Topic = '" . $Topic . "',";
			}
			$sql	.= "Verified = '" . $Verified . "',";
			$sql	.= "Author = '" . $Author . "',";
			$sql	.= "AuthorEmail = '" . $AuthorEmail . "',";
			$sql	.= "AuthorURL = '" . $AuthorURL . "',";
			$tmp	= $EmailComments == "on" ? 1 : 0;
			$sql	.= "EmailComments = " . $tmp . ",";
			$sql	.= "Summary = '" . addslashes($Summary) . "',";
			$sql	.= "Content = '" . addslashes($Content) . "'";
			$sql	.= " WHERE Rid = '$item' ";
			$RET	= @mysql_query($sql,$db);
			F_logAccess("Updated story $item");
			if ($RET<1) {
				F_error("Unable to update story -- " . mysql_error());
			}
			export_rdf();
			if (!empty($Links)) F_updateIndexLinks($Links,$item);
			header("Location:$G_URL/" . urldecode($where));
			exit();
		break;
		case "T_Blocks":
			if ($Type>0 && !@fopen($URL,"r")) {
				$msg    = urlencode("Error! URL not found: <b>$URL</b>");
				header("Location:$G_URL/admin/blocks.php?msg=$msg");
				exit();
			}
			$sm		= ($ShowMain=="on" ? "1" : "0");
			$pc		= ($PageComments=="on" ? "1" : "0");
			$sql	= "UPDATE T_Blocks SET ";
			$sql	.= "Heading = '" . addslashes($Heading) . "',";
			$sql	.= "Type 	= '" . $Type . "',";
			$sql	.= "Display = '" . $Display . "',";
			$sql	.= "ShowMain = '" . $sm . "',";
			$sql	.= "PageComments = '" . $pc . "',";
			$sql	.= "OrderID = '" . $OrderID . "',";
			$sql	.= "Cache = '" . $Cache . "',";
			$sql	.= "Content = '" . addslashes($Content) . "',";
			if ($nocache=="on") {
				$sql	.= "Birthstamp	= now(),";
				$sql	.= "Timestamp	= now(),";
			}
			$sql	.= "URL 	= '" . $URL . "'";
			$sql	.= " WHERE Rid = '$item' ";
			$RET	= @mysql_query($sql,$db);
			F_logAccess("Updated block $item");
			if ($RET<1) {
				F_error("Unable to update block -- " . mysql_error());
			}
			if ($Display=="f" || $Display=="p") {
				$where	= "admin/blocks.php";
			} else {
				export_rdf();
			}
			header("Location:$G_URL/" . ereg_replace(" ","+",urldecode($where)));
			exit();
		break;
		case "T_Comments":
			$sql	= "UPDATE T_Comments set ";
			$sql	.= "Author = '" . addslashes($Author) . "',";
			$sql	.= "AuthorEmail = '" . addslashes($AuthorEmail) . "',";
			$sql	.= "AuthorURL = '" . addslashes($AuthorURL) . "',";
			$sql	.= "Content = '" . addslashes($Content) . "'";
			$sql	.= " WHERE Rid = '$item' ";
			$RET	= @mysql_query($sql,$db);
			F_logAccess("Updated comment $item");
			if ($RET<1) {
				F_error("Unable to update comment -- " . mysql_error());
			}
			header("Location:$G_URL/" . urldecode($where));
			exit();
		break;
		case "T_Topics":
			$sql	= "UPDATE T_Topics set ";
			$sql	.= "Topic = '" . addslashes($Topic) . "',";
			$sql	.= "AltTag = '" . addslashes($AltTag) . "',";
			$sql	.= "ImgURL = '" . addslashes($ImgURL) . "',";
			$sql	.= "NoPosting = '" . $NoPosting . "',";
			$sql	.= "NoComments = '" . $NoComments . "'";
			$sql	.= " WHERE Rid = '$item' ";
			$RET	= @mysql_query($sql,$db);
			F_logAccess("Updated topic $item");
			if ($RET<1) {
				F_error("Unable to update topic -- " . mysql_error());
			}
			header("Location:$G_URL/admin/topics.php");
			exit();
		break;
		case "T_PollQuestions":
			$A[1] = $Aid1;
			$A[2] = $Aid2;
			$A[3] = $Aid3;
			$A[4] = $Aid4;
			$A[5] = $Aid5;
			$A[6] = $Aid6;
			$A[7] = $Aid7;
			$A[8] = $Aid8;
			$A[9] = $Aid9;
			$A[10] = $Aid10;
			$V[1] = $Votes1;
			$V[2] = $Votes2;
			$V[3] = $Votes3;
			$V[4] = $Votes4;
			$V[5] = $Votes5;
			$V[6] = $Votes6;
			$V[7] = $Votes7;
			$V[8] = $Votes8;
			$V[9] = $Votes9;
			$V[10] = $Votes10;
			$Voters = 0;
			for ($i=1; $i<=10; $i++) {
				$Voters = $Voters + $V[$i];
			}
			F_savePoll(stripslashes($Rid), stripslashes($Display), stripslashes($Question), $Voters, $A, $V);
			F_logAccess("Created poll $Rid");
			header("Location:$G_URL/admin/polls.php");
		break;
		case "T_LinkCats":
			if ($ParentCat==$item) {
				$ParentCat	= "null";
			}
			$sql	= "UPDATE T_LinkCats set ";
			$sql	.= "Name = '" . $Category . "',";
			$sql	.= "ParentRid = '" . $ParentCat . "'";
			$sql	.= " WHERE Rid = '$item' ";
			$RET	= @mysql_query($sql,$db);
			F_logAccess("Updated link category $item");
			if ($RET<1) {
				F_error("Unable to update comment -- " . mysql_error());
			}
			header("Location:$G_URL/links.php");
			exit();
		break;
		default:
			header("Location:$G_URL/stories.php");
		break;
	}
} else {
	switch ($what) {
		case "T_Stories":
			include("../include/header.inc.php");

			$sql	= "SELECT * FROM T_Stories ";
			$sql	.= "WHERE Rid = '$item'";
			$result	= @mysql_query($sql,$db);
			if ($result<1) {
				F_error("Unable to select story -- " . mysql_error());
			}
			$A	= mysql_fetch_array($result);

			$VAR["Heading"] = "Edit : Story : " . $A["Rid"];
			$VAR["Content"] = "<form\taction\t= \"$PHP_SELF\"\n";
//			$VAR["Content"]	.= "\tenctype\t= \"multipart/form-data\"\n";
			$VAR["Content"] .= "\tmethod\t= post>\n";

			$VAR["Content"] .= "<table\n";
			$VAR["Content"] .= "\tborder\t= \"0\"\n";
			$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
			$VAR["Content"] .= "\tcellpadding\t= \"2\"\n";
			$VAR["Content"] .= "\twidth\t= \"100%\">\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Verified:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<select\tname\t=\"Verified\">\n";
			$VAR["Content"]	.= "<option value=\"Y\" " . ($A["Verified"]=="Y" ? "selected" : "") . ">Yes</option>\n";
			$VAR["Content"]	.= "<option value=\"N\" " . ($A["Verified"]=="N" ? "selected" : "") . ">No</option>\n";
			$VAR["Content"] .= "</select>\n</td></tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Name:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"Author\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[Author]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 32></td></tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Email:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"AuthorEmail\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[AuthorEmail]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "URL:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"AuthorURL\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[AuthorURL]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Title:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"Heading\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[Heading]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
			$VAR["Content"] .= "</tr>\n";

			if ($CONF["Topics"]>0) {
				$VAR["Content"] .= "<tr>\n";
				$VAR["Content"] .= "<td>\n";
				$VAR["Content"] .= "Topic:</td>\n";
				$VAR["Content"] .= "<td>\n";
				$VAR["Content"] .= "<select\tname\t= \"Topic\">\n";
				$VAR["Content"] .= F_topicsel($A["Topic"]);
				$VAR["Content"] .= "</select>\n</tr>\n";
			}

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			if ($CONF["SummaryLength"]>0) {
				$VAR["Content"] .= "Summary:</td>\n";
				$VAR["Content"] .= "<td>\n";
				$VAR["Content"] .= "<textarea\n";
				$VAR["Content"] .= "\tname\t= \"Summary\"\n";
				$VAR["Content"] .= "\twrap\t= \"virtual\"\n";
				$VAR["Content"] .= "\trows\t= 10\n";
				$VAR["Content"] .= "\tcols\t= 80>";
				$VAR["Content"] .= stripslashes($A["Summary"]);
				$VAR["Content"] .= "</textarea></td>\n";
			    	$VAR["Content"] .= "</tr>\n";
			}
			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Content:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<textarea\n";
			$VAR["Content"] .= "\tname\t= \"Content\"\n";
			$VAR["Content"] .= "\twrap\t= \"virtual\"\n";
			$VAR["Content"] .= "\trows\t= 20\n";
			$VAR["Content"] .= "\tcols\t= 80>";
			$VAR["Content"] .= stripslashes($A["Content"]);
			$VAR["Content"] .= "</textarea></td>\n";
			$VAR["Content"] .= "</tr>\n";

			if ($CONF["EmailComments"]>0) {
				$VAR["Content"] .= "<tr>\n";
				$VAR["Content"] .= "<td>\n";
				$VAR["Content"] .= "&nbsp;</td>\n";
				$VAR["Content"] .= "<td>\n";
				$tmp	= $A["EmailComments"]==1 ? $tmp = "checked" : "";
				$VAR["Content"] .= "<input\ttype\t= checkbox\n";
				$VAR["Content"] .= "\tname\t= \"EmailComments\" " . $tmp . ">\n";
				$VAR["Content"] .= "Send any comments to the poster.\n";
				$VAR["Content"] .= "</td></tr>\n";
			}

			$VAR["Content"]	.= F_editIndexes2($A["Rid"]);

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td\tcolspan\t= 2\n";
			$VAR["Content"] .= "\talign\t= center>\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"confirm\"\n";
			$VAR["Content"] .= "\tvalue\t= \"yes\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"mode\"\n";
			$VAR["Content"] .= "\tvalue\t= \"edit\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"where\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . urlencode($where) . "\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"what\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$what\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"item\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Rid]\">\n";
			$VAR["Content"] .= "<input\ttype\t= \"submit\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
			$VAR["Content"] .= "</tr></table></form>\n";
			F_drawMain($VAR);
			include("../include/footer.inc.php");
		break;
		case "T_Blocks":
			include("../include/header.inc.php");

			$sql	= "SELECT * FROM T_Blocks ";
			$sql	.= "WHERE Rid = '$item'";
			$result	= @mysql_query($sql,$db);
			if ($result<1) {
				F_error("Unable to select block -- " . mysql_error());
			}
			$A	= mysql_fetch_array($result);

			$VAR["Heading"] = "Edit : Block : " . $A["Rid"];
			$VAR["Content"] = "<form\taction\t= \"$PHP_SELF\"\n";
			$VAR["Content"] .= "\tname\t= Blocks\n";
			$VAR["Content"] .= "\tonsubmit\t= \"return validateBlocks()\"\n";
			$VAR["Content"] .= "\tmethod\t= post>\n";
	
			$VAR["Content"] .= "<table\n";
			$VAR["Content"] .= "\tborder\t= \"0\"\n";
			$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
			$VAR["Content"] .= "\tcellpadding\t= \"2\"\n";
			$VAR["Content"] .= "\twidth\t= \"100%\">\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Display:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<select name\t= \"Display\">\n";
			$VAR["Content"] .= sprintf("<option value=\"0\" %s>Off</option>\n",($A["Display"]=="0" ? "selected" : ""));
			$VAR["Content"] .= sprintf("<option value=\"l\" %s>Left Block</option>\n",($A["Display"]=="l" ? "selected" : ""));
			$VAR["Content"] .= sprintf("<option value=\"r\" %s>Right Block</option>\n",($A["Display"]=="r" ? "selected" : ""));
			$VAR["Content"] .= sprintf("<option value=\"p\" %s>Page</option>\n",($A["Display"]=="p" ? "selected" : ""));
			$VAR["Content"] .= sprintf("<option value=\"f\" %s>Feature</option>\n",($A["Display"]=="f" ? "selected" : ""));
			$VAR["Content"] .= "</select>\n";
			$VAR["Content"] .= "</td>\n";
			$VAR["Content"] .= "</tr>\n";


			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Block Options:</td>\n";
			$VAR["Content"] .= "<td>Sort Order\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"OrderID\"\n";
			$VAR["Content"] .= "\tsize\t= 3\n";
			$VAR["Content"] .= "\tmaxlength\t= 3\n";
			$VAR["Content"] .= "\tvalue\t= \"" . $A["OrderID"] . "\">\n";
			$VAR["Content"] .= "<input\ttype\t= checkbox\n";
			$VAR["Content"] .= "\tname\t= \"ShowMain\"";
			if ($A["ShowMain"]=="1") {
				$VAR["Content"]	.= "checked";
			}
			$VAR["Content"] .= "> Display block on main page only";
			$VAR["Content"] .= "</td></tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Page/Feature Options:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= checkbox\n";
			$VAR["Content"] .= "\tname\t= \"PageComments\"";
			if ($A["PageComments"]=="1") {
				$VAR["Content"]	.= "checked";
			}
			$VAR["Content"] .= "> Allow user comments";
			$VAR["Content"] .= "</td></tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Title:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"Heading\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . htmlspecialchars($A["Heading"]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 48></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td	colspan	= 2><hr>\n";
			$VAR["Content"] .= "<input\ttype\t= radio\n";
			$VAR["Content"] .= "\tname\t= Type\n";
			$VAR["Content"] .= sprintf("\tvalue\t= \"0\" %s> HTML Block\n",($A["Type"]==0?"checked":""));
			$VAR["Content"] .= "</td>\n";
			$VAR["Content"] .= "</tr><tr>\n";
			$VAR["Content"] .= "<td	colspan	= 2>\n";
			$VAR["Content"] .= "<textarea\n";
			$VAR["Content"] .= "\tname\t= \"Content\"\n";
			$VAR["Content"] .= "\twrap\t= \"virtual\"\n";
			$VAR["Content"] .= "\trows\t= 20\n";
			$VAR["Content"] .= "\tcols\t= 80>";
			if ($A["Type"]==0) {
				$VAR["Content"] .= stripslashes($A["Content"]);
			}
			$VAR["Content"] .= "</textarea></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"]	.= "<tr><td colspan=2><hr>";
			$VAR["Content"]	.= "Importing: ";
			$VAR["Content"]	.= "<input\ttype\t= checkbox\n";
			$VAR["Content"]	.= "\tname\t= \"nocache\" checked> Update cache";
			$VAR["Content"]	.= "</td></tr>\n";

			$VAR["Content"] .= "<tr><td	colspan = 2>\n";
			$VAR["Content"] .= "<input\ttype\t= radio\n";
			$VAR["Content"] .= "\tname\t= Type\n";
			$VAR["Content"] .= sprintf("\tvalue\t= \"1\" %s> RDF Block <small>(Only supports RSS/RDF format)</small>\n",($A["Type"]==1?"checked":""));
			$VAR["Content"] .= "</td></tr>\n";

			$VAR["Content"] .= "<tr><td	colspan = 2>\n";
			$VAR["Content"] .= "<input\ttype\t= radio\n";
			$VAR["Content"] .= "\tname\t= Type\n";
			$VAR["Content"] .= sprintf("\tvalue\t= \"2\" %s> INCLUDE Block <small>(full URL or PATH only)</small>\n",($A["Type"]==2?"checked":""));
			$VAR["Content"] .= "</td></tr>\n";

			$VAR["Content"]	.= "<tr>\n";
			$VAR["Content"] .= "<td>URL / RDF:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"]	.= "\tname\t= URL\n";
			$VAR["Content"]	.= "\tsize\t= 40\n";
			$VAR["Content"]	.= "\tvalue\t= \"" . $A["URL"] . "\">\n";
			$VAR["Content"] .= "</td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"]	.= "<tr>\n";
			$VAR["Content"] .= "<td>Cache Time:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"]	.= "\tname\t= Cache\n";
			$VAR["Content"]	.= "\tsize\t= 4\n";
			$VAR["Content"]	.= "\tvalue\t= \"" . $A["Cache"] . "\">\n";
			$VAR["Content"] .= "(minutes / 0=no cache)</td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td\tcolspan\t= 2\n";
			$VAR["Content"] .= "\talign\t= center>\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"confirm\"\n";
			$VAR["Content"] .= "\tvalue\t= \"yes\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"mode\"\n";
			$VAR["Content"] .= "\tvalue\t= \"edit\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"where\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . urlencode($where) . "\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"what\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$what\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"item\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Rid]\">\n";
			$VAR["Content"] .= "<input\ttype\t= \"submit\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
			$VAR["Content"] .= "</tr></table></form>\n";
			F_drawMain($VAR);
			include("../include/footer.inc.php");
		break;
		case "T_Topics":
			include("../include/header.inc.php");

			$sql	= "SELECT * FROM T_Topics ";
			$sql	.= "WHERE Rid = '$item'";
			$result	= @mysql_query($sql,$db);
			if ($result<1) {
				F_error("Unable to select topic -- " . mysql_error());
			}
			$A	= mysql_fetch_array($result);

			$VAR["Heading"] = "Edit : Topic : " . $A["Rid"];
			$VAR["Content"] = "<form\taction\t= \"$PHP_SELF\"\n";
			$VAR["Content"] .= "\tmethod\t= post>\n";

			$VAR["Content"] .= "<table\n";
			$VAR["Content"] .= "\tborder\t= \"0\"\n";
			$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
			$VAR["Content"] .= "\tcellpadding\t= \"2\"\n";
			$VAR["Content"] .= "\twidth\t= \"100%\">\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Topic:</td>\n";
			$VAR["Content"] .= "<td colspan=2>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"Topic\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Topic]\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 48></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Image URL:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"ImgURL\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[ImgURL]\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
			if (!empty($A["ImgURL"])) {
				$tmp	= sprintf("<img src=\"%s\">\n",$A["ImgURL"]);
			} else {
				$tmp	= "&nbsp;";
			}
			$VAR["Content"] .= "<td>$tmp</td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Alt Tag:</td>\n";
			$VAR["Content"] .= "<td colspan=2>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"AltTag\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[AltTag]\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 64></td>\n";
			$VAR["Content"] .= "</tr>\n";
			$VAR["Content"]	.= "<tr>\n<td>";

			if ($CONF["AllowContrib"]>0) {
				$VAR["Content"]	.= "Posting:</td>\n";
				$VAR["Content"]	.= "<td colspan=2>\n";
				$VAR["Content"] .= "<input type=radio name=NoPosting value=\"0\" ";
				$VAR["Content"] .= ( $A["NoPosting"]==0 ? "checked" : "" ) . "> On \n";
				$VAR["Content"] .= "<input type=radio name=NoPosting value=\"1\" ";
				$VAR["Content"] .= ( $A["NoPosting"]==1 ? "checked" : "" ) . "> Off <br>\n";
			} else {
				$VAR["Content"] .= "<input type=hidden name=NoPosting value=\"" . $A["NoPosting"] . "\">";
			}
			$VAR["Content"]	.= "</td>\n";
			$VAR["Content"]	.= "</tr>\n";
			$VAR["Content"]	.= "<tr>\n<td>";
			if ($CONF["Comments"]>0) {
				$VAR["Content"]	.= "Comments:</td>\n";
				$VAR["Content"]	.= "<td colspan=2>\n";
				$VAR["Content"] .= "<input type=radio name=NoComments value=\"0\" ";
				$VAR["Content"] .= ( $A["NoComments"]==0 ? "checked" : "" ) . "> On \n";
				$VAR["Content"] .= "<input type=radio name=NoComments value=\"1\" ";
				$VAR["Content"] .= ( $A["NoComments"]==1 ? "checked" : "" ) . "> Off \n";
				$VAR["Content"] .= "<input type=radio name=NoComments value=\"2\" ";
				$VAR["Content"] .= ( $A["NoComments"]==2 ? "checked" : "" ) . "> Display Only\n";
			} else {
				$VAR["Content"] .= "<input type=hidden name=NoComments value=\"" . $A["NoComments"] . "\">";
			}
			$VAR["Content"]	.= "</td>\n";
			$VAR["Content"]	.= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td\tcolspan\t= 3\n";
			$VAR["Content"] .= "\talign\t= center>\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"confirm\"\n";
			$VAR["Content"] .= "\tvalue\t= \"yes\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"mode\"\n";
			$VAR["Content"] .= "\tvalue\t= \"edit\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"what\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$what\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"item\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Rid]\">\n";
			$VAR["Content"] .= "<input\ttype\t= \"submit\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
			$VAR["Content"] .= "</tr></table></form>\n";
			F_drawMain($VAR);
			include("../include/footer.inc.php");
		break;
		case "T_Comments":
			include("../include/header.inc.php");

			$sql	= "SELECT * FROM T_Comments ";
			$sql	.= "WHERE Rid = '$item'";
			$result	= @mysql_query($sql,$db);
			if ($result<1) {
				F_error("Unable to select comment -- " . mysql_error());
			}
			$A	= mysql_fetch_array($result);

			$VAR["Heading"] = "Edit : Comment : " . $A["Rid"];
			$VAR["Content"] = "<form\taction\t= \"$PHP_SELF\"\n";
			$VAR["Content"] .= "\tmethod\t= post>\n";

			$VAR["Content"] .= "<table\n";
			$VAR["Content"] .= "\tborder\t= \"0\"\n";
			$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
			$VAR["Content"] .= "\tcellpadding\t= \"2\"\n";
			$VAR["Content"] .= "\twidth\t= \"100%\"\n";
			$VAR["Content"] .= "\tbgcolor\t= \"#eeeeee\">\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Name:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"Author\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[Author]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 32></td></tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Email:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"AuthorEmail\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[AuthorEmail]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "URL:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"AuthorURL\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A[AuthorURL]) . "\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Comment:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<textarea\n";
			$VAR["Content"] .= "\tname\t= \"Content\"\n";
			$VAR["Content"] .= "\twrap\t= \"virtual\"\n";
			$VAR["Content"] .= "\trows\t= 10\n";
			$VAR["Content"] .= "\tcols\t= 80>";
			$VAR["Content"] .= stripslashes($A["Content"]);
			$VAR["Content"] .= "</textarea></td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td\tcolspan\t= 2\n";
			$VAR["Content"] .= "\talign\t= center>\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"confirm\"\n";
			$VAR["Content"] .= "\tvalue\t= \"yes\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"mode\"\n";
			$VAR["Content"] .= "\tvalue\t= \"edit\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"where\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . urlencode($where) . "\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"what\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$what\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"item\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Rid]\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"ParentRid\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[ParentRid]\">\n";
			$VAR["Content"] .= "<input\ttype\t= \"submit\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
			$VAR["Content"] .= "</tr></table></form>\n";
			F_drawMain($VAR);
			include("../include/footer.inc.php");
		break;
		case "T_PollQuestions":
			include("../include/header.inc.php");
			$sql	= "SELECT * FROM T_PollQuestions ";
			$sql	.= "WHERE Rid='$item'";
			$question	= mysql_query($sql,$db);
			$nquestions 	= mysql_num_rows($question);
			if ($nquestions > 0) {
				$sql	= "SELECT Answer,Aid,Votes FROM T_PollAnswers ";
				$sql	.= "WHERE Rid='$item'";
				$answers	= mysql_query($sql,$db);
				$nanswers	= mysql_num_rows($answers);
				if ($nanswers > 0) {
					$Q = mysql_fetch_array($question);
					$VAR["Heading"] = "Edit : Poll : " . $Q["Rid"];
					$VAR["Content"] = "<form \taction\t= \"$G_URL/admin/submit.php\"\n";
					$VAR["Content"] .= "\tmethod\t= post>\n";

					$VAR["Content"] .= "<table\n";
					$VAR["Content"] .= "\tborder\t= \"0\"\n";
					$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
					$VAR["Content"] .= "\tcellpadding\t= \"2\"\n";
					$VAR["Content"] .= "\twidth\t= \"100%\"\n";
					$VAR["Content"] .= "\tbgcolor\t= \"#eeeeee\">\n";

					$VAR["Content"] .= "<tr>\n";
					$VAR["Content"] .= "<td>Question:</td>\n";
					$VAR["Content"] .= "<td><input \ttype\t= \"text\"\n";
					$VAR["Content"] .= "\tname \t= Question\n";
					$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($Q["Question"]) . "\"\n";
					$VAR["Content"] .= "\tsize\t=32\n";
					$VAR["Content"] .= "maxlength\t=255></td>\n";
					$VAR["Content"] .= "</tr>\n";

					$VAR["Content"] .= "<tr>\n";
					$VAR["Content"] .= "<td>Display</td>\n";
					$VAR["Content"] .= "<td>\n";
					$VAR["Content"] .= "<select name\t=Display>\n";
					$VAR["Content"] .= "<option value=\"0\" " . ($Q["Display"] == "0" ? "selected" : "") . ">None</option>\n";
					$VAR["Content"] .= "<option value=\"l\" " . ($Q["Display"] == "l" ? "selected" : "") . ">Left</option>\n";
					$VAR["Content"] .= "<option value=\"r\" " . ($Q["Display"] == "r" ? "selected" : "") . ">Right</option>\n";
					$VAR["Content"] .= "</select></td>\n";
					$VAR["Content"] .= "</tr>\n";

					$VAR["Content"] .= "<tr>\n";
					$VAR["Content"] .= "<td>Expiration:</td>\n";
					$VAR["Content"] .= "<td><input \ttype\t= \"text\"\n";
					$VAR["Content"] .= "\tname \t= Days\n";
					$VAR["Content"] .= "\tvalue\t= \"" . $Q["ExpireDays"] . "\"\n";
					$VAR["Content"] .= "\tsize\t=3\n";
					$VAR["Content"] .= "maxlength\t=3> days</td>\n";
					$VAR["Content"] .= "</tr>\n";

					for ($i=1; $i<=10; $i++) {
						$A	= mysql_fetch_array($answers);
						$VAR["Content"] .= "<tr>\n";
						$VAR["Content"] .= "<td>Answer #" . $i . "</td>";
						$VAR["Content"] .= "<td>\n<input \ttype\t= \"text\"\n";
						$VAR["Content"] .= "\tname\t= Aid" . $i . "\n";
						$VAR["Content"] .= "\tvalue\t= \"" . stripslashes($A["Answer"]) . "\"\n";
						$VAR["Content"] .= "\tsize\t= 24\n";
						$VAR["Content"] .= "\tmaxlength\t= 255>\n";
						$VAR["Content"] .= "Votes:\n<input \ttype\t= \"text\"\n";
						$VAR["Content"] .= "\tname \t= Votes" . $i . "\n";
						$VAR["Content"] .= "\tvalue\t= \"" . $A["Votes"] . "\"\n";
						$VAR["Content"] .= "\tsize\t= 5>\n</td>\n";
						$VAR["Content"] .= "</tr>\n";
					}

					$VAR["Content"] .= "<tr>\n";
					$VAR["Content"] .= "<td colspan=2>";
					$VAR["Content"] .= "<input\ttype\t= hidden\n";
					$VAR["Content"] .= "\tname\t= \"what\"\n";
					$VAR["Content"] .= "\tvalue\t= \"poll\">\n";
					$VAR["Content"] .= "<input\ttype\t= hidden\n";
					$VAR["Content"] .= "\tname\t= \"where\"\n";
					$VAR["Content"] .= "\tvalue\t= \"" . $where . "\">\n";
					$VAR["Content"] .= "<input\ttype\t= hidden\n";
					$VAR["Content"] .= "\tname\t= \"Rid\"\n";
					$VAR["Content"] .= "\tvalue\t= \"" . $Q["Rid"] . "\">\n";
					$VAR["Content"] .= "<input \ttype\t= submit";
					$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
					$VAR["Content"] .= "</tr>\n";
					$VAR["Content"] .= "</table>\n";
					$VAR["Content"] .= "</form>\n";
					F_drawMain($VAR);
				} else {
					F_error("Unable to select poll answer -- " . mysql_error());
				}
			} else {
				F_error("Unable to select poll question -- " . mysql_error());
			}
		include("../include/footer.inc.php");
		break;
		case "T_LinkCats":
			include("../include/header.inc.php");

			$sql	= "SELECT * FROM T_LinkCats ";
			$sql	.= "WHERE Rid = '$item'";
			$result	= @mysql_query($sql,$db);
			if ($result<1) {
				F_error("Unable to select link category -- " . mysql_error());
			}
			$A	= mysql_fetch_array($result);

			$VAR["Heading"] = "Edit : Link Category : " . $A["Rid"];
			$VAR["Content"] = "<form\taction\t= \"$PHP_SELF\"\n";
			$VAR["Content"] .= "\tmethod\t= post>\n";

			$VAR["Content"] .= "<table\n";
			$VAR["Content"] .= "\tborder\t= \"0\"\n";
			$VAR["Content"] .= "\tcellspacing\t= \"1\"\n";
			$VAR["Content"] .= "\tcellpadding\t= \"2\"\n";
			$VAR["Content"] .= "\twidth\t= \"100%\"\n";
			$VAR["Content"] .= "\tbgcolor\t= \"#eeeeee\">\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Category Name:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= \"text\"\n";
			$VAR["Content"] .= "\tname\t= \"Category\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Name]\"\n";
			$VAR["Content"] .= "\tsize\t= 40\n";
			$VAR["Content"] .= "\tmaxlength\t= 48></td><td>&nbsp;</td>\n";
			$VAR["Content"] .= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "Parent Category:</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<select\tname\t= \"ParentCat\">\n";
			$VAR["Content"] .= F_listcats($A["ParentRid"]);
			$VAR["Content"] .= "</select>\n";
			$VAR["Content"]	.= "</tr>\n";

			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td\tcolspan\t= 3\n";
			$VAR["Content"] .= "\talign\t= center>\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"confirm\"\n";
			$VAR["Content"] .= "\tvalue\t= \"yes\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"mode\"\n";
			$VAR["Content"] .= "\tvalue\t= \"edit\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"what\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$what\">\n";
			$VAR["Content"] .= "<input\ttype\t= hidden\n";
			$VAR["Content"] .= "\tname\t= \"item\"\n";
			$VAR["Content"] .= "\tvalue\t= \"$A[Rid]\">\n";
			$VAR["Content"] .= "<input\ttype\t= \"submit\"\n";
			$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\"></td>\n";
			$VAR["Content"] .= "</tr></table></form>\n";
			F_drawMain($VAR);
			include("../include/footer.inc.php");
		break;
	}
}

?>
