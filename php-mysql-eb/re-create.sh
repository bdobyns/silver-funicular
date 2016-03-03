#!/bin/bash

USEEXISTINGAPP=true

function randomline
{
    if [ ! -z `which randline` ] ; then
	randline $1
    elif [ ! -z `which shuf` ] ; then
	shuf $1
    elif [ -f $1 ] ; then
	cat $1 | head -$((${RANDOM} % `wc -l < $1` + 1)) $1 | tail -1
    fi
}

SHUF=randomline

# make up a sensible password for the database
WORDS=/usr/share/dict/words
if [ -f $WORDS ] ; then 
    DBUSER=`$SHUF $WORDS`
    DBPASS=`$SHUF $WORDS`-`$SHUF $WORDS`-`$SHUF $WORDS`-`$SHUF $WORDS`
else
    DBUSER=dimwit-flathead
    DBPASS=correct-battery-staple-horse  # xkcd.com/936/
fi

# fabricate a sensible app and environment name
if [  -z $1 ] && [ ! -z $SHUF ] && [ -f $WORDS ] ; then
    NAME=$USER-`$SHUF $WORDS | tr A-Z a-z`
    ENV=`$SHUF $WORDS | tr A-Z a-z`
    PASS=`$SHUF $WORDS`-`$SHUF $WORDS`-`$SHUF $WORDS`
    USER=`$SHUF $WORDS`
elif [ -z $1 ] ; then
    NAME=$USER-`basename $PWD`
    ENV=$USER
    PASS=`date | md5`
elif [ ! -z $1 ] && [ ! -z $2 ] ; then
    NAME=$1-$USER
    ENV=$2
elif [ -z $2 ] ; then 
    NAME=$1-$USER
    ENV=`$SHUF $WORDS`
    PASS=`$SHUF $WORDS`-`$SHUF $WORDS`
fi

# if we are supposed to use the existing app
if [ $USEEXISTINGAPP = true ]; then 
    NAME=`./deploy.sh appname`
    if [ -z $NAME ] ; then 
	echo ERROR: we expected that you had already created an app with 
	echo ./deploy.sh new appname --region us-west-2 --platform php --keyname somekey_rsa
	exit
    fi
fi

# trim the environment name if it is too long, aws only allows 23 chars
EL=`echo ${ENV}-$NAME | wc -c`
if [ $EL -ge 23 ] ; then
    NL=`echo $NAME | wc -c`
    ENV=`echo $ENV | cut -c 1-$[ 22 - $NL ]`
fi

set -x 

# this is NOT the same as the function of the same name in lib_eb_deploy.sh
function vpcsubnets
{
    if [ ! -z $1 ] ; then 
#	aws ec2 describe-subnets --filters "Name=vpc-id,Values=$1" | jq .Subnets[].CidrBlock | tr -d '"' | tr '\n' , | sed -e s'/,$//'
	aws ec2 describe-subnets --filters "Name=vpc-id,Values=$1" | jq .Subnets[].SubnetId | tr -d '"' | tr '\n' , | sed -e s'/,$//'
    fi
}

VPC=vpc-a1fc39c4
SUBNETS=`vpcsubnets $VPC`

if [ $USEEXISTINGAPP = false ] ; then 
    ./deploy.sh new $NAME --region us-west-2 --platform php --keyname barry_rsa
fi

./deploy.sh create $ENV -i m1.small --timeout 60 \
    -db.engine mysql -db.i db.m1.small -db.size 5 -db.pass $PASS -db.user $USER 
#    --vpc.id $VPC --vpc.dbsubnets $SUBNETS --vpc.elbsubnets $SUBNETS --vpc.publicip
