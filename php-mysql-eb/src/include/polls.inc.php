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

function F_pollResults($rid) {
	global $db,$G_URL,$CONF;
	$sql	= "SELECT * FROM T_PollQuestions ";
	$sql	.= "WHERE Rid='$rid'";
	$question = mysql_query($sql,$db);
	$nquestion = mysql_num_rows($question);
	if ($nquestion == 1) {
		$sql	= "SELECT * FROM T_PollAnswers ";
		$sql	.= "WHERE Rid='$rid'";
		$answers	= mysql_query($sql,$db);
		$nanswers	= mysql_num_rows($answers);
		if ($nanswers > 0) {
			$Q = mysql_fetch_array($question);
			$VAR["Heading"]	= _POLLRESULTS;
			$VAR["Content"]	= "";
			$VAR["Content"] .= "<table border=0 cellpadding=2 cellspacing=0 width=\"100%\">";
			$VAR["Content"] .= "<tr>";
			$VAR["Content"] .= "<td colspan= 3>";
			$VAR["Content"] .= stripslashes($Q["Question"]);
			$VAR["Content"] .= "</td></tr>";
			for ($i=1; $i<=$nanswers; $i++) {
				$A = mysql_fetch_array($answers);
				if ($Q["Voters"] == 0) {
					$percent = 0;
				} else {
					$percent = $A["Votes"] / $Q["Voters"];
				}
				$VAR["Content"] .= "<tr>";
				$VAR["Content"] .= "<td width= \"55%\" align=right>";
				$VAR["Content"] .= stripslashes($A["Answer"]) . "&nbsp;";
				$VAR["Content"] .= "</td>";
				$VAR["Content"] .= "<td width= \"350\">";
				$width	= $percent * 300;
				if ($width != 0) {
					$VAR["Content"] .= "<img src=\"$G_URL/images.d/bar.gif\" width=\"$width\" height=\"10\" align=\"bottom\">";
				}
				$VAR["Content"] .= "</td><td width= \"45%\"><b>";
				$VAR["Content"] .= sprintf("%.2f", $percent * 100);
				$VAR["Content"] .= "%</b>&nbsp;(" . $A["Votes"] . ")";
				$VAR["Content"] .= "&nbsp;</td>";
				$VAR["Content"] .= "</tr>";
			}
			$VAR["Content"] .= "</table>";
			F_drawMain($VAR);
		} else {
			F_error("Unable to select poll answer.");
		}
	} else {
		F_error("Unable to select poll question.");
	}
}

function F_pollResultsBlock($rid) {
	global $db,$G_URL,$CONF;
	$sql	= "SELECT * FROM T_PollQuestions ";
	$sql	.= "WHERE Rid='$rid'";
	$question = mysql_query($sql,$db);
	$nquestion = mysql_num_rows($question);
	if ($nquestion==1) {
		$sql	= "SELECT * FROM T_PollAnswers ";
		$sql	.= "WHERE Rid='$rid'";
		$answers	= mysql_query($sql,$db);
		$nanswers	= mysql_num_rows($answers);
		if ($nanswers > 0) {
			$Q = mysql_fetch_array($question);
			$VAR["Heading"]	= _POLLRESULTS;
			$VAR["Content"] = "<small><b>" . stripslashes($Q["Question"]) . "</b>";
			$VAR["Content"] .= "<br>";
			for ($i=1; $i<=$nanswers; $i++) {
				$A = mysql_fetch_array($answers);
				if ($Q["Voters"] == 0) {
					$percent = 0;
				} else {
					$percent = $A["Votes"] / $Q["Voters"];
				}
				$VAR["Content"] .= stripslashes($A["Answer"]) . "&nbsp;<b>";
				$VAR["Content"] .= sprintf("%.2f", $percent * 100);
				$VAR["Content"] .= "%</b>&nbsp;(" . $A["Votes"] . ")";
				$VAR["Content"]	.= "<br>";
			}
			if ($CONF["Comments"]>0) {
				$VAR["Content"] .= "<br>" . F_count("T_Comments","TopRid",$rid) . " " . _COMMENTS . " | ";
			}
			$VAR["Content"] .= $Q["Voters"] . " " . _VOTES . "<br>";
			$VAR["Content"] .= "<div align=right><a href=\"$G_URL/pollbooth.php\">" . _MOREPOLLS . "</a></div>\n";
			$VAR["Content"]	.= "</small>";
			F_drawBlock($VAR);
		} else {
			F_error("Unable to select poll answer.");
		}
	} else {
		F_error("Unable to select poll question.");
	}
}


function F_pollVote($rid,$type="") {
	global $db,$G_URL,$PHP_SELF,$CONF;
	$sql	= "SELECT Question FROM T_PollQuestions ";
	$sql	.= "WHERE Rid='$rid'";
	$question = mysql_query($sql,$db);
	$nquestion = mysql_num_rows($question);
	if ($nquestion == 1) {
		$sql	= "SELECT Answer,Aid FROM T_PollAnswers ";
		$sql	.= "WHERE Rid='$rid'";
		$answers	= @mysql_query($sql,$db);
		$nanswers	= mysql_num_rows($answers);
		if ($nanswers > 0) {
			$Q = mysql_fetch_array($question);
			$VAR["Heading"] = $CONF["SiteName"] . " " . ucfirst(_POLLS);
			$VAR["Content"] = "<form action= \"$G_URL/pollbooth.php\" name=Vote method= POST>";
			$VAR["Content"] .= "<input type= hidden name= poll value= \"" . stripslashes($rid) . "\">";
			$VAR["Content"] .= stripslashes($Q["Question"]) . "<br>";
			for ($i=1; $i<=$nanswers; $i++) {
				$A = mysql_fetch_array($answers);
				$VAR["Content"] .= "<input type= radio name= aid value=" . $A["Aid"] . ">";
				$VAR["Content"] .= stripslashes($A["Answer"]);
				$VAR["Content"] .= "<br>";
			}
			$VAR["Content"] .= "<center><input type= submit value= " . _VOTE ." ><br>";
			$VAR["Content"] .= " [ ";
			$VAR["Content"] .= "<a href= \"$G_URL/pollbooth.php?poll=" . $rid . "&aid=-1\">" . _RESULTS . "</a>";
			$VAR["Content"] .= " | ";
			$VAR["Content"] .= "<a href= \"$G_URL/pollbooth.php\">" . _POLLS . "</a>";
			$VAR["Content"] .= " ] ";
			$VAR["Content"] .= "</center></form>";
			$VAR["Content"] .= F_admin("T_PollQuestions",$rid,$PHP_SELF);

			if ($type=="block") {
				F_drawBlock($VAR);
			} else {
				F_drawMain($VAR);
			}
		} else {
			F_error("Unable to select poll answer.");
		}
	} else {
		F_error("Unable to select poll question.");
	}
}


function F_doVote($Rid,$Aid) {
	global	$db;
	$sql	= "UPDATE T_PollQuestions SET ";
	$sql	.= "Voters = Voters + 1 WHERE ";
	$sql	.= "Rid = '$Rid'";
	$RET	= mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to update poll question.");
	}
	$sql	= "UPDATE T_PollAnswers SET ";
	$sql	.= "Votes = Votes + 1 WHERE ";
	$sql	.= "Rid = '$Rid' AND Aid = '$Aid'";
	$RET	= mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to update poll answer.");
	}
}

function F_pollList() {
	global $db,$G_URL;
	$sql	= "SELECT * FROM T_PollQuestions";
	$question = @mysql_query($sql,$db);
	$nquestion = mysql_num_rows($question);
	$VAR["Heading"] = _LISTPOLLS;
	if ($nquestion > 0) {
		$VAR["Content"] = "<table \tborder\t= 0\n";
		$VAR["Content"] .= "\tcellspacing\t=0\n";
		$VAR["Content"] .= "\tcellpadding\t=2\n";
		$VAR["Content"] .= "\twidth\t= \"100%\">\n";
		$VAR["Content"] .= "<tr><th>" . _QUESTION . "</th><th>" . ucwords(_VOTES) . "</th><th>" . ucwords(_COMMENTS) . "</th></tr>\n";
		for ($i=1; $i<=$nquestion; $i++) {
			$Q = mysql_fetch_array($question);
			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td width=\"100%\"><a class=none \thref\t = \"$G_URL/pollbooth.php?poll=" . $Q["Rid"] . "\">" . stripslashes($Q["Question"]) . "</a>\n";
			$VAR["Content"] .= "[ <a class=none \thref\t= \"$G_URL/pollbooth.php?poll=" . $Q["Rid"] . "&aid=-1\">" . _RESULTS . "</a> ]</td>\n";
			$VAR["Content"] .= "<td align=center>" . $Q["Voters"] . "</td>\n";
			$VAR["Content"] .= "<td align=center>" . F_count("T_Comments","TopRid",$Q["Rid"]) . "</td>\n";
			$VAR["Content"] .= "</tr>\n";
		}
		$VAR["Content"] .= "</table>\n";
	} else {
		$VAR["Content"] .= _NOPOLLS;
	}
	F_drawMain($VAR);
}


?>
