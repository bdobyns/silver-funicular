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

/*== dont allow contributions if they are disabled except by admin ==*/
if (!F_isAdmin()) {
	$msg	= urlencode(_NOFILES);
	header("location:$G_URL/stories.php?msg=$msg");
	exit();
}

include("./include/header.inc.php");

Function F_linkcatsel($topic="1",$type="") {
	global	$db;
	$sql	= "SELECT * FROM T_LinkCats WHERE Verified = 1 ORDER BY Name ASC";
#	$sql	= "SELECT * FROM T_LinkCats WHERE Verified = 1 ORDER BY Rid DESC";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	$tmp	= "";
	for ($i=0;$i<$nrows;$i++) {
		$A	= mysql_fetch_array($result);
		$ass	= ($A["Rid"]==$topic ? "selected" : "");
		$tmp	.= sprintf("<option\tvalue\t= \"%s\" %s>%s</option>\n",
				$A["Rid"],$ass,$A["Name"]);
	}
	return $tmp;
}

Function F_toolcatsel($topic="1",$type="") {
	global	$db;
	$sql	= "SELECT * FROM T_ToolCats WHERE Verified = 1 ORDER BY Name ASC";
	$result	= mysql_query($sql,$db);
	$nrows	= mysql_num_rows($result);
	$tmp	= "";
	for ($i=0;$i<$nrows;$i++) {
		$A	= mysql_fetch_array($result);
		$ass	= ($A["Rid"]==$topic ? "selected" : "");
		$tmp	.= sprintf("<option\tvalue\t= \"%s\" %s>%s</option>\n",
				$A["Rid"],$ass,$A["Name"]);
	}
	return $tmp;
}

if (strlen($type) == 0) $der = "local";
else $der = $type;

// Test to see if we have a file to dispose of
if (strlen($uploadfile) >0 ) {
    // This move_uploaded_file() actually writes the uploaded file
    $LocalFile = $_FILES['uploadfile']['name'];
    $LocalName = "./$der/$LocalFile";
    $LocalURL = "$G_URL/$der/$LocalFile";
    if (is_file($LocalName)) {
	$existf = "<p><table><tr><th>WARNING:</th><td>Existing File $LocalFile Replaced.</td></tr>
		<tr><td>&nbsp;</td<td>Previous file date for $LocalFile was ". date("d-M-Y H:i",filemtime($LocalName)) .
	       "</td></tr></table>";
    }
    move_uploaded_file($_FILES['uploadfile']['tmp_name'],$LocalName)
      or die("Cannot move ".$LocalFile."<PRE>");

    $message = system( " cd $type ; cvs add $LocalFile ; cvs commit -m \"$Description\" $LocalFile");
    $VAR["Heading"]  = _UPLOADMSG ." $LocalFile";
    $VAR["Content"]  = _FILEOK ."<br><A HREF=\"$LocalURL\">$LocalURL</a> \n";
    $VAR["Content"] .= $existf;

    if ($type == "local") {
	if(F_isAdmin()) { $appr = 1; } else { $appr = 0; }
	$sql	= "DELETE FROM T_Links 	 WHERE Url='$LocalURL' ; " ;
	$result = mysql_query($sql,$db);	  
	$sql	= "INSERT into T_Links ";
        $sql	.= "(Rid,CatRid,Url,Name,Verified,Hits,";
        $sql	.= "SubmitDate) values ";
	if (strlen($Description) == 0) $Description = $LocalFile;
	$sql	.= "('" . F_getRid() . "','$Topic','$LocalURL','$Description',";
	$sql	.= "'$appr',0,now())";
	$result = mysql_query($sql,$db)
	  or die("Cannot '$sql' <br>". mysql_error($db));
	$VAR["Content"] .= "<p>Added as a document in this <a href=\"$G_URL/links.php?node=$Topic\">Document Category</a>";
    } elseif ($type == "tools") {
	if(F_isAdmin()) { $appr = 1; } else { $appr = 0; }
	$sql	= "DELETE FROM T_Tools 	 WHERE Url='$LocalURL' ; " ;
	$result = mysql_query($sql,$db);
	$sql	= "INSERT into T_Tools ";
	$sql	.= "(Rid,CatRid,Url,Name,Verified,Hits,";
	$sql	.= "SubmitDate) values ";
	if (strlen($Description) == 0) $Description = $LocalFile;
	$sql	.= "('" . F_getRid() . "','$ToolTopic','$LocalURL','$Description',";
	$sql	.= "'$appr',0,now())";
	$result = mysql_query($sql,$db)
	  or die("Cannot '$sql' <br>". mysql_error($db));
	$VAR["Content"] .= "<p>Added as a tool in this <a href=\"$G_URL/tools.php?node=$ToolTopic\">Tool Category</a>";
    }
} else {
    $VAR["Heading"]  = _UPLOADMSG;
    $VAR["Content"]  = "<FORM enctype=\"multipart/form-data\" ACTION=\"$PHP_SELF\" METHOD=\"POST\"> 
    <TABLE> 
      <TR> 
	<td align=right><b> "._FILENAME." </b></td>
	<TD> <INPUT TYPE=\"FILE\" NAME=\"uploadfile\" SIZE=65 > </b></td>
      </TR> 
      <TR> 
	<td align=right><b> "._FILEDESC." </b></td> 
	<TD> <INPUT TYPE=TEXT NAME=\"Description\" SIZE=65 > </b></td>
      </TR> 
      <tr>
        <TD align=right><b> Docs <INPUT TYPE=RADIO NAME=type value=\"local\"></b></td>
	<TD><b> Document Category (pdf):
	<select	name	= \"Topic\">";
        $VAR["Content"] .= F_linkcatsel(0,"post");
        $VAR["Content"] .= "
	</select> </b></td>
     </tr>
      <tr>
        <TD align=right><b> Tool <INPUT TYPE=radio NAME=type value=\"tools\"></b></td>
	<TD><b> Tool Category (word, excel):
        <select	name	= \"ToolTopic\">";
        $VAR["Content"] .= F_toolcatsel(0,"post");
        $VAR["Content"] .= "
	</select> </b></td>
     </tr>";
      /* these dir names ought to be read from a table in the database, and then driven dynamically. 
       * dirs, links and tools should not be distinct, but should be renameable, and just a checkbox
       * of some sort away from podcast or images.d - way too much work for right now.  -b */
     if ($G_DB == "nothingtodeclare")
      $VAR["Content"] .= "
      <tr> <TD align=right><INPUT TYPE=radio NAME=type value=\"images.d\"></td> <td><b>Image</b></td></tr>
      <tr> <TD align=right><INPUT TYPE=radio NAME=type value=\"podcast/holding/story\" checked></td> <td><b>Story Podcast</b></td></tr>
      <tr> <TD align=right><INPUT TYPE=radio NAME=type value=\"podcast/holding/poetry\"></td> <td><b>Poetry Podcast</b></td></tr>
      <tr> <TD align=right><INPUT TYPE=radio NAME=type value=\"podcast/holding/fairy\"></td> <td><b>Childrens Podcast</b></td></tr> ";
     else $VAR["Content"] .= "
      <tr> <TD align=right><INPUT TYPE=radio NAME=type value=\"images.d\"></td> <td><b>Image</b></td></tr>";
      $VAR["Content"] .= "
     <TR> 
	<TD COLSPAN=2 align=center > 
	<INPUT TYPE=SUBMIT VALUE=Upload >       </b></td> 
      </TR> 
    </TABLE> 
  </FORM>  ";
}

F_drawMain($VAR);

include("./include/footer.inc.php");

?>
