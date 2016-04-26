#!/bin/bash -e
# blame: barry@productops.com  Feb 2016


ME=$( basename $0 )
DNZ=$( dirname $0 )
LIBAPIDEPLOY=lib_api_deploy.sh

givehelp()
{
cat <<EOF

STANDARD VERBS:
	$ME gway env deploy              deploy all endpoints 
	$ME gway env deploy endpoint     deploy one named endpoint

AWS API GATEWAY VERBS:
        $ME import some.json       import swagger 2.0 and make an api gateway
        $ME list                   list defined gateways
        $ME gway update some.json  update api gateway with swagger spec
        $ME gway endpoints         list the endpoints (resources)
        $ME gway models            list the models

        $ME gway mockall           mock all the endpoints in this gateway
        $ME gway stubs java com.you.project.packagename 
                                   create lambda stubs in java (meodel only)
        $ME gway stubs python      create lambda stubs in python for all endpoints
        $ME gway stubs node        create lambda stubs in node.js for all endpoints

        $ME gway mock endpoint     mock one endpoint in this gateway
        $ME gway stub java endpt   create one lambda stub in java for one endpoint
        $ME gway stub py endpt     create one lambda stub in python for one endpoint
        $ME gway stub node endpt   create one lambda stub in node.js for one endpoint
        $ME gway route53 a.b.com   wire up the gateway to route53 name a.b.com

ENVIRONMENT VERBS:
        $ME gway list               list defined environments (stages)
        $ME gway create env         create an environment (stage) for this gateway
        $ME gway env describe       describe the gateway and environment (stage)
        $ME gway env uri            display uri of this gateway+env 

OTHER HANDY STUFF:
        $ME vpcs                   show all available vpcs and subnets
        $ME vpcs vpc-id            show subnets for a given vpc

SORRY FAIL:
	$ME gway env update      (same as deploy)
	$ME env ssh              not implemented/nonsense
	$ME env put here there   not implemented/nonsense
	$ME env get there here   not implemented/nonsense
        $ME env open             not implemented/nonsense
        $ME env use              not implemented/nonsense
        $ME local run            not implemented/nonsense

EOF
	exit 3
}

# ----------------------------------------------------------------------

if [ -f $DNZ/$LIBAPIDEPLOY ] ; then 
    source $DNZ/$LIBAPIDEPLOY
elif [ -f $DNZ/lib/$LIBAPIDEPLOY ] ; then 
    source $DNZ/lib/$LIBAPIDEPLOY    
else
    echo "ERROR: missing $DNZ/$LIBAPIDEPLOY"
    exit 1
fi

# ----------------------------------------------------------------------

# detect no args whatsoever
if [ -z "$1" ] ; then 
    givehelp
    exit 31
fi

# Process the 'no gway' verbs
case "$1" in
    # first arg is usually the gateway
    # but sometiemes it's a verb
#        $ME import some.json     import swagger 2.0 and make an api gateway
	import)
	    shift
	    api_import_json $*  # could have --name foo
	    exit 
	    ;;
#        $ME list                 list defined gateways
        list)
            api_list_all
	    exit
	    ;;
        vpcs)
#        $ME vpcs                 show available vpcs and subnets
#        $ME vpcs vpc-id          show subnets for given vpc
	    shift
	    vpcsubnets $*
	    exit
	    ;;
esac


# ----------------------------------------------------------------------
# detect bad environment name by trying to switch to it

GWAY="$1"
ACTION="$2"

if api_gway_name_exists "$GWAY" >/dev/null ; then
    GWAY_NAME="$GWAY"
    GWAY_ID=$( api_gway_id_from_name "$GWAY" )
elif api_gway_id_exists "$GWAY" >/dev/null ; then
    GWAY_ID="$GWAY"
    GWAY_NAME=$( api_gway_name_from_id "$GWAY" )
else
    echo "ERROR: '$GWAY' is not a valid gateway name"
    echo "    maybe you meant one of "
    api_gway_names
    exit
fi

# detect no "Verb" at all
if [ -z $ACTION ] ; then
    givehelp
    exit 43
else
    shift # move past gateway name
    shift # move past action name
fi

# ----------------------------------------------------------------------
# Process the 'no env' verbs
case $ACTION in
#        $ME gway update some.json  import swagger 2.0 and update gway
        udpate)
	   api_update_json ""$1""
	   exit
	   ;;
#        $ME gway endpoints       list the endpoints (resources)
        endpoints)
	    api_gway_endpoints
	    exit
	    ;;
        models)
	    api_get_model_names
	    exit
	    ;;
#        $ME gway mockall         mock all the endpoints in this gateway
	mockall)
	    api_mockall $*
	    exit
	    ;;
#        $ME gway mock endpoint   mock one endpoint in this gateway
	mock)
	    api_mock_one $*
	    exit
	    ;;       
        stubs)
	    CGLANG="$1"
	    shift
	    case $CGLANG in 
#        $ME gway stubs java      create lambda stubs in java for all endpoints
		java)
		    api_stub_java_all $*
		    exit
		    ;;
#        $ME gway stubs python    create lambda stubs in pythonfor all endpoints
		py|python)
		    api_stub_python_all $*
		    exit
		    ;;
#        $ME gway stubs node      create lambda stubs in node.js for all endpoints
		node|node.js|js)
		    api_stub_node_all $*
		    exit
		    ;;
		*)
		    givehelp
		    exit
		    ;;
	    esac
	    ;;
	 stub)
	    CGLANG="$3"
	    shift
	    case $CGLANG in 
#        $ME gway stub java endpt create one lambda stub in java for one endpoint
		java)
		    api_stub_java_one $*
		    exit
		    ;;
#        $ME gway stub py endpt   create one lambda stub in pythonfor one endpoint
		py|python)
		    api_stub_python_one $*
		    exit
		    ;;
#        $ME gway stub node endpt create one lambda stub in node.js for one endpoint
		node|node.js|js)
		    api_stub_node_one $*
		    exit
		    ;;
		*)
		    givehelp
		    exit
		    ;;
	    esac
	    ;;
#        $ME gway models          list the models
         models)
	    api_model_list
	    exit
	    ;;
#        $ME gway list             list defined environments (stages)
         stages|list)
	    api_stage_list
	    exit
	    ;;
#        $ME gway create env       create an environment (stage) for this gateway
        create)
	    api_env_create $*
	    exit
	    ;;
#        $ME gway r53 f.b.com  wire up the route 53 name to the gateway
     r53|route53)
	api_route53_wire $*
	exit
	;;
esac

# ----------------------------------------------------------------------

ENVARG=$ACTION
ACTION="$1"

if api_env_name_exists "$ENVARG" >/dev/null ; then
    ENV_NAME="$ENVARG"
#    ENV_ID=$( api_gway_id_from_name "$GWAY" )
#elif api_env_id_exists "$ENVARG" >/dev/null ; then
#    ENV_ID="$GWAY"
#    ENV_NAME=$( api_gway_name_from_id "$GWAY" )
else
    echo "ERROR: '$ENVARG' is not a valid environment (stage) name in '$GWAY_NAME'"
    echo "    maybe you meant one of "
    api_stage_list 
    exit
fi


if [ -z $ACTION ] ; then
    givehelp
    exit 57
else
    shift
fi

# ----------------------------------------------------------------------

# Process the 'with env' verbs
# now parse the 'action' keyword
case $ACTION in
#	$ME gway env deploy     deploy to the given environment
#	$ME gway env update     (same as deploy)
    deploy|update)
	api_env_deploy 
	;;
#        $ME gway env describe     describe the gateway and environment (stage)
    describe)
	api_env_describe
	;;
#        $ME gway env cname        display cname of this gateway+env 
    uri)
	api_env_uri
	;;
    *)
	givehelp
	;;
esac
