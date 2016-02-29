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

#echo md5($passwd) . " : " . $CONF["Passwd"];

/* check for passwd */
if (md5($passwd)==$CONF["Passwd"]) {
	F_logAccess("Entered admin mode");
	$name	= $CONF["SiteKey"] . "_admin";
	setcookie(md5($name),md5(rot13($CONF["SiteKey"])),0,"/","",0);
} elseif (!F_isAdmin()) {
	include("../include/header.inc.php");
	if (!empty($warn)) {
		F_logAccess("Failed login");
		F_notice("Invalid password. Try again.");
	}
	$VAR["Heading"] = "Authentication Required";
	$VAR["Content"] = "<form \taction\t=\t\"$PHP_SELF\"\n";
	$VAR["Content"] .= "\tname\t= AUTH\n";
	$VAR["Content"] .= "\tmethod\t= POST>\n";
	$VAR["Content"] .= "Password:\n";
	$VAR["Content"] .= "<input\ttype\t= password\n";
	$VAR["Content"] .= "\tname\t= passwd\n";
	$VAR["Content"] .= "\tsize\t= 10\n";
	$VAR["Content"] .= "\tmaxlength\t= 10>\n";
	$VAR["Content"] .= "<input\ttype\t= hidden\n";
	$VAR["Content"] .= "\tname\t=\"warn\"\n";
	$VAR["Content"] .= "\tvalue\t= \"1\">\n";
	$VAR["Content"] .= "<input\ttype\t= submit\n";
	$VAR["Content"] .= "\tname\t=\"mode\"\n";
	$VAR["Content"] .= "\tvalue\t= \"login\">\n";
	$VAR["Content"] .= "</form>\n";
	F_drawMain($VAR);
?>
<script language="javascript">
	document.AUTH.passwd.focus();
</script>
<?
	include("../include/footer.inc.php");
	exit();

}

?>
