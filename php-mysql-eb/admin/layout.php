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

$dir	= $G_PATH . "/backend/template/";
$ldir	= $G_PATH . "/backend/layouts/";

function F_getTmpl($what,$value) {
	global	$dir,$LAYOUT,$G_URL;
	$handle=opendir($dir);
	$s		= "";
	while ($file = readdir($handle)) {
		$tmpl	= $dir . $file . "/" . $what . ".tmpl";
		if ($file != "." && $file != ".." && file_exists($tmpl)) {
			$foo	= $LAYOUT[$value]==$file ? "selected" : "";
			$s .= sprintf("<option value=\"%s\" %s>%s</option>\n",$file,$foo,$file);
		}
	}
	closedir($handle);
	return	$s;
}


	$VAR["Heading"] = "Page Layout: Viewing layout \"$THEME\"";

	$VAR["Content"] = "
<table
	cellpadding	= 2
	cellspacing	= 1
	border	= 0
	width	= 100%>

<tr>
<td	colspan	= 4
	align	= center>
<form	action	= \"$G_URL/admin/submit.php\"
	method	= POST
	name	= \"Layout\">
Layout Name:
<select	name	= name
	onchange= \"JS_swapLayout(document.Layout);\">";

	$handle=opendir($G_PATH . "/backend/layouts");
	while ($file = readdir($handle)) {
		$tmp    = substr($file,-5,5);
		$name   = eregi_replace(".xlay", "", $file);
		$crap	= !empty($preview_layout) ? $preview_layout : $CONF["Layout"];
		if ($file != "." && $file != ".." && strtolower($tmp) == ".xlay") {
			$foo    = ($name==$crap) ? "selected" : "";
			$VAR["Content"] .= sprintf("<option value=\"%s\" %s>%s</option>\n",$name,$foo,$name);
		}
	}
	closedir($handle);

$VAR["Content"] .= "
</select>
<input	type	= text
	name	= Layout
	value	= \"$THEME\"
	size	= 12
	maxlength=12>
<input	type	= hidden
	name	= \"what\"
	value	= \"layout\">
<input	type	= submit
	name	= \"layout_mode\"
	value	= \"View/Load\">
&nbsp;&nbsp;&nbsp;
<input	type	= submit
	onclick	= \"return getconfirm();\"
	name	= \"layout_mode\"
	value	= \"Kill\">
<input	type	= submit
	name	= \"layout_mode\"
	value	= \"Add/Update\">
</td>
</tr>

<tr>
<td colspan=4><h3>Layout Configurator (XLAY)</h3>
<small>(found in $ldir)</small></td>
</tr>

<tr>
<td>Author Name</td>
<td 	colspan = 3><input type=text name=Author maxlength=24 value=\"" . $LAYOUT["Author"] . "\"></td>
</tr>

<tr>
<td>Layout Description</td>
<td 	colspan = 3><input type=text name=Description maxlength=96 value=\"" . $LAYOUT["Description"] . "\"></td>
</tr>

<tr>
<td>BG Image (URL)</td>
<td 	colspan = 3><input type=text name=XLAY_BgURL maxlength=96 value=\"" . $LAYOUT["BgURL"] . "\"></td>
</tr>

<tr>
<td>Logo (URL)</td>
<td	colspan=3><input type=text name=XLAY_LogoURL maxlength=96 value=\"" . $LAYOUT["LogoURL"] . "\"></td>
</tr>

<tr>
<td>Font Family</td>
<td 	colspan=3><input type=text name=XLAY_FontFamily maxlength=96 value=\"" . $LAYOUT["FontFamily"] . "\"></td>
</tr>

<tr>
<td>Page Width (pixel / %)</td>
<td><input 	type=text  name=XLAY_PageWidth size=7 maxlength=10 value=\"" . $LAYOUT["PageWidth"] . "\"></td>
<td>Area Padding</td>
<td><input 	type=text  name=XLAY_AreaPadding size=7 maxlength=10 value=\"" . $LAYOUT["AreaPadding"] . "\"></td>
</tr>

<tr>
<td>Background Color</td>
<td><input 	type=text  name=XLAY_BgColor size=7 maxlength=10 value=\"" . $LAYOUT["BgColor"] . "\"></td>
<td>Foreground Color</td>
<td><input 	type=text  name=XLAY_FgColor size=7 maxlength=10 value=\"" . $LAYOUT["FgColor"] . "\"></td>
</tr>


<tr>
<td>Link Text Color</td>
<td><input 	type=text name=XLAY_LinkColor size=7 maxlength=10 value=\"" . $LAYOUT["LinkColor"] . "\"></td>
<td>Hover Color</td>
<td><input 	type=text  name=XLAY_HoverColor size=7 maxlength=10 value=\"" . $LAYOUT["HoverColor"] . "\"></td>
</tr>

<tr>
<td>Navigation Text</td>
<td><input 	type=text name=XLAY_NavColor size=7 maxlength=10 value=\"" . $LAYOUT["NavColor"] . "\"></td>
<td>Story Title Text</td>
<td><input 	type=text  name=XLAY_TitleColor size=7 maxlength=10 value=\"" . $LAYOUT["TitleColor"] . "\"></td>
</tr>

<tr>
<td>Blocks Width (Left)</td>
<td><input 	type=text  name=XLAY_LeftBlocksWidth size=7 maxlength=10 value=\"" . $LAYOUT["LeftBlocksWidth"] . "\"></td>
<td>Blocks Width (right)</td>
<td><input 	type=text  name=XLAY_RightBlocksWidth size=7 maxlength=10 value=\"" . $LAYOUT["RightBlocksWidth"] . "\"></td>
</tr>

<tr>
<td>Default Blocks Align</td>
<td>
<select	name	=XLAY_BlocksAlign>
	<option value=\"left\" " . ($LAYOUT["BlocksAlign"]=="left" ? "selected" : "") . ">Left</option>
	<option value=\"right\" " . ($LAYOUT["BlocksAlign"]=="right" ? "selected" : "") . ">Right</option>
</select>
</td>
<td colspan=2>&nbsp;</td>
</tr>

<tr>
<td colspan=4><h3>Themes / Templates</h3>
<small>(found in $dir)</small></td>
</tr>

<tr>
<td>Header</td>
<td>
<select	name	= XLAY_TMPL_Header>";
	$VAR["Content"] .= F_getTmpl("header","TMPL_Header");

$VAR["Content"] .= "
</select>
</td>
<td>Footer</td>
<td>
<select	name	= XLAY_TMPL_Footer>";
	$VAR["Content"] .= F_getTmpl("footer","TMPL_Footer");

$VAR["Content"] .= "
</select>
</td>
</tr>

<tr>
<td>Left Block</td>
<td>
<select	name	= XLAY_TMPL_LeftBlock>";
	$VAR["Content"] .= F_getTmpl("block","TMPL_LeftBlock");

$VAR["Content"] .= "
</select>
</td>
<td>Right Block</td>
<td>
<select	name	= XLAY_TMPL_RightBlock>";
	$VAR["Content"] .= F_getTmpl("block","TMPL_RightBlock");

$VAR["Content"] .= "
</select>
</td>
</tr>


<tr>
<td>Summary</td>
<td>
<select	name	= XLAY_TMPL_Summary>";
	$VAR["Content"] .= F_getTmpl("summary","TMPL_Summary");

$VAR["Content"] .= "
</select>
</td>
<td>Story</td>
<td>
<select	name	= XLAY_TMPL_Story>";
	$VAR["Content"] .= F_getTmpl("story","TMPL_Story");

$VAR["Content"] .= "
</select>
</td>
</tr>


<tr>
<td>Comment</td>
<td>
<select	name	= XLAY_TMPL_Comment>";
	$VAR["Content"] .= F_getTmpl("comment","TMPL_Comment");

$VAR["Content"] .= "
</select>
</td>
<td>Main</td>
<td>
<select	name	= XLAY_TMPL_Main>";
	$VAR["Content"] .= F_getTmpl("main","TMPL_Main");

$VAR["Content"] .= "
</select>
</td>
</tr>

</table>
</form>";

F_drawMain($VAR);
include("../include/footer.inc.php");



?>
