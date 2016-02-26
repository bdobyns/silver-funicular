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

/*== site stats ==*/

$VAR["Heading"] = _SITESTATS;
$tmp = F_count("T_Stories");

$VAR["Content"] = "<table cellpadding=0 cellspacing=1 border=0 width=\"99%\">\n";
$VAR["Content"] .= "<tr><td>" . _TOTALSTORIES . "</td><td align=right>" . $tmp . "</td></tr>\n";

$sql	= "SELECT floor(avg(Hits)) FROM T_Stories ";
$sql	.= "WHERE Verified = 'Y'";
$result	= @mysql_query($sql,$db);
$VAR["Content"]	.= "<tr><td>" . _AVGHITS . "</td><td align=right>" . mysql_result($result,0) . "</td></tr>\n";

if ($CONF["Comments"] > 0) { 
	$tmp = F_count("T_Comments");
	$VAR["Content"] .= "<tr><td>" . _TOTALCOMMENTS . "</td><td align=right>" . $tmp . "</td></tr>\n"; 
}
if (F_count("T_PollQuestions") > 0) { 
	$tmp = F_count("T_PollQuestions");
	$VAR["Content"] .= "<tr><td>" . _TOTALPOLLS . "</td><td align=right>" . $tmp . "</td></tr>\n"; 
}
if ($CONF["Links"] > 0) { 
	$tmp = F_count("T_Links");
	$VAR["Content"] .= "<tr><td>" . _TOTALLINKS . "</td><td align=right>" . $tmp . "</td></tr>\n"; 
}
$VAR["Content"] .= "</table>\n";
F_drawMain($VAR);


/*== story stats ==*/

$sql	= "SELECT Rid,Heading,Hits from T_Stories ";
$sql	.= "WHERE Hits > 0 ";
$sql	.= "ORDER BY Hits desc LIMIT 10";
$result	= mysql_query($sql,$db);
$nrows  = mysql_num_rows($result);

$VAR["Heading"] = _TOPSTORIES;
if ($nrows>0) {
	$VAR["Content"]	= "<table cellpadding=0 cellspacing=1 border=0 width=\"99%\">\n";
	for ($i=0;$i<$nrows;$i++) {
		$A      = mysql_fetch_array($result);
		$VAR["Content"]	.= "<tr>\n";
		$VAR["Content"]	.= "\t<td><a href=\"" . F_Story($A["Rid"]) . "\">" . $A["Heading"] . "</a> (" . F_count("T_Comments","TopRid",$A["Rid"]) . ")</td>\n";
		$VAR["Content"]	.= "\t<td align=right>" . $A["Hits"] . " " . _HITS . "</td>\n";
		$VAR["Content"]	.= "</tr>\n";
		}
        $VAR["Content"]	.= "</table>\n";
} else {
	$VAR["Content"] = _NOSTORIES;
}
F_drawMain($VAR);


/*== poll stats ==*/
$sql	= "SELECT Rid,Question,Voters from T_PollQuestions ";
$sql	.= "WHERE Voters > 0 ";
$sql	.= "ORDER BY Voters desc LIMIT 10";
$result	= mysql_query($sql,$db);
$nrows  = mysql_num_rows($result);

$VAR["Heading"] = _TOPPOLLS;
if ($nrows>0) {
	$VAR["Content"]	= "<table cellpadding=0 cellspacing=1 border=0 width=\"99%\">\n";
	for ($i=0;$i<$nrows;$i++) {
		$A      = mysql_fetch_array($result);
		$VAR["Content"]	.= "<tr>\n";
		$VAR["Content"]	.= "\t<td><a href=\"$G_URL/pollbooth.php?poll=" . $A["Rid"] . "&aid=-1\">" . $A["Question"] . "</a> (" . F_count("T_Comments","TopRid",$A["Rid"]) . ")</td>\n";
		$VAR["Content"]	.= "\t<td align=right>" . $A["Voters"] . " " . _VOTES . "</td>\n";
		$VAR["Content"]	.= "</tr>\n";
	}
	$VAR["Content"]	.= "</table>\n";
} else {
	$VAR["Content"] = _NOPOLLS;
}
F_drawMain($VAR);


/*== pages stats ==*/
$sql	= "SELECT Rid,Heading,Hits from T_Blocks ";
$sql	.= "WHERE Hits > 0 ";
$sql	.= "AND (Display = 'f' OR Display = 'p') ";
$sql	.= "ORDER BY Hits desc LIMIT 10";
$result	= mysql_query($sql,$db);
$nrows  = mysql_num_rows($result);
$VAR["Heading"] = _TOPPAGES;
if ($nrows>0) {
	$VAR["Content"]	= "<table cellpadding=0 cellspacing=1 border=0 width=\"99%\">\n";
	for ($i=0;$i<$nrows;$i++) {
		$A      = mysql_fetch_array($result);
		$VAR["Content"]	.= "<tr>\n";
		$VAR["Content"]	.= "\t<td>";
		$VAR["Content"]	.= sprintf("<a href = \"$G_URL/pages.php?page=%s\">%s</a>",
			urlencode(stripslashes($A["Heading"])),
			stripslashes($A["Heading"]));
		$VAR["Content"]	.= "</td>\n";
		$VAR["Content"]	.= "<td><small>" . $A["Url"] . "</small></td>\n";
		$VAR["Content"]	.= "<td align=right>" . $A["Hits"] . " " . _HITS . "</td>\n";
		$VAR["Content"]	.= "</tr>\n";
		}
        $VAR["Content"]	.= "</table>\n";
} else {
	$VAR["Content"] = _NOPAGES;
}
F_drawMain($VAR);


/*== link stats ==*/
if ($CONF["Links"] > 0) {
$sql	= "SELECT Rid,Url,Name,Hits from T_Links ";
$sql	.= "WHERE Hits > 0 ";
$sql	.= "ORDER BY Hits desc LIMIT 10";
$result	= mysql_query($sql,$db);
$nrows  = mysql_num_rows($result);
$VAR["Heading"] = _TOPLINKS;
if ($nrows>0) {
	$VAR["Content"]	= "<table cellpadding=0 cellspacing=1 border=0 width=\"99%\">\n";
	for ($i=0;$i<$nrows;$i++) {
		$A      = mysql_fetch_array($result);
		$VAR["Content"]	.= "<tr>\n";
		$VAR["Content"]	.= "\t<td>";
		$VAR["Content"]	.= sprintf("<a target= \"_blank\" href = \"$G_URL/portal.php?url=%s&what=T_Links&rid=%s\">%s</a> - <small>%s</small>",
			urlencode($A["Url"]),
			$A["Rid"],
			stripslashes($A["Name"]),
			$A["Url"]);
		$VAR["Content"]	.= "</td>\n";
		$VAR["Content"]	.= "<td align=right>" . $A["Hits"] . " " . _HITS . "</td>\n";
		$VAR["Content"]	.= "</tr>\n";
		}
        $VAR["Content"]	.= "</table>\n";
} else {
	$VAR["Content"] = _NOLINKS;
}
F_drawMain($VAR);
}

include("./include/footer.inc.php");

?>
