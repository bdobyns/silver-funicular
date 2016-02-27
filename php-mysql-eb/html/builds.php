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

# include("./include/common.inc.php");
# include("./include/header.inc.php");

if ($handle = opendir('.')) {
    $filetypes = array(".iso",".dfp");
    foreach ($filetypes as $what) {
        echo "\n<H1> $what Images Available </h1>\n<ul>\n";

        while (false !== ($file = readdir($handle))) { 
	    if (strstr($file,$what))
	        echo "<li><a href=$file>$file</a>\n";
        }
        Rewinddir($handle); 
        echo "</ul>\n<p>";
    }
    closedir($handle); 
}

# include("./include/footer.inc.php");
?>
