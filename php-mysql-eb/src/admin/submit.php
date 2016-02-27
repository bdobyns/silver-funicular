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

/* debug */
if (0) {
	F_debug($HTTP_POST_VARS);
}
if (empty($HTTP_POST_VARS) && 0) {
	$msg	= urlencode("Access denied!");
	F_logAccess("Access denied trying to submit.");
	header("Location:$G_URL/?msg=$msg");
	exit();
}
if (!F_isAdmin()) {
	$msg	= urlencode("Access denied!");
	F_logAccess("Access denied trying to submit as non-admin.");
	header("Location:$G_URL/?msg=$msg");
	exit();
}

switch	($what) {

case "block":
	if ($Type>0 && !@fopen($URL,"r")) {
		$msg	= urlencode("Error! URL not found: <b>$URL</b>");
		header("Location:$G_URL/admin/blocks.php?msg=$msg");
		exit();
	}
	$sm		= ($ShowMain=="on" ? "1" : "0");
	$pc		= ($PageComments=="on" ? "1" : "0");
	$sql	= "INSERT INTO T_Blocks ";
	$sql	.= "(Rid,Heading,Content,URL,Display,ShowMain,PageComments,Cache,Type,Hits,OrderID,Birthstamp,Timestamp) ";
	$sql	.= "VALUES (";
	$sql	.= "'" . F_getRid() . "',";
	$sql	.= "'" . addslashes($Heading) . "',";
	$sql	.= "'" . addslashes($Content) . "',";
	$sql	.= "'" . $URL . "',";
	$sql	.= "'" . $Display . "',";
	$sql	.= "'" . $sm . "',";
	$sql	.= "'" . $pc . "',";
	$sql	.= "'" . $Cache . "',";
	$sql	.= $Type . ",";
	$sql	.= "0,";
	$sql	.= $OrderID . ",";
	$sql	.= "now(),";
	$sql	.= "now())";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Added new block");
	if ($RET<1) {
		F_error("Unable to insert block.");
	}
	header("Location:$G_URL/admin/blocks.php");
break;


case "topic":
	$NoPosting = !empty($NoPosting) ? 1 : 0;
	$NoComments = !empty($NoComments) ? 1 : 0;

	$sql	= "INSERT INTO T_Topics ";
	$sql	.= "(Topic,ImgURL,AltTag,NoPosting,NoComments,Timestamp) ";
	$sql	.= "VALUES (";
	$sql	.= "'" . addslashes($Topic) . "',";
	$sql	.= "'" . addslashes($URL) . "',";
	$sql	.= "'" . addslashes($AltTag) . "',";
	$sql	.= "'" . $NoPosting . "',";
	$sql	.= "'" . $NoComments . "',";
	$sql	.= "now()";
	$sql	.= ")";
	$RET	= @mysql_query($sql,$db);
	F_logAccess("Added new topic - $Topic");
	if ($RET<1) {
		F_error("Unable to insert topic.");
	}
	header("Location:$G_URL/admin/topics.php");
break;


case "layout":
	switch ($layout_mode) {
	case "View/Load":
		$msg	= urlencode("You must set this layout as the default in Configure to enable.");
		header("Location:$G_URL/admin/layout.php?preview_layout=$name&msg=$msg");
	break;

	case "Kill":
		if ($name==$CONF["Layout"]) {
			$msg	= urlencode("You cannot kill the active layout. Nothing changed.");
			header("Location:$G_URL/admin/layout.php?msg=$msg");
		} else {
			$foo	= $G_PATH . "/backend/layouts/" . $name . ".xlay";
			$RET	= unlink($foo);
			if ($RET<1) {
				F_error("Unable to delete layout.");
			} else {
				$msg	= urlencode("Layout deleted: <b>$foo</b>.");
				header("Location:$G_URL/admin/layout.php?msg=$msg");
			}
		}
		F_logAccess("Deleted layout $name");
	break;

	case "Add/Update":
		export_layout($HTTP_POST_VARS);
		$foo	= $G_PATH . "/backend/layouts/" . $Layout . ".xlay";
		$msg	= urlencode("Layout file added as <b>$foo</b>.");
		F_logAccess("Updated layout $Layout");
		header("Location:$G_URL/admin/layout.php?preview_layout=$Layout&msg=$msg");
	break;
	}
break;

case "config-site":
	$tmp	= urlencode("Changes Saved.");
	if (!empty($ToolPasswd) || !empty($ToolPasswd2)) {
		if ($HTTP_POST_VARS["ToolPasswd"]==$HTTP_POST_VARS["ToolPasswd2"]) {
			$sql	= "UPDATE T_Config set ";
			$sql	.= "Value = '" . md5($HTTP_POST_VARS["ToolPasswd"]) . "' ";
	 		$sql    .= "WHERE Name = 'ToolPasswd'";    
			$RET	= @mysql_query($sql,$db);
		} else {
			$tmp	= urlencode(_SAVEDNOTPW);
		}
	}
	if (!empty($Passwd) || !empty($Passwd2)) {
		if ($HTTP_POST_VARS["Passwd"]==$HTTP_POST_VARS["Passwd2"]) {
			$sql	= "UPDATE T_Config set ";
			$sql	.= "Value = '" . md5($HTTP_POST_VARS["Passwd"]) . "' ";
	 		$sql    .= "WHERE Name = 'Passwd'";    
			$RET	= @mysql_query($sql,$db);
		} else {
			$tmp	= urlencode(_SAVEDNOPW);
		}
	}
	for (reset($HTTP_POST_VARS);$k=key($HTTP_POST_VARS);next($HTTP_POST_VARS)) {
		# update configuration
		if ($k!="what" && $k!="Passwd" && $k!="Passwd2" 
		               && $k!="ToolPasswd" && $k!="ToolPasswd2" ) {
			$sql	= "UPDATE T_Config set ";
			$sql	.= "Value = '" . $HTTP_POST_VARS[$k] . "' ";
	 		$sql    .= "WHERE Name = '" . $k . "'";    
			@mysql_query($sql,$db);
		}
	}
	if ($HTTP_COOKIE_VARS["phpWebLog"]!=rot13($SiteKey)) {
		$name   = $SiteKey . "_admin";
		setcookie(md5($name),md5(rot13($SiteKey)),0,"/","",0);
	}
	F_logAccess("Updated site configuration.");
	header("Location:$G_URL/admin/config-site.php?msg=$tmp");
break;

case "config-story":
	$tmp	= urlencode("Changes Saved.");
	for (reset($HTTP_POST_VARS);$k=key($HTTP_POST_VARS);next($HTTP_POST_VARS)) {
		# update configuration
		if ($k!="what" && $k!="Passwd" && $k!="Passwd2") {
			$sql	= "UPDATE T_Config set ";
			$sql	.= "Value = '" . $HTTP_POST_VARS[$k] . "' ";
	 		$sql    .= "WHERE Name = '" . $k . "'";    
			@mysql_query($sql,$db);
		}
	}
	F_logAccess("Updated story configuration.");
	header("Location:$G_URL/admin/config-story.php?msg=$tmp");
break;

case "config-extend":
	$tmp	= urlencode("Changes Saved.");
	# force a value for checkboxes that are null

	for (reset($HTTP_POST_VARS);$k=key($HTTP_POST_VARS);next($HTTP_POST_VARS)) {
		# update configuration
		if ($k!="what" && $k!="Passwd" && $k!="Passwd2" && $k!="IndexName" && $k!="IndexKills" && $k!="action") {
			$sql	= "UPDATE T_Config set ";
			$sql	.= "Value = '" . $HTTP_POST_VARS[$k] . "' ";
	 		$sql    .= "WHERE Name = '" . $k . "'";    
			@mysql_query($sql,$db);
		# add link index name
		} elseif (!empty($IndexName)) {
			$sql	= "INSERT INTO T_IndexNames (Name) VALUES ('$IndexName')";
			@mysql_query($sql,$db);
			$IndexName	= "";
		}
	}
	# kill link index name
	if (!empty($IndexKills) && $action=="kill") {
		$sql	= "DELETE FROM T_IndexNames WHERE Rid=0 ";
		for ($i=0;$i<count($IndexKills);$i++) {
			$sql	.= "OR Name='$IndexKills[$i]'";
		}
		@mysql_query($sql,$db);
		# kill all associated story links as well
		$sql	= "DELETE FROM T_IndexLinks WHERE Rid=0 ";
		for ($i=0;$i<count($IndexKills);$i++) {
			$sql	.= "OR Name='$IndexKills[$i]'";
		}
		@mysql_query($sql,$db);
	}

	F_logAccess("Updated extended configuration.");
	header("Location:$G_URL/admin/config-extend.php?msg=$tmp");
break;

case "poll":
	F_logAccess("Updated poll $Rid");
	$A[1] = $Aid1;
	$A[2] = $Aid2;
	$A[3] = $Aid3;
	$A[4] = $Aid4;
	$A[5] = $Aid5;
	$A[6] = $Aid6;
	$A[7] = $Aid7;
	$A[8] = $Aid8;
	$A[9] = $Aid9;
	$A[10] = $Aid10;
	$V[1] = $Votes1;
	$V[2] = $Votes2;
	$V[3] = $Votes3;
	$V[4] = $Votes4;
	$V[5] = $Votes5;
	$V[6] = $Votes6;
	$V[7] = $Votes7;
	$V[8] = $Votes8;
	$V[9] = $Votes9;
	$V[10] = $Votes10;
	$Voters = 0;
	for ($i=1; $i<=10; $i++) {
		$Voters = $Voters + $V[$i];
	}

	if (empty($Voters)) { $Voters = "0"; }
	$sql	= "DELETE FROM T_PollQuestions WHERE Rid = '$Rid'";
	$result = mysql_query($sql,$db);
	$sql	= "DELETE FROM T_PollAnswers WHERE Rid = '$Rid'";
	$result = mysql_query($sql,$db);

	$sql	= "INSERT INTO T_PollQuestions ";
	$sql	.= "(Rid, Question, Voters, ExpireDays, Birthstamp, Display) ";
	$sql	.= "VALUES (";
	$sql	.= "'" . $Rid . "',";
	$sql	.= "'" . addslashes($Question) . "',";
	$sql	.= $Voters . ",";
	$sql	.= $Days . ",";
	$sql	.= "now(),";
	$sql	.= "'" . $Display . "')";
	$result = mysql_query($sql,$db);	
	for ($i = 1; $i <= 10; $i++) {
		if (!empty($A[$i])) {
			if (empty($V[$i])) { $V[$i] = "0"; }
			$sql 	= "INSERT INTO T_PollAnswers ";
			$sql	.= "(Rid, Aid, Answer, Votes) ";
			$sql	.= "VALUES (";
			$sql	.= "'" . $Rid . "',";
			$sql	.= $i . ",";
			$sql	.= "'" . addslashes($A[$i]) . "',";
			$sql	.= $V[$i] . ")";
			$result = mysql_query($sql,$db);
		}
	}

	header("Location:$G_URL/$where");

break;

}

?>
