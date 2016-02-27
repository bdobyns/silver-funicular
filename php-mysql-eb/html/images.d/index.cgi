#!/bin/bash
PATH=/bin:/usr/bin:/sbin:/usr/sbin

echo Content-type: text/html
echo

echo '<body><table border=1>'
echo '<tr> '
pix=0
for img in *.[Gg][iI][Ff] *.[Jj][Pp][Gg] *.[Jj][Pp][Ee][Gg]
do
 if [ -f "$img" ] ; then	
   pix=$[ $pix + 1 ]
   if [ $pix -gt 5 ] ; then
      echo ' </tr><tr>'
      pix=0
   fi
   echo '  <td><a href=/image.d/'$img'><img border=0 src=/image.d/'$img'><br>'$img'</a></td>'
 fi
done
echo '</tr></table>'

