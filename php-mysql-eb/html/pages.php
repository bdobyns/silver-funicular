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

	$sql	= "SELECT *,UNIX_TIMESTAMP(Birthstamp) AS Birthstamp,";
	$sql	.= "UNIX_TIMESTAMP(Timestamp) AS Timestamp from T_Blocks";
	$sql	.= " WHERE Heading = '" . urldecode($page). "' LIMIT 1";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	$A	= mysql_fetch_array($result);
	if ($nrows==0) {
		$VAR["Heading"] = "Page not found.";
		$VAR["Content"] = "<br>The page you have requested is not found. ";
		$VAR["Content"] .= "If this error persists, please notify the ";
		$VAR["Content"] .= "administrator of this site.<br><br>";
		F_drawMain($VAR);
	} else {
		F_uphits("T_Blocks",$A["Rid"]);
		$A["Content"]	= F_getContent($A);
		$A["Content"]	.= "<br>\n" . F_admin("T_Blocks",$A["Rid"],"pages.php?page=" . urlencode($page));
		F_drawMain($A);

		if ($A["PageComments"]>0) {
			F_doComments($A["Rid"],$A["Rid"],"pages.php?page=".urlencode($page));
			print "<br>";
			F_PostComment($A["Rid"],$A["Rid"],"pages.php?page=".urlencode($page));
		}
	}

	include("./include/footer.inc.php");
?>
