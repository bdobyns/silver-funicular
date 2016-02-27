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

	$sql	= "SELECT * from T_Topics";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	$ncols	= 3;
	$cols	= $nrows / $ncols;

	$VAR["Heading"]	= _TOPICS;
	$VAR["Content"]	= "<table cellspacing=4 cellpadding=0 border=0 width=\"100%\">\n";
	for ($i=0;$i<$cols;$i++) {
	    $VAR["Content"]	.= "<tr>\n";
	    for ($j=0;$j<$ncols;$j++) {
	        $A  = mysql_fetch_array($result);
	        $s  = "\n<td\talign\t=center valign=bottom>\n";
			if (!empty($A["Rid"])) {
				$s	.= "<a href=\"$G_URL/stories.php?topic=" . $A["Rid"] . "\">";
				if (!empty($A["ImgURL"])) {
					$s   .= "<img\tsrc\t= \"" . $A["ImgURL"] . "\"\n";
					$s   .= "\talt=\"" . $A["AltTag"] . "\" border=0>\n";
				}
				$s	.= "<br>" . $A["Topic"] . "</a>\n";
			} else {
				$s	.= "&nbsp;";
			}
	        $s  .= "</td>\n";
	        $VAR["Content"]	.= $s;
	    }
	    $VAR["Content"]	.= "</tr>\n";
	}
	$VAR["Content"]	.= "</table>\n";
	F_drawMain($VAR);


include("./include/footer.inc.php");
?>
