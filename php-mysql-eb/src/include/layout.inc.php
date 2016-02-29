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

  /* To use PHP in a section of phpWebLog, find the line that starts
   *     print $tmp;
   * and replace it with the text inside the comment
   *     eval("?>" . $tmp);
   * You will need to do this in each section where you want PHP enabled.
   * This could have security implications, so be sure that you only do
   * this for sections where the admin has complete control.  You are
   * strongly advised to not use this for comments.
   */

function F_drawStory($A,$whe="") {
	global	$G_URL,$CONF,$G_PATH,$LAYOUT,$PHP_SELF;
	$heading	= stripslashes($A["Heading"]);

	$author = F_author($A);
	$T		= F_getTopic($A["Topic"]);
	$topic	= sprintf("<a\thref\t=\"$G_URL/stories.php?topic=%s\">%s</a>",$A["Topic"],$T["Topic"]);
	if (!empty($T["ImgURL"])) {
		$icon	= sprintf("<a href=\"$G_URL/stories.php?topic=%s\">",$A["Topic"]);
		$icon	.= "<img\talign\t= \"right\"\n";
		$icon	.= "\tsrc\t= \"" . $T["ImgURL"] . "\"\n";
		$icon	.= "\tborder\t= \"0\"\n";
		$icon	.= "\talt=\"" . $T["AltTag"] . "\"></a>\n";
	}
	$date		= F_dateFormat($A["Birthstamp"]);
	$article	= $A["Content"];
	$link		= F_getIndexes($A["Rid"]);
	$content 	= F_parseContent($article,$CONF["ParseLevel"]);
	if ($A["Repostamp"]>$A["Birthstamp"]) {
		$content	.= "<br><i>" . _REPOSTED . " " . F_dateFormat($A["Repostamp"]) . "</i>";
	}
	if (basename($PHP_SELF)!="print.php" && basename($PHP_SELF)!="preview.php") {
		$content	.= "<br>\n" . F_admin("T_Stories",$A["Rid"],$whe);
	}

	$address    = "(" . F_fixHost($A["Host"]) . ")";
	$templatefile	= "$G_PATH/backend/template/" . $LAYOUT["TMPL_Story"] . "/story.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $LAYOUT["TMPL_Story"] . "/images";

	$templ_arr	= @file($templatefile);
	$template	= @implode("", $templ_arr);
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- STORY START " . $LAYOUT["TMPL_Story"] . " -->\n";
	print $tmp;	/* eval("?>" . $tmp); */
	print "\n<!-- STORY END -->\n";
	if (basename($PHP_SELF)!="moderate.php") F_notice(F_NP_Story($A["Repostamp"]));

}

function F_drawSummary($A,$whe="") {
	global	$G_URL,$CONF,$G_PATH,$LAYOUT;
	$heading	= sprintf("<a class=\"title\" href=\"%s\">%s</a>\n",F_story($A["Rid"]),stripslashes($A["Heading"]));
	$author		= F_author($A);
	$T			= F_getTopic($A["Topic"]);
	$topic		= sprintf("<a href=\"$G_URL/stories.php?topic=%s\">%s</a>",$A["Topic"],$T["Topic"]);
	$icon = "";
	if (!empty($T["ImgURL"])) {
		$icon	= sprintf("<a href=\"$G_URL/stories.php?topic=%s\">",$A["Topic"]);
		$icon	.= "<img\talign\t= \"right\"\n";
		$icon	.= "\tsrc\t= \"" . $T["ImgURL"] . "\"\n";
		$icon	.= "\tborder\t= \"0\"\n";
		$icon	.= "\talt=\"" . $T["AltTag"] . "\"></a>\n";
	}
	$date		= F_dateFormat($A["Birthstamp"]);

	$content 	= F_parseContent($A["Summary"],$CONF["ParseLevel"]);
	/*== append [...] if summary != story ==*/
	if ($CONF["SummaryLength"]>0 && $A["Summary"] != $A["Content"]) {
		$content	.= sprintf(" <a href=\"%s/stories.php?story=%s\">[...]</a>",$G_URL,$A["Rid"]);
	}
	if ($A["Repostamp"]>$A["Birthstamp"]) {
		$content	.= "<br><i>" . _REPOSTED . " " . F_dateFormat($A["Repostamp"]) . "</i>";
	}
	$content	.= "<br>\n" . F_admin("T_Stories",$A["Rid"],$whe);

	$link		= F_getIndexes($A["Rid"]);
	$address    = "(" . F_fixHost($A["Host"]) . ")";

	$ncmts		= F_count("T_Comments","TopRid",$A["Rid"]);
	$vcmts		= $ncmts > 1 ? _COMMENTS : _COMMENT;
	if ($ncmts>0) {
		$tmp	= "(" . $ncmts . " " . $vcmts . ")";
	} elseif ($A["NoComments"]==0) {
		$tmp	= "-- " . _COMMENT;
	} else {
		$tmp	= "";
	}
	if (($CONF["Comments"]>0) && ($A["NoComments"]!=1)) {
		$comments	= sprintf("<a href=\"%s/stories.php?story=%s\">" . _READMORE . " %s</a>\n",$G_URL,$A["Rid"],$tmp);
	} else {
		$comments	= sprintf("<a href=\"%s/stories.php?story=%s\">" . _READMORE . "</a>\n",$G_URL,$A["Rid"]);
	}
	$modified	= F_lastModified($A["Rid"]);
	$views		= _VIEWS . " " . $A["Hits"];
	$templatefile	= "$G_PATH/backend/template/" . $LAYOUT["TMPL_Summary"] . "/summary.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $LAYOUT["TMPL_Summary"] . "/images";

	$templ_arr	= @file($templatefile);
	$template	= @implode("", $templ_arr);
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- SUMMARY START " . $LAYOUT["TMPL_Summary"] . " -->\n";
	print $tmp;	/* eval("?>" . $tmp); */
	print "\n<!-- SUMMARY END -->\n";
}

function F_drawBlock($A) {
	global	$G_URL,$G_PATH,$LAYOUT;
	if (isset($A["Display"]) && $A["Display"]=="l") {
		$baz	= $LAYOUT["TMPL_LeftBlock"];
	} elseif (isset($A["Display"]) && $A["Display"]=="r") {
		$baz	= $LAYOUT["TMPL_RightBlock"];
	} else {
		if ($LAYOUT["BlocksAlign"]=="right") {
			$baz	= $LAYOUT["TMPL_RightBlock"];
		} else {
			$baz	= $LAYOUT["TMPL_LeftBlock"];
		}
	}
	$heading	= stripslashes($A["Heading"]);
	$content	= stripslashes(F_getContent($A));
	isset($A["Birthstamp"]) ? $date = F_dateFormat($A["Birthstamp"]) : $date ='1970-01-01 00:00:00 ';
	$templatefile	= "$G_PATH/backend/template/" . $baz . "/block.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $baz . "/images";

	$template	= implode("", file($templatefile));
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- BLOCK START " . $baz . " -->\n";
	print $tmp;	/* eval("?>" . $tmp); */
	print "\n<!-- BLOCK END -->\n";
}

function F_drawMain($A) {
	global	$G_PATH,$LAYOUT,$G_URL;
	$heading	= stripslashes($A["Heading"]);
	$content	= stripslashes($A["Content"]);
	$templatefile	= "$G_PATH/backend/template/" . $LAYOUT["TMPL_Main"] . "/main.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $LAYOUT["TMPL_Main"] . "/images";

	$templ_arr	= @file($templatefile);
	$template	= @implode("", $templ_arr);
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- MAIN START " . $LAYOUT["TMPL_Main"] . " -->\n";
	print $tmp;	/* eval("?>" . $tmp); */
	print "\n<!-- MAIN END -->\n";
}


function usa_money_format($fmt,$value) {
    # this is a low-budget USA-only version of the local-aware 
    # money_format() which is ONLY available in 4.3.0 and up, 
    # and which is a wrapper around the glibc strfmon()

    # $value = 0x7ffffffffffff;
    if ($value > 1000000000) {
	$bils= $value/1000000000;
	$mils= ($value % 1000000000)/1000000;
	$thou= ($value % 1000000)/1000;
	$ones= ($value % 1000);    
	$rstr = sprintf("$ %d,%03d,%03d,%03d",$bils,$mils,$thou,$ones );
    } elseif ($value > 1000000) {
	$mils= $value/1000000;
	$thou= ($value % 1000000)/1000;
	$ones= ($value % 1000);    
	$rstr = sprintf("$ %d,%03d,%03d",$mils,$thou,$ones );
    } elseif ($value > 1000) {
	$thou= ($value % 1000000)/1000;
	$ones= ($value % 1000);    
	$rstr = sprintf("$ %d,%03d",$thou,$ones );
    } else {
	$rstr = sprintf("$ %d",$value );
    }
    return $rstr;
}


function F_drawHeader() {
	global	$G_DB, $G_HOST, $G_PORT, $G_USER, $G_PASS,
                $DATA_DB, $DATA_HOST, $DATA_PORT, $DATA_USER, $DATA_PASS,
                $G_PATH,$G_URL,$G_DATE,$CONF,$LAYOUT,$db,$navigation;
	$siteemail	= stripslashes($CONF["EmailAddress"]);
	$sitename	= stripslashes($CONF["SiteName"]);
	$siteowner	= stripslashes($CONF["SiteOwner"]);
	$siteslogan	= stripslashes($CONF["SiteSlogan"]);
	$today		= getdate();
	$currentyear	= $today["year"]; 
#        $countries      = stripslashes($CONF["Countries"]);
#        $podcastsubs    = stripslashes($CONF["PodCastSubs"]);
        $copyrightstart = stripslashes($CONF["CopyrightStart"]);
        
        if (strlen($DATA_DB)) {       
            /* query the l'peef data database so that 
	     * we see a "live" thermometer of progress
	     */
            $ddb = mysql_pconnect($DATA_HOST.":".$DATA_PORT,$DATA_USER,$DATA_PASS);  
            $sresult = mysql_db_query($DATA_DB,"SELECT COUNT(MoneyDonorNumber) AS Donors FROM npa_Money WHERE Epoch = $currentyear");
            $totdonors      = mysql_result($sresult,0,"Donors"); mysql_free_result($sresult);

	    $sresult = mysql_db_query($DATA_DB,"SELECT SUM(MoneyDollars) AS Dollars FROM npa_Money WHERE Epoch = $currentyear AND
						 ( MoneyDonorCategory NOT LIKE 'Sponsorship' ) AND 
						 ( MoneyDonorCategory NOT LIKE 'Advertising' ) " );

	    $totdollars     = mysql_result($sresult,0,"Dollars"); mysql_free_result($sresult);
	    $totdollars     = usa_money_format( "%", $totdollars);	
	    $keepyear = $currentyear;

	    /* reset the database to the real one */
	    $db	= mysql_pconnect($G_HOST.":".$G_PORT,$G_USER,$G_PASS);
	    mysql_select_db($G_DB);
	}

	if (!empty($LAYOUT["LogoURL"])) {
		$logo	= "<a\thref\t= \"" . $G_URL . "/\"><img\n";
		$logo	.= "\talt\t= \"" . $CONF["SiteName"] . " - " . $CONF["SiteSlogan"] . "\"\n";
		$logo	.= "\tsrc\t= \"" . $LAYOUT["LogoURL"] . "\"\n";
		$logo	.= "\tborder\t= 0></a>\n";
	} else {
		$logo	= "<h1>" . $CONF["SiteName"] . "</h1>\n";
	}
	$siteslogan	= stripslashes($CONF["SiteSlogan"]);

	$searchbox	= "<form\taction\t= \"" . $G_URL . "/search.php\"\n";
	$searchbox	.= "\tmethod\t= \"get\">\n";
	$searchbox	.= "<input\ttype\t= text\n";
	$searchbox	.= "\tsize\t= 15\n";
	$searchbox	.= "\tname\t= \"query\">\n";
    	$searchbox	.= _FIND . "\n";
	$searchbox	.= "</form>\n";


	$date		= strftime($G_DATE,time());
	/*== CONSTRUCT NAVIGATION BAR ==*/
        # home
	$navigation   = "<a class=\"nav\" href = \"" . $G_URL . "/index.php\">" . _HOME . "</a>\n";
	# news index
	$navigation  .= " | ";
	$navigation  .= "<a class=\"nav\" href = \"" . $G_URL . "/stories.php\">" . _INDEX . "</a>\n";
	# search
	$navigation .= " | ";
	$navigation .= "<a class=\"nav\" href = \"" . $G_URL . "/search.php\">" . _SEARCH . "</a>\n";
	# if archive is enabled
	if ($CONF["Archive"]>0) {
		$navigation .= " | ";
		$navigation .= "<a class=\"nav\" href = \"" . $G_URL . "/archive.php\">" . _ARCHIVE . "</a>\n";
	}
	# contribute news
	if ($CONF["AllowContrib"]>0) {
		$navigation	.= " | ";
		$navigation	.= "<a class=\"nav\" href = \"" . $G_URL . "/contrib.php\">" . _CONTRIB . "</a>\n";
	}
	# if links are enabled
	if ($CONF["Links"]>0) {
	    // only display if there are links, or if you are admin
	    if (F_count("T_Links") > 0 || F_isAdmin()) {
		$navigation .= " | ";
		$navigation .= "<a class=\"nav\" href = \"" . $G_URL . "/links.php\">" . _LINKS . "</a>\n";
	    }
	}
        // Show The Tools Link If You're Authorized
        if (F_isTools() || F_isAdmin()) {
		$navigation .= " | ";
		$navigation .= "<a class=\"nav\" href = \"" . $G_URL . "/tools.php\">" . _TOOLS . "</a>\n";
	}
	if (F_count("T_PollQuestions")>0) {
		$navigation	.= " | ";
		$navigation	.= "<a class=\"nav\" href = \"" . $G_URL . "/pollbooth.php\">" . _POLLS . "</a>\n";
	}
	# list all pages
	$sql	= "SELECT Heading from T_Blocks ";
	$sql	.= "WHERE Display = 'p' ORDER BY OrderID";
	$result	= @mysql_query($sql,$db);
	if ($result) {
		while (list($row,) = mysql_fetch_row($result)) {
			$navigation	.= "|" . " <a class=\"nav\" href = \"" . $G_URL . "/pages.php?page=" . 
				urlencode($row) . "\">" . $row . "</a>\n";
		}
	}
	# if stats are enabled
	if ($CONF["SiteStats"]>0) {
		$navigation .= " | ";
		$navigation .= "<a class=\"nav\" href = \"" . $G_URL . "/stats.php\">" . _STATS . "</a>\n";
	}
	# contact
	$navigation .= " | ";
	$navigation .= "<a class=\"nav\" href\t= \"" . $G_URL . "/profiles.php?Author=" . urlencode($CONF["SiteOwner"]) . 
		"&AuthorEmail=" . rot13($CONF["EmailAddress"]) . 
		"&AuthorURL=" . $G_URL . "\">" . _CONTACT . "</a>\n";

	$templatefile	= "$G_PATH/backend/template/" . $LAYOUT["TMPL_Header"] . "/header.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $LAYOUT["TMPL_Header"] . "/images";

	$templ_arr	= @file($templatefile);
	$template	= @implode("", $templ_arr);
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- HEADER START " . $LAYOUT["TMPL_Header"] . " -->\n";
	print $tmp;	/* eval("?>" . $tmp); */
	print "\n<!-- HEADER END -->\n";
}

function F_drawFooter() {
	global	$G_PATH,$CONF,$LAYOUT,$G_URL,$navigation;
	$siteemail	= stripslashes($CONF["EmailAddress"]);
	$sitename	= stripslashes($CONF["SiteName"]);
	$siteowner	= stripslashes($CONF["SiteOwner"]);
	$siteslogan	= stripslashes($CONF["SiteSlogan"]);
#        $countries      = stripslashes($CONF["Countries"]);
#        $podcastsubs    = stripslashes($CONF["PodCast"]);
        $copyrightstart = stripslashes($CONF["CopyrightStart"]);
	$today		= getdate();
	$currentyear	= $today["year"];
	$templatefile	= "$G_PATH/backend/template/" . $LAYOUT["TMPL_Footer"] . "/footer.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $LAYOUT["TMPL_Footer"] . "/images";

	$templ_arr	= @file($templatefile);
	$template	= @implode("", $templ_arr);
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- FOOTER START " . $LAYOUT["TMPL_Footer"] . " -->\n";
	print $tmp;	/* eval("?>" . $tmp); */
	print "\n<!-- FOOTER END -->\n";
}

function F_drawComment($A,$where) {
	global	$G_PATH,$G_URL,$CONF,$LAYOUT;
	if ($A["Author"]=="Anonymous" && $CONF["AllowAnon"]>0) {
		$author	= strip_tags(stripslashes($A["Author"]));
	} else {
		$author	= sprintf("<a href = \"%s/profiles.php?Author=%s&AuthorEmail=%s&AuthorURL=%s\">%s</a>",
			$G_URL,
			strip_tags(urlencode($A["Author"])),
			rot13(strip_tags($A["AuthorEmail"])),
			urlencode($A["AuthorURL"]),
			strip_tags(stripslashes($A["Author"])));
	}
	$address    = "(" . F_fixHost($A["Host"]) . ")";
	$content	= $A["Content"];
	$date       = F_dateFormat($A["Birthstamp"]);
	if ($CONF["Comments"]==2) {
		$reply		= "[ <a \tonclick\t= \"JS_makeParent('" . $A["Rid"] . "')\" ";
		$reply		.= "\thref=\"#comments\">" . _REPLY . "</a>";
		$reply		.= " | ";
		$reply		.= "<a \tonclick\t= \"JS_makeParent('" . $A["ParentRid"] . "')\" ";
		$reply		.= "\thref=\"#comments\">" . _PARENT . "</a> ]";
	}
	$templatefile	= "$G_PATH/backend/template/" . $LAYOUT["TMPL_Comment"] . "/comment.tmpl";
	$imagedir	= "$G_URL/backend/template/" . $LAYOUT["TMPL_Comment"] . "/images";

	$templ_arr	= @file($templatefile);
	$template	= @implode("", $templ_arr);
	$template	= addslashes($template);
	$template	= "\$tmp=\"".$template."\";";
	eval($template);
	print "\n<!-- COMMENT START " . $LAYOUT["TMPL_Comment"] . " -->\n";
	print $tmp;
	print "\n<!-- COMMENT END -->\n";
}

function export_layout($array) {
	global	$db,$G_URL,$G_PATH,$G_VER,$CONF;
	$outputfile	= $G_PATH . "/backend/layouts/" . $array["Layout"] . ".xlay";

	if (!$file=fopen($outputfile,"w")) {
		F_error("Unable to write to $outputfile\n");
	} else {
		fputs ($file, "<?xml version=\"1.0\"?>\n\n");
		fputs ($file, "<layout>\n\n");
		fputs ($file, "<info>\n");
		fputs ($file, "<author>$array[Author]</author>\n");
		fputs ($file, "<description>$array[Description]</description>\n");
		fputs ($file, "<version>$G_VER</version>\n");
		fputs ($file, "</info>\n\n");

		for (reset($array);$k=key($array);next($array)) {
			if (substr($k,0,5)=="XLAY_") {
				$tmp	= substr($k,5);
				fputs ($file, "<item>\n");
				$element = "<element>" . $tmp . "</element>\n";
				$value  = "<value>" . $array[$k] . "</value>\n";
				fputs ($file, $element);
				fputs ($file, $value);
				fputs ($file, "</item>\n\n");
			}
		}
	}
	fputs ( $file, "</layout>\n");
	fclose( $file );
}

function import_layout($layout) {
	global	$G_PATH;
	$filename	= $G_PATH . "/backend/layouts/" . $layout . ".xlay";
	$fpread = fopen($filename,"r");
	if(!$fpread) {
		F_error("Unable to read layout file $filename<br>\n");
	} else {
		#echo "<p>Reading file: $filename<br>";
		$XLAY	= array();
		while(!feof($fpread) ) {
			$buffer 	= ltrim(chop(fgets($fpread, 256)));
			if ($buffer == "<info>") {
				$author	= ltrim(chop(fgets($fpread, 256)));
				$author = ereg_replace( "<author>", "",$author);
				$author = ereg_replace( "</author>", "",$author);
				$XLAY["Author"] = $author;
				$desc	= ltrim(chop(fgets($fpread, 256)));
				$desc = ereg_replace( "<description>", "",$desc);
				$desc = ereg_replace( "</description>", "",$desc);
				$XLAY["Description"] = $desc;
				$version = ltrim(chop(fgets($fpread, 256)));
				$version = ereg_replace( "<version>", "",$version);
				$version = ereg_replace( "</version>", "",$version);
				$XLAY["Version"] = $version;
			} elseif ($buffer == "<item>") {
				$element = ltrim(chop(fgets($fpread, 256)));
				$value	= ltrim(chop(fgets($fpread, 256)));

				$element = ereg_replace( "<element>", "",$element);
				$element = ereg_replace( "</element>", "",$element);
				$value = ereg_replace( "<value>", "",$value);
				$value = ereg_replace( "</value>", "",$value);
				$XLAY[$element] = $value;
			}
		}
		fclose($fpread);
	}
	return $XLAY;
}

?>
