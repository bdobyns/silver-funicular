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
?>

<html>
<head>
<title><? print $CONF["SiteName"]; ?></title>
</head>
<body	bgcolor="#ffffff">


<?
$sql	= "SELECT * FROM T_Stories ";
$sql	.= "WHERE Rid = '$story' ";
if ($CONF["Moderation"]>0 && empty($HTTP_COOKIE_VARS["phpWebLog"])) {
	$sql	.= "AND Verified = 'Y'";
}
$result	= @mysql_query($sql,$db);
$nrows	= mysql_num_rows($result);

if ($nrows==0) {

	$VAR["Heading"] = _NOSTORY;
	$VAR["Content"] = "<br>" . _NOSTORY . "<br><br>";
	F_drawMain($VAR);

} else {

	$A	= mysql_fetch_array($result);
	F_drawStory($A);

}
?>

</body>
</html>
