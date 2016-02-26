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


if (empty($poll)) {

	include("./include/header.inc.php");
	F_pollList();
	include("./include/footer.inc.php");

} elseif (empty($aid)) {

	include("./include/header.inc.php");
	if (empty($HTTP_COOKIE_VARS[$poll])) {
		F_pollVote($poll);
	} else {
		F_pollResults($poll);
		F_doComments($poll,$poll,"pollbooth.php?poll=$poll");
		print "<br>";
		F_PostComment($poll,$poll,"pollbooth.php?poll=$poll");
	}
	include("./include/footer.inc.php");

} elseif ($aid>0 && empty($HTTP_COOKIE_VARS[$poll])) {
	$sql	= "SELECT ExpireDays FROM T_PollQuestions ";
	$sql	.= "WHERE Rid = '" . $poll . "'";
	$result	= @mysql_query($sql,$db);
	$exp	= mysql_result($result,0,"ExpireDays") * 86400;
	setcookie(urlencode($poll),$aid,time()+$exp);
	include("./include/header.inc.php");
	F_doVote($poll,$aid);
	F_pollResults($poll);
	F_doComments($poll,$poll,"pollbooth.php?poll=$poll");
	print "<br>";
	F_PostComment($poll,$poll,"pollbooth.php?poll=$poll");
	include("./include/footer.inc.php");

} else {

	include("./include/header.inc.php");
	F_pollResults($poll);
	F_doComments($poll,$poll,"pollbooth.php?poll=$poll&aid=-1");
	print "<br>";
	F_PostComment($poll,$poll,"pollbooth.php?poll=$poll&aid=-1");
	include("./include/footer.inc.php");

}

?>
