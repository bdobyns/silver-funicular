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

function F_doBlocks($align) {
	global	$G_URL,$LAYOUT,$CONF,$db,$HTTP_COOKIE_VARS,$story;
	global	$PHP_SELF,$topic,$REQUEST_URI;
	$cnt	= 0;
	$aln	= substr($LAYOUT["BlocksAlign"],0,1);

	# check if we are on the main page
	if (basename($PHP_SELF)=="stories.php" || basename($PHP_SELF)=="index.php") {
		$mainpage	= 1;
	}

	$sql	= "SELECT *,UNIX_TIMESTAMP(Timestamp) AS Timestamp,";
	$sql	.= "UNIX_TIMESTAMP(Birthstamp) As Birthstamp ";
	$sql	.= "FROM T_Blocks ";
	$sql	.= "WHERE Display = '$align' ";
	$sql	.= "ORDER BY OrderID";
	$blocks_result	= @mysql_query($sql,$db);
	$blocks_nrows	= @mysql_num_rows($blocks_result);
	$cnt	= $cnt	+ $blocks_nrows;

	$sql	= "SELECT Rid FROM T_PollQuestions ";
	$sql	.= "WHERE Display = '$align'";
	$polls_result = @mysql_query($sql,$db);
	/* $polls_result will be false if query fails */
	if (!$polls_result) {
		$polls_nrows = 0;
	} else {
		$polls_nrows = mysql_num_rows($polls_result);
	}
	if (basename($PHP_SELF)!="pollbooth.php") {
		$cnt	= $cnt	+ $polls_nrows;
	}

	if ($CONF["Older"]>0 && $aln==$align) {
		$sql = "SELECT Rid,Heading ";
		$sql .= "FROM T_Stories ";
		$sql .= "WHERE Verified = 'Y' ";
		$sql .= "ORDER BY Repostamp desc ";
		$sql .= sprintf("LIMIT %s,%s",$CONF["LimitNews"],$CONF["LimitNews"]); 
		$older_result = @mysql_query($sql,$db);
		$older_nrows = @mysql_num_rows($older_result);
		$cnt	= $cnt	+ $older_nrows;
	}

	$tmp	= F_count("T_Stories");
	if ($CONF["Top5"]>0 && $aln==$align && $tmp>$CONF["LimitNews"]) {
		$sql = "SELECT T_Stories.Heading,T_Stories.Rid,count(*) AS Cmts ";
		$sql .= ", T_Comments.TopRid AS Test ";
		$sql .= "FROM T_Stories LEFT JOIN T_Comments ";
		$sql .= "ON T_Comments.TopRid = T_Stories.Rid ";
		$sql .= "WHERE T_Stories.Verified = 'Y' ";
		$sql .= "GROUP BY T_Stories.Rid ";
		$sql .= "ORDER BY T_Stories.Hits desc ";
		$sql .= "LIMIT 5";
		$top5_result = @mysql_query($sql,$db);
		$top5_nrows = @mysql_num_rows($top5_result);
		$cnt	= $cnt	+ $top5_nrows;
	}

	if ($CONF["Hot5"]>0 && $aln==$align && $tmp>$CONF["LimitNews"]) {
		$sql = "SELECT T_Stories.Heading,T_Stories.Rid,count(*) AS Cmts ";
		$sql .= "FROM T_Stories,T_Comments ";
		$sql .= "WHERE T_Comments.TopRid = T_Stories.Rid ";
		$sql .= "AND T_Stories.Verified = 'Y' ";
		$sql .= "GROUP BY T_Comments.TopRid ";
		$sql .= "ORDER BY Cmts desc ";
		$sql .= "LIMIT 5";
		$hot5_result = @mysql_query($sql,$db);
		$hot5_nrows = @mysql_num_rows($hot5_result);
		$cnt	= $cnt	+ $hot5_nrows;
	}

	if ($aln==$align) {
		$sql	= "SELECT * FROM T_Blocks";
		$sql	.= " WHERE Display = 'f'";
		$sql	.= " ORDER BY Heading ASC";
		$features_result	= @mysql_query($sql,$db);
		$features_nrows	= @mysql_num_rows($features_result);
	}

	if ($cnt==0 && $align!=$aln) { return ""; }

	$width	= $align=="r" ? $LAYOUT["RightBlocksWidth"] : $LAYOUT["LeftBlocksWidth"];

	print "<!-- begin blocks column ($align) //-->\n";
	if ($align=="r") {
		print "<td><img\n";
		print "\tsrc\t= \"" . $G_URL . "/images.d/speck.gif\"\n";
		print "\theight\t= 1\n";
		print "\twidth\t= \"" . $LAYOUT["AreaPadding"] . "\"></td>\n";
	}
	print "<td\tvalign\t= top\n";
	print "\twidth\t= \"" . $width . "\">\n";

	# admin block
	if (F_isAdmin() && $align==$aln) {

		$VAR["Heading"] = "Admin Menu";
		$VAR["Content"] = "";
		$num	= F_Count("T_Stories","Verified","N");
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/moderate.php\">Moderation</a> ($num new)</li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/contrib.php\">Add Story</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/config-site.php\">Site Configuration</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/config-story.php\">Story Control</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/config-extend.php\">Extended Control</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/layout.php\">Layouts</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/blocks.php\">Blocks / Pages</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/topics.php\">Topics</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/polls.php\">Polls</a></li>";
    		$VAR["Content"]	.= "<li><a href=\"$G_URL/files.php\">Add Files/Tools</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/logs.php\">View Logs</a></li>";
		$VAR["Content"]	.= "<li><a href=\"$G_URL/admin/logout.php\">Log Out Admin</a></li>";
		$VAR["Content"] .= "<br>";
		F_drawBlock($VAR);
	}

	# topics block
	if ($CONF["Topics"]>0 && $align==$aln) {
		$VAR["Heading"]	= _TOPICS;
		$VAR["Content"]	= "";
		$sql	= "SELECT * FROM T_Topics";
		if ($CONF["TopicSort"]=="asc") {
			$sql	.= " ORDER BY Topic ASC";
		} elseif ($CONF["TopicSort"]=="desc") {
			$sql	.= " ORDER BY Topic DESC";
		}
		$topics_result	= @mysql_query($sql,$db);
		$topics_nrows	= @mysql_num_rows($topics_result);
		$numtops = $topics_nrows; 
		if ($CONF["TopicSort"]=="brief") { 
			if ($topics_nrows > 4) { 
				$topics_nrows = 5; 
			} 
		}
		for ($i=0;$i<$topics_nrows;$i++) {
			$A	= mysql_fetch_array($topics_result);
			$foo	= $A["Topic"];
			$cmt	= F_count("T_Stories","Topic",$A["Rid"],"Verified","Y");
			# change by b.dobyns don't display if count is zero
			if ($cmt > 0) {
			    if ($A["Rid"]==$topic) {
				$VAR["Content"]	.= sprintf("<li><em>%s</em> (%s)</li>\n",
					$foo,$cmt);
			    } else {
				$VAR["Content"]	.= sprintf("<li><a href=\"$G_URL/stories.php?topic=%s\">%s</a> (%s)</li>\n",
					$A["Rid"],$foo,$cmt);
			    }
			}
		}
		if ($numtops>$topics_nrows) {
			$VAR["Content"]	.= "<br><div align=right><a href=\"$G_URL/topics.php\">" . _MORE . " &gt;&gt;</a> </div>";
		}
		$VAR["Content"] .= "<br>";
		F_drawBlock($VAR);
	}

	# story related links
	if (isset($mainpage) && $mainpage==1 && !empty($story) && $align==$aln) {
		$VAR["Heading"]	= _MORELINKS;
		$VAR["Content"]	= F_extractLinks($story);
		if ($CONF["PrintStory"]>0) {
			$VAR["Content"]	.= "<li><a target=_blank href=\"$G_URL/print.php?story=$story\">" . _PRINTFRIEND . "</a></li>";
		}
		if ($CONF["MailFriend"]>0) {
			$VAR["Content"]	.= "<li><a href=\"$G_URL/friend.php?story=$story\">" . _MAILTOFRIEND . "</a></li>";
		}
		if (!empty($VAR["Content"])) {
			$VAR["Content"] .= "<br>";
			F_drawBlock($VAR);
		}
	}


	# features block
	if ($align==$aln && $features_nrows>0) {
		$VAR["Heading"]	= _FEATURES;
		$VAR["Content"]	= "";
		for ($i=0;$i<$features_nrows;$i++) {
			$A	= mysql_fetch_array($features_result);
			$VAR["Content"]	.= sprintf("<li><a href=\"%s/pages.php?page=%s\">%s</a></li>\n",
					$G_URL,urlencode($A["Heading"]),$A["Heading"]);
		}
		$VAR["Content"] .= "<br>";
		F_drawBlock($VAR);
	}


	# user-defined blocks
	for ($i=0;$i<$blocks_nrows;$i++) {
		$A	= mysql_fetch_array($blocks_result);
		if (empty($mainpage) && $A["ShowMain"]==1) {
		} else {
			$A["Content"] .= "<br>\n" . F_admin("T_Blocks",$A["Rid"],"stories.php");
			F_drawBlock($A);
		}
	}

	# top 5 stories block
	if ($CONF["Top5"]>0 && $top5_nrows>0) { 
		$VAR["Heading"]	= _TOP5;
		$VAR["Content"] = "";
		for ($i=0;$i<$top5_nrows;$i++) { 
			$A = mysql_fetch_array($top5_result); 
			if (!$A["Test"]) { $A["Cmts"] = 0; }
			$VAR["Content"]	.= "<li><a href=\"" . F_Story($A["Rid"]) . "\">" . stripslashes($A["Heading"]) . "</a> (" . $A["Cmts"] . ")</li>\n";
		}
		$VAR["Content"] .= "<br>";
		F_drawBlock($VAR);
	}

	# hot 5 stories block
	if ($CONF["Hot5"]>0 && $hot5_nrows>0) { 
		$VAR["Heading"]	= _HOT5;
		$VAR["Content"] = "";
		for ($i=0;$i<$hot5_nrows;$i++) { 
			$A = mysql_fetch_array($hot5_result); 
			$VAR["Content"]	.= "<li><a href=\"" . F_Story($A["Rid"]) . "\">" . stripslashes($A["Heading"]) . "</a> (" . $A["Cmts"] . ")</li>\n";
		}
		$VAR["Content"] .= "<br>";
		F_drawBlock($VAR);
	}

	# older stories block
	if ($CONF["Older"]>0 && $older_nrows>0) {
		$VAR["Heading"]	= _OLDER;
		$D = "noday";
		$VAR["Content"] = "";
		for ($i=0;$i<$older_nrows;$i++) {
			$A = mysql_fetch_array($older_result);
			if ($D != F_dateFormat($A["Repostamp"],"%A")) {
				$D	= F_dateFormat($A["Repostamp"],"%A");
				$VAR["Content"]	.= "<b>" . $D . "</b>\n"; 
			}
			$VAR["Content"]	.= "<li><a href=\"" . F_Story($A["Rid"]) . "\">" . stripslashes($A["Heading"]) . "</a> (" . F_count("T_Comments","TopRid",$A["Rid"]) . ")</li>\n";
		}
		$VAR["Content"] .= "<br>";
		F_drawBlock($VAR);
	}

	# polls block
	if ($polls_nrows > 0 && basename($PHP_SELF)!="pollbooth.php") {
		for ($i=1; $i<=$polls_nrows; $i++) {
			$A = mysql_fetch_array($polls_result);
			$rid = $A["Rid"];
			if (empty($HTTP_COOKIE_VARS["$rid"])) {
				F_pollVote($rid,"block");
			} else {
				F_pollResultsBlock($rid);
			}
		}
	}

	print "<img\tsrc\t= \"" . $G_URL . "/images.d/speck.gif\"\n";
	print "\twidth\t= \"" . $width . "\"\n";
	print "\theight\t= \"1\"></td>\n";
	if ($align=="l") {
		print "<td><img\n";
		print "\tsrc\t= \"" . $G_URL . "/images.d/speck.gif\"\n";
		print "\theight\t= 1\n";
		print "\twidth\t= \"" . $LAYOUT["AreaPadding"] . "\"></td>\n";
	}
	print "<!-- end blocks column ($align) //-->\n";

}

?>
