#!/bin/sh

# phpWebLog database setup utility v.02
# Jason Hines - http://phpweblog.org

mysqladmin="`which mysqladmin 2> /dev/null`"
mysql="`which mysql 2>/dev/null`"

# check for mysql binaries
if [ ! -x "$mysqladmin" ]; then
	echo "ERROR: mysqladmin not found in path.. Please remedy."
	exit
fi
if [ ! -x "$mysql" ]; then
	echo "ERROR: mysql not found in path.. Please remedy."
	exit
fi



# display intro
echo "Welcome to phpWebLog!"
echo "---------------------"
echo "This script will attempt to create the required database"
echo "for use with phpWebLog, and import the default values."
echo "If all goes well, your new site will be ready in no time."
echo "I am assuming that you already have MySQL installed and"
echo "running, and Apache/PHP installed with MySQL support."
echo "the included INSTALL for more information."
echo ""


echo -n "Name of database to create (PHPWEBLOG): "
read database
echo -n "Database username (root): "
read username
echo -n "Database password (none): "
read password
echo -n "Database host (localhost): "
read host
echo -n "Database port (3306): "
read port

# set defaults
if [ "$database" = "" ]; then
	database="PHPWEBLOG"
fi
if [ "$username" = "" ]; then
	username="root"
fi
if [ "$password" = "" ]; then
	password=""
fi
if [ "$host" = "" ]; then
	host="localhost"
fi
if [ "$port" = "" ]; then
	port="3306"
fi

# show values
echo ""
echo "phpWebLog database settings"
echo "---------------------------"
echo "database : $database"
echo "username : $username"
echo "password : $password"
echo "host     : $host"
echo "port     : $port"
echo ""
#echo "Warning: Proceeding will destroy this database if it exists."
echo -n "Continue with these values? (yN): "
read yesorno
if [ "$yesorno" != "y" -a "$yesorno" != "Y" ]; then
	echo "Aborting installation."
	exit
fi

# proceed with installation

if [ "$username" != "root" ]; then
	$mysqladmin -u $username -p create $database
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Could not create database \"$database\"!"
		exit
	fi
	$mysql -u $username -p $database < tables.sql
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Could not import tables!"
		exit
	fi
	$mysql -u $username -p $database < data.sql
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Could not import data!"
		exit
	fi
else
	$mysqladmin -u root create $database
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Could not create database \"$database\"!"
		exit
	fi
	$mysql -u root $database < tables.sql
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Could not import tables!"
		exit
	fi
	$mysql -u root $database < data.sql
	RET=$?
	if [ $RET -ne 0 ]; then
		echo "Could not import data!"
		exit
	fi
fi

echo ""
echo "Installation complete!"
echo ""
echo "Next, you must edit /path/phpweblog/include/common.inc.php and change"
echo "the top portion to match these database settings. Then you can point"
echo "your browser to www.yoursite.com/admin.  When prompted for a password"
echo "use the default password, \"password\"."
echo ""
echo "If you run into file permission problems, run the fix_permission.sh"
echo "script included in this directory."
echo ""
