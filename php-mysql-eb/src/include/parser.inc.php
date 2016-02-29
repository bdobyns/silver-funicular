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

=*=========================================================================

parser.php3 originally from the Terra Cotta system available at
http://www.terracottasoftware.com which was released under the GPL.

=*=======================================================================*/


//input should be url-decoded string, \n-separated
//for lines. Output will be HTML string.

// parselevel is:

// 0 = no action. pass-through. Included for completeness
// 1 = allow full html, do in-lining and syntax-parsing
// 2 = reduced html (bold, links, etc. by syntax array) with line-breaks
// 3 = micro html (only line-breaks, in-line linking)
// 4 = no html (all stripped, only line-breaks)
// 5 = no html whatsoever, no line breaks added. (included for "completeness")

function F_parseContent($input, $parselevel) {

  global $syntax;
  //abort out on simple cases
  if ($parselevel == 0) {
     return stripslashes($input);
  }
  if ($parselevel == 5) {
     return htmlspecialchars(stripslashes($input));
  }

  $htmlOut = "";

  $strings = explode("\n",$input);
  $lines = count($strings);

    for ($i=0; $i<$lines; $i++) {

      //working memory...add spaces to string ends to make regexps easier
      $ostring = " ".trim($strings[$i])." \n";
    //  print "pristine |$ostring|\n\n";

      //find tags we recognize with this parselevel and subst them to
      //<tag> ===> [tag]
      //and then subst the rest < --> &lt; > --> &gt;
      if ($parselevel == 2) {
         reset($syntax);
         $k = current($syntax);
         while ($k) {
            $k = key($syntax);
            $htmlk = "<(" . $k . ")>";   //construct the html key
            $ostring = eregi_replace($htmlk,"[\\1]",$ostring);
            $k = next($syntax);
         }
         $ostring = htmlspecialchars($ostring);
      }

//      print "ps|$ostring|sp\n\n";

      //interpret tags for parse levels 1 and 2
      if ($parselevel <= 2) {
        reset($syntax);
        $k = current($syntax);
        while ($k) {
          $k = key($syntax);
          $v = $syntax[$k];

          $bracketk = "\[" . $k . "\]";
          $ostring = eregi_replace($bracketk,$v,$ostring);

          $bracketk = "<" . $k . ">";
          $ostring = eregi_replace($bracketk,$v,$ostring);

          $k = next($syntax);
        }
      }
      
      //for level 3,4, start with htmlspecialchars
      if ($parselevel >= 3) {
        $ostring = htmlspecialchars($ostring);
      }


      //do in-lining of http://xxx.xxx to link, xxx@xxx.xxx to email
      if ($parselevel < 4) {
         //mailto
         $ostring = eregi_replace("([ ]+)([[:alnum:]\.\-]+\@[[:alnum:]\-]+\.[\.[:alnum:]]+)([^\.[:alnum:]])","\\1<a href=\"mailto:\\2\">\\2</A>\\3", $ostring);

         //urls
         $ostring = eregi_replace("([ ]+)((http|https|ftp)\:\/\/[[:alnum:]\-]+\.[[:alnum:]\-]+[^[:space:]]*)([ ]+)","\\1<a target=_blank href=\"\\2\">\\2</A>\\4", $ostring);
      }

      //do the blank-line ---> <P> substitution.
      //everybody gets this; if you don't want even that, just save
      //the htmlspecialchars() version of the input
      if (strlen(trim($strings[$i]))==0) {
        $ostring = "<p>";
      }

      if ($parselevel > 1) {
            $htmlOut = $htmlOut . trim($ostring) . "<br>\n";
      } else {
            $htmlOut = $htmlOut . trim($ostring) . "\n";
      }

    }

  return stripslashes($htmlOut);
}

?>
