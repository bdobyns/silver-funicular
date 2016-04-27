#!/bin/bash -e 
# blame: barry@productops March 2016
# ... this is the library behind the deploy.sh for REST on aws gateway and aws lambda
ME=$( basename $0 )
DNZ=$( dirname $0 )

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

function endpoint_path_to_camelcase
{
    echo "$1" | tr -d s 'a-zA-Z0-9' '_' | sed 's/_\([a-z]\)/\u\1/g'
}

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
export API_ENDPOINTS=$( api_endpoints ) # cache it for subsequent use

function api_endpoint_ids
{
    # use $GWAY_ID to list the endpoint paths and ids
    api_endpoints | jq -r '.items[].id' 2>/dev/null
}

function api_endpoint_paths
{
    # use $GWAY_ID to list the endpoint paths and ids
    api_endpoints | jq -r '.items[].path' 2>/dev/null
}

function api_endpoint_id_exists
{
    FOO=$( api_endpoint_path_from_id )
    if [ -z "$FOO" ] ; then
	false
    else
	true
    fi
}

function api_endpoint_path_exists
{
    BAR=$( api_endpoint_id_from_path )
    if [ -z "$BAR" ] ; then
	false
    else
	true
    fi

}

function api_endpoint_id_from_path
{
    # path in $1
    api_endpoints | jq -r ' .items[] | select(.path == "'"$1"'") | .id ' 2>/dev/null
}

function api_endpoint_path_from_id
{
    # id in $1
    api_endpoints | jq -r ' .items[] | select(.id == "'"$1"'") | .path ' 2>/dev/null
}

function api_endpoint_methods_from_id
{
    api_endpoints | jq -r ' .items[] | select(.id == "'"$1"'") | .resourceMethods | keys ' 2>/dev/null | tr -dc 'A-Z \n'
}

# ENVIRONMENTS (STAGES) IN $GWAY_ID
function api_env_name_exists
{
    api_stage_list | grep '^'"$1"'$' >/dev/null
}

# ROUTE53 MAGIC

function route53_find_cname
{
# given a cname in $1 return the route53 name bound to it, if any
    aws route53 list-hosted-zones | jq -r .HostedZones[].Id | cut -d / -f 3 | while read ZONE
    do
	RRSET=$( aws route53 list-resource-record-sets --hosted-zone-id $ZONE )
	if echo $RRSET | jq . | grep "$1">/dev/null ; then
	    # yep this one is in there
	    echo $RRSET | jq -r ' .ResourceRecordSets[] | select(.ResourceRecords[].Value == "'"$1"'") | .Name '
	    break
	fi
    done
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
export API_LIST_ALL=$( api_list_all )  # cache it for subsequent use

function api_import_json
{
#        $ME import some.json     import swagger 2.0 and make an api gateway
    IMPORTER=aws-apigateway-importer/aws-api-import.sh
    IMPORTERDIR=$( dirname $IMPORTER )
    IMPORTERSH=$( basename $IMPORTER )
    if [ ! -f $1 ] ; then
	echo "ERROR: the json swagger 2.0 spec '$1' does not exist"
	givehelp
    elif ! cat $1 | jq . >/dev/null 2>/dev/null ; then
	echo "ERROR: '$1' is not valid json"
	givehelp
    elif [ -f $IMPORTER ] ; then
	APINAME=$( cat $1 | jq -r .info.title )
	if api_gway_name_exists "$APINAME"
	then
	    echo "ERROR: API '$APINAME' already exists, maybe you want to update instead"
	    echo " "
	    givehelp
	else
	    cd $IMPORTERDIR
	    ./$IMPORTERSH --create ../$1
	fi
    else
	echo "ERROR: you do not have '$IMPORTER' available"
	echo "   ... did you "
	echo '       git submodule add git@github.com:awslabs/aws-apigateway-importer.git'
	givehelp
    fi
}

function api_update_json
{
#        $ME gway update some.json  update gateway with swagger spec
    IMPORTER=aws-apigateway-importer/aws-api-import.sh
    IMPORTERDIR=$( dirname $IMPORTER )
    IMPORTERSH=$( basename $IMPORTER )
    if [ ! -f $1 ] ; then
	echo "ERROR: the json swagger 2.0 spec '$1' does not exist"
	givehelp
    elif ! cat $1 | jq . >/dev/null 2>/dev/null ; then
	echo "ERROR: '$1' is not valid json"
	givehelp
    elif [ -f $IMPORTER ] ; then
	APINAME=$( cat $1 | jq -r .info.title )
	if api_gway_name_exists "$APINAME"
	then
	    cd $IMPORTERDIR
	    ./$IMPORTERSH --update $GWAY_ID ../$1
	else
	    echo "ERROR: API '$APINAME' does not exist, cannot update. "
	    echo "       maybe you wanted to create instead"
	    echo " "
	    givehelp
	fi
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
    api_endpoints
}


function api_mockall
{
#        $ME gway mockall         mock all the endpoints in this gateway
#   PASS=0
    for ENDPOINT_ID in $( api_endpoint_ids )
    do
	api_mock_one_by_id $ENDPOINT_ID

#	PASS=$[ $PASS + 1 ]
#	if [ $PASS -ge 2 ] ; then break ; fi
    done
}

function api_mock_one_by_id
{
#    $1 is the resource-id of the endpoint in question
    ENDPOINT_ID=$1
    REGION=$( awsregion )
    STATUS200="--status-code 200"
    for METHOD in $( api_endpoint_methods_from_id $ENDPOINT_ID )
    do
	# see https://alestic.com/2015/11/amazon-api-gateway-aws-cli-redirect/
	echo "$METHOD "$( api_endpoint_path_from_id $ENDPOINT_ID )
	METHODARGS="--region $REGION --rest-api-id $GWAY_ID --resource-id $ENDPOINT_ID --http-method $METHOD "
	# this is not necessary if we already have a method response defined (as in via swagger)
	if ! aws apigateway get-method-response $METHODARGS $STATUS200 2>/dev/null >/dev/null
	then
	aws apigateway put-method-response \
	    $METHODARGS \
	    $STATUS200 \
	    --response-models '{"application/json":"Empty"}' \
	    --response-parameters '{"method.response.header.Location":true}'
	fi
	RESPONSEOBJ=$( aws apigateway get-method-response $METHODARGS $STATUS200 --query 'responseModels.* | [0]' )
	# not necessary if already done
	if ! aws apigateway get-integration $METHODARGS 2>/dev/null >/dev/null
        then
	aws apigateway put-integration \
	    $METHODARGS \
	    --request-templates '{"application/json":"{\"statusCode\": 200}"}'  \
	    --type MOCK
	fi
	if ! aws apigateway get-integration-response $METHODARGS $STATUS200 2>/dev/null >/dev/null
	then
	aws apigateway put-integration-response \
	    $METHODARGS \
	    $STATUS200 \
	    --response-templates '{"application/json": '"$RESPONSEOBJ"'}'  \
	    --selection-pattern '\d{3}'  # this means any numeric code, as 5\d{2} means any 5xx code.
#	    --response-parameters \
#	    '{"method.response.header.Location":"'"'$target_url'"'"}'
	fi
    done
}

function api_mock_one  # [ $ENDPOINT_ID | $ENDPOINT_PATH ]
{
#        $ME gway mock endpoint   mock one endpoint in this gateway
    ID=$( api_endpint_id_from_path "$1" )
    PATH=$( api_endpoint_path_from_id "$1" )
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

function api_get_model_names
{
# return the names of the models in this $GWAY_ID
    aws apigateway get-models --rest-api-id $GWAY_ID --query items[].name | jq -r '.[]'
}

function api_nodejs_one_by_id # ENDPOINT_ID
{
#        $ME gway stub node endpt create one lambda stub in node.js for one endpoint
    TODAY=$( date +%Y-%m-%d )
    ENDPOINT_ID=$( api_endpoint_id_from_path "$1" )   # we don't care if they gave us a endpoint id
    ENDPOINT_PATH=$( api_endpoint_path_from_id "$1" ) # or a endpoint path, because we convert it 
    for METHOD in $( api_endpoint_methods_from_id $ENDPOINT_ID )
    do
	JSPATH=nodejs/$( $ENDPOINT_PATH/$METHOD )
	mkdir -p $JSPATH
	JSMETHOD=$JSPATH/handler.js
cat >$JSMETHOD <<EOF
// $JSMETHOD
// AWS Lambda Handler for $METHOD $ENDPOINT_PATH
// for $GWAY_NAME
// 
// auto-generated by $0 on $TODAY by $USER

var AWS = require('aws-sdk');
var MAX_OUTPUT = 1024 * 1024 * 1024; // 1 GB
var ctx = context;
exports.handler = function(event, context) {
            var result = {
                 "event": = event,
                 "context": = ctx
            };
            context.succeed(result);
        }
    );
}
EOF
    done
}

function api_stub_nodejs_all
{

#        $ME gway stubs node      create lambda stubs in node.js for all endpoints
    # make some models first, since any one lambda probably needs all the models
    if ! api_stub_models_java $1 ; then exit ; fi

    for ENDPOINT_ID in $( api_endpoint_ids )
    do
	api_stub_nodejs_one_by_id $ENDPOINT_ID
    done
}

function api_stub_models_json_one # $MODELNAME $MODELPATH
{
MODELNAME="$1" # $1 = $MODELNAME  typically GET foo/${id}/baz -> fooIdBazGet
JSMODELPATH="$2" # $2 = $MODELPATH  typically nodejs/endpoint
if [ -z "$MODENAME" ] ; then 
    echo ERROR: you must specify a modelname to download
elif [ -z "$MODELPATH" ] ; then
    echo ERROR: you must specify a modelpath to write to
fi
	echo model $MODELNAME
	mkdir -p $JSMODELPATH
	JSMODEL=$JSMODELPATH/${MODELNAME}.json
	aws apigateway get-model --rest-api-id $GWAY_ID --model-name $MODELNAME | jq -r .schema >$JSMODEL
}

function api_stub_models_json_all
{
    JSMODELPATH=nodejs/model
    MODEL_LIST=$( api_get_model_names )
    for MODELNAME in $MODEL_LIST
    do
	api_stub_models_json_one $MODELNAME $JSMODELPATH
    done

    
    for ENDPOINT_ID in $( api_endpoint_ids )
    do
        api_nodejs_one_by_id $ENDPOINT_ID
    done
}

function api_stub_models_java # com.you.project.packagename
{
# $1 = com.you.project.packagename    
PKGNAME="$1"
if [ -z "$PKGNAME" ]; then
    echo "ERROR: you must specify a package name for the top of the package to be generated"
    echo "       e.g.   com.1e80.farce.comedyapi   or    org.cirrostratus.subman.productcatalog"
    givehelp
    exit 1
fi
    JAVACODE=src/main/java
    mkdir -p $JAVACODE
    JSMODELPATH=src/nodejs/model
    MODEL_LIST=$( api_get_model_names )
    for MODELNAME in $MODEL_LIST
    do
	api_stub_models_json_one $MODELNAME $JSMODELPATH

	jsonschema2pojo \
	    --source $JSMODEL \
	    --target $JAVACODE \
	    --annotation-style JACKSON2 \
	    --class-prefix ${PKGNAME}.model \
	    --package ${PKGNAME}.model
#	    --generate-builders \
#	    --generate-constructors \
#	    --joda-dates \

    done
}

function api_stub_java_one_by_id
{
    ENDPOINTID="$1"
    if ! api_endpoint_id_exists "$ENDPOINTID" ; then
	echo "ERROR: 'id' is not an endpoint id"
	givehelp
	exit
    else
	for METHOD in $( api_endpoint_methods_from_id $ENDPOINTID )
	do
	    ENDPOINT_PATH=$( api_endpoint_path_from_id )
	    METHOD_DETAILS=$( apigateway get-method --rest-api-id $GWAY_ID --resource-id $ENDPOINTID --http-method $METHOD )
	done
    fi
}

function api_stub_java_one
{
#        $ME gway stub java endpt create one lambda stub in java for one endpoint
    ENDPOINTID=$( api_endpoint_id_from_path "$1" )   # we don't care if they gave us a endpoint id
    ENDPOINTPATH=$( api_endpoint_path_from_id "$1" ) # or a endpoint path, because we convert it 
#    ENDPOINTID="$1"
    if ! api_endpoint_id_exists "$ENDPOINTID" ; then
	echo "ERROR: 'id' is not an endpoint id"
	givehelp
	exit
    else
        # make some models first, since any one lambda probably needs all the models
	if ! api_stub_models_java $ENDPOINTID ; then exit ; fi
#	api_stub_java_one_by_id $ENDPOINTID
    fi
}

function api_stub_java_all
{
#        $ME gway stubs java com.you.project.packagename 
#                                   create lambda stubs in java for all endpoints

    # make some models first, since any one lambda probably needs all the models
    if ! api_stub_models_java $1 ; then exit ; fi

    for ENDPOINT_ID in $( api_endpoint_ids )
    do
	api_stub_java_one_by_id $ENDPOINT_ID
    done
}

#        $ME gway stubs python    create lambda stubs in pythonfor all endpoints
#        $ME gway stubs node      create lambda stubs in node.js for all endpoints
#        $ME gway stub java endpt create one lambda stub in java for one endpoint
#        $ME gway stub py endpt   create one lambda stub in pythonfor one endpoint
#        $ME gway models          list the models

function api_stage_list
{
#        $ME gway list             list defined environments (stages)
    aws apigateway get-stages --rest-api-id $GWAY_ID | jq -r .item[].stageName
}

function api_env_create
{
#        $ME gway create env       create an environment (stage) for this gateway
    aws apigateway create-deployment --rest-api-id $GWAY_ID --stage-name $1
}

function api_env_deploy
{
#	 $ME gway env update     (same as deploy)
#  	 $ME gway env deploy     deploy to the given environment
    aws apigateway create-deployment --rest-api-id $GWAY_ID --stage-name $ENV_NAME 
}

function api_env_describe
{
#        $ME gway env describe     describe the gateway and environment (stage)
    aws apigateway get-stage --rest-api-id $GWAY_ID --stage-name $ENV_NAME
}


function api_env_uri
{
#        $ME gway env cname        display cname of this gateway+env 
    AWSHOST=$GWAY_ID.execute-api.$( awsregion ).amazonaws.com
    R53NAME=$( route53_find_cname "$AWSHOST" )
    if [ ! -z "$R53NAME" ] ; then
	echo "https://"$R53NAME/$ENV_NAME
    else
	echo "https://"$AWSHOST/$ENV_NAME
    fi
    
}

function api_route53_wire
{
#        $ME gway env r53 f.b.com  wire up the route 53 name to the gateway
    route53wire $( api_uri | cut -f 3 -d / ) $1
}


#eof
