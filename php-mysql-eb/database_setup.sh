#!/bin/bash
DUMP=html/newlpef_db.sql

# ONLY RESTORES IF THE DATABASE IS NOT PRESENT
if ! echo SELECT Rid FROM T_Blocks | mysql -h $RDS_HOSTNAME -u $RDS_USERNAME --password=$RDS_PASSWORD $RDS_DB_NAME >/dev/null ; then
    if [ -f $DUMP ] ; then
	cat $DUMP | mysql -h $RDS_HOSTNAME -u $RDS_USERNAME --password=$RDS_PASSWORD $RDS_DB_NAME
	rm $DUMP
    else
	echo ERROR: unable to update because $DUMP does not exist
	exit 3
    fi
fi
