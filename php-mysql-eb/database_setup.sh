#!/bin/bash
STAGE=`/opt/elasticbeanstalk/bin/get-config container -k app_staging_dir`
DUMP=newlpef_db.sql
for F in $DUMP  src/$DUMP src/$DUMP $STAGE/$DUMP $STAGE/src/$DUMP
do
    if [ -f $F ] ; then 
	DUMP=$F
	break
    fi
done


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
