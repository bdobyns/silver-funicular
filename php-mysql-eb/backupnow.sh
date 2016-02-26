#!/bin/bash
# $Id: backupnow.sh,v 1.2 2006/01/13 18:41:24 cvs Exp $

. ~/.bash_profile

   WHERE=~/newlpef/public_html
    WHAT=newlpef_db.sql
 DBUNAME=dbo146981002
DATABASE=db146981002
  DBHOST=db233.perfora.net
  DBPASS="3gq.MKP3"

# ---- should not need to change anything below here ----

. $WHERE/backupnow.include

# eot
