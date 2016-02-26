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

switch	($what) {
case "comment":
	if ($save=="on") {
		F_saveUser($Author,$AuthorEmail,$AuthorURL);
	}
        # these are some disallowed strings that
	# reject comment spam and comment pr0n
        if (stristr($Content,"href=") || 
	    stristr($Content,"http://") ||
	    stristr($Content,"url=") ||
	    stristr($Content,"poker") ||
	    stristr($Content,"cialis") ||
	    stristr($Content,"cigarettes") ||
	    stristr($Content,"cigaretes") ||
	    stristr($Content,"viagra") ||
	    stristr($Content,"shit") ||
	    stristr($Content,"piss") ||
	    stristr($Content,"fuck") ||
	    stristr($Content,"cunt") ||
	    stristr($Content,"cocksucker") ||
	    stristr($Content,"motherfucker") ||
	    stristr($Content,"tits") ||
	    stristr($Content,"c_cox@yahoo.com") ||
	    stristr($Content,"thehowdydog.org") ||
	    stristr($Content,"zack@yahoo.com") ||
	    stristr($Content,"ingrid@mail.com") ||
	    stristr($Content,"Hello WebMaster! GOOD Site!") ||
	    stristr($AuthorEmail,"andre@yarex.com") ) {
	  $anon = "off";
	  $sql	= "INSERT INTO disallowed_Comments ";
	} else {
	  $sql	= "INSERT INTO T_Comments ";
	}
	$sql	.= "(Rid,ParentRid,TopRid,Author,AuthorEmail,AuthorURL,Content,Host,Birthstamp) ";
	$sql	.= "VALUES (";
	$sql	.= "'" . F_getRid() . "',";
	$sql	.= "'" . $ParentRid . "',";
	$sql	.= "'" . $TopRid . "',";
	if ($anon=="on") {
		$Author	= "Anonymous Coward";
		$sql	.= "'" . addslashes($Author) . "',";
		$sql	.= "'',";
		$sql	.= "'',";
	} else {
	        if (strlen(ltrim($Author)) == 0) $Author = "Anonymous Coward";
		$sql	.= "'" . addslashes($Author) . "',";
		$sql	.= "'" . addslashes($AuthorEmail) . "',";
		$sql	.= "'" . addslashes($AuthorURL) . "',";
	}
	$sql	.= "'" . addslashes($Content) . "',";
	$sql	.= "'" . F_getIP() . "',";
	$sql	.= "now()";
	$sql	.= ")";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to insert comment.");
	} else {
	   if ( strstr($sql,"disallowed") == FALSE ) {
		if ($CONF["EmailComments"]>0) {
			F_mailThread($TopRid,$Author,$Content,$AuthorEmail);
		}
		header("Location:$G_URL/$where");
           }
	}
break;
case "contact":
	$tmp	= urlencode(_MAILERROR);
	if (	!empty($Author) && 
		!empty($AuthorEmail) && 
		!empty($Subject) && 
		!empty($Message) && 
		!empty($MailTo) && 
		!empty($MailToEmail)) {
		$RET	= @mail($MailTo . " <" . rot13($MailToEmail) . ">",
			strip_tags(stripslashes($Subject)),
			strip_tags(stripslashes($Message)),
			"From: $Author <$AuthorEmail>\nReturn-Path: <$AuthorEmail>\nX-Mailer: phpWebLog $G_VER\nX-Originating-IP: " . F_getIP() . "\n");
		if ($RET>0) {
			$tmp	= urlencode(_MAILSENT);
		} else {
			F_error(_MAILERROR);
		}
	}
	header("Location:$G_URL/stories.php?msg=$tmp");
break;
case "mailfriend":
	$tmp	= urlencode(_MAILERROR);
	if (!empty($Author) && 
		!empty($AuthorEmail) && 
		!empty($Story) && 
		!empty($MailTo) && 
		!empty($MailToEmail)) {
		$tmp	= urlencode(_MAILSENT);
		F_mailFriend($Story,$MailTo,$MailToEmail,$Author,$AuthorEmail,$Message);
	}
	header("Location:$G_URL/stories.php?story=$Story&msg=$tmp");
break;
}

?>
