#!/bin/sh

# fix_permissions.sh
# phpWebLog - http://phpweblog.org

G_PATH=$1

if [ "$G_PATH" = "" ] ; then
	echo "usage: $0 /path/to/phpweblog"
	exit;
fi;

if [ -d $G_PATH ] ; then
	cd $G_PATH
	chmod 777 logs/
	chmod 666 logs/error.log
	chmod 666 logs/access.log
	chmod 777 backend/layouts/
	chmod 666 backend/layouts/*.xlay
	cd -
	echo ""
	echo "Done."
        exit;
else
	echo "Error: The directory $G_PATH does not exist."
	echo "Edit the path at the top of this script and try again."
	exit;
fi;



