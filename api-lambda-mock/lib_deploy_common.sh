#!/bin/bash -e
# blame: barry@productops.com  Feb 2016
# ... this is the library behind the deploy.sh for elastic beanstalk
ME=`basename $0`
DNZ=`dirname $0`

EBCONFIG=".elasticbeanstalk/config.yml"  # must be in current dir
EC2USER=ec2-user
AWSCONFIG=~/.aws/config

# ----------------------------------------------------------------------
# ----------------------------------------------------------------------
# not all verbs write history, as some are merely informative
function write_history 
{
    NOW=`date "+%Y-%m-%d %H:%M"`
    HFILE=.deploy_history
    if [ ! -f $HFILE ] ; then 
	HERE=`basename $PWD`
	cat >$HFILE <<EOF 
#!/bin/bash -x -e
# History file for $HERE started on $NOW by $LOGNAME@$HOSTNAME 

EOF
	git add $HFILE
    fi
    if [ $1 = create ] || [ $1 = new ] ; then echo " " >>$HFILE ; fi     # throw an extra newline before a create
    cat >>$HFILE <<EOF
$DNZ/$ME $*  # by $LOGNAME@$HOSTNAME on $NOW
EOF
}


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
  "Comment": "$ME for AWS EB by '$LOGNAME' on '$HOSTNAME' in '$PWD'", 
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


# eof
