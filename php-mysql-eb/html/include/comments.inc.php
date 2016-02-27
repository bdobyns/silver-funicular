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

function F_doComments($rid,$toprid,$where,$reply=0) {
	global	$CONF,$LAYOUT,$db,$G_URL;
	if ($CONF["Comments"]>0) {
		/*== followed by user comments ==*/
		$sql	= "SELECT * FROM T_Comments ";
		$sql	.= "WHERE ParentRid = '$rid' ";
		$sql	.= sprintf("ORDER By Birthstamp %s",$CONF["CommentSort"]);
		$result	= mysql_query($sql,$db);
		$nrows	= mysql_num_rows($result);

		if ($nrows>0) {
#			print "<p>\n";
			for ($i=0;$i<$nrows;$i++) {	
				$A	= mysql_fetch_array($result);
				$A["Content"] = F_parseContent($A["Content"],$CONF["ParseLevelCmt"]);
				$A["Content"] .= F_admin("T_Comments",$A["Rid"],urlencode($where));
				if ($reply>0) {
					$tmp	= $reply * 25;
					print "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\">\n";
					print "<tr><td width=" . $tmp . "><img\n";
					print "\tsrc\t= \"" . $G_URL . "/images.d/speck.gif\" height=1\n";
					print "\twidth\t= \"" . $tmp . "\"></td>\n<td>\n";
				}
				F_drawComment($A,$where);
				if ($reply>0) {
					print "</td></tr></table>\n";
				}
				if ($CONF["Comments"]==2) {
					$sql	= "SELECT * from T_Comments ";
					$sql	.= "WHERE ParentRid = '" . $A["Rid"] . "' ";
					$sql    .= "AND TopRid = '" . $toprid . "' ";
					$sql	.= sprintf("ORDER By Birthstamp %s",$CONF["CommentSort"]);
					$tresult	= mysql_query($sql,$db);
					$tnrows	= mysql_num_rows($tresult);
					if ($tnrows>0) {
						$tmp	= $reply + 1;
						F_doComments($A["Rid"],$toprid,$where,$tmp);
					}
				}
			}
		} else {
			F_notice(_NOCOMMENTS);
		}
	}
}


function F_postComment($parentrid,$toprid,$where) {
	global	$CONF,$HTTP_COOKIE_VARS,$C_USER,$G_URL;
	if ($CONF["Comments"]>0) {
		$tmp	= $HTTP_COOKIE_VARS[$C_USER];
		$USER	= explode("|",rot13($tmp));
		$VAR["Heading"] = "<a \tname\t= \"comments\"> </a>";
		$VAR["Heading"] .= _POSTCOMMENT;
		$VAR["Content"] = "<table\n";
		$VAR["Content"] .= "\tcellspacing\t= 0\n";
		$VAR["Content"] .= "\tcellpadding\t= 0\n";
		$VAR["Content"] .= "\tborder\t= 0>\n";
		$VAR["Content"] .= "<form\taction\t= \"$G_URL/submit.php\"\n";
		$VAR["Content"] .= "\tmethod\t= POST\n";
		$VAR["Content"] .= "\tname\t= \"Com\"\n";
		$VAR["Content"] .= "\tonsubmit= \"return validateComment()\">\n";
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td>" . _NAME . ":</td>\n";
		$VAR["Content"] .= "<td><input\n";
		$VAR["Content"] .= "\ttype\t= text\n";
		$VAR["Content"] .= "\tname\t= Author\n";
		$VAR["Content"] .= "size\t= 32\n";
		$VAR["Content"] .= "\tvalue\t= \"$USER[0]\"\n";
		$VAR["Content"] .= "\tmaxlength\t= 32></td>\n";
		$VAR["Content"] .= "</tr>\n";
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td>" . _EMAIL . ":</td>\n";
		$VAR["Content"] .= "<td>\n";
		$VAR["Content"] .= "<input\ttype\t= text\n";
		$VAR["Content"] .= "\tname\t= AuthorEmail\n";
		$VAR["Content"] .= "\tsize\t= 32\n";
		$VAR["Content"] .= "\tvalue\t= \"$USER[1]\"\n";
		$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
		$VAR["Content"] .= "</tr>\n";
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td>" . _URL . "</td>\n";
		$VAR["Content"] .= "<td>\n";
		$VAR["Content"] .= "<input\ttype\t= text\n";
		$VAR["Content"] .= "\tname\t= AuthorURL\n";
		$VAR["Content"] .= "\tsize\t= 32\n";
		$VAR["Content"] .= "\tvalue\t= \"$USER[2]\"\n";
		$VAR["Content"] .= "\tmaxlength\t= 96></td>\n";
		$VAR["Content"] .= "</tr>\n";
		if ($CONF["SaveInfo"]>0) {
			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>&nbsp;</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= checkbox\n";
			$VAR["Content"] .= "\tname\t= save>\n";
			$VAR["Content"] .= _SAVEINFO . "</td>\n";
			$VAR["Content"] .= "</tr>\n";
		}
		if ($CONF["AllowAnon"]>0) {
			$VAR["Content"] .= "<tr>\n";
			$VAR["Content"] .= "<td>&nbsp;</td>\n";
			$VAR["Content"] .= "<td>\n";
			$VAR["Content"] .= "<input\ttype\t= checkbox\n";
			$VAR["Content"] .= "name\t= anon>\n";
			$VAR["Content"] .= _ANONYMOUS . "</td>\n";
			$VAR["Content"] .= "</tr>\n";
		}
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td colspan=2>" . ucfirst(_COMMENT) . ":<br>\n";
		$VAR["Content"] .= "<textarea\n";
		$VAR["Content"] .= "\tname\t= Content\n";
		$VAR["Content"] .= "\twrap\t= virtual\n";
		$VAR["Content"] .= "\trows\t= 10\n";
		$VAR["Content"] .= "\tcols\t= 50></textarea>\n";
		$VAR["Content"] .= "<br><small>";
		$VAR["Content"]	.= F_ifHTML(1); /* tell function this is a comment */
		$VAR["Content"] .= "</small><br><small>";
		$VAR["Content"]	.= _NOPUBLIC;
		$VAR["Content"]	.= "</small></td>\n";
		$VAR["Content"] .= "</tr>\n";
		$VAR["Content"] .= "<tr>\n";
		$VAR["Content"] .= "<td\tcolspan\t= 2\n";
		$VAR["Content"] .= "\talign\t= center>\n";
		$VAR["Content"] .= "<input\ttype\t= hidden\n";
		$VAR["Content"] .= "\tname\t= what\n";
		$VAR["Content"] .= "\tvalue\t= comment>\n";
		$VAR["Content"] .= "<input\ttype\t= hidden\n";
		$VAR["Content"] .= "\tname\t= ParentRid\n";
		$VAR["Content"] .= "\tvalue\t= \"$parentrid\">\n";
		$VAR["Content"] .= "<input\ttype\t= hidden\n";
		$VAR["Content"] .= "\tname\t= TopRid\n";
		$VAR["Content"] .= "\tvalue\t= \"$toprid\">\n";
		$VAR["Content"] .= "<input\ttype\t= hidden\n";
		$VAR["Content"] .= "\tname\t= where\n";
		$VAR["Content"] .= "\tvalue\t= \"$where\">\n";
		$VAR["Content"] .= "<input\ttype\t= submit\n";
		$VAR["Content"] .= "\tvalue\t= \"" . F_submit() . "\">\n";
		$VAR["Content"] .= "</td>\n</tr>\n";
		$VAR["Content"] .= "</form>\n";
		$VAR["Content"] .= "</table>\n";
		F_drawMain($VAR);
	}
}

?>
