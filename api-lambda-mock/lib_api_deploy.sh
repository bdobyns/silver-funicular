#!/bin/bash -e 
# blame: barry@productops March 2016
# ... this is the library behind the deploy.sh for REST on aws gateway and aws lambda
ME=`basename $0`
DNZ=`dirname $0`

EC2USER=ec2-user
AWSCONFIG=~/.aws/config
LIBCOMMON=lib_deploy_common.sh
# ----------------------------------------------------------------------
# get the common lib, used by eb, 

if [ -f $DNZ/$LIBCOMMON ] ; then 
    source $DNZ/$LIBCOMMON
elif [ -f $DNZ/lib/$LIBCOMMON ] ; then 
    source $DNZ/lib/$LIBCOMMON    
else
    echo "ERROR: missing $DNZ/$LIBCOMMON"
    exit 1
fi

# ----------------------------------------------------------------------
# ----------------------------------------------------------------------
# ----------------------------------------------------------------------

function api_list_all
{
#        $ME list                 list defined gateways
    aws apigateway get-rest-apis
}
#        $ME import some.json     import swagger 2.0 and make an api gateway
#        $ME vpcs                 show available vpcs and subnets
#        $ME vpcs vpc-id          show subnets for given vpc
#        $ME gway endpoints       list the endpoints (resources)
#        $ME gway mockall         mock all the endpoints in this gateway
#        $ME gway mock endpoint   mock one endpoint in this gateway
#        $ME gway stubs java      create lambda stubs in java for all endpoints
#        $ME gway stubs python    create lambda stubs in pythonfor all endpoints
#        $ME gway stubs node      create lambda stubs in node.js for all endpoints
#        $ME gway stub java endpt create one lambda stub in java for one endpoint
#        $ME gway stub py endpt   create one lambda stub in pythonfor one endpoint
#        $ME gway stub node endpt create one lambda stub in node.js for one endpoint
#        $ME gway models          list the models
#        $ME gway list             list defined environments (stages)
#        $ME gway create env       create an environment (stage) for this gateway
#	$ME gway env deploy     deploy to the given environment
#	$ME gway env update     (same as deploy)
#        $ME gway env describe     describe the gateway and environment (stage)
#        $ME gway env cname        display cname of this gateway+env 
#        $ME gway env r53 f.b.com  wire up the route 53 name to the gateway
