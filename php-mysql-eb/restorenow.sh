#!/bin/bash
# $Id: restorenow.sh,v 1.1 2005/12/09 01:51:34 cvs Exp $

. ~/.bash_profile

   WHERE=~/newlpef/public_html
    WHAT=newlpef_db.sql
 DBUNAME=dbo146981002
DATABASE=db146981002
  DBHOST=db233.perfora.net
  DBPASS="3gq.MKP3"

# ---- should not need to change anything below here ----

if [ -f $WHERE/$WHAT ] ; then
    mysql -u $DBUNAME --password=$DBPASS -h $DBHOST $DATABASE <$WHERE/$WHAT
else
    echo "ERROR: Cannot Find $WHERE/$WHAT"
    exit
fi
