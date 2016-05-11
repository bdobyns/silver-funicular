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

function txt_to_camelcase
{
    echo "$1" | tr 'A-Z' 'a-z' | tr -c -s 'a-zA-Z0-9' '_' | gsed -e 's/_\([a-z]\)/_\u\1/g' -e 's/^_//' -e 's/_$//'
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
	if [ -z "$CACHED_API_ENDPOINTS" ] ; then 
	    aws apigateway get-resources --rest-api-id $GWAY_ID
	else
	    echo "$CACHED_API_ENDPOINTS"
	fi
    fi
}
export CACHED_API_ENDPOINTS=$( api_endpoints ) # cache it for subsequent use

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

function api_endpoint_id_exists # ID
{
    FOO=$( api_endpoints | jq -r ' .items[] | select(.id == "'"$1"'") | .path ' 2>/dev/null )
    if [ -z "$FOO" ] ; then
	false
    else
	true
    fi
}

function api_endpoint_path_exists # PATH
{
    BAR=$( api_endpoints | jq -r ' .items[] | select(.path == "'"$1"'") | .id ' 2>/dev/null )
    if [ -z "$BAR" ] ; then
	false
    else
	true
    fi

}

function api_endpoint_id_from_path # PATH
{
    if api_endpoint_id_exists "$1"
    then
	# pfft they gave us the answer, not the question
	echo "$1"
    else
        # path in $1
	api_endpoints | jq -r ' .items[] | select(.path == "'"$1"'") | .id ' 2>/dev/null
    fi
}

function api_endpoint_path_from_id # ID
{
    if api_endpoint_path_exists "$1"
    then
	# pfft they gave us the answer, not the question
	echo "$1"
    else
        # id in $1
	api_endpoints | jq -r ' .items[] | select(.id == "'"$1"'") | .path ' 2>/dev/null
    fi
}

function api_endpoint_methods_from_id # ID
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
	echo "ERROR: the json swagger 2.0 spec '$1' does not exist ($FUNCNAME)"
	givehelp
    elif ! cat $1 | jq . >/dev/null 2>/dev/null ; then
	echo "ERROR: '$1' is not valid json"
	givehelp
    elif [ -f $IMPORTER ] ; then
	APINAME=$( cat $1 | jq -r .info.title )
	if api_gway_name_exists "$APINAME"
	then
	    echo "ERROR: API '$APINAME' already exists, maybe you want to update instead ($FUNCNAME)"
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


function api_get_model_names
{
# return the names of the models in this $GWAY_ID
    aws apigateway get-models --rest-api-id $GWAY_ID --query items[].name | jq -r '.[]'
}


#==== M O C K ======================================================================

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
    if api_endpoint_id_exists "$1" ; then
	ENDPOINT_ID="$1"
	ENDPOINT_PATH=$( api_endpoint_path_from_id $ENDPOINT_ID)
    elif api_endpoint_path_exists "$1" ; then
	ENDPOINT_ID=$( api_endpoint_id_from_path "$1" )
	ENDPOINT_PATH="$1"
    else
	echo "ERROR: '$1' is not an endpoint id ($FUNCNAME)"
	exit
    fi

    if [ ! -z "$ENDPOINT_ID" ] ; then
	# they gave us the path, we found the id
	api_mock_one_by_id "$ID"
    elif [ ! -z "$ENDPOINT_PATH" ] ; then
	# they gave us the id, we validated by finding a path
	api_mock_one_by_id "$ENDPOINT_ID"
    else
	echo "ERROR: '$1' is neither the path of an endpoint, nor the id (api_mock_one)"
	givehelp
    fi
}

#= N O D E  = S T U B S =====================================================================

function api_write_nodejs_code # ENDPOINT_ID METHOD_NAME
{
    ENDPOINT_ID=$1
    METHOD_NAME=$2
    if [ -z $ENDPOINT_ID ] ; then
	echo "ERROR: '$1' is not a vaild endpoint ($FUNCNAME)"
    elif [ -z $METHOD_NAME ] ; then
	echo "ERROR: '$2' is not a valid method name ($FUNCNAME)"
	exit
    fi
    TODAY=$( date +%Y-%m-%d )

    for METHOD in $( api_endpoint_methods_from_id $ENDPOINT_ID )
    do
	METHOD_DETAILS=$( aws apigateway get-method --rest-api-id $GWAY_ID --resource-id $ENDPOINT_ID --http-method $METHOD )

	if [ -z $METHOD_NAME ] ; then
	    echo ERROR "$ENDPOINT_PATH/$METHOD ($FUNCNAME)"
	fi
	JSPATH=nodejs/$METHOD_NAME
	mkdir -p $JSPATH
	JSMETHOD=$JSPATH/${METHOD_NAME}.js
	FULLNAME=$( id -F )
	if [ -z "$FULLNAME" ] ; then FULLNAME="$USER@$HOSTNAME" ; fi
	echo endpoint "$ENDPOINT_PATH" method "$METHOD" in $JSMETHOD


# this is the actual lambda method itself
cat >$JSMETHOD <<EOF
/* $JSMETHOD
 * node.js AWS Lambda Handler for $METHOD on $ENDPOINT_PATH
 *     in gateway $GWAY_NAME
 * 
 * auto-generated 
 *   by $0 
 *   on $TODAY 
 *   by $FULLNAME ($USER@$HOSTNAME) 
 *   in $PWD
 *   MUST use nodejs4.3
 */

EOF

     RESULT_MODEL=$( echo $METHOD_DETAILS | jq -r ' .methodResponses."200".responseModels[]' )
     if [ ! -z "$RESULT_MODEL" ] ; then
cat >>$JSMETHOD <<EOF
/* the returned result of the $METHOD needs to be a '$RESULT_MODEL' object
 *
 * so we give you a schema model that you can extend with a constructor
 *   and comes with a validator that you can use right away
 */
/*
import SchemaClass from 'json-schema-class';  // https://www.npmjs.com/package/json-schema-class
class ${RESULT_MODEL}Schema = new SchemaClass(
EOF
JSMODELPATH=nodejs/model
         # we pipe it thru jq to format it pretty
	 cat $JSMODELPATH/${RESULT_MODEL}.json | jq . >>$JSMETHOD
cat >>$JSMETHOD <<EOF
);
*/


/*
// write your own class with a constructor here.  something like this:

class $RESULT_MODEL extends ${RESULT_MODEL}Schema {
    constructor(id, foo, bar ...) {
        this.id = id;
        this.foo = foo;
        this.bar = bar;

        this.validate(this);
    }
}
*/
EOF
     fi	 

cat >>$JSMETHOD <<EOF
var AWS = require('aws-sdk');
var _ = require('lodash');

/* This is your handler for the AWS Gateway $GWAY_NAME on endpoint $ENDPOINT_PATH method $METHOD
 *
 * @param event {object} the event data given to your method.  your REST parameters will be event.paramname or event.body
 *    see https://docs.aws.amazon.com/lambda/latest/dg/nodejs-prog-model-handler.html
 * @param context {object} a context object for your invocation
 *    see https://docs.aws.amazon.com/lambda/latest/dg/nodejs-prog-model-context.html
 * @param callback {function} takes two args (Error error, Object result)
 *    see https://docs.aws.amazon.com/lambda/latest/dg/nodejs-prog-model-handler.html#nodejs-prog-model-handler-callback
 */
exports.handler = function(event, context, callback) {

            var result = event;  // this is just a passthru until you write your own code
            // var result = new ${RESULT_MODEL}();

            // for nodejs4.3 you use the callback
            callback(null,result);
        }
    );
}
EOF

# $JSMETHOD is going to need a package.json
cat >$JSPATH/package.json <<EOF  
{
  "name": "$JSMETHOD",
  "description": "node.js AWS Lambda Handler for $METHOD on endpoint '$ENDPOINT_PATH' in '$GWAY_NAME' ",
  "version": "0.0.1",
  "author": {
      "name": "$FULLNAME"
      "email": "$USER@$HOSTNAME",
      "__blame": "auto-generated by $0 on $TODAY by $USER@$HOSTNAME in $PWD"
  },
  "license": "private, not open source"
  "dependencies": {
      "aws-sdk"   
      "json-schema-class",
      "lodash"
  },
  "devDependencies": {
   
  }
}
EOF
    done
}

function api_wire_nodejs_code # ENDPOINT_ID METHOD_NAME
{
    ENDPOINT_ID=$1
    METHOD_NAME=$2
    if [ -z $ENDPOINT_ID ] ; then
	echo "ERROR: '$1' is not a vaild endpoint ($FUNCNAME)"
    elif [ -z $METHOD_NAME ] ; then
	echo "ERROR: '$2' is not a valid method name ($FUNCNAME)"
	exit
    fi
#   echo "$FUNCNAME not implemented yet, not really"
}

function 	api_stub_nodejs_one_by_id # ENDPOINT_ID 
{
#        $ME gway stub node endpt create one lambda stub in node.js for one endpoint
    if api_endpoint_id_exists "$1" ; then
	ENDPOINT_ID="$1"
	ENDPOINT_PATH=$( api_endpoint_path_from_id $ENDPOINT_ID)
    elif api_endpoint_path_exists "$1" ; then
	ENDPOINT_ID=$( api_endpoint_id_from_path "$1" )
	ENDPOINT_PATH="$1"
    else
	echo "ERROR: '$1' is not an endpoint id ($FUNCNAME)"
	exit
    fi

    METHOD_NAME=$( txt_to_camelcase "$ENDPOINT_PATH/$METHOD" | tr -d _ )
    api_write_nodejs_code $ENDPOINT_ID $METHOD_NAME
    api_wire_nodejs_code $ENDPOINT_ID $METHOD_NAME
}

function api_stub_models_json_one # $MODELNAME $MODELPATH
{
MODELNAME="$1" # $1 = $MODELNAME  typically GET foo/${id}/baz -> fooIdBazGet
JSMODELPATH="$2" # $2 = $MODELPATH  typically nodejs/endpoint
if [ -z "$MODELNAME" ] ; then 
    echo "ERROR: you must specify a modelname to download ($FUNCNAME)"
    exit # kill -ABRT $$
elif [ -z "$JSMODELPATH" ] ; then
    echo "ERROR: you must specify a modelpath to write to ($FUNCNAME)"
    exit # kill -ABRT $$
fi
	mkdir -p $JSMODELPATH
	JSMODEL=$JSMODELPATH/${MODELNAME}.json
	if [ ! -e $JSMODEL ] ; then 
	    echo model $MODELNAME
	    aws apigateway get-model --rest-api-id $GWAY_ID --model-name $MODELNAME | jq -r .schema >$JSMODEL
	fi
}

function api_stub_models_node
{
    JSMODELPATH=nodejs/model
    MODEL_LIST=$( api_get_model_names )
    for MODELNAME in $MODEL_LIST
    do
	api_stub_models_json_one $MODELNAME $JSMODELPATH
    done
}

function api_stub_nodejs_all
{
echo "ENTER: stub nodejs all"
#        $ME gway stubs node      create lambda stubs in node.js for all endpoints
    # make some models first, since any one lambda probably needs at least one model
    if ! api_stub_models_node $1 ; then exit ; fi

    # now create a lambda for each endpoint/method pair    
    for ENDPOINT_ID in $( api_endpoint_ids )
    do
	if ! api_stub_nodejs_one_by_id $ENDPOINT_ID ; then exit ; fi
    done

echo "EXIT: stub nodejs all"

}

#= J A V A = S T U B S =====================================================================

function api_stub_models_java # com.you.project.packagename
{
# $1 = com.you.project.packagename    
PKGNAME="$1"
if [ -z "$PKGNAME" ]; then
    echo "ERROR: you must specify a package name for the top of the package to be generated ($FUNCNAME)"
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

function api_stub_java_one_by_id # $ENDPOINT_ID
{
    if api_endpoint_id_exists "$1" ; then
	ENDPOINT_ID="$1"
	ENDPOINT_PATH=$( api_endpoint_path_from_id $ENDPOINT_ID)
    elif api_endpoint_path_exists "$1" ; then
	ENDPOINT_ID=$( api_endpoint_id_from_path "$1" )
	ENDPOINT_PATH="$1"
    else
	echo "ERROR: '$1' is not an endpoint id ($FUNCNAME)"
	exit
    fi

    # oky, now work
    for METHOD in $( api_endpoint_methods_from_id $ENDPOINT_ID )
    do
	echo endpoint $ENDPOINT_PATH method $METHOD
	METHOD_DETAILS=$( aws apigateway get-method --rest-api-id "$GWAY_ID" --resource-id "$ENDPOINT_ID" --http-method "$METHOD" )
    done
}

function api_stub_java_one # ENDPOINT_ID
{
#        $ME gway stub java endpt create one lambda stub in java for one endpoint
    if api_endpoint_id_exists "$1" ; then
	ENDPOINT_ID="$1"
	ENDPOINT_PATH=$( api_endpoint_path_from_id $ENDPOINT_ID)
    elif api_endpoint_path_exists "$1" ; then
	ENDPOINT_ID=$( api_endpoint_id_from_path "$1" )
	ENDPOINT_PATH="$1"
    else
	echo "ERROR: '$1' is not an endpoint id ($FUNCNAME)"
	exit
    fi

    # make some models first, since any one lambda probably needs all the models
    if ! api_stub_models_java $ENDPOINT_ID ; then exit ; fi
    api_stub_java_one_by_id $ENDPOINT_ID
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

#==== I N F O ======================================================================

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
