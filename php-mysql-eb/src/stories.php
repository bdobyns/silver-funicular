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


if (!empty($story)) {

	/*== show full story ==*/
	F_uphits("T_Stories",$story);

	$sql	= "SELECT S.*, T.NoComments FROM T_Stories AS S, T_Topics AS T ";
	$sql	.= "WHERE S.Rid = '$story' ";
	$sql	.= "AND S.Topic = T.Rid ";
	if ($CONF["Moderation"]>0 && empty($HTTP_COOKIE_VARS["phpWebLog"])) {
		$sql	.= "AND S.Verified = 'Y'";
	}
	$result	= @mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);

	if ($nrows==0) {

		$VAR["Heading"] = _NOSTORY;
		$VAR["Content"] = "<br>" . _NOSTORY . "<br><br>";
		F_drawMain($VAR);

	} else {

		$A	= mysql_fetch_array($result);
		$tmp	= urlencode("stories.php?story=" . $A["Rid"]);
		F_drawStory($A,$tmp);

		if ( $A["NoComments"] != 1 ) {
			F_doComments($story,$story,"stories.php?story=" . $A["Rid"] ,0,$A["Heading"]);
			print "<p>\n";
		}
		if ( $A["NoComments"] == 0 ) {
			F_postComment($story,$story,"stories.php?story=" . $A["Rid"]);
		}
	}

} else {

	/*== determine pages ==*/
	if (empty($page)) { $page = 1; }
	$prev_page = $page - 1; 
	$next_page = $page + 1; 

	$page_start = ($CONF["LimitNews"] * $page) - $CONF["LimitNews"]; 

	/*== get story data ==*/
	$sql	= "SELECT S.*, T.NoComments FROM T_Stories AS S, T_Topics AS T WHERE ";

	if ($CONF["Moderation"]>0) {
		$sql	.= " S.Verified = 'Y' AND ";
	}
	if ($CONF["Topics"]>0 && !empty($topic)) {
		$sql	.= " S.Topic = '$topic' AND ";
	}
	$sql	.= " S.Topic = T.Rid AND ";
	$sql	.= " S.Rid > '0'";
	$sql	.= " ORDER BY S.Repostamp desc";
	$sql	.= sprintf(" LIMIT %s,%s",$page_start,$CONF["LimitNews"]);

	$result	= @mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	if ($nrows>0) {

		/*== show items ==*/
		for ($i=0;$i<$nrows;$i++) {
			$A	= mysql_fetch_array($result);
			F_drawSummary($A);
		}

		/*== determine number of pages (yes, this is messy) ==*/
		if (!empty($topic)) {
			$num_rows = F_count("T_Stories","Topic",$topic,"Verified","Y");
		} else {
			$num_rows = F_count("T_Stories","Verified","Y");
		}
		if ($num_rows <= $CONF["LimitNews"]) { 
			$num_pages = 1; 
		} elseif (($num_rows % $CONF["LimitNews"]) == 0) { 
			$num_pages = ($num_rows / $CONF["LimitNews"]); 
		} else { 
			$num_pages = ($num_rows / $CONF["LimitNews"]) + 1; 
		} 
		$num_pages = (int) $num_pages;

		/*== round start page to lowest decade ==*/
		$s_pages	= $page<10 ? 1 : floor($page / 10) * 10;

		/*== extend end page plus 9 ==*/
		$e_pages	= $page + 9;

		/*== only if not exceeding number of pages ==*/
		if ($e_pages>$num_pages) {
			$e_pages	= $num_pages;
		}

		/*== construct next/prev links ==*/
		$blah	= "";
		if ($prev_page)  {
			$blah .= "<a href = \"$G_URL/stories.php?page=$prev_page&topic=$topic\">[&lt;&lt; " . _PREV . "]</a> \n";
		}
		for ($i = $s_pages; $i <= $e_pages; $i++) { 
			if ($i != $page) { 
				$blah .= "<a href = \"$G_URL/stories.php?page=$i&topic=$topic\">$i</a> \n"; 
			} elseif ($i != 1) {
				$blah .= " $i "; 
			} elseif ($page != $num_pages) {
				$blah .= " 1 ";
			}
		} 
		if ($page != $num_pages) { 
			$blah .= "<a href = \"$G_URL/stories.php?page=$next_page&topic=$topic\">[" . _NEXT . " &gt;&gt;]</a> \n";
		}


		/*== show pages if any ==*/
		if (!empty($blah)) { F_notice("<center>" . $blah . "</center>\n"); }

	} else {

		$T	= F_getTopic($topic);
		$VAR["Heading"] = (empty($T)) ? _NOSTORY : $T["Topic"];
		if ($CONF["Topics"]>0 && !empty($topic)) {
			$tmp	= _EMPTYTOPIC;
		} else {
			$tmp	= _EMPTY;
		}
		$VAR["Content"] = $tmp . "<br><br>";
		F_drawMain($VAR);
	}
}

include("./include/footer.inc.php");
?>
