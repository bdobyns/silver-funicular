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

## MySQL connectivity variables
$G_DB   = "db146981002";       # database name
$G_HOST = "db233.perfora.net"; # hostname
$G_PORT = "3306";              # mysql port
$G_USER = "dbo146981002";      # mysql username
$G_PASS = "3gq.MKP3";          # mysql password

$DATA_DB   = "db146981121";
$DATA_HOST = "db244.perfora.net";
$DATA_PORT = "3306";
$DATA_USER = "dbo146981121";
$DATA_PASS = "aCpH5tyM";

## (do not add a trailing / to the following paths)

## full path to the phpWebLog base directory
$G_PATH = "/kunden/homepages/2/d146177287/htdocs/newlpef/public_html";

## full URL to your phpWebLog document root
$G_URL  = "http://www.lpef.org";

/*== No need to edit past this line ====================================*/

# Allowed HTML tags in stories.
# see parser-syntax.txt for details.
$syntax = array (
	"B" => "<B>",
	"\/B" => "</B>",
	"I" => "<I>",
	"\/I" => "</I>",
	"U" => "<U>",
	"\/U" => "</U>",
	"Q"   => "<BLOCKQUOTE>",
	"\/Q" => "</BLOCKQUOTE>",
	"LIST" => "<UL>",
	"\/LIST" => "</UL>",
	"\*" => "<LI>"
		);

$G_VER	= "0.5.2";

/*== establish database connection ======================================*/

$db	= mysql_pconnect($G_HOST.":".$G_PORT,$G_USER,$G_PASS);
@mysql_select_db($G_DB) or die("Unable to connect to database $G_DB. Be sure to edit include/common.inc.php.");

/*== read in configuration data ==*/
$sql	= "SELECT * FROM T_Config";
$result	= @mysql_query($sql,$db);
$nrows	= mysql_num_rows($result);
$CONF	= array();

for ($i=0;$i<$nrows;$i++) {
	$A	= mysql_fetch_array($result);
	$CONF[$A["Name"]] = $A["Value"];
}

/*== include in language translation ==*/
$LANG	= !empty($CONF["Language"]) ? $CONF["Language"] : "english";
include($G_PATH . "/backend/language/" . $LANG . ".lng");

/*== include functions ==================================================*/

include("$G_PATH/include/func.inc.php");
include("$G_PATH/include/blocks.inc.php");
include("$G_PATH/include/layout.inc.php");
include("$G_PATH/include/parser.inc.php");
include("$G_PATH/include/polls.inc.php");
include("$G_PATH/include/search.inc.php");
include("$G_PATH/include/comments.inc.php");

/*====================================================================*/

/*== read in layout data ==*/
$THEME	= !empty($preview_layout) ? $preview_layout : $CONF["Layout"];
$LAYOUT	= import_layout($THEME);

/*== determine userinfo cookie name ==*/
$C_USER	= md5($CONF["SiteKey"] . "_userinfo");


?>
