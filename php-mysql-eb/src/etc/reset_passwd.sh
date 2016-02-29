#!/bin/sh

#
# phpWebLog reset password script
# (run this script to reset your admin password to 'password'.
#

DATABASE=PHPWEBLOG;
USER=root;


MYSQL=`which mysql`;

# end config
if [ ! -x $MYSQL ] ; then 
	echo -e "No mysql executable found: $MYSQL"; 
	exit; 
fi; 

$MYSQL -u $USER $DATABASE -e "UPDATE T_Config set Value='5f4dcc3b5aa765d61d8327deb882cf99' WHERE Name = 'Passwd'";

# eof