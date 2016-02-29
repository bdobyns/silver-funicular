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

/* if no summary, form summary from story. */
if (empty($Summary)) {
	if ($CONF["SummaryLength"]==0) {
		$summ	= $Content;
	} else {
		$summ	= F_genSummary($Content);
	}
} else {
	$summ	= $Summary;
}

if ($mode==_SUBMIT) {

	$msg	= urlencode(_STORYOK);
	$rid	= F_getRid();
	if ($save=="on") {
		F_saveUser($Author,$AuthorEmail,$AuthorURL);
	}
	if ($CONF["Moderation"]==0 || F_isAdmin()) {
		$valflag	= "'Y',";
	} else {
		$valflag	= "'N',";
	}
	$sql	= "INSERT INTO T_Stories ";
	$sql	.= "(Rid,Verified,Topic,Heading,Summary,Content,Host,Author,AuthorEmail,AuthorURL,EmailComments,Hits,Birthstamp,Repostamp) ";
	$sql	.= "VALUES (";
	$sql	.= "'" . $rid . "',";
	$sql	.= $valflag;
	$sql	.= $CONF["Topics"]>0 ? $Topic : 0;
	$sql	.= ",'" . htmlspecialchars(addslashes($Heading)) . "',";
	$sql	.= "'" . addslashes($summ) . "',";
	$sql	.= "'" . addslashes($Content) . "',";
	$sql	.= "'" . F_getIP() . "',";
	$sql	.= "'" . strip_tags(addslashes($Author)) . "',";
	$sql	.= "'" . addslashes($AuthorEmail) . "',";
	$sql	.= "'" . addslashes($AuthorURL) . "',";
	$sql	.= $EmailComments=="on" ? "1," : "0,";
	$sql	.= "1,";
	$sql	.= "now(),";
	$sql	.= "now()";
	$sql	.= ")";
	$RET	= @mysql_query($sql,$db);
	if ($RET<1) {
		F_error("Unable to insert story $rid");
	} else {

		if (!empty($Links)) {
			for (reset($Links);$k=key($Links);next($Links)) {
				$sql	= "INSERT INTO T_IndexLinks ";
				$sql	.= "(ParentRid,Name,URL,Hits) ";
				$sql	.= "VALUES ('$rid','$k','$Links[$k]',0)";
				@mysql_query($sql,$db);
			}
		}
		if ($CONF["Backend"]>0) {
			export_rdf();
		}
		if ($CONF["MailingList"]>0 && $valflag == "'Y',") {
			F_mailtoList($rid);
		}
		if ($CONF["Moderation"]==2) {
			F_notifyAdmin($Topic,$Heading,$Author,$AuthorEmail);
		}
		header("Location:$G_URL/stories.php?msg=$msg");
	}
	exit();
}

include("./include/header.inc.php");


$HTTP_POST_VARS["Birthstamp"] = date("Y-m-d H:i:s",time());
$HTTP_POST_VARS["Host"] = F_getIP();
F_drawStory($HTTP_POST_VARS);

$VAR["Heading"] = _PRESTORY;
$VAR["Content"] = _PRETEXT;
$VAR["Content"] .= "
<form
	action	= \"$G_URL/preview.php\"
	name	= \"Preview\"
	method	= post
	onsubmit= \"return validatePreview()\">
<table	width	= 100%
	cellspacing	= 0
	cellpadding	= 3
	border	= 0>
<tr>
<td>" . _TITLE . ":</td>
<td><input	type	= text
	size	= 40
	maxlength=48
	name	= Heading
	value	= \"" . strip_tags(stripslashes($Heading)) . "\"></td>
</tr>";

if ($CONF["Topics"]>0) {
$VAR["Content"] .= "
	<tr>
	<td>" . _TOPIC . ":</td>
	<td>
	<select	name	= \"Topic\">";
$tmp = !empty($Topic) ? $Topic : 0;
$VAR["Content"] .= F_topicsel($tmp,"post");
$VAR["Content"] .= "
	</select>
	</tr>";
}

if ($CONF["SummaryLength"]>0) {
$VAR["Content"] .= "
<tr>
<td	colspan	= 2>" . _SUMMARY . ":<br>
<textarea
	name	= Summary
	rows	= 5
	cols	= 80
	wrap	= virtual>" . stripslashes($summ) . "</textarea></td>
</tr>";
}

$VAR["Content"] .= "
<tr>
<td	colspan	= 2>" . _STORY . ":<br>
<textarea
	name	= Content
	rows	= 10
	cols	= 80
	wrap	= virtual>" . stripslashes($Content) . "</textarea></td>
</tr>";

/*== off-site link / external url ==*/
if (!empty($Links)) {
	$VAR["Content"] .= F_editIndexes($Links);
}

$VAR["Content"] .= "
<tr>
<td	colspan	= 2>
<input	type	= hidden
	name	= Author
	value	= \"" . $Author . "\">
<input	type	= hidden
	name	= AuthorEmail
	value	= \"" . $AuthorEmail . "\">
<input	type	= hidden
	name	= AuthorURL
	value	= \"" . $AuthorURL . "\">
<input	type	= hidden
	name	= save
	value	= \"" . $save . "\">
<input	type	= hidden
	name	= EmailComments
	value	= \"" . $EmailComments . "\">
<input	type	= hidden
	name	= what
	value	= \"news\">
<input	type	= submit
	name	= mode
	value	= \"" . _SUBMIT . "\">
<input	type	= submit
	name	= mode
	value	= \"" . _PREVIEW . "\"></td>
</tr>
</table>
</form>";

F_drawMain($VAR);


include("./include/footer.inc.php");
?>
