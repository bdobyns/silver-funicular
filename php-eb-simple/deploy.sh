#!/bin/bash -e
# blame: barry@productops.com  Feb 2016
# you can run this -x to learn what it does under the hood

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
        $ME env use              use environment env (not necessary)
        $ME local run            run a local copy of the app
        
ELASTIC BEANSTALK VERBS:
        $ME init                 initialize elastic beanstalk (after git clone)
        $ME init appname         initialize elastic beanstalk (after git clone)
        $ME list                 list available environments
        $ME env describe         describe the environment

        $ME env id               get instance id (of first instance)
        $ME env ipaddr           get instance ipaddress
        $ME env instance         describe the instance

        $ME myip                 find out what my (laptop) ip is

TECH LEAD VERBS:
        $ME new                  create application based on this dir name
        $ME new appname          create application 'appname'
        $ME new appname args..   create application appname
        $ME createenv env        create environment 'env-appname'
        $ME createenv env [more args]

        $ME env sgn              get security group name
        $ME env sgid             get security group id
        $ME env security         describe security group 

        $ME env cname            display the cname of the elastic load balancer
        $ME env r53 f.b.com      wire up a route53 name 'f.b.com' to the lb'
        $ME env1 swap env2       swap the lb cnames for env and env2

        $ME env asg              get autoscaling group name
        $ME env asgdescribe      describe the autoscaling group
        $ME env scale min max    set asg min and max 
        $ME env cooldown n       cooldown in seconds between asg actions
        $ME env setitype type    set instance type, like t1.micro or m3.medium

        $ME env limitip          limit ssh ip to my public ip
        $ME env limitip cidr     limit ssh ip e.g. 0.0.0.0/0

EOF
	exit 3
}

# ----------------------------------------------------------------------
LIBEBDEPLOY=lib_eb_deploy.sh
DNZ=`dirname $0`
if [ -f $DNZ/$LIBEBDEPLOY ] ; then 
    source $DNZ/$LIBEBDEPLOY
elif [ -f $DNZ/lib/$LIBEBDEPLOY ] ; then 
    source $DNZ/lib/$LIBEBDEPLOY    
else
    echo "ERROR: missing $DNZ/$LIBEBDEPLOY"
    exit 1
fi
# ----------------------------------------------------------------------

# Process the 'no env' verbs
    case $1 in
    # first arg is usually the Environment, 
    # but sometiemes it's a verb
	new)
#        $ME new                  create application based on this dir name
#        $ME new appname          create application appname
#        $ME new appname args..     create application appname
	     ebnew $*
	     exit
	     ;;
	 init)
#        $ME init                 initialize elastic beanstalk (after git clone)
#        $ME init appname         initialize elastic beanstalk (after git clone)
	     ebinit $*
	     exit
	     ;;
	create|createenv)
#        $ME create env           create environment 'env-appname'
	    ebcreate $*
	    exit 
	    ;;
	list)
#        $ME list                 list available environments
	    eb list
	    exit 
	    ;;
	local)
#        $ME local run            run a local copy of the app
	    # could probably vagrant or docker to do this, but not today
	    echo "ERROR: '$ME local $2' is not supported" >&2
	    givehelp
	    exit 
	    ;;
	myip)
#        $ME myip                 find out what my (laptop) ip is
	    whatsmyip
	    exit 
	    ;;
    esac
fi



# ----------------------------------------------------------------------
# detect bad environment name by trying to switch to it

ENV=`ebenvexpand $1`
if [ -z $ENV ] ; then exit 11 ; fi

# detect no "Verb" at all
if [ -z $2 ] ; then
    givehelp
    exit 43
else
    ACTION=$2
    shift; shift
fi



# ----------------------------------------------------------------------
# ----------------------------------------------------------------------
# ----------------------------------------------------------------------

# Process all other verbs
# now parse the 'action' keyword
case $ACTION in
    use)
#        $ME env use              use environment env (not necessary)
	# already done by the time we reach this point by ebenvexpand
	;;

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
	ebputget $*
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
	ebinstanceipaddr `ebinstance`
	;;
    instance)
#        $ME env instance         describe the instance
	aws ec2 describe-instances --instance-ids `ebinstance`
	;;
    sgn)
#        $ME env sgn              get security group id
	ebsgn
	;;
    sgid)
#        $ME env sgid             get security group id
	ebsgid
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
	ebdescribe
	;;
    r53cname|r53)
#        $ME env r53 f.b.com      wire up a route53 name 'f.b.com' to the lb'
	route53cname $1
	;;
    scale)
#        $ME env scale min max    set asg min and max 
	ebsetscale $1 $2
	;;
    cooldown)
#        $ME env cooldown n       cooldown in seconds between asg actions
	ebsetcooldown $1
	;;
    count)
#        $ME env count n          set asg max and min to n
	ebsetcount $1
	;;
    setitype)
#        $ME env setitype type    set instance type, like t1.micro or m3.medium
	ebsetitype $1
	;;
    asg)
#        $ME env asg              get autoscaling group name
	asgname
	;;
    asgdescribe)
#        $ME env asgdescribe      describe the autoscaling group
	asgdescribe
	;;
    limitip)
#        $ME env limitip          limit ssh ip to my public ip	
	eblimitip $1
	;;
    swap)
#        $ME env1 swap env2        swap the lb cnames for env and env2
	ebswap $ENV $1
	;;
    *)
	givehelp
	;;
esac
