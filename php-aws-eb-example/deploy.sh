#!/bin/bash -e

GIT_BRANCH=` git status | head -1 | awk '{ print $3}' `
ME=`basename $0`

givehelp()
{
cat <<EOF

usage: 
        $ME init                   initialize elastic beanstalk
        $ME list                   list available environments

	$ME [env] deploy           deploy to the given environment
	$ME [env] update           just update the artifact in the instance
	$ME [env] ssh              ssh to the given box
	$ME [env] put here there   copy a file to /home/ec2-user/there
	$ME [env] get there here   copy a file from there to here
        $ME [env] open             open a browser on the box 
        $ME local run              run a local copy of the app

EOF
	exit 7
}

# ----------------------------------------------------------------------
# detect no args at all
if [ -z $1 ] ; then
    givehelp
    exit
else
    # first arg is usually the Environment
    ENV=$1

    # check to see if we've run `eb init` yet
    if [ $1 != init ] && [ ! -d `dirname $0`/.elasticbeanstalk ] ; then
	echo "ERROR: you must run '$ME init' first before anything else"
	echo " "
	givehelp
	exit 9
    fi

    case $ENV in
	init)
	    if [ -f ~/.aws/config ] ; then 
		REGION=`cat ~/.aws/config | grep ^region | head -1 | cut -f 2 -d =`
	    fi
	    if [ -z $REGION ] ; then
		REGION=us-west-2
	    fi
	    echo WARNING: this works best if the application `basename $PWD` exists and is sane
            eb init `basename $PWD` --region $REGION
	    exit
	    ;;
	list)
	    eb list
	    exit
	    ;;
        # use "fail" as a special case environment
	local)
	    echo "ERROR: '$ME local $2' is not supported"
	    givehelp
	    exit
	    ;;
	test|prod)
	    ;;

	# detect bad environment name by trying to switch to it
	*)
	    eb use $1
	    ;;
    esac
fi

if [ -z $2 ] ; then
    givehelp
    exit
else
    ACTION=$2
    shift; shift
fi


REGION=`cat .elasticbeanstalk/config.yml | grep "  default_region:" | cut -f 2 -d : `
KEYNAME=`cat .elasticbeanstalk/config.yml | grep "  default_ec2_keyname:" | cut -f 2 -d : `
EC2USER=ec2-user

# now parse the 'action' keyword
case $ACTION in
    update|deploy)
	set -x
	eb deploy 
	;;
    ssh)
	set -x
	eb ssh
	;;
    put|get)
	if [ -z $1 ] || [ -z $2 ] ; then
	    echo "ERROR - no files to $ACTION"
	    givehelp
        else
	    INSTANCE=`eb list -v | tail -1 | cut -d \' -f 2`
	    IPADDR=`aws ec2 describe-instances --instance-ids $INSTANCE | jq .Reservations[].Instances[].PublicIpAddress | tr -d \"`
	    set -x
	    if [ $ACTION = put ] ; then 
		scp $* ${EC2USER}@${IPADDR}:/home/$EC2USER
	    else
		scp ${EC2USER}@${IPADDR}:/home/$EC2USER/"$1" "$2"
	    fi
	fi
	;;
    open)
	set -x
	eb open
	;;

    *)
	givehelp
	;;
esac
