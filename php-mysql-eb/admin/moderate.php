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


if (!empty($HTTP_POST_VARS["action"])) {
	if ($HTTP_POST_VARS["action"]=="Approve All") {
		$sql    = "UPDATE T_Stories ";
		$sql    .= "SET Verified = 'Y' ";
		$sql    .= "WHERE Verified = 'N'";
		$RET    = @mysql_query($sql,$db);
		F_logAccess("Verified all pending stories");
		if ($RET<1) {
		    F_error("Unable to update pending stories.");
		}
		F_notice("Verified all pending stories.");
	} elseif ($HTTP_POST_VARS["action"]=="Remove All") {
		$sql    = "DELETE FROM T_Stories ";
		$sql    .= "WHERE Verified = 'N' ";
		$RET    = @mysql_query($sql,$db);
		F_logAccess("Removed all pending stories");
		if ($RET<1) {
		    F_error("Unable to remove pending stories.");
		}
		F_notice("Removed all pending stories.");
	}
	if ($CONF["MailingList"]>0) {
	    F_mailtoList($item);
	}
	export_rdf();
} else {

	$sql	= "SELECT * FROM T_Stories";
	$sql	.= " WHERE Verified != 'Y'";
	$result	= mysql_query($sql,$db);
	$nrows	= (!$result) ? 0 : mysql_num_rows($result);

	if ($nrows>0) {

		$VAR["Heading"] = "Story Moderation";
		$VAR["Content"] = "
		<center>
		<form method=post>
		<input name=\"action\" type=submit value=\"Approve All\">
		<input name=\"action\" type=submit value=\"Remove All\" onclick= \"return getconfirm();\">
		</form>
		</center>
		";
		F_drawMain($VAR);


		for ($i=0;$i<$nrows;$i++) {
			$A	= mysql_fetch_array($result);
			F_drawStory($A,"admin/moderate.php");
		}
	} else {
		$VAR["Heading"] = "Nothing pending.";
		$VAR["Content"] = "<br>There are no new stories to verify.<br><br>";
		F_drawMain($VAR);
	}
}

include("../include/footer.inc.php");

?>
