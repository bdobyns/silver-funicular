#!/bin/sh
# ENTRYPOINT NEEDS TO START SOME SERVICES
PATH=/usr/kerberos/sbin:/usr/kerberos/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
for SERVICE in atd crond mysqld httpd-ent resin-krugle-api hub krugle-monitor 
do
    # we do a service stop first to cleanup any lingering pid files
    # hub is especially sensitive to this
    service $SERVICE stop
    # now we can cleanly start
    service $SERVICE start
done

# the docker container will exit if the entrypoint.sh exits.
# so this script needs to keep running *forever*
sleep 9223372036854775807
# this is LONG_MAX in sensible (64bit) systems
# which is roughly the year 292,278,994

exit 0

