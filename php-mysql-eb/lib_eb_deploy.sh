#!/bin/bash -e
# blame: barry@productops.com  Feb 2016
# ... this is the library behind the deploy.sh for elastic beanstalk
ME=`basename $0`
DNZ=`dirname $0`

if [ $DNZ = . ] ; then
    EBCONFIG=".elasticbeanstalk/config.yml"
else
    EBCONFIG=$DNZ/".elasticbeanstalk/config.yml"
fi
EC2USER=ec2-user
AWSCONFIG=~/.aws/config

# ----------------------------------------------------------------------
# ----------------------------------------------------------------------
# ----------------------------------------------------------------------

# some helper methods/functions

# get a config value from a config file
function cfgget 
{
    # $1 = filename
    # $2 = section
    # $3 = item
    if ! python -c "import ConfigParser, os;" 2>/dev/null ; then
	pip install ConfigParser 2>/dev/null >/dev/null
    fi
    python -c "import ConfigParser, os ; config=ConfigParser.ConfigParser() ; config.readfp(open('"$1"')); print config.get('"$2"','"$3"')"
}

# does yaml to json on stdin
function yaml2json 
{
    # if we have y2j and yq from https://github.com/wildducktheories/y2j then use it because it's more robust
    if [ `which y2j` ] ; then 
	y2j
    elif python -c "import sys, yaml, json;" 2>/dev/null ; then
	python -c 'import sys, yaml, json; json.dump(yaml.load(sys.stdin), sys.stdout, indent=4)' 
    else
	echo "ERROR: your python is broken, and you don't have y2j installed" >&2
	echo "       https://github.com/wildducktheories/y2j " >&2
    fi
}

function ebregion 
{
    cat $EBCONFIG | yaml2json | jq .global.default_region | tr -d '"' 
}

function awsregion 
{
    if [ -f $AWSCONFIG ] ; then 
	# should be in the default section
	REGION=`cfgget $AWSCONFIG default region 2>/dev/null`
	if [ -z $REGION ] ; then
	    # maybe in the global section
	    REGION=`cfgget $AWSCONFIG global region 2>/dev/null`
	fi
	if [ -z $REGION ] ; then 
	    # some other section, then?
	    REGION=`cat $AWSCONFIG | grep ^region | head -1 | cut -f 2 -d =`
	fi
	if [ ! -z $REGION ] ; then 
	    echo $REGION 
	fi
    fi
}

function setregion 
{
    # get the region out of your aws config or eb config
    if [ -f $EBCONFIG ] ; then
    	REGION=`ebregion`
    fi
    if [ -f $AWSCONFIG ] && [ -z $REGION ] ; then 
	REGION=`awsregion`
    fi
    if [ -z $REGION ] ; then
	# pick a sensible default
	REGION=us-west-2
    fi
    echo $REGION
}

function ebkeyname 
{
    cat $EBCONFIG | yaml2json |  jq .global.default_ec2_keyname | tr -d '"' 
}

function ebdescribe 
{
#        $ME env describe         describe the environment
	aws elasticbeanstalk describe-environments --environment-names $ENV 
}

function ebinstance 
{
#        $ME env id               get instance id
    # INSTANCE=`eb list -v | grep $ENV | cut -d \' -f 2`
    if [ ! -z $1 ] ; then 
	ORDINAL=$1
    else
	ORDINAL=
    fi
    aws elasticbeanstalk describe-environment-resources  --environment-name $ENV | jq .EnvironmentResources.Instances[${ORDINAL}].Id | tr -d \" 
}

function ebinstanceipaddr 
{
#        $ME env ipaddr           get instance ipaddress
    INSTANCE=$1
    if [ ! -z $INSTANCE ] ; then
	aws ec2 describe-instances --instance-ids $INSTANCE | jq .Reservations[].Instances[].PublicIpAddress | tr -d \"
    fi
}

function ebdefaultenv 
{
    # stupid jq can't take names with a dash in them
    cat $EBCONFIG | sed -e s/branch-defaults/foo/ | y2j .foo.default.environment
}

function whatsmyip 
{
#        $ME myip                 find out what my (laptop) ip is
    curl -s http://www.whatsmyip.website/api/plaintext | head -1
}

function ebsgn 
{
#        $ME env sgn              get security group id
    ID=`ebinstance`
    aws ec2 describe-instances --instance-ids $ID | jq .Reservations[].Instances[].SecurityGroups[].GroupName | tr -d \" 
}

function ebsgid 
{
#        $ME env sgid             get security group id
    ID=`ebinstance`  
    aws ec2 describe-instances --instance-ids $ID | jq .Reservations[].Instances[].SecurityGroups[].GroupId | tr -d \" 
}

function sgingress
{
#        $ME env ingress othersg  permit ingress from this env to othersg
    MYSGID=`ebsgid`
    OTHERSG=$1
    TODAY=`date +%Y-%m-%d`
    shift
    if [ -z $1 ] ; then
        # just go ahead and do the usual ports that everyone wants: web
	PORTS="80 443 8080 8443"  # 8000 3306 5432
    else 
	PORTS="$*"
    fi 
    if [ ! -z $OTHERSG ] && [ ! -z $MYSGID ]; then 
	if aws ec2 describe-security-groups | jq .SecurityGroups[].GroupId | tee /tmp/$$.sgids |  grep $OTHERSG >/dev/null ; then 
	    set -x
	    for PORT in $PORTS # 80 443 8080 8443 8000 3306 5432
	    do
		aws ec2 authorize-security-group-ingress --group-id $OTHERSG --source-group $MYSGID --port $PORT --protocol tcp
	        aws ec2 create-tags --resources $OTHERSG  --tags "Key=Blame_$PORT,Value=\"Modified by $LOGNAME@$HOSTNAME using $ME on $TODAY\""
		# aws ec2 authorize-security-group-egress --group-id $OTHERSG --source-group $MYSGID --port $PORT --protocol tcp
		# aws ec2 authorize-security-group-ingress --group-id $MYSGID --source-group $OTHERSG --port $PORT --protocol tcp
	        # aws ec2 create-tags --resources $MYSGID  --tags "Key=Blame_$PORT,Value=\"Modified by $LOGNAME@$HOSTNAME using $ME on $TODAY\""
	    done
	    set +x
	else 
	    # aws ec2 describe-security-groups | jq .SecurityGroups[].GroupId >/tmp/$$.sgids
	    aws ec2 describe-security-groups | jq .SecurityGroups[].GroupName >/tmp/$$.sgnms
	    echo "ERROR $OTHERSG is not the id of a valid security group"
	    echo "     maybe you can Try one one of these:"
	    paste /tmp/$$.sgids /tmp/$$.sgnms
	fi
	rm -rf /tmp/$$.sgids /tmp/$$.sgnms
    elif [ -z $MYSGID ] ; then 
	echo "ERROR can't determine the security group of this environment"
	exit 4
    else # [ -z $OTHERSG ] ; then 
	echo "ERROR must specify the security group id of a target group"
	exit 7
    fi
}

function ebcname 
{
#        $ME env cname            display the cname of the lb
    ebdescribe | jq .Environments[].CNAME | tr -d \"
}

function route53wire 
{
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
    exit 5
fi

# create a resource record to update this guy
RESREC=/tmp/route53.$$.json
cat >$RESREC <<EOF
{
  "Comment": "$0 for AWS EB by '$LOGNAME' on '$HOSTNAME' in '$PWD'", 
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

function route53cname 
{
#        $ME env r53 f.b.com      wire up a route53 name 'f.b.com' to the lb'
#        $ME env r53cname foo     wire up a route53 name 'foo'
	if [ ! -z $1 ] ; then 
	    route53wire `ebcname` $1
	else
	    echo "ERROR: no target name to wire up" >&2
	fi
}

function asgname 
{
#        $ME env asg              get autoscaling group name
    ID=`ebinstance`
    aws ec2 describe-instances --instance-ids $ID  | jq .Reservations[].Instances[].Tags[].Value | tr -d \" | grep -v ^AWSEBAutoScalingGroup | grep AWSEBAutoScalingGroup | tail -1
}

function asgdescribe 
{
#        $ME env asgdescribe      describe the autoscaling group
    aws autoscaling describe-auto-scaling-groups --auto-scaling-group-names `asgname`
}

function appname 
{
    cat $EBCONFIG | yaml2json | jq .global.application_name | tr -d \"  
}

# create an ed script for editing a configuration as produced by `eb config`
# and then execute it with
#     export EDITOR="cat $FILE | ed" ; eb config
# this completely replaces the $KEY line with $KEY: $VAL
function ebeditconfig 
{
    EFILE=/tmp/ebconfig.hack.$$
    if [  -f $EFILE ] ; then rm -rf $EFILE ; fi

    while [ ! -z $3 ] 
    do
	SECTION=$1
	KEY=$2
	VAL=$3
# find the section
# delete the key
# go back to the top of the section
# insert the new key right after the section header
TODAY=`date +%Y-%m-%d`
cat >>$EFILE <<EOF
/  $SECTION/
/$KEY:/d
/  $SECTION/
a
    $KEY: $VAL # written using $0 on $TODAY by $LOGNAME@$HOSTNAME
.

EOF
shift ; shift ; shift 
done
cat >>$EFILE <<EOF
w
w /tmp/ebeditconfig_$$.sav
q

EOF
    export EDITOR="cat $EFILE | ed >/dev/null "
    eb config # && rm $EFILE
}

# create an ed script for editing a configuration as produced by `eb config`
# and then execute it with
#     export EDITOR="cat $FILE | ed" ; eb config
# this appends ,$VAL to the end of the $KEY line
function ebappendconfig 
{
    EFILE=/tmp/ebconfig.hack.$$
    if [  -f $EFILE ] ; then rm -rf $EFILE ; fi

    while [ ! -z $3 ] 
    do
	SECTION=$1
	KEY=$2
	VAL=$3
# find the section
# delete the key
# go back to the top of the section
# insert the new key right after the section header
TODAY=`date +%Y-%m-%d`
cat >>$EFILE <<EOF
/  $SECTION/
/$KEY:/
s/$/,$VAL/

EOF
shift ; shift ; shift 
done
cat >>$EFILE <<EOF
w
w /tmp/ebeditconfig_$$.sav
q

EOF
    export EDITOR="cat $EFILE | ed >/dev/null "
    eb config # && rm $EFILE
}

function eblistapps 
{
#        $ME listapps             list available apps
    aws elasticbeanstalk describe-applications | jq .Applications[].ApplicationName | tr -d \"
}

function eblimitip 
{
#        $ME env limitip          limit ssh ip to my public ip	
	if [ -z $1 ] ; then
	    CIDR=`whatsmyip`/32
	else
	    CIDR=$1
	    if ! echo $CIDR | grep / >/dev/null ; then
		echo "ERROR: $CIDR is not in CIDR a.b.c.d/m form" >&2
		exit 73
	    fi
	fi
	echo  INFO: About To Set SSHSourceRestriction: tcp,22,22,$CIDR
	ebeditconfig aws:autoscaling:launchconfiguration: SSHSourceRestriction tcp,22,22,$CIDR
	aws ec2 describe-security-groups --group-names `ebsgn` | grep CidrIp
}

function ebsetitype 
{
#        $ME env setitype type    set instance type, like t1.micro or m3.medium
	if [ -z $1 ] ; then 
	    echo " --- current value ---"
	else
	    # if MinInstancesInService is set to 0 you may get a service outage
	    MAXSIZE=`asgdescribe | jq .AutoScalingGroups[].MaxSize`
	    MINSIZE=`asgdescribe | jq .AutoScalingGroups[].MinSize`
	    MAXBATCH="aws:autoscaling:updatepolicy:rollingupdate: MaxBatchSize '1'"
	    MININSTANCES="aws:autoscaling:updatepolicy:rollingupdate: MinInstancesInService '$MINSIZE'"
	    ROLLUPTRUE="aws:autoscaling:updatepolicy:rollingupdate: RollingUpdateEnabled 'true'"
	    ROLLUPTYPE="aws:autoscaling:updatepolicy:rollingupdate: RollingUpdateType 'Health'"
	    if [ $MAXSIZE -eq 1 ] || [ $MAXSIZE -eq $MINSIZE ] ; then
		NEWMAX=$[ $MAXSIZE + 1 ]
		echo "INFO: Auto Scaling Group MaxSize Increased To $NEWMAX"
		BUMPMAX="aws:autoscaling:asg: MaxSize '$NEWMAX'"
	    fi	    
	    # if youhavebeenwarned ; then 
		if ! ebeditconfig $MAXBATCH $MININSTANCES $BUMPMAX $ROLLUPTRUE $ROLLUPTYPE aws:autoscaling:launchconfiguration: InstanceType $1
		then 
		    echo "If you failed due to the dreaded VPC problem, read"
		    echo '  https://mike-thomson.com/blog/?p=2103#more-2103'
		fi
            # fi
	    asgdescribe | egrep 'Size|Desired|MinInstancesInService|RollingUpdate|MaxBatchSize'
	fi
	aws ec2 describe-instances --instance-ids `ebinstance` | grep InstanceType
}

function ebsetcount 
{
#        $ME env count n          set asg max and min to n
	if [ -z $1 ] ; then 
	    echo " --- current values ---"
	else    
	    # we could probably the scale code above, just with MIN = MAX
	    # BUT 'eb scale' sets AutoScalingGroups[].DesiredCapacity which is NOT in the config
	    eb scale $1
	fi
	asgdescribe | egrep 'Size|Desired'
}

function ebsetcooldown 
{
#        $ME env cooldown n       cooldown in seconds between asg actions
	if [ -z $1 ] ; then 
	    echo " --- current values ---"
	else    
	    # aws autoscaling update-auto-scaling-group --auto-scaling-group-name `asgname` --default-cooldown $1
	    ebeditconfig aws:autoscaling:asg: Cooldown $1
	fi
	asgdescribe | grep Cooldown
}

function ebsetscale 
{
#        $ME env scale min max    set asg min and max 
	if [ -z $1 ] || [ -z $2 ] ; then
	    echo " --- current values ---"
	    asgdescribe | egrep 'Size|Desired'
	else
	    if [ $1 -lt $2 ] ; then
		MINV=$1
		MAXV=$2
	    else
	        # swap the args if we need to
		MINV=$2
		MAXV=$1
	    fi
	    # set the MinInstancesInService to something sensible, based on max
	    MAXBATCH="aws:autoscaling:updatepolicy:rollingupdate: MaxBatchSize '1'"
	    MIN="aws:autoscaling:asg: MinSize '$MINV'"
	    MAX="aws:autoscaling:asg: MaxSize '$MAXV'"
	    ROLLUPTRUE="aws:autoscaling:updatepolicy:rollingupdate: RollingUpdateEnabled 'true'"
	    if [ $MAXV -eq 1 ] ; then 
		MININSTANCES="aws:autoscaling:updatepolicy:rollingupdate: MinInstancesInService '0'"
	    elif [ $MAXV -gt $MINV ] ; then 
		MININSTANCES="aws:autoscaling:updatepolicy:rollingupdate: MinInstancesInService '$MINV'"
	    else 
		MININSTANCES="aws:autoscaling:updatepolicy:rollingupdate: MinInstancesInService '1'"	    
	    fi
	    ebeditconfig $MIN $MAX $MAXBATCH $MININSTANCES $ROLLUPTRUE
	    asgdescribe | egrep 'Size|Desired|MinInstancesInService|RollingUpdate|MaxBatchSize'
	fi
}

function ebnew 
{
#        $ME new                  create application based on this dir name
#        $ME new appname          create application appname
	    if [ -z $2 ] ; then
		APPNAME=`basename $PWD`
		shift
	    else
		APPNAME=$2
		shift ; shift
	    fi
	    if [ $# -eq 0 ] ; then # no other args
		if ! eblistapps | grep '"'$APPNAME'"' >/dev/null; then
		    eb init $APPNAME -p PHP --region $REGION 
		else
		    echo "ERROR: appname $APPNAME already exists" >&2
		    echo "     maybe you want to pick a different name (not in the list below)" >&2
		    eblistapps 
		fi
	    else
#        $ME new appname args..     create application appname
		    # if they gave us a bunch of args, just pass them all thru as if they know what they're doing
                 eb init $APPNAME $*
	     fi
}

function ebinit 
{
#        $ME init                 initialize elastic beanstalk (after git clone)
#        $ME init appname         initialize elastic beanstalk (after git clone)
            REGION=`setregion`
	    if [ -z $2 ] ; then
		APPNAME=`basename $PWD`
	    else
		APPNAME=$2
	    fi
	    # this is for use by regular developers
	    if [ ! -z $3 ] ; then
		 echo " "
		 echo "ERROR: no other args than appname are permitted" >&2
		 echo "   these are the valid appnames:" >&2
		 eblistapps 
	    elif ! eblistapps | grep '^'$APPNAME'$' >/dev/null; then
		 echo " "
		 echo "ERROR: appname $APPNAME does not exist" >&2
		 echo "   maybe you meant to use one of these:" >&2
		 eblistapps 
	     else
		 if [ ! -z $REGION ] ; then
		     eb init $APPNAME --region $REGION		    
		 else
		     eb init $APPNAME
		 fi
	     fi
}

function ebcreate 
{
#        $ME create env [args]       create environment 'env-appname'
	    shift
	    if [ -z $1 ] ; then 
		echo "ERROR: you must specify an environment name prefix like 'test' or 'prod'" >&2
		exit
	    fi
	    ENVNAME=${1}-` appname `
	    if [ $ENVNAME = ${1}- ] ; then 
		ENVNAME=${1}-`basename $PWD`
	    fi
	    echo " BE PATIENT: THIS MAY TAKE A WHILE AND WILL DEPLOY AT LEAST ONE INSTANCE ALONG THE WAY "
	    shift 
	    if echo $* | grep ' --tags' >/dev/null  ; then 
		eb create $ENVNAME $*
	    else
		eb create $ENVNAME $* --tags "Name=${ENVNAME},Blame=\"Created by $LOGNAME@$HOSTNAME using $ME on "`date +%Y-%m-%d`'"'
	    fi
}

function ebputget 
{
#	$ME env put here there   copy a file to /home/ec2-user/there
#	$ME env get there here   copy a file from there to here
	if [ -z $1 ] || [ -z $2 ] ; then
	    echo "ERROR - no files to $ACTION" >&2
	    givehelp
	    exit 53
        else
	    INSTANCE=` ebinstance `
	    IPADDR=` ebinstanceipaddr $INSTANCE `
	    # do this to open port 22
	    cat /dev/null | eb ssh -o
	    if [ $ACTION = put ] ; then 
		scp "$1" ${EC2USER}@${IPADDR}:/home/$EC2USER/"$2"
	    elif [ $ACTION = get ] ; then
		scp ${EC2USER}@${IPADDR}:/home/$EC2USER/"$1" "$2"
	    else
		# unreachable
		echo "ERROR: don't know how to $ME $ENV $ACTION" >&2
		exit 59
	    fi
	    # do this to close port 22
	    cat /dev/null | eb ssh 
	fi
}

function youhavebeenwarned 
{
    echo "WARNING: THIS MAY KILL ALL THE INSTANCES IN YOUR ENVIRONMENT"
    echo "  you can avoid a service outage by:" 
    echo "    - create a new environment"
    echo "    - change the instance type there"
    echo "    - use 'eb swap' to interchange the environments"
    echo "    - delete the extra environment"
    echo -n "ARE YOU ABSOLUTELY SURE YOU WANT TO PROCEED? "
    read ANSWER
    if [ $ANSWER = y ] || [ $ANSWER = yes ] ||  [ $ANSWER = Y ] || [ $ANSWER = Yes ] ; then 
	echo " --- YOU HAVE BEEN WARNED! ---"
	true
    else 
	false
    fi
}


function ebenvexpand 
{
    # first arg is usually the Environment
    # try several likely combinations
    ENV1=$1
    ENV2=${1}-` appname `
    ENV3=${1}-`basename $PWD`
    if  eb use $ENV1 1>/dev/null 2>/dev/null; then
	ENV=$ENV1
    elif eb use $ENV2 1>/dev/null 2>/dev/null ; then
	ENV=$ENV2
    elif eb use $ENV3 1>/dev/null 2>/dev/null ; then
	ENV=$ENV3
    else 
	cat >&2 <<EOF    
ERROR: cannot find a working environment
ERROR: environment '$ENV1' does not exist
ERROR: environment '$ENV2' does not exist
ERROR: environment '$ENV3' does not exist
  maybe you want one of these:
EOF
	eb list >&2
    fi
    echo $ENV
}

function ebswap 
{
#        $ME env1 swap env2       swap the lb cnames for env and env2
    # $1 is the current environment
    # $2 is the other environment
    if [ -z $2 ] ; then
	echo ERROR: must specify another environment to swap with >&2
    else 
	OTHERENV=`ebenvexpand $2`
	THISENV=`ebenvexpand $1`
	if [ -z $OTHERENV ] ; then 
	    echo ERROR: other environment does not exist, cannot swap >&2
	else
	    ebswap $THISENV --destination_name $OTHERENV
	fi
    fi	
}

function eblogstos3  
{
#        $ME env s3logs true      send logs to s3
#        $ME env s3logs false     do not send logs to s3 (default)
    case $1 in 
	true|True|TRUE|false|False|FALSE)
	    VALUE=`echo $1 | tr A-Z a-z`
	    LOGTOS3=" aws:elasticbeanstalk:hostmanager:  LogPublicationControl '$VALUE' "
	    ebeditconfig $LOGTOS3
	    ;;
	*)
	    echo "ERROR: you must specify either 'true' or 'false' for the log config"
	    ;;
    esac	    
}

function ebnodeploy 
{
#        $ME env nodeploy file    do not deploy file in instances
#
# see http://docs.aws.amazon.com/elasticbeanstalk/latest/dg/customize-containers-ec2.html#linux-container-commands
#     http://www.infoq.com/news/2012/11/elastic-beanstalk-config-files
    NDCONFIG=.ebextensions/${ACTION}.config
    if [ ! -z $1 ] ; then 
	NM=`echo $1 | tr -dc A-Za-z0-9`
	DT=`date +%Y-%m-%d`
	if [ ! -f $NDCONFIG ] ; then 
	    mkdir -p .ebextensions
	    echo "# $NDCONFIG created using $0 on $DT by $USER" >$NDCONFIG
	    echo "container_commands:" >>$NDCONFIG
	fi
    cat >>$NDCONFIG <<EOF
  remove_$NM: # $NM written using $0 on $DT by $USER
    command: "rm -rf $*"
EOF
    git add $NDCONFIG
    fi
cat $NDCONFIG
}

function vpcsubnets
{
#        $ME vpcs                 show available vpcs and subnets
#        $ME vpcs vpc-id          show subnets for given vpc
    shift
    # use args if you have them
    if [ ! -z $1 ] ; then 
	VPCS="$*"
    else
	VPCS=`aws ec2 describe-vpcs | jq .Vpcs[].VpcId | tr -d \" `
    fi

    echo `setregion`
    for VPC in $VPCS 
    do
	echo "    "$VPC
	echo -n "        "
	aws ec2 describe-subnets --filters "Name=vpc-id,Values=$VPC" | jq .Subnets[].CidrBlock | tr -d \" | tr '\n' ' '
	echo " "
    done
}

function ebconfigphperrors
{
#        $ME env phperrors on     turn on display_errors in php.ini
#        $ME env phperrors off    turn on display_errors in php.ini
    if cfgget $EBCONFIG global default_platform | grep PHP >/dev/null ; then
      case $1 in 
	on|On|ON|t|T|true|True|TRUE|0)
	   ERRORS="aws:elasticbeanstalk:container:php:phpini: display_errors 'On'"
	   eb editconfig $ERRORS
	   ;;
	off|Off|f|F|False|false|FALSE|0)
	   ERRORS="aws:elasticbeanstalk:container:php:phpini: display_errors 'On'"
	   eb editconfig $ERRORS
	   ;;	   
	*)
	    echo "ERROR: must specify either on or off" 2>&1
	    ;;
      esac
    fi
}

#function addsg 
#{
#        $ME env addsg            add an existing security group to this env
#    if [ -z "$1" ] ; then 
#	echo "ERROR you must specify the name of a security group" >&1	
#    elif true ; then #  aws ec2 describe-security-groups | jq .SecurityGroups[].GroupName | grep \"$1\" >/dev/null ; then
#	ebappendconfig aws:autoscaling:launchconfiguration: SecurityGroups  $1
#    else
#	echo "ERROR $1 is not the name of a security group" >&1
#    fi
#}

# ----------------------------------------------------------------------
