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


/*== functions ==========================================================*/

function F_notice($notice) {
	global	$LAYOUT;
	$s	= "<table \tborder\t= \"0\"\n";
	$s	.= "\tcellpadding\t= \"0\"\n";
	$s	.= "\tcellspacing\t= \"0\"\n";
	$s	.= "\twidth\t= \"100%\">\n";
	$s	.= "<tr>\n<td class=notice>\n";
	$s	.= $notice;
	$s	.= "</td>\n</tr>\n</table><br>\n";
	print ($s);
}

function F_story($story) {
	global	$G_URL;
	return ($G_URL . "/stories.php?story=" . $story);
}

function F_count($table,$field="",$what="",$field2="",$what2="") {
	global	$db;
	$sql	= sprintf("SELECT count(*) FROM %s",$table);
	if (!empty($what)) {
		$sql	.= sprintf(" WHERE %s = '%s'",$field,$what);
	}
	if (!empty($what2)) {
		$sql	.= sprintf(" AND %s = '%s'",$field2,$what2);
	}
	$result	= mysql_query($sql,$db);
	/* $result will be false if query failed so use 0 as count */
	$count = (!$result) ? 0 : mysql_result($result,0);
	return ($count);
}

# re Dylan Reeve
function F_genSummary($doo) {
	global $CONF;
	$doo	= trim($doo);
	if ( strlen($doo) > $CONF["SummaryLength"] ) {
		$words  = explode(" ", $doo);
		$stop   = 0;
		$tmpcnt = 0;
		$wordcount = count($words);
		while ($stop == 0) {
			$summ = $summ . " " . $words[$tmpcnt];
			if (strlen($summ) > $CONF["SummaryLength"]) {
				$stop   = 1;
			} elseif (count($words) < $tmpcnt) {
				$stop   = 1;
			}
			$tmpcnt++;
		}
	} else {
		$summ   = $doo;
	}
	return $summ;
}

function rot13($text) {
	for ($i=0,$len=strlen($text);$i<$len;$i++) {
		if (strstr('abcdefghijklmnopqrstuvwxzyABCDEFGHIJKLMNOPQRSTUVWXYZ',$text[$i])) {
			$text[$i] = chr(ord($text[$i]) + ((strtoupper($text[$i]) > 'M') ? -13 : 13));
		}
	}
	return $text;
}

function F_admin($what,$item,$where="") {
	global	$HTTP_COOKIE_VARS,$CONF,$G_URL,$PHP_SELF;
	if (F_isAdmin()) {
		# kill
		$s	= sprintf("<a onclick= \"return getconfirm();\" href= \"$G_URL/admin/kill.php?what=%s&item=%s&where=%s\"><img alt= \"kill\" border= 0 src= \"%s/images.d/kill.gif\"></a>\n",$what,$item,$where,$G_URL);
		# edit
		$s	.= sprintf("<a href= \"$G_URL/admin/edit.php?what=%s&item=%s&where=%s\"><img alt= \"edit\" border=0 src= \"%s/images.d/edit.gif\"></a>\n",$what,$item,$where,$G_URL);
		# verify
		if (basename($PHP_SELF)=="moderate.php" && $what!="T_Blocks" && $what!="T_PollQuestions") {
			$s	.= sprintf("<a href= \"$G_URL/admin/verify.php?what=%s&item=%s&where=%s\"><img alt= \"verify\" border=0 src= \"%s/images.d/verify.gif\"></a>\n",$what,$item,"admin/moderate.php",$G_URL);
		# repost
		} elseif ($what=="T_Stories") {
			$s	.= sprintf("<a href= \"$G_URL/admin/repost.php?what=%s&item=%s&where=%s\"><img alt= \"repost\" border=0 src= \"%s/images.d/repost.gif\"></a>\n",$what,$item,$where,$G_URL);
		}
	return $s;
	}
}

function F_getTopic($Rid,$ret="") {
	global	$db;
	$sql	= "SELECT Topic,ImgURL,AltTag FROM T_Topics ";
	$sql	.= "WHERE Rid = '$Rid'";
	$result	= mysql_query($sql,$db);
	$A		= mysql_fetch_array($result);
	if (empty($ret)) {
		return $A;
	} else {
		return $A[$ret];
	}
}

function F_topicsel($topic="1",$type="") {
	global	$db;
	$sql	= "SELECT * FROM T_Topics ORDER BY Topic ASC";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	$tmp	= "";
	for ($i=0;$i<$nrows;$i++) {
		$A	= mysql_fetch_array($result);
		$ass	= ($A["Rid"]==$topic ? "selected" : "");
		if ($type=="post" && $A["NoPosting"]=="1" && !F_isAdmin()) {
			# do nothing
		} else {
			$tmp	.= sprintf("<option\tvalue\t= \"%s\" %s>%s</option>\n",
				$A["Rid"],$ass,$A["Topic"]);
		}
	}
	return $tmp;
}

function F_uphits($tbl,$rid) {
	global	$db;
	$sql	= "UPDATE $tbl set Hits = Hits + 1";
	$sql	.= " WHERE Rid = '$rid'";
#	$sql	.= " OR ParentRid = '$rid'";
	@mysql_query($sql,$db);
}

function F_debug($A) {
	echo "<pre><p>---- DEBUG ----\n";
	for (reset($A);$k=key($A);next($A)) {
		printf("<li>%13s [%s]</li>\n",$k,$A[$k]); 
	}
	echo "<br>---------------\n</pre>\n";
}


function F_saveUser($Author,$AuthorEmail,$AuthorURL) {
	global $CONF;
	$time	= time()+5800000;
	$name	= $CONF["SiteKey"] . "_userinfo";
	setcookie(md5($name),rot13("$Author|$AuthorEmail|$AuthorURL"),$time,"","",0);
}

// This is the tools auth test
function F_isTools() {
	global	$HTTP_COOKIE_VARS,$CONF;
	$name	= md5($CONF["SiteKey"] . "_tools");
	#echo $HTTP_COOKIE_VARS[$name];
	#echo crypt("admin",$CONF["SiteKey"]);
	return ($HTTP_COOKIE_VARS[$name]==md5(rot13($CONF["SiteKey"])) ? 1 : 0);
}

function F_isAdmin() {
	global	$HTTP_COOKIE_VARS,$CONF;
	$name	= md5($CONF["SiteKey"] . "_admin");
	#echo $HTTP_COOKIE_VARS[$name];
	#echo crypt("admin",$CONF["SiteKey"]);
	return ($HTTP_COOKIE_VARS[$name]==md5(rot13($CONF["SiteKey"])) ? 1 : 0);
}

function F_lastModified($rid) {
	global	$db;
	$sql    = "SELECT Birthstamp FROM T_Comments ";
	$sql    .= "WHERE TopRid = '$rid' ";
	$sql    .= "ORDER By Birthstamp desc ";
	$sql	.= "LIMIT 1";
	$result	= @mysql_query($sql,$db);
	$tmp	= @mysql_result($result,0,"Birthstamp");
	if ($tmp>0) { return _MODIFIED . " " . F_dateFormat($tmp); }
}

function F_mailThread($StoryRid,$Author,$Content,$AuthorEmail="") {
	global	$db,$CONF,$G_VER,$G_URL,$G_DATE;
	$sql	= "SELECT * FROM T_Stories ";
	$sql	.= "WHERE Rid = '$StoryRid'";
	$result	= mysql_query($sql,$db);
	$A	= mysql_fetch_array($result);
	if ($A["EmailComments"]>0) {
		/*== contruct mail ==*/
		$pre	= _CMAIL1 . " " . $CONF["SiteName"] . ".\n";
		$pre	.= _CMAIL2 . "\n\n";
		$pre	.= "--- " . _CMAIL3 . " --------------------------------\n";
		$pre	.= _TITLE . ": " . stripslashes($A["Heading"]) . "\n";
		$pre	.= _TOPIC . ": " . F_getTopic($A["Topic"],"Topic") . "\n";
		$pre	.= _DATE . ": " . F_dateFormat($A["Birthstamp"]) . "\n\n";
		$pre	.= substr($A["Content"],0,$CONF["SummaryLength"]) . "...\n\n";
		$pre	.= "----------------------------------------------------\n";
		$pre	.= _COMADDED . " " . F_dateFormat(date("Y-m-d H:i:s",time())) . "\n";
		$pre	.= stripslashes($Author) . " " . _WRITES . ":\n\n";
		$pre	.= stripslashes($Content) . "\n\n";
		$pre	.= _DETAILS . " " . $G_URL . "/stories.php?story=" . $StoryRid;
		/*== send it ==*/
		$mailto	= stripslashes($A["Author"]) . " <" . $A["AuthorEmail"] . ">";
		$subject = strip_tags(stripslashes("Re: " . $A["Heading"]));
		$authoremail	= !empty($AuthorEmail) ? $AuthorEmail : "anonymous@unknown";
		@mail($mailto,$subject,
			stripslashes($pre),
			"From: " . $Author . " <" . $authoremail . ">\nReturn-Path: <" . $CONF["EmailAddress"] . ">\nX-Mailer: phpWebLog $G_VER");
	}
}

function F_mailFriend($ParentRid,$MailTo,$MailToEmail,$Author,$AuthorEmail,$Message="") {
	global	$db,$CONF,$G_VER,$G_URL,$G_DATE;
	$sql	= "SELECT * FROM T_Stories ";
	$sql	.= "WHERE Rid = '$ParentRid'";
	$result	= mysql_query($sql,$db);
	$A	= mysql_fetch_array($result);
	/*== contruct mail ==*/
	$pre	= $MailTo . ",\n";
	$pre	.= _MAILFRIEND . "\n" . $Message . "\n\n";
	$pre	.= _DETAILS . " " . $G_URL . "/stories.php?story=" . $ParentRid . "\n\n";
	$pre	.= $Author . "\n" . $AuthorEmail . "\n";
	$subject	= $CONF["SiteName"] . ": " . $A["Heading"];
	/*== send it ==*/
	mail($MailTo . "<" . $MailToEmail . ">",$subject,strip_tags(stripslashes($pre)),
		"From: " . $Author . " <" . $AuthorEmail . ">\nReturn-Path: <" . $AuthorEmail . ">\nX-Mailer: phpWebLog $G_VER\nX-Originating-IP: " . F_getIP() . "\n");
}

function F_notifyAdmin($Topic,$Heading,$Author,$AuthorEmail) {
	global	$CONF,$G_VER,$G_URL;
	/*== contruct mail ==*/
	$pre	= _NMAIL1 . " " . stripslashes($CONF["SiteName"]) . " " . _NMAIL2 . ":\n\n";
	$pre	.= "-------------------------------------------------------------------\n";
	$pre	.= _TITLE . ": " . stripslashes($Heading) . "\n";
	$pre	.= _TOPIC . ": " . F_getTopic($Topic,"Topic") . "\n";
	$pre	.= _AUTHOR . ": " . stripslashes($Author) . " (" . stripslashes($AuthorEmail) . ")" . "\n";
	$pre	.= "-------------------------------------------------------------------\n";
	$pre	.= _NMAIL3 . " " . $G_URL . "/admin";
	/*== send it ==*/
	$mailto	= $CONF["SiteOwner"] . " <" . $CONF["EmailAddress"] . ">";
	$subject = _UNVERIFIED . " " . $CONF["SiteName"];
	@mail($mailto,$subject,
		strip_tags(stripslashes($pre)),
		"From: " . $CONF["SiteName"] . " <" . $CONF["EmailAddress"] . ">\nReturn-Path: <" . $CONF["EmailAddress"] . ">\nX-Mailer: phpWebLog $G_VER");
}

function F_mailtoList($rid) {
	global	$db,$CONF,$G_VER,$G_URL;
	$sql	= "SELECT * FROM T_Stories ";
	$sql	.= "WHERE Rid = '$rid'";
	$result	= mysql_query($sql,$db);
	$A	= mysql_fetch_array($result);
	if (!empty($CONF["MailingAddress"])) {
		/*== contruct mail ==*/
		$pre	.= _TITLE . ": " . stripslashes($A["Heading"]) . " (" . F_getTopic($A["Topic"],"Topic") . ")\n";
		$pre	.= _DATE . ": " . F_dateFormat($A["Birthstamp"]) . "\n";
		$pre	.= _AUTHOR . ": " . stripslashes($A["Author"]) . " (" . stripslashes($A["AuthorEmail"]) . ")" . "\n\n";
		$pre	.= strip_tags(stripslashes($A["Content"])) . "\n\n";
		$pre	.= _RELATED . ": " . $A["StoryURL"] . "\n";
		$pre	.= "---------------------------------------------------------------\n";
		$pre	.= _MMAIL1 . " " . stripslashes($CONF["SiteName"]) . " (" . $G_URL . ")\n";
		$pre	.= _MMAIL2 . " " . $G_URL . "/stories.php?story=" . $rid;
		/*== send it ==*/
		$mailto	=  $CONF["MailingAddress"];
		$subject = "[" . $CONF["SiteName"] . "] " . strip_tags(stripslashes($A["Heading"]));
		@mail($mailto,$subject,
			strip_tags(stripslashes($pre)),
			"From: " . $CONF["SiteName"] . " <" . $CONF["EmailAddress"] . ">\nReturn-Path: <" . $CONF["EmailAddress"] . ">\nX-Mailer: phpWebLog $G_VER");
	}
}

function export_rdf() {
	global	$db,$G_URL,$G_PATH,$CONF,$rdf_lang;
	if ($CONF["Backend"]>0) {
		if (!empty($CONF["BackendFile"])) {
			$outputfile	= $G_PATH . "/backend/" . $CONF["BackendFile"];
		} else {
			$outputfile	= $G_PATH . "/backend/weblog.rdf";
		}
		$rdf_encoding	= "UTF-8";
		$rdf_title	= $CONF["SiteName"];
		$rdf_link	= $G_URL;
		$rdf_descr	= $CONF["SiteSlogan"];

		$sql	= "SELECT * FROM T_Stories ";
		$sql	.= "WHERE Verified = 'Y' ORDER BY Birthstamp DESC limit 10";
		$result	= mysql_query($sql,$db);
       	if (!$file=@fopen($outputfile,"w")) {
			F_error("Unable to write to $outputfile");
		} else {
			fputs ($file, "<?xml version=\"1.0\" encoding=\"$rdf_encoding\"?>\n\n");
			fputs ($file, "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\"\n \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n");
			fputs ($file, "<rss version=\"0.91\">\n\n");
			fputs ($file, "<channel>\n");
			fputs ($file, "<title>$rdf_title</title>\n");
			fputs ($file, "<link>$rdf_link</link>\n");
			fputs ($file, "<description>$rdf_descr</description>\n");
			fputs ($file, "<language>$rdf_lang</language>\n\n");
	
			while ($row=mysql_fetch_array($result)) {
				$title	= "Heading";
				$link	= "Rid";
				$author	= "Author";
				fputs ($file, "<item>\n");
				$title = "<title>" . strip_tags(stripslashes($row[$title])) . "</title>\n";
				$author = "<author>" . strip_tags(stripslashes($row[$author])) . "</author>\n";
				$link  = "<link>" .        F_story($row[$link]) . "</link>\n" .
				         "<description>" . $row["Summary"] . "</description>\n" .
			                 "<pubDate>" .     $row["Birthstamp"] . "</pubDate>\n";
				fputs ($file, $title);
				fputs ($file, $link);
				fputs ($file, "</item>\n\n");
			}
		}
		fputs ($file, "</channel>\n");
		fputs ($file, "</rss>\n");
		fclose($file);
	}
}

function F_error($msg) {
	global $G_PATH,$G_URL;
	$fn	= $G_PATH . "/logs/error.log";
	$err	= date ("m/d/y H:i:s") . " : " . $msg . "\n";
	if (!$file=@fopen($fn,"a")) {
		print "ERROR! Could not write to $fn<br>\n";
		print "Be sure permissions are correct. ";
		print "Run the included fix_permissions.sh script.";
		exit();
	} else {
		fputs($file, $err);
	}
	fclose($file);
	$tmp	= urlencode("<font color=darkred>ERROR! $msg -- " . _PROBLEM . "</font>");
	header("Location:$G_URL/stories.php?msg=$tmp");
}

function F_logAccess($str) {
	global $REQUEST_URI,$G_PATH;
	$fn	= $G_PATH . "/logs/access.log";
	$msg	= date ("m/d/y H:i:s") . " : " . F_getIP() . " : " . $str . "\n";
	if (!$file=@fopen($fn,"a")) {
		 F_error("ERROR! Could not write to file $fn\n");
	} else {
		fputs($file, $msg);
	}
	fclose($file);
}

function F_ifHTML($pl=0) {
	/* Added pl so that the function can determine which parselevel to */
	/* produce - story or comment */
	global	$CONF,$syntax;
	$ParseLevel = ($pl==0) ? $CONF["ParseLevel"] : $CONF["ParseLevelCmt"];
	switch ($ParseLevel) {
		case "1":
			$tmp = _FULLHTML;
		break;
		case "2":
			$tmp = _YESHTML;
			for (reset($syntax);$k=key($syntax);next($syntax)) {
				if (substr($k,0,2) != "\/") {
					$tmp	.= " &lt;" . $k . "&gt;";
				}
			}
		break;
		case "3":
			$tmp = _AUTOHTML;
		break;
		case "4":
			$tmp = _NOHTML;
		break;
		case "5":
			$tmp = _CODEHTML;
		break;
	}
	return $tmp;
}

function F_submit() {
	global	$nonsense;
	$max	= count($nonsense)-1;
	return $nonsense[rand(0,$max)];
}

function F_getIP() {
	global	$REMOTE_ADDR,$REMOTE_HOST;
	if (!empty($REMOTE_HOST)) {
		return $REMOTE_HOST;
	} else {
		return $REMOTE_ADDR;
	}
}

function F_getRid() {
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$time = substr($mtime[1],-4,4);
	$micro = substr($mtime[0],3,-4);
	$str = date("y/m/d",time()) . "/" . $time . $micro;
	return ($str);         
}

function F_author($A) {
        global  $CONF,$G_URL;
	$s	= sprintf("<span class=\"author\"><a href=\"%s/profiles.php?Author=%s&AuthorEmail=%s&AuthorURL=%s\">%s</a></span>",
			$G_URL,
			urlencode(strip_tags(stripslashes($A["Author"]))),
			rot13(strip_tags(stripslashes($A["AuthorEmail"]))),
			stripslashes(urlencode($A["AuthorURL"])),
			stripslashes(strip_tags($A["Author"])));
	return $s;
}

function F_getContent($A) {
	global $db;
	isset($A["Birthstamp"]) ? $date = date("YmdHis",$A["Birthstamp"]) : $date = "1970-01-01 00:00:00";
	isset($A["Timestamp"])  ? $updated = date("YmdHis",$A["Timestamp"]) : $updated = "1970-01-01 00:00:00";;
	$time		= date("YmdHis",time());
	isset($A["Cache"]) ? $cache = $A["Cache"] * 60 : $cache = 600;
	$nextupdate = $updated + $cache;
	if (isset($A["Type"])) {
	if ($A["Type"]==0) {
		# html
		$content	= $A["Content"];
	} elseif ($A["Type"]==1) {
		# rdf import
		if ($nextupdate < $time || $date==$updated) {
			$tmp = F_getrdf($A["URL"]);
			if (!empty($tmp)) { $content = $tmp; }

			/* update db */
			$sql 	= "UPDATE T_Blocks SET Content = '" . urlencode($content) . "' ";
			$sql	.= "WHERE Rid = '" . $A["Rid"] . "'";
			$result = @mysql_query($sql,$db);
			if ($result<1) {  
				F_error("Unable to update RDF block content.");
			}
		} else {
			$content	= urldecode($A["Content"]);
		}
	} elseif ($A["Type"]==2) {
		# url fetch
		if ($nextupdate < $time || $date==$updated) {
            $tmp = F_geturl($A["URL"]);
            if (!empty($tmp)) { $content = $tmp; }

			/* update db */
			$sql 	= "UPDATE T_Blocks SET Content = '" . urlencode($content) . "' ";
			$sql	.= "WHERE Rid = '" . $A["Rid"] . "'";
			$result = @mysql_query($sql,$db);
			if ($result<1) {  
				F_error("Unable to update URL block content.");
			}
		} else {
			$content	= urldecode($A["Content"]);
		}
	    }
	}	
	return $content;
}

function F_dateFormat($datestamp,$str="") {
	global	$G_DATE;
	$tmp	= empty($str) ? $G_DATE : $str;
	$month	= substr($datestamp, 5, 2);
	$day	= substr($datestamp, 8, 2);
	$year	= substr($datestamp, 0, 4);
	$hour	= substr($datestamp, 11,2);
	$min	= substr($datestamp, 14,2);
	$sec	= substr($datestamp, 17,2);
	$ts	= mktime($hour,$min,$sec,$month,$day,$year);
	$date	= strftime($tmp,$ts);
	return $date;
}

function F_getrdf($rdf) {
	$url = parse_url($rdf);
	/* open connection */
	$fp = fsockopen($url['host'], 80);
	if ($fp) {
		socket_set_timeout($fp, 5);
		fputs($fp, "GET " . $url['path'] . "?" . $url['query'] . " HTTP/1.0\r\n");
		fputs($fp, "HOST: " . $url['host'] . "\r\n\r\n");
		$string	= "";
		while(!feof($fp)) {
			$pagetext = fgets($fp,228);
			$string .= chop($pagetext);
		}
		fputs($fp,"Connection: close\r\n\r\n");
		fclose($fp);

		$items = explode("</item>",$string);
		$s	= "<ul>";
		/* return items only */
		for ($i=0;$i<10;$i++) {
			$link = ereg_replace(".*<link>","",$items[$i]);
			$link = ereg_replace("</link>.*","",$link);
			$title = ereg_replace(".*<title>","",$items[$i]);
			$title = ereg_replace("</title>.*","",$title);
			$desc = ereg_replace(".*<description>","",$desc[$i]);
			$desc = ereg_replace("</description>.*","",$desc);
#			if (!empty($title)) {
			if (strcmp($link,$title)) {
				$s	.= "<li><a href=\"$link\">" . strip_tags($title) . "</a></li>\n";
			}
		}
		$s	.= "</ul>";
		return $s;
	}
}

function F_geturl($str) {
	$string = join ('', @file($str));
	if (!empty($string)) {
		return $string;
	}
}

function F_listcats($cat) {
	global	$db;
	$sql	= "SELECT Rid,Name from T_LinkCats ";
	$sql	.= "WHERE Verified = '1'";
	$sql	.= " ORDER BY Name";
	$result	= mysql_query($sql,$db);
	$sel	= $cat=="null" ? "selected" : "";
	$s	= "<option value=\"null\" $sel>Top</option>";
	while (list($id,$name,) = mysql_fetch_row($result)) {
		$sel	= $cat==$id ? "selected" : "";
		$s	.= "<option value=\"$id\" $sel>$name</option>\n";
	}
	return $s;
}

function F_getSearchBox() {
	global	$G_URL;
	$s	= "<form\taction\t= \"" . $G_URL . "\/search.php\"\n";
	$s	.= "\tmethod\t= \"get\">\n";
	$s	.= _FIND . "\n";
	$s	.= "<input\ttype\t= text\n";
	$s	.= "\tsize\t= 15\n";
	$s	.= "\tname\t= \"query\">\n";
	$s	.= "</form>\n";
	return $s;
}

function F_extractLinks($story) {
	global $db;
	$sql	= "SELECT Content FROM T_Stories ";
	$sql	.= "WHERE Rid = '$story'";
	$result = mysql_query($sql,$db);

	$data	= @mysql_result($result,0);
	$s = strtolower($data);
	$pos_start=strpos($s,"<a");
	while($pos_start) {
		$pos_close = strpos($s,"</a",$pos_start);
		if($pos_close) {
			$pos_close += 4;
		} else {
			break;
		}
		$array[] = substr( $data , $pos_start , $pos_close-$pos_start );
		$pos_start=strpos($s,"<a",$pos_close);
	}
	for($i=0;$i<count($array);$i++) {
		eregi('href *= *"?([^" >]*)"?[^>]*>(.*)</a *>?',$array[$i]);
		$out	.= "<li>" . $array[$i] ."</li>";
	}
	return $out;
}

function F_getIndexes($rid) {
	global $db,$G_URL,$CONF;
	$s	= "";
	$sql	.= "SELECT * FROM T_IndexLinks";
	$sql	.= " WHERE ParentRid = '$rid'";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	for ($i=0;$i<$nrows;$i++) {
		$A	= mysql_fetch_array($result);
		if (!empty($A["URL"])) {
			$s	.= $A["Name"] . ": <a target=_blank href=\"$G_URL/portal.php?url=" . urlencode($A["URL"]) . "&what=T_IndexLinks&rid=" . $A["Rid"] . "\">" . $A["URL"] . "</a><br>\n";
		}
	}
	return $s;
}

function F_editIndexes($foo) {
	global $db;
	$s	= "";
	for (reset($foo);$k=key($foo);next($foo)) {
		$s	.= "<tr><td>" . $k . ":</td>";
		$s	.= "<td><input type=text name=\"Links[" . $k . "]\" value=\"" . $foo[$k] . "\" size=40 maxlength=128></td></tr>\n";
	}
	return $s;
}

function F_editIndexes2($rid="") {
	global $db,$CONF;
	$s	= "";
	$sql    = "SELECT Name FROM T_IndexNames";
	$result = mysql_query($sql,$db);   
	while (list($name,) = mysql_fetch_row($result)) {

		$lsql	= "SELECT URL FROM T_IndexLinks";
		$lsql	.= " WHERE ParentRid = '$rid' ";
		$lsql	.= " AND Name = '$name'";
		$lresult = @mysql_query($lsql,$db);
		$url	= @mysql_result($lresult,0);

		$s  .= "<tr><td>" . $name . ":</td>";
		$s  .= "<td><input type=text name=\"Links[" . $name . "]\" value=\"" . $url . "\" size=40 maxlength=128>";
		$s	.= "</td></tr>\n";
	}
	return $s;
}

function F_updateIndexLinks($arr,$rid) {
	global	$db;
	$dsql	= "DELETE FROM T_IndexLinks ";
	$dsql	.= "WHERE ParentRid = '$rid'";
	@mysql_query($dsql,$db);
	for (reset($arr);$k=key($arr);next($arr)) {
		if ( $arr[$k] ) {
			$sql    = "INSERT INTO T_IndexLinks (ParentRid,Name,URL,Hits) ";
			$sql	.= "VALUES (";
			$sql    .= "'$rid',";
			$sql    .= "'$k',";
			$sql    .= "'$arr[$k]',";
			$sql	.= "0)";
			@mysql_query($sql,$db);
		}
	}
}

function F_NP_Story($Repostamp,$Topic="") {
	global   $db,$G_URL,$CONF;

	$tmp 	= "";
	$sql	= "SELECT Rid,Heading FROM T_Stories WHERE Verified = 'Y' AND ";
	$sql	.= "Repostamp < '$Repostamp' ";
	if (!empty($Topic)) {
		$sql	.= "AND Topic = '$Topic' ";
	}
	$sql	.= "ORDER BY Repostamp DESC LIMIT 1";
	$result	= mysql_query($sql,$db);

	if ($A = mysql_fetch_array($result)) {
		$tmp	.= "&lt; <a href=\"".$G_URL."/stories.php?story=".$A["Rid"]."\">".stripslashes($A["Heading"])."</a> | ";
	}

	$sql	= "SELECT Rid,Heading FROM T_Stories WHERE Verified = 'Y' AND ";
	$sql	.= "Repostamp > '$Repostamp' ";
	if (!empty($Topic)) {
		$sql	.= "AND Topic = '$Topic' ";
	}
	$sql	.= "ORDER BY Repostamp ASC LIMIT 1";
	$result	= mysql_query($sql,$db);
	if ($A = mysql_fetch_array($result)){
		$tmp	.= "<a href=\"".$G_URL."/stories.php?story=".$A["Rid"]."\">".stripslashes($A["Heading"])."</a> &gt;";
	}
	return "<center>" . $tmp . "</center>\n";
}


function F_formatSize($size) {
	$ret = "";
	if ($size < 1024) {
		$ret = sprintf("%.2f b",round($size));
	} else {
		$size = $size/1024;
		if ($size < 1024) {
			$ret = sprintf("%.2f kb",round($size));
		} else {
			$size = $size/1024;
			if ($size < 1024) {
				$ret = sprintf("%.2f mb",round($size));
			}
		}
	}
	$ret	= str_replace(".00","",$ret);
	return $ret;
}

function F_fixHost($real_host) {
	$A		= explode(".", $real_host);
	$cnt	= sizeof($A);
	if($cnt>1) {
		if (intval($A[$cnt-1])!=0) {
			$host = substr($real_host,0,strrpos($real_host,".")).".---";
		} else {
			$host = "---".strstr($real_host, ".");
		}
	} else {
		$host = $real_host;
	}
	return $host;
}


?>
