#!/bin/bash
# $Id: restorenow.sh,v 1.1 2005/12/09 01:51:34 cvs Exp $

. ~/.bash_profile

   WHERE=~/newlpef/public_html
    WHAT=newlpef_db.sql
 DBUNAME="$RDS_USERNAME"
DATABASE="$RDS_DB_NAME"
  DBHOST="$RDS_HOSTNAME"
  DBPASS="$RDS_PASSWORD"

# ---- should not need to change anything below here ----

if [ -f $WHERE/$WHAT ] ; then
    mysql -u $DBUNAME --password=$DBPASS -h $DBHOST $DATABASE <$WHERE/$WHAT
else
    echo "ERROR: Cannot Find $WHERE/$WHAT"
    exit
fi
