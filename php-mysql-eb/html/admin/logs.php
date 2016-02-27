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

function F_viewLog($type) {
	global	$G_PATH,$CONF;
	$filename = $G_PATH . "/logs/" . $type . ".log";
	$fd = fopen($filename, "r");
	$contents = fread($fd, filesize($filename));
	fclose($fd);

	$VAR["Heading"]	= ucfirst($type) . " Log for $CONF[SiteName]";
	if (!empty($contents)) {
		$VAR["Content"]	= nl2br($contents);
	} else {
		$VAR["Content"] = "Nothing recorded.<br>\n";
	}
	$VAR["Content"]	.= F_resetButton($type);
	F_drawMain($VAR);
}

function F_resetButton($type) {
	global	$PHP_SELF;
	$s	= "<form action=\"$PHP_SELF\" method=POST>\n";
	$s	.= "<input type=hidden name=\"what\" value=\"$type\">\n";
	$s	.= "<input type=submit value=\"Reset " . ucfirst($type) . " Log\">\n";
	$s	.= "</form>\n";
	return $s;
}



if (!empty($what)) {
	$fp=fopen($G_PATH . "/logs/" . $what . ".log", 'w'); 
	fclose($fp);
	if ($what=="access") {
		F_logAccess("Log reset");
	}
}


F_viewLog("access");
F_viewLog("error");










include("../include/footer.inc.php");
?>
