#!/bin/bash -e

if [ `dirname $0` = . ] ; then
    EBCONFIG=".elasticbeanstalk/config.yml"
else
    EBCONFIG=`dirname $0`/".elasticbeanstalk/config.yml"
fi
ME=`basename $0`

givehelp()
{
cat <<EOF

usage: 
        $ME init                   initialize elastic beanstalk
        $ME list                   list available environments

	$ME [env] deploy           deploy to the given environment
	$ME [env] update           just update the artifact 
	$ME [env] ssh              ssh to the given box
	$ME [env] put here there   copy a file to /home/ec2-user/there
	$ME [env] get there here   copy a file from there to here
        $ME [env] open             open a browser on the box 
        $ME local run              run a local copy of the app

EOF
	exit 7
}

# ----------------------------------------------------------------------
# some helper methods

# get a config value from a config file
cfgget() {
    # $1 = filename
    # $2 = section
    # $3 = item
    python -c "import ConfigParser, os ; config=ConfigParser.ConfigParser() ; config.readfp(open('"$1"')); print config.get('"$2"','"$3"')"
}

# does yaml to json on stdin
yaml2json() {
    # if we have y2j and yq from https://github.com/wildducktheories/y2j then use it because it's more robust
    if [ `which y2j` ] ; then 
	y2j
    elif test -n python -c "import sys, yaml, json;" 2>/dev/null ; then
	python -c 'import sys, yaml, json; json.dump(yaml.load(sys.stdin), sys.stdout, indent=4)' 
    else
	echo "ERROR: your python is broken, and you don't have y2j installed" >&2
	echo "       https://github.com/wildducktheories/y2j " >&2
    fi
}

ebregion() {
    cat $EBCONFIG | yaml2json | jq .global.default_region | tr -d '"' 
}

ebkeyname() {
    cat $EBCONFIG | yaml2json |  jq .global.default_ec2_keyname | tr -d '"' 
}

ebinstance() {
    if [ -n $1 ] ; then 
	ORDINAL=$1
    else
	ORDINAL=0
    fi
    aws elasticbeanstalk describe-environment-resources  --environment-name $ENV | jq .EnvironmentResources.Instances[${ORDINAL}].Id | tr -d \" 

}

instanceipaddr() {
    INSTANCE=$1
    if [ -n $INSTANCE ] ; then
	aws ec2 describe-instances --instance-ids $INSTANCE | jq .Reservations[].Instances[].PublicIpAddress | tr -d \"
    fi
}

ebdefaultenv() {
    # stupid jq can't take names with a dash in them
    cat $EBCONFIG | sed -e s/branch-defaults/foo/ | y2j .foo.default.environment
}

whatsmyip() {
    curl -s http://www.whatsmyip.website/api/plaintext | head -1
}

EC2USER=ec2-user

# ----------------------------------------------------------------------
# detect no args at all
if [ -z $1 ] ; then
    givehelp
    exit
else
    # first arg is usually the Environment
    ENV=$1

    # check to see if we've run `eb init` yet
    if [ $1 != init ] && [ ! -f $EBCONFIG ] ; then
	echo "ERROR: you must run '$ME init' first before anything else" >&2
	echo " " >&2
	givehelp
	exit 9
    fi

    case $ENV in
	init)
	    if [ -f ~/.aws/config ] ; then 
		# get the region out of your aws config
		# REGION=`cat ~/.aws/config | grep ^region | head -1 | cut -f 2 -d =`
		REGION=`cfgget ~/.aws/config default region`
	    fi
	    if [ -z $REGION ] ; then
		# pick a sensible default
		REGION=us-west-2
	    fi
	    echo WARNING: this works best if the application `basename $PWD` exists and is sane
mkdir -p .ebextensions
MYIP=`whatsmyip`
cat >.ebextensions/security.config <<EOF	    
AWSEBSecurityGroup:
    Type: “AWS::EC2::SecurityGroup”
    Properties:
      GroupDescription: “Security group to allow HTTP, HTTPS,SSH”
      SecurityGroupIngress:
        - {CidrIp: “0.0.0.0/0″, IpProtocol: “tcp“, FromPort: “8080”, ToPort: “8080”}
        - {CidrIp: “0.0.0.0/0″, IpProtocol: “tcp“, FromPort: “8443”, ToPort: “8443”}
        - {CidrIp: “0.0.0.0/0″, IpProtocol: “tcp“, FromPort: “443”, ToPort: “443”}
        - {CidrIp: “0.0.0.0/0″, IpProtocol: “tcp“, FromPort: “80”, ToPort: “80”}
        - {CidrIp: “$MYIP/32″, IpProtocol: “tcp“, FromPort: “22”, ToPort: “22”}
EOF
#            eb init `basename $PWD` --region $REGION
	    exit
	    ;;
	list)
	    eb list
	    exit
	    ;;
        # use "fail" as a special case environment
	local)
	    echo "ERROR: '$ME local $2' is not supported" >&2
	    givehelp
	    exit
	    ;;
	test|prod)
	    ;;

	# detect bad environment name by trying to switch to it
	*)
	    eb use $1 || exit
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



# ----------------------------------------------------------------------
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
	    echo "ERROR - no files to $ACTION" >&2
	    givehelp
	    exit 11
        else
	    # INSTANCE=`eb list -v | grep $ENV | cut -d \' -f 2`
	    INSTANCE=` ebinstance `
	    IPADDR=` instanceipaddr $INSTANCE `
	    set -x
	    if [ $ACTION = put ] ; then 
		scp $* ${EC2USER}@${IPADDR}:/home/$EC2USER
	    elif [ $ACTION = get ] ; then
		scp ${EC2USER}@${IPADDR}:/home/$EC2USER/"$1" "$2"
	    else
		# unreachable
		echo "ERROR: don't know how to $ME $ENV $ACTION" >&2
		exit 13
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
