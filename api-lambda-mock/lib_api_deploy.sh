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
# helper functions for API work

# GATEWAYS
function api_gway_name_exists
{
    # the name we're trying to find is in $1
    api_list_all | jq -r .items[].name | grep '^'"$1"'$' >/dev/null
}

function api_gway_id_exists
{
    # the id we're trying to find is in $1
    api_list_all | jq -r .items[].id | grep '^'"$1"'$' >/dev/null
}
function api_gway_names
{
    api_list_all | jq -r .items[].name 
}

function api_gway_id_from_name
{
    api_list_all | jq -r ' .items[] | select(.name == "'"$1"'") | .id '
}

function api_gway_name_from_id
{
    api_list_all | jq -r ' .items[] | select(.id == "'"$1"'") | .name '
}

# ENDPOINTS IN $GWAY_ID
function api_endpoints
{
    if [ ! -z $GWAY_ID ] ; then
        # use $GWAY_ID to list the endpoint paths and ids
	if [ -z "$API_ENDPOINTS" ] ; then 
	    aws apigateway get-resources --rest-api-id $GWAY_ID
	else
	    echo "$API_ENDPOINTS"
	fi
    fi
}
export API_ENDPOINTS=`api_endpoints` # cache it for subsequent use

function api_endpoint_ids
{
    # use $GWAY_ID to list the endpoint paths and ids
    api_endpoints | jq -r '.items[].id'
}

function api_endpoint_paths
{
    # use $GWAY_ID to list the endpoint paths and ids
    api_endpoints | jq -r '.items[].path'
}

function api_endpoint_id_from_path
{
    # path in $1
    api_endpoints | jq -r ' .items[] | select(.path == "'"$1"'") | .id '
}

function api_endpoint_path_from_id
{
    # id in $1
    api_endpoints | jq -r ' .items[] | select(.id == "'"$1"'") | .path '
}

function api_endpoint_methods_from_id
{
    api_endpoints | jq -r ' .items[] | select(.id == "'"$1"'") | .resourceMethods | keys ' | tr -dc 'A-Z \n'
}

# ----------------------------------------------------------------------
# ----------------------------------------------------------------------
# METHODS CALLED DIRECTLY BY THE VERB CASES

function api_list_all
{
#        $ME list                 list defined gateways
    if [ -z "$API_LIST_ALL" ] ; then
	aws apigateway get-rest-apis
    else
	echo "$API_LIST_ALL"
    fi
}
export API_LIST_ALL=`api_list_all`  # cache it for subsequent use

function api_import_json
{
#        $ME import some.json     import swagger 2.0 and make an api gateway
    IMPORTER=aws-apigateway-importer/aws-api-import.sh
    IMPORTERDIR=`dirname $IMPORTER`
    IMPORTERSH=`basename $IMPORTER`
    if [ ! -f $1 ] ; then
	echo "ERROR: the json swagger 2.0 spec '$1' does not exist"
	givehelp
    elif ! cat $1 | jq . >/dev/null 2>/dev/null
	echo "ERROR: '$1' is not valid json"
	givehelp
    elif [ -f $IMPORTER ] ; then
	cd $IMPORTERDIR
	./$IMPORTERSH -c ../$1
    else
	echo "ERROR: you do not have '$IMPORTER' available"
	echo "   ... did you "
	echo '       git submodule add git@github.com:awslabs/aws-apigateway-importer.git'
	givehelp
    fi
}
function api_update_json
{
#        $ME gway update some.json  import swagger 2.0 and update gway   
    IMPORTER=aws-apigateway-importer/aws-api-import.sh
    IMPORTERDIR=`dirname $IMPORTER`
    IMPORTERSH=`basename $IMPORTER`
    if [ ! -f $1 ] ; then
	echo "ERROR: the json swagger 2.0 spec '$1' does not exist"
	givehelp
    elif ! cat $1 | jq . >/dev/null 2>/dev/null
	echo "ERROR: '$1' is not valid json"
	givehelp
    elif [ -f $IMPORTER ] ; then
	cd $IMPORTERDIR
	./$IMPORTERSH --update $GWAY_ID ../$1
    else
	echo "ERROR: you do not have '$IMPORTER' available"
	echo "   ... did you "
	echo '       git submodule add git@github.com:awslabs/aws-apigateway-importer.git'
	givehelp
    fi
}


#        $ME vpcs                 show available vpcs and subnets
#        $ME vpcs vpc-id          show subnets for given vpc

function api_gway_endpoints
{
#        $ME gway endpoints       list the endpoints (resources)
    api_list_all $GWAY_ID
}


function api_mockall
{
#        $ME gway mockall         mock all the endpoints in this gateway

    for ID in `api_endpoint_ids`
    do
	api_mock_one_by_id 
    done
}

function api_mock_one_by_id
{
#    $1 is the resource-id of the endpoint in question
    ENDPOINT_ID=$1
    for METHOD in `api_endpoint_menthods_from_id $ENDPOINT_ID`
    do
	aws apigateway put-integration --rest-api-id $GWAY_ID --resource-id $ENDPOINT_ID --http-method $METHOD --type MOCK
    done
}

function api_mock_one
{
#        $ME gway mock endpoint   mock one endpoint in this gateway
    ID=`api_endpint_id_from_path "$1" `
    PATH=`api_endpoint_path_from_id "$1" `
    if [ ! -z "$ID" ] ; then
	# they gave us the path, we found the id
	api_mock_one_by_id "$ID"
    elif [ ! -z "$PATH" ] ; then
	# they gave us the id, we validated by finding a path
	api_mock_one_by_id "$1"
    else
	echo "ERROR: '$1' is neither the path of an endpoint, nor the id"
	givehelp
    fi
}

#        $ME gway stubs java      create lambda stubs in java for all endpoints
#        $ME gway stubs python    create lambda stubs in pythonfor all endpoints
#        $ME gway stubs node      create lambda stubs in node.js for all endpoints
#        $ME gway stub java endpt create one lambda stub in java for one endpoint
#        $ME gway stub py endpt   create one lambda stub in pythonfor one endpoint
#        $ME gway stub node endpt create one lambda stub in node.js for one endpoint
#        $ME gway models          list the models
#        $ME gway list             list defined environments (stages)
#        $ME gway create env       create an environment (stage) for this gateway
#  	 $ME gway env deploy     deploy to the given environment
#	 $ME gway env update     (same as deploy)
#        $ME gway env describe     describe the gateway and environment (stage)
#        $ME gway env cname        display cname of this gateway+env 
#        $ME gway env r53 f.b.com  wire up the route 53 name to the gateway
