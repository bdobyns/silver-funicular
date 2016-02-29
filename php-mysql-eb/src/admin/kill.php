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


function F_killChildren($table,$item) {
	global	$db;
	/* build array of children */
	$sql	= "SELECT Rid FROM $table ";
	$sql	.= "WHERE ParentRid = '" . $item . "'";
	$result	= @mysql_query($sql,$db);
	if ($result<1) { F_error("Unable to select children from $table."); }

	for ($i=0;$i<mysql_num_rows($result);$i++) {
		$_rid	= mysql_result($result, $i);
		/*== destroy record ==*/
		$sql	= "DELETE FROM $table ";
		$sql	.= "WHERE Rid = '" . $_rid . "'";
		$RET	= @mysql_query($sql,$db);
		F_logAccess("Deleted item $_rid from $table");
		if ($RET<1) { 
			F_error("Unable to delete child $_rid from $table"); 
		} else {
			F_killChildren($table,$_rid);
		}
	}

	/*== destroy record ==*/
	$sql	= "DELETE FROM $table ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) { F_error("Unable to delete item $item from $table"); }
}



if ($what=="T_Comments") {

	F_killChildren("T_Comments",$item);
	header("location:$G_URL/$where");
	exit();

} elseif ($what=="T_Stories") {

	/*== destroy record ==*/
	$sql	= "DELETE FROM T_Stories ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted story $item");
	if ($RET<1) {
		F_error("Unable to delete item $item from stories");
	}
	$sql	= "DELETE FROM T_Comments ";
	$sql	.= "WHERE TopRid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted comments for story $item");
	if ($RET<1) {
		F_error("Unable to delete item $item from comments.");
	}
	$sql	= "DELETE FROM T_IndexLinks ";
	$sql	.= "WHERE ParentRid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted index links for story $item");
	if ($RET<1) {
		F_error("Unable to delete item $item from index links.");
	}
	export_rdf();
	header("Location:$G_URL/$where");
	exit();

} elseif ($what=="T_PollQuestions") {

	/*== destroy record ==*/
	$sql	= "DELETE FROM T_PollQuestions ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted poll $item");
	if ($RET<1) {
		F_error("Unable to delete item $item from poll questions");
	}
	$sql	= "DELETE FROM T_PollAnswers ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to delete item $item from polls.");
	}
	$sql	= "DELETE FROM T_Comments ";
	$sql	.= "WHERE TopRid = '$item'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to delete item $item from comments.");
	}
	header("Location:$G_URL/$where");
	exit();

} elseif ($what=="T_Topics") {

	/* get array of stories */
	$sql	= "SELECT Rid FROM T_Stories ";
	$sql	.= "WHERE Topic = '$item'";
	$result	= @mysql_query($sql,$db);
	if ($result<1) {
		F_error("Unable to select $item from stories.");
	}
	$cid	= array();
	for ($i=0;$i<mysql_num_rows($result);$i++) {
		$cid[$i]	= mysql_result($result, $i);
	}

	/* delete comments associated with deleted stories */
	$sql	= "DELETE FROM T_Comments WHERE ";
	for ($i=0;$i<sizeof($cid);$i++) {
		$sql	.= sprintf("TopRid = '%s' OR ",$cid[$i]);
	}
	$sql	.= "Rid = 'NULL'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to delete comments.");
	}

	/* delete index links associated with deleted stories */
	$sql	= "DELETE FROM T_IndexLinks WHERE ";
	for ($i=0;$i<sizeof($cid);$i++) {
		$sql	.= sprintf("ParentRid = '%s' OR ",$cid[$i]);
	}
	$sql	.= "ParentRid = 'NULL'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to delete index links.");
	}

	/* delete stories associated with deleted topic */
	$sql	= "DELETE FROM T_Stories ";
	$sql	.= "WHERE Topic = '$item'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to delete topic from stories.");
	}

	/* destroy the topic itself */
	$sql	= "DELETE FROM T_Topics ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted topic $item");
	if ($RET<1) {
		F_error("Unable to delete item $item from topics");
	}

	export_rdf();
	header("Location:$G_URL/admin/topics.php");
	exit();

} elseif ($what=="T_LinkCats") {

	F_killChildren("T_LinkCats",$item);
	F_logAccess("Deleted link category $item");
	header("location:$G_URL/links.php");
	exit();

} elseif ($what=="T_Blocks") {

	$sql	= "DELETE FROM $what ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted item $item from $what");
	if ($RET<1) {
		F_error("Unable to delete item $item from $what");
	}
	$sql	= "DELETE FROM T_Comments ";
	$sql	.= "WHERE TopRid = '$item'";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to delete item $item from page comments.");
	}

} else {

	/*== destroy anything else ==*/
	$sql	= "DELETE FROM $what ";
	$sql	.= "WHERE Rid = '$item'";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Deleted item $item from $what");
	if ($RET<1) {
		F_error("Unable to delete item $item from $what");
	}

}

header("Location:$G_URL/$where");

?>
