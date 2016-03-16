#!/bin/sh
# ENTRYPOINT NEEDS TO START SOME SERVICES
PATH=/usr/kerberos/sbin:/usr/kerberos/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
for SERVICE in atd crond mysqld httpd-ent resin-krugle-api hub krugle-monitor 
do
    service $SERVICE stop
    service $SERVICE start
done

# the docker container will exit if the entrypoint.sh exits.
# so this script needs to keep running *forever*
sleep 9223372036854775807
# this is LONG_MAX in sensible (64bit) systems
# which is roughly the year 292,278,994

exit 0

