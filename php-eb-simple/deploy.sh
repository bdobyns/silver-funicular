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

STANDARD VERBS:
	$ME env deploy           deploy to the given environment
	$ME env update           just update the artifact 
	$ME env ssh              ssh to the given box
	$ME env put here there   copy a file to /home/ec2-user/there
	$ME env get there here   copy a file from there to here
        $ME env open             open a browser on the box 
        $ME local run            run a local copy of the app
        
SPECIAL VERBS:
        $ME init                 initialize elastic beanstalk (after git clone)
        $ME init appname         initialize elastic beanstalk (after git clone)
        $ME list                 list available environments
        $ME myip                 find out what my (laptop) ip is

ELASTIC BEANSTALK VERBS:
        $ME env describe         describe the environment

        $ME env id               get instance id
        $ME env ipaddr           get instance ipaddress
        $ME env instance         describe the instance

        $ME env sgn              get security group id
        $ME env security         describe security group 

        $ME env cname            display the cname of the lb
        $ME env r53cname foo     wire up a route53 name 'foo'

        $ME env asg              get autoscaling group name
        $ME env asgdescribe      describe the autoscaling group
        $ME env scale min max    set asg min and max 
        $ME env cooldown n       cooldown in seconds between asg actions

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
    # INSTANCE=`eb list -v | grep $ENV | cut -d \' -f 2`
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

ebsgn() {
    ID=`ebinstance`
    aws ec2 describe-instances --instance-ids $ID | jq .Reservations[].Instances[].SecurityGroups[].GroupName | tr -d \" 
}

ebcname() {
    aws elasticbeanstalk describe-environments --environment-names $ENV | jq .Environments[].CNAME | tr -d \"
}

route53wire() {
# something like 
#    route53wire `ebcname` foo.example.com
TONAME=$1
R53NAME=$2
# assume it's three-part, not four
R53DOMAIN=`echo $R53NAME | cut -f 2-3 -d .`
# now get the zone id
ZID=`aws route53 list-hosted-zones-by-name --dns-name $R53DOMAIN | jq .HostedZones[].Id | tr -d \" | cut -d / -f 3`
if [ -z $ZID ] ; then
    echo "ERROR $R53DOMAIN is not hosted in aws route53"
    exit 12
fi

# create a resource record to update this guy
RESREC=/tmp/route53.$$.json
cat >$RESREC <<EOF
{
  "Comment": "$0 for AWS EB by '$USER' on '$HOSTNAME' in '$PWD'", 
  "Changes": [
    {
      "Action": "UPSERT",
      "ResourceRecordSet": {
        "Name": "$R53NAME",
        "Type": "CNAME",
        "ResourceRecords": [
          {
            "Value": "$TONAME"
          }
        ],
        "TTL": 300
      }
    }
  ]
}
EOF

# now actually write the record
aws route53 change-resource-record-sets --hosted-zone-id $ZID --change-batch file://$RESREC
rm $RESREC
}

asgname() {
	ID=`ebinstance`
	DESCRIBE=`aws ec2 describe-instances --instance-ids $ID`
	echo $DESCRIBE | jq .Reservations[].Instances[].Tags[].Value | tr -d \" | grep -v ^AWSEBAutoScalingGroup | grep AWSEBAutoScalingGroup | tail -1
}

asgdescribe() {
	aws autoscaling describe-auto-scaling-groups --auto-scaling-group-names `asgname`
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
            eb init `basename $PWD` --region $REGION
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
	myip)
	    whatsmyip
	    exit
	    ;;
    esac
fi

# ----------------------------------------------------------------------
# detect bad environment name by trying to switch to it
if ! eb use $ENV 1>/dev/null 2>/dev/null; then
    # also try variants like test-appname where you only pass in 'test'
    ENV=${1}-` cat $EBCONFIG | yaml2json | jq .global.application_name | tr -d \"  `
    if !  eb use $ENV 1>/dev/null 2>/dev/null ; then
	ENV=${1}-`basename $PWD`
	eb use $ENV || exit
    fi
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
#	$ME env deploy           deploy to the given environment
#	$ME env update           just update the artifact 
	eb deploy 
	;;
    ssh)
#	$ME env ssh              ssh to the given box
	eb ssh
	;;
    put|get)
#	$ME env put here there   copy a file to /home/ec2-user/there
#	$ME env get there here   copy a file from there to here
	if [ -z $1 ] || [ -z $2 ] ; then
	    echo "ERROR - no files to $ACTION" >&2
	    givehelp
	    exit 11
        else
	    INSTANCE=` ebinstance `
	    IPADDR=` instanceipaddr $INSTANCE `
	    # do this to open port 22
	    cat /dev/null | eb ssh -o
	    if [ $ACTION = put ] ; then 
		scp $* ${EC2USER}@${IPADDR}:/home/$EC2USER
	    elif [ $ACTION = get ] ; then
		scp ${EC2USER}@${IPADDR}:/home/$EC2USER/"$1" "$2"
	    else
		# unreachable
		echo "ERROR: don't know how to $ME $ENV $ACTION" >&2
		exit 13
	    fi
	    # do this to close port 22
	    cat /dev/null | eb ssh 
	fi
	;;
    open)
#        $ME env open             open a browser on the box 
	eb open
	;;
    id)
#        $ME env id               get instance id
	ebinstance
	;;
    ipaddr)
#        $ME env ipaddr           get instance ipaddress
	instanceipaddr `ebinstance`
	;;
    instance)
#        $ME env instance         describe the instance
	aws ec2 describe-instances --instance-ids `ebinstance`
	;;
    sgn)
#        $ME env sgn              get security group id
	ebsgn
	;;
    security)
#        $ME env security         describe security group 
	aws ec2 describe-security-groups --group-names `ebsgn`
	;;
    cname)
#        $ME env cname            display the cname of the lb
	ebcname
	;;
    describe)
#        $ME env describe         describe the environment
	aws elasticbeanstalk describe-environments --environment-names $ENV 
	;;
    r53cname|route53cname)
#        $ME env r53cname foo     wire up a route53 name 'foo'
	if [ -n $1 ] ; then 
	    route53wire `ebcname` $1
	else
	    echo "ERROR: no target name to wire up"
	fi
	;;
    scale)
#        $ME env scale min max    set asg min and max 
	if [ -z $1 ] || [ -z $2 ] ; then
	    echo ERROR must specify min and max
	else    
	    aws autoscaling update-auto-scaling-group --auto-scaling-group-name `asgname` --min-size $1 --max-size $2
	    asgdescribe | grep Size
	fi
	;;
    cooldown)
#        $ME env cooldown n       cooldown in seconds between asg actions
	if [ -z $1 ] ; then 
	    echo ERROR must specify cooldown value
	else    
	    aws autoscaling update-auto-scaling-group --auto-scaling-group-name `asgname` --default-cooldown $1
	    asgdescribe | grep Cooldown
	fi
	;;
    count)
#        $ME env count n          set asg max and min to n
	if [ -z $1 ] ; then 
	    echo ERROR must specify instance count value
	else    
	    # eb scale $1
	    aws autoscaling update-auto-scaling-group --auto-scaling-group-name `asgname` --desired-capacity $1 
	    asgdescribe | grep Size
	fi
	;;
    asg)
#        $ME env asg              get autoscaling group name
	asgname
	;;
    asgdescribe)
#        $ME env asgdescribe      describe the autoscaling group
	asgdescribe
	;;
    *)
	givehelp
	;;
esac
