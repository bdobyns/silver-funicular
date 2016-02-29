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


$sql	= "UPDATE T_Stories ";
$sql	.= "SET Repostamp = now() ";
$sql	.= "WHERE Rid = '$item'";
$RET	= @mysql_query($sql,$db);
F_logAccess("Reposted story $item");
if ($RET<1) {
	F_error("Unable to repost stories.");
}
if ($CONF["MailingList"]>0) {
	F_mailtoList($item);
}
export_rdf();
header("Location:$G_URL/$where");

?>
