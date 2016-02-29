#!/bin/bash
# $Id: backupnow.sh,v 1.2 2006/01/13 18:41:24 cvs Exp $

. ~/.bash_profile

   WHERE=.
    WHAT=newlpef_db.sql
 DBUNAME="$RDS_USERNAME"
DATABASE="$RDS_DB_NAME"
  DBHOST="$RDS_HOSTNAME"
  DBPASS="$RDS_PASSWORD"

# ---- should not need to change anything below here ----

. $WHERE/backupnow.include

# eot
