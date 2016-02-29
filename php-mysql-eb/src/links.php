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

# Original links code written by Twyst (http://anime-central.net)
# Modified for use with phpWebLog by Jason Hines
# Thanks Twyst!

include("./include/common.inc.php");
include("./include/header.inc.php");

function do_main($node="",$start=0) {
	$A["Heading"] = ucwords(_LINKS);
	$A["Content"] = F_nodewalk($node,0);
#	$A["Content"] .= F_showalphanodes($node);
	$A["Content"] .= F_shownodes($node);
	$A["Content"] .= "<br>\n";
	$A["Content"] .= F_showitems($node,$start);
	$A["Content"] .= F_suggest($node);
	F_drawMain($A);

	if(F_isAdmin()) { 
		do_admin();
	}
}

function F_nodewalk($startnode="",$linklast=0) { 
	global $PHP_SELF,$db;
	$currnode = $startnode;
	while(!empty($currnode)) {
		$sql	= "SELECT * from T_LinkCats ";
		$sql	.= "WHERE Rid='$currnode' ";
		$sql	.= "AND Verified='1'";
		$result=mysql_query($sql,$db);
		list($id,$name,$currnode) = mysql_fetch_row($result);
		if (!empty($name)) {
			if($id != $startnode) {
				$temp = " &gt; <a href=\"$PHP_SELF?node=$id\">$name</a>" . $output;
				$output = $temp;
			} else {
				if($linklast == 1) {
					$temp = " &gt; <a href=\"$PHP_SELF?node=$id\">$name</a>" . $output;
					$output = $temp;
				} else {
					$temp = " &gt; $name " . $output;
					$output = $temp;
				}
			}
		}
	}
	$temp = "<a href=\"$PHP_SELF\">Top</a>" . $output;
	return $temp;
}

function F_shownodes($node="") { 
	global $db,$PHP_SELF;
	if ((empty($node)) || ($node=="null")) { 
		$search = "ParentRid='NULL'"; 
	} else {
		$search = "ParentRid='$node'"; 
	}
	$output = "<table cellpadding=2 cellspacing=0 border=0 width=\"100%\">";
	$sql	= "SELECT Rid,Name FROM T_LinkCats ";
	$sql	.= "WHERE $search and Verified='1' ";
	$sql	.= "AND length(Name) > 1 ORDER BY Name";
	$result = mysql_query($sql,$db);
	$numrows = mysql_num_rows($result);
	$half = $numrows / 2;
	$count = 0;
	$output .="<tr><td valign=top>\n";
	while($count < $half) { 
		list($id,$name) = mysql_fetch_row($result);
		$cnt	= F_count("T_Links","CatRid",$id,"Verified","1");
		$output .="<li><a href=\"$PHP_SELF?node=$id\">$name</a> ($cnt)" . 
			F_admin("T_LinkCats",$id,"links.php?node=$node") . "</li>\n";
		$count++;
	}
	$output .="</td><td valign=top>\n";
	while(list($id,$name) = mysql_fetch_row($result)) { 
		$cnt    = F_count("T_Links","CatRid",$id,"Verified","1");
		$output .="<li><a href=\"$PHP_SELF?node=$id\">$name</a> ($cnt)" . 
			F_admin("T_LinkCats",$id,"links.php?node=$node") . "</li>\n";
	}
	$output .="</td></tr></table>";
	return $output;
}

function F_showalphanodes($node="") {
	global $db,$PHP_SELF;
	$sql	= "SELECT Rid,Name FROM T_LinkCats ";
	if ((empty($node)) || ($node=="null")) { 
		$sql	.= "WHERE ParentRid = 'NULL'"; 
	} else { 
		$sql	.= "WHERE ParentRid = '$node'"; 
	}
	$sql	.= " AND Verified='1' AND length(Name) = 1 ";
	$sql	.= "ORDER BY Name";
echo $sql;
	$result = mysql_query($sql,$db);
	if (mysql_num_rows($result) > 0) { 
		$s	= "<table border=1 width=\"100%\">\n";
		$s	.= "<tr><td colspan=2 align=\"center\">| ";
		while(list($id,$name) = mysql_fetch_row($result)) { 
			$s .= " <a href=\"$PHP_SELF?node=$id\">$name</a> |\n";
		}
		$output .= "</td></tr></table>\n";
	}
	return $s;
}

function F_showitems($node="",$start="") {
	global $db,$PHP_SELF,$G_URL;
	if ((empty($node)) || ($node=="null"))  { 
		$search = "CatRid = 'NULL'"; 
	} else { 
		$search = "CatRid = '$node'"; 
	}
	$output = "<table width=\"100%\" cellspacing=\"2\">";
	$sql	= "SELECT Rid from T_Links ";
	$sql	.= "WHERE $search and Verified='1'";
	$temp = mysql_query($sql,$db);
	$total = mysql_num_rows($temp);
	if ($total > 0) { 
		if ($start > 0) { 
			$pr = $start - 26;
			if ($pr < 0) { $pr = 0; }
			$prevlink = "<a href=\"$PHP_SELF?node=$node&start=$pr\">[&lt;&lt; " . _PREV . "]</a>";
		}
		if (empty($start)) { $start = 0; }
		$end = $start + 25;
		if ($end > $total) { $end = $total; }
		if ($end < $total) { 
			$nx = $end + 1;
			$nextlink = "<a href=\"$PHP_SELF?node=$node&start=$nx\">[" . _NEXT . " &gt;&gt;]</a>";
		}
		$tmp	= $start+1;
		$display = _SHOWING . " <b>$tmp</b> - <b>$end</b> of <b>$total</b>";
		$sql	= "SELECT Rid,Url,Name,Description,SubmitDate ";
		$sql	.= "FROM T_Links ";
		$sql	.= "WHERE $search and Verified='1' ";
		$sql	.= "ORDER BY Name limit $start,25";
		$result = mysql_query($sql,$db);
		$ck = 0;
		while (list($id,$url,$name,$desc,$sdate) = mysql_fetch_row($result)) { 
		        $sdate=substr($sdate,0,10);
			$ck++;
			if(($ck % 2) < 1) { 
				$output .= "<tr><td>"; 
			} else { 
				$output .="<tr><td>"; 
			}
			$tmp	= urlencode($url);
			$output	.= "<a href=\"$G_URL/portal.php?url=$tmp&what=T_Links&rid=$id\" target=\"_blank\">$name</a> ($sdate)\n";
		        if ( ! stristr($url, $G_URL) ) $output .= " - <small>$url</small>\n";
			if (!empty($desc)) {
				$output	.= "<br>&nbsp;&nbsp;&nbsp;<small>$desc</small>";
			}

			if(F_isAdmin()) { 
				$output .= "<tr><td><a href=\"$PHP_SELF?unapprove=$id\"><img src=\"$G_URL/images.d/unok.gif\" border=0></a>&nbsp;<a href=\"$PHP_SELF?editlink=$id\"><img src=\"$G_URL/images.d/edit.gif\" border=0></a></td></tr>"; 
			}
			$output .="</td></tr>\n";
		}
		$output .= "</table><center>$prevlink $display $nextlink</center>";
	} else { 
		$output = _NOLINKS;
	}
	return $output;
}

function F_suggest($node="") {
	global $PHP_SELF,$CONF;
	if ($node == "") { $node = "null"; }
	$output	= "<table width=\"100%\">\n";
	$output	.= "<tr><form action=\"$PHP_SELF\" method=POST>\n<td align=\"center\">" . _FIND . " <input type=\"text\" name=\"search\" size=15></td></form></tr>\n";
	if ($CONF["Links"]==1 || F_isAdmin()) {
		$output	.= "<tr><td align=\"center\"><a href=\"$PHP_SELF?sugnode=$node\">" . _SUGCAT . "</a> | <a href=\"$PHP_SELF?suglink=$node\">" . _SUGLINK . "</a></td></tr>\n";
	}
	$output	.= "</table>";
	return $output;
}

function F_suggestnode($node="") { 
	global $db,$PHP_SELF;
	$output = F_nodewalk($node,1);
	$output .= "<table width=\"100%\">
	<form action=\"$PHP_SELF\" name=\"SUGNODE\" method=POST onsubmit=\"return validateSugNode();\">
	<tr><td width=\"15%\">" . _CATEGORY . ":</td><td><input type=\"text\" name=\"newnode\" size=\"40\"><input type=\"hidden\" name=\"parent\" value=\"$node\"></td></tr>
	<tr><td></td><td><input type=\"submit\" name=\"addnode\" value=\"" . F_submit() . "\"></td></tr>
	</form>
	</table>";
	return $output;
}
function editlink($node="") {
	global $db,$PHP_SELF;
	$sql	= "SELECT * from T_Links ";
	$sql	.= "WHERE Rid='$node'";
	$result = mysql_query($sql,$db);
	if (mysql_num_rows($result) < 1) { 
		$output = "No such ID"; 
	} else {
		list($linkid,$catid,$url,$linkname,$desc,$approved,$sname,$semail,$sdate) = mysql_fetch_row($result);
		$output = F_nodewalk($catid,1);
		$output .= "<table width=\"100%\" border=0>\n";
		$output	.= "<form action=\"$PHP_SELF\" method=POST>\n";
		$output	.= "<tr><td width=\"15%\">" . _SITENAME . ":</td><td><input type=\"text\" name=\"linkname\" size=\"40\" value=\"$linkname\">\n";
		$output	.= "<input type=\"hidden\" name=\"linkid\" value=\"$linkid\"></td></tr>\n";
		$output	.= "<tr><td width=\"15%\">" . _DESCRIPTION . ":</td><td><input type=\"text\" name=\"description\" size=\"60\" value=\"$desc\"></td></tr>\n";
		$output	.= "<tr><td width=\"15%\">" . _URL . ":</td><td><input type=\"text\" name=\"url\" size=\"60\" value=\"$url\"></td></tr>\n";
		$output	.= "<tr><td width=\"15%\">" . _CATEGORY . ":</td><td><select name=\"category\">\n";
		$output	.= F_listCats($catid);
		$output	.= "</select></td></tr>\n";
		$output	.= "<tr><td width=\"15%\">" . _NAME . ":</td><td><input type=\"text\" name=\"subname\" size=\"60\" value=\"$sname\"></td></tr>\n";
		$output	.= "<tr><td width=\"15%\">" . _EMAIL . ":</td><td><input type=\"text\" name=\"subemail\" size=\"60\" value=\"$semail\"></td></tr>\n";
		$output	.= "<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"updatelink\" value=\"" . F_submit() . "\"></td></tr>\n";
		$output	.= "</form>\n</table>";
	}
	return $output;
}

function F_suggestlink($node="") {
	global $db,$PHP_SELF,$C_USER,$HTTP_COOKIE_VARS;
	$tmp    = $HTTP_COOKIE_VARS["$C_USER"];  
	$USER   = explode("|",rot13($tmp));
	$output = F_nodewalk($node,1);
	$output .= "<table width=\"100%\">
	<form action=\"$PHP_SELF\" method=POST name=\"Link\" onsubmit=\"return validateLink();\">
	<tr><td width=\"15%\">" . _SITENAME . ":</td><td><input type=\"text\" name=\"linkname\" size=\"40\" maxlength=64>
	<input type=\"hidden\" name=\"parent\" value=\"$node\"></td></tr>
	<tr><td width=\"15%\">" . _DESCRIPTION . ":</td><td><input type=\"text\" name=\"description\" size=\"40\" maxlength=255></td></tr>
	<tr><td width=\"15%\">" . _URL . ":</td><td><input type=\"text\" name=\"url\" size=\"40\" maxlength=255></td></tr>
	<tr><td width=\"15%\">" . _NAME . ":</td><td><input type=\"text\" name=\"subname\" value=\"" . $USER[0] . "\" size=\"40\"></td></tr>
	<tr><td width=\"15%\">" . _EMAIL . ":</td><td><input type=\"text\" name=\"subemail\" value=\"" . $USER[1] . "\" size=\"40\"></td></tr>
	<tr><td></td><td><input type=\"submit\" name=\"addlink\" value=\"" . F_submit() . "\"></td></tr>
	</form>
	</table>";
	return $output;
}

function do_search($keywords="",$start) {
	global $db,$PHP_SELF,$G_URL;
	$kw = urlencode($keywords);
	$sql	= "SELECT distinct Rid from T_Links ";
	$sql	.= "WHERE (Name like '%$keywords%' ";
	$sql	.= "or Description like '%$keywords%') ";
	$sql	.= "AND Verified='1'";
	$temp  = mysql_query($sql,$db);
	$total = mysql_num_rows($temp);
	if ($total > 0) {
		if($total > 1) { $rs = "s"; }
		$header = "$total result$rs found";
		$output = "<table width=\"100%\" cellspacing=\"2\">";
		if ($start > 0) { 
			$pr = $start - 26;
			if ($pr < 0) { $pr = 0; }
			$prevlink = "<a href=\"$PHP_SELF?search=$kw&start=$pr\">[&lt;&lt; " . _PREV . "]</a>";
		}
		if (empty($start)) { $start = 0; }
		$end = $start + 25;
		if ($end > $total) { $end = $total; }
		if ($end < $total) { 
			$nx = $end + 1;
			$nextlink = "<a href=\"$PHP_SELF?search=$kw&start=$nx\">[" . _NEXT . " &gt;&gt;]</a>";
		}
		$display = _SHOWING . " $start - $end";
		$sql	= "SELECT distinct CatRid,Rid,Url,Name,Description,SubmitDate ";
		$sql	.= "FROM T_Links ";
		$sql	.= "WHERE (Name like \"%$keywords%\" or ";
		$sql	.= "Description like \"%$keywords%\") ";
		$sql	.= "AND Verified='1' ";
		$sql	.= "ORDER BY Name limit $start,25";
		$result = mysql_query($sql,$db);
		$ck = 0;
		while (list($cat,$id,$url,$name,$desc,$sdate) = mysql_fetch_row($result)) { 
			$ck++;
			$node = F_nodewalk($cat,1);
			if(($ck % 2) < 1) { 
				$output .= "<tr><td bgcolor=\"#DDDDDD\">"; 
			} else { 
				$output .="<tr><td>"; 
			}
			$tmp	= urlencode($url);
			$output	.= "<a href=\"$G_URL/portal.php?url=$tmp&what=T_Links&rid=$id\" target=\"_blank\">$name</a> ($sdate)";
		        if ( ! stristr($url, $G_URL) ) $output .= " - <small>$url</small>\n";
			if (!empty($desc)) {
				$output	.= "<br>&nbsp;&nbsp;&nbsp;<small>$desc</small>";
			}
			$output	.= "<br>&nbsp;&nbsp;&nbsp;<small>[" . $node . "]</small>";
			if(F_isAdmin()) { $output .= "<tr><td><a href=\"$PHP_SELF?unapprove=$id\"><img src=\"$G_URL/images.d/unok.gif\" border=0></a>&nbsp;<a href=\"$PHP_SELF?editlink=$id\"><img src=\"$G_URL/images.d/edit.gif\" border=0></a></td></tr>"; }
		}
		$output .= "</table><center>$prevlink $display $nextlink</center>";
	} else { 
		$header = _SEARCHRESULTS;
		$output = _NOMATCHES;
	}
	$A["Heading"]=$header;
	$A["Content"]=$output;
	F_drawMain($A);
	$suggest = F_suggest("");
	$A["Heading"]="Search";
	$A["Content"]=$suggest;
	F_DrawMain($A);
}

function do_admin() {
	global $db,$PHP_SELF,$G_URL;
	$sql	= "SELECT * from T_LinkCats ";
	$sql	.= "WHERE Verified='0' ";
	$sql	.= "ORDER BY SubmitDate DESC";
    $result = mysql_query($sql,$db);
	if(mysql_num_rows($result) > 0) {
		$A["Heading"]="Submitted Nodes";
		$A["Content"]="<table width=\"100%\" border=0><tr><td valign=top>\n";
		while(list($CatID,$CatName,$CatParent) = mysql_fetch_row($result)) {
			$A["Content"] .= "$CatName - <small>(in " . F_nodewalk($CatParent,1) .")</small><br>\n";
			$A["Content"] .= "<a href=\"$PHP_SELF?approvenode=$CatID\"><img src=\"$G_URL/images.d/verify.gif\" border=0></a> | <a href=\"$PHP_SELF?delnode=$CatID\"><img src=\"$G_URL/images.d/kill.gif\" border=0></a><hr>";
		}
		$A["Content"] .= "</td></tr></table>";
		F_drawMain($A);
	}
	$sql	= "SELECT * from T_Links ";
	$sql	.= "WHERE Verified='0' ";
	$sql	.= "ORDER BY SubmitDate DESC";
	$result = mysql_query($sql,$db);
	if(mysql_num_rows($result) > 0) { 
		$A["Heading"]="Submitted Links";
		$A["Content"]="<table width=\"100%\"><tr><td>\n";
		while(list($id,$cat,$url,$name,$desc,$app,$sname,$semail,$sdate) = mysql_fetch_row($result)) {
			$A["Content"] .= "<a target=_blank href=\"$url\">$name</a> - <small>(in " . F_nodewalk($cat,1) .")</small><br>\n";
			$A["Content"] .= "<small>Description: $desc <br>URL: $url</small><br><small>Submitted by <a href=\"mailto:$semail\">$sname</a> - $sdate</small><br>";
			$A["Content"] .= "<a href=\"$PHP_SELF?approvelink=$id\"><img src=\"$G_URL/images.d/verify.gif\" border=0></a> | <a href=\"$PHP_SELF?dellink=$id\"><img src=\"$G_URL/images.d/kill.gif\" border=0></a><hr>";
		}
		$A["Content"] .= "</td></tr></table>";
		F_drawMain($A);
	}
}




if ($editlink) {
	$edit = editlink($editlink);
	$A["Heading"]="Editing Link";
	$A["Content"] = $edit;
	F_drawMain($A);

} else if ($updatelink) {
	$sql	= "UPDATE T_Links set ";
	$sql	.= "Name='$linkname',";
	$sql	.= "Url='$url',";
	$sql	.= "CatRid='$category',";
	$sql	.= "Description='$description',";
	$sql	.= "SubmitName='$subname',";
	$sql	.= "SubmitEmail='$subemail' ";
	$sql	.= "WHERE Rid='$linkid'";
	$result = mysql_query($sql,$db);
	F_notice("Link successfully changed.");
	do_main();

} else if ($unapprove) {
	$sql	= "UPDATE T_Links set Verified='0' ";
	$sql	.= "WHERE Rid='$unapprove'";
	$result = mysql_query($sql,$db);
	F_notice("Link successfully unapproved.");
	do_main();

} else if ($approvenode) { 
	$sql	= "UPDATE T_LinkCats SET Verified='1' ";
	$sql	.= "WHERE Rid='$approvenode'";
	$result=mysql_query($sql,$db);
	F_notice("Node successfully approved.");
	do_main();

} else if ($approvelink) { 
	$sql	= "UPDATE T_Links SET Verified='1' ";
	$sql	.= "WHERE Rid='$approvelink'";
	$result=mysql_query($sql,$db);
	F_notice("Link successfully approved.");
	do_main();

} else if ($delnode) {
	$sql	= "DELETE FROM T_LinkCats ";
	$sql	.= "WHERE Verified='0' ";
	$sql	.= "AND Rid='$delnode'";
	$result=mysql_query($sql,$db);
	F_notice("Node successfully deleted.");
	do_main();

} else if ($dellink) {
	$sql	= "DELETE FROM T_Links ";
	$sql	.= "WHERE Verified='0' ";
	$sql	.= "AND Rid='$dellink'";
	$result=mysql_query($sql,$db);
	F_notice("Link successfully deleted.");
	do_main();

} else if ($sugnode) {
	$A["Heading"] = _ADDSUG;
	$A["Content"] = F_suggestnode($sugnode);
	F_drawMain($A);

} else if ($suglink) { 
	$A["Heading"] = _ADDSUG;
	$A["Content"] = F_suggestlink($suglink);
	F_drawMain($A);

} else if ($addnode) { 
	if(F_isAdmin()) { $appr = 1; } else { $appr = 0; }        
	$sql	= "INSERT into T_LinkCats ";
	$sql	.= "(Rid,Name,ParentRid,Verified) values ";
	$sql	.= "('" . F_getRid() . "','$newnode','$parent','$appr')";
	$result = mysql_query($sql,$db);
	F_notice(_SUGTHANK);
	do_main($parent);

} else if ($addlink) { 
	if(F_isAdmin()) { $appr = 1; } else { $appr = 0; }
	$sql	= "INSERT into T_Links ";
	$sql	.= "(Rid,CatRid,Url,Name,Description,Verified,Hits,";
	$sql	.= "SubmitName,SubmitEmail,SubmitDate) values ";
	$sql	.= "('" . F_getRid() . "','$parent','$url','$linkname','$description',";
	$sql	.= "'$appr',0,'$subname','$subemail',now())";
	$result = mysql_query($sql,$db);
	F_notice(_SUGTHANK);
	do_main($parent);

} else if ($search) { 
	do_search($search,$start);

} else {
	do_main($node,$start);
}


include("./include/footer.inc.php");
?>
