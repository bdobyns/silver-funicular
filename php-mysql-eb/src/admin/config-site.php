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

$VAR["Heading"] = "Site Configuration";

$VAR["Content"] = "
<form	action	= \"$G_URL/admin/submit.php\"
	name	= \"config\"
	method	= \"POST\">
<table
	cellpadding	= 2
	cellspacing	= 1
	width	= 100%
	border	= 0>

<tr>
<td>Site Name</td>
<td><input 	type=text  name=SiteName  size=40  maxlength=64 value=\"" . stripslashes($CONF["SiteName"]) . "\"></td>
</tr>

<tr>
<td>Site Slogan</td>
<td><input 	type=text  name=SiteSlogan  size=40  maxlength=64 value=\"" . stripslashes($CONF["SiteSlogan"]) . "\"></td>
</tr>

<tr>
<td>Site Owner</td>
<td><input 	type=text  name=SiteOwner  size=24  maxlength=24 value=\"" . stripslashes($CONF["SiteOwner"]) . "\"></td>
</tr>

<tr>
<td>Site Email</td>
<td><input 	type=text  name=EmailAddress  size=24  maxlength=96 value=\"" . $CONF["EmailAddress"] . "\"></td>
</tr>

<tr>
<td>Admin Password</td>
<td>
<input 	type=password name=Passwd size=10 maxlength=10>
 Again 
<input 	type=password name=Passwd2 size=10 maxlength=10>
<small>(For changing only)</small></td>
</tr>

<tr>
<td>Tools Password</td>
<td>
<input 	type=password name=ToolPasswd size=10 maxlength=10>
 Again 
<input 	type=password name=ToolPasswd2 size=10 maxlength=10>
<small>(For changing only)</small></td>
</tr>

<tr>
<td>Unique Key</td>
<td><input 	type=text  name=SiteKey  size=10  maxlength=10 value=\"" . $CONF["SiteKey"] . "\">
<small>(Used for cookie values)</small></td>
</tr>

<tr>
<td>Site Layout</td>
<td>
<select	name	= Layout>";

	$handle=opendir($G_PATH . "/backend/layouts");
	while ($file = readdir($handle)) {
		$tmp	= substr($file,-5,5);
		$name	= eregi_replace(".xlay", "", $file);
		if ($file != "." && $file != ".." && strtolower($tmp) == ".xlay") {
			$foo	= strtolower($CONF["Layout"])==strtolower($name) ? "selected" : "";
			$VAR["Content"] .= sprintf("<option value=\"%s\" %s>%s</option>\n",$name,$foo,$name);
		}
	}
	closedir($handle);

$VAR["Content"] .= "
</select>
<small>(Found in $G_PATH/backend/layouts/)</small></td>
</tr>

<tr>
<td>Site Language</td>
<td>
<select	name	= Language>";

	$handle=opendir($G_PATH . "/backend/language");
	while ($file = readdir($handle)) {
		$tmp	= substr($file,-4,4);
		$name	= eregi_replace(".lng", "", $file);
		if ($file != "." && $file != ".." && strtolower($tmp) == ".lng") {
			$foo	= strtolower($CONF["Language"])==strtolower($name) ? "selected" : "";
			$VAR["Content"] .= sprintf("<option value=\"%s\" %s>%s</option>\n",$name,$foo,$name);
		}
	}
	closedir($handle);

$VAR["Content"] .= "
</select>
<small>(Found in $G_PATH/backend/language/)</small></td>
</tr>

<tr>
<td 	colspan	= 4>
<input	type	= hidden
	name	= what
	value	= config-site>
<input	type	= submit
	value	= \"Save Changes\">
</td>
</tr>

</table>
</form>
";


	F_drawMain($VAR);
	include("../include/footer.inc.php");
?>
