# Technical Leaders Create The Project `php-myysql-eb` example

read thru the background and pre-requisites documented in the Readme.md

This Readme walks you thru creating an elastic beanstalk project, using an
existing php and mysql application (provided in the `src` directory in
this example)

## Elastic Beanstalk Background

The AWS documentation for Elastic Beanstalk talks about using both the
EB CLI `eb verb` and the AWS CLI `aws elasticbeanstalk verb` nearly
interchangably, but they are NOT equivalent. You should try to limit
your actual behavior to using only the EB CLI, and only using the AWS
CLI when you absolutely have to.  That's because the AWS CLI operates
at a lower level of abstraction, supplies few if any sensible default
values, and is generally much harder to use.

Conceptually, the AWS CLI for `aws elasticbeanstalk verb` simply wraps
the AWS REST API in the thinnest possible wrapper.  Nobody wants to
`curl` at raw endpoints unless they have to, and the AWS CLI is hardly
any better than that.

AWS documentation for eb and git integration assume that the top of
the git repository (where you did `git init`) and the top of the eb
repository (where you `eb init`) are the same directory.  This is *not
necessary*, and most real projects contain several components, or
modules all in the same git repository.  you can safely `eb init` in
any subdirectory in the git repository, and when eb packages up
everything in a deployment event, it doesn't take things all the way
to the git root, but only to the top of the directory where you did
`eb init`.

----

## Create A Project

### Using `deploy.sh` Or Not.  Up To You.

The accompanying `./deploy.sh` script is mostly for the convenience of
the developers who join your project after you've set it up.  However,
several features in it have been designed to make life easier for you,
the tech lead.

### Locate A Sensible SSH keypair (Tech Leads)

If you're the tech lead, look in the EC2 dashboard and pick `Key
Pairs` from the `Network & Security` section in the left-hand panel.
Try to figure out if you already have the private key for any of the
named keys there, and if so, do you want to share that key with the
whole team?  

No, I didn't think so.  Create a new keypair using `ssh-keygen` and
upload the *public* part of the key here.  You will distribute the
private part of the key to the rest of the team, along with the name
you just gave it in the dashboard.

using `eb init` (or `./deploy.sh newapp`) later without a keypair argument will offer to create a keypair.

### Checkout and Initialize This Project (Tech Leads)

create a directory for your project (in this case we assume you've
simply checked out the example) and then initialize your new project
with elastic beanstalk.

    git clone git@github.com:productOps/best-practices.git
    cd best-practices/php-mysql-eb
    ./deploy.sh new php-mysql-eb --region us-west-2 --platform php5.5 --keyname yourkey_rsa_here

The `deploy new` is a thin wrapper around `eb init`, but first
checks that the appname you use does not yet exist (so you don't
destroy someone else's 'most-excellent-app').  With no args it will
also try to to guess a sensible name for your app (where sensible is
`basename $PWD`), pick up the current region as defined in your
`~/.aws/config` default region.  `eb init` will try to guess the type
of app by looking for source code in the various supported languages.

Note that `eb init` relies on your `AWS_ACCESS_KEY` and
`AWS_SECRET_KEY` being set to sensible working values, and your having
already run `aws config`.

This example project already includes a snapshot from 2007 of blog
software (sadly for PHP4.x) and a mysql dump of the database (again Mysql 4.x) that
matches the blog.  The database has already been fixed up so it can restore properly. 

`eb init` is idempotent, meaning you can run it again and again, and
it doesn't hurt.  If there is already an application defined in AWS
with the same name, and with those options, it will do nothing.  If
there is already an application with that name, `eb init` updates the
application.

In this project the "home dir" of the application is in the top of
`best-practices/php-mysql-eb/src` and there's three scripts already
written to run in the instance that move the files up a directory
level in the target, restore the database into the RDS if the RDS has
no tables at all, delete deploy.sh and other scripts that are
unnecesary in the target.  You should review these scripts because you
will likely need to create similar ones for your projects.

    cat .ebextensions/01movefiles.config
    cat .ebextensions/02database_setup.config
    cat .ebextensions/99nodeploy.config

We have also edited the application configuration to pick up the
hostname, username and password for the RDS from the environment,
which EB will happily arrange for you.
    
    vi src/config.php

In particular, the last one, nodeploy, has a convenient ./deploy.sh
verb that allows you to add more files to be removed from the target.

### Create An Environment (Tech Leads)

Typically, you have several environments, most commonly [ *dev* *prod*
] or [ *dev* *staging* *production* ] or [ *dev* *test* *prod* ].

In this project we need an RDS as well as intances, so we are going to
give a lot of argumetns to `./deploy.sh create`

    ./deploy.sh create test -i m1.small --timeout 60 \
    -db.engine mysql -db.i db.m1.small -db.size 5 -db.pass battery-staple -db.user tinfoilhat
    
This will grind away for a long long time, that's why we gave it an
insane timeout of 60 minutes, because the RDS seems to take forever to
start up.  

After you finally get back a prompt:

    ./deploy.sh open

should get you to your instance in your default browser, which will be ... ugly.  sorry.

Here, `./deploy.sh create`  wraps `eb create` so that  you and your
devs can  use a  short name like  'test' and  under the hood,  it will
append the appname and create test-$appname.

With no other args, `./deploy.sh create` sets up a load balancer, a
web tier, autoscaling group, security group, cloud watch alarms, and
assigns an s3 bucket for your environment, using the keyname and the
region you specified eariler with `eb init`.  This defaults are
usually all reasonable, but you can override most of them with
arguments.

ALSO, this will launch an EC2 instance, and deploy everything in the
current directory into /var/www/html in your newly launched instance,
and will start up apache in that instance to run your PHP code.


### Changing Config After The Fact

`eb create` is NOT idempotent, however you can easily change any of
the (many) parameters by using `eb config` to edit the yaml
configuration document that describes your environment

    export EDITOR=emacs
    eb config

Note that `eb config` tries to apply those changes *right now* so be
careful.  There's some other ways to get configuration changes into
your environment that include creating and using [saved
configuration](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/environment-configuration-methods-during.html#configuration-options-during-console-savedconfig) or using project local [configuration files](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/environment-configuration-methods-during.html#configuration-options-during-ebcli-ebextensions)

After you edit your config (or even if you didn't edit it) it's
probably a good idea to always record the current configuration, and
check it into git.  you need to explicitly `git add` the config file
because the .elasticbeanstalk directory is normally excluded by a
.gitignore (created by `eb init` thankyouverymuch).

    export EDITOR=vi
    eb config
    eb config save --cfg mostexcellent
    git add .elasticbeanstalk/saved_configs/mostexcellent.cfg.yml


Of special interest for a php app might be the settings that get
stuffed into the php.ini, found in the
`aws:elasticbeanstalk:container:php:phpini:` section.  It might be
useful to turn on display_errors in the test environment, and turn up
the memory_limit if you have picked a bigger instance size than
t1.micro
 
     ./deploy.sh test phperrors on
     ./deploy.sh setitype m1.large

As always, the [Amazon
documentation](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/environment-configuration-methods-after.html)
on this matter are lengthy and hard to grok in a single go.

### Special Config Changes

The deploy script implements several of the most common configuration
edits directly with command line parameters.

Set the upper and lower bound of the number of instances in the
auto-scaling-group with

    ./deploy.sh env scale min  max

Set the count (both upper and lower bound) of the number of instances in the
auto-scaling-group with

    ./deploy.sh env count n

Set the "cooldown" period for the auto-scaling-group (how long after
the last change (add or remove an instance) before it permits another) in minutes.

    ./deploy.sh env cooldown n

Set the instance type for the auto-scaling-group (this will re-deploy all the instances.  ouch.)

    ./deploy.sh env setitype m3.medium 

Open up the ./deploy.sh script and you can see all of these are
trivially implemented with editconfig, and it's easy to add more.

### Tighten Up The SSH Controls

By default, when you create an application and environment, `eb ssh`
opens port 22 to the world.  Which is probably not what you were
hoping for.

    ./deploy.sh test limitip

Will determine your public IP address (the office address) and then
limit ssh sessions to that address only.  It can still be overridden
with

    eb ssh --force

But limiting the ssh to just our public ip address makes the instance
much more The.

### Whole Example Worked For Real

```
[you@yourbox ~] $  git clone git@github.com:productOps/best-practices.git
Cloning into 'best-practices'...
remote: Counting objects: 1004, done.
remote: Compressing objects: 100% (761/761), done.
remote: Total 1004 (delta 204), reused 0 (delta 0), pack-reused 220
Receiving objects: 100% (1004/1004), 11.19 MiB | 316.00 KiB/s, done.
Resolving deltas: 100% (321/321), done.

[you@yourbox ~] $ cd best-practices/php-mysql-eb

[you@yourbox php-mysql-eb]$ ./deploy.sh new bdobyns-php-mysql --region us-west-2 --platform php --keyname barry_rsa
Application bdobyns-php-mysql has been created.

[you@yourbox php-mysql-eb]$ ./deploy.sh create test -i m1.small --timeout 60 -db.engine mysql -db.i db.m1.small -db.size 5 -db.pass uzara-Janthinidae-ponchoed -db.user tchervonets
 BE PATIENT: THIS MAY TAKE A WHILE AND WILL DEPLOY AT LEAST ONE INSTANCE ALONG THE WAY 
Creating application version archive "app-160226_164956".
Uploading: [##################################################] 100% Done...
Environment details for: test-bdobyns-php-mysql
  Application name: bdobyns-php-mysql
  Region: us-west-2
  Deployed Version: app-160226_164956
  Environment ID: e-hv2pi24s68
  Platform: 64bit Amazon Linux 2015.09 v2.0.8 running PHP 5.4
  Tier: WebServer-Standard
  CNAME: UNKNOWN
  Updated: 2016-02-27 00:50:06.569000+00:00
Printing Status:
INFO: createnvironment is starting.
INFO: Using elasticbeanstalk-us-west-2-317994125539 as Amazon S3 storage bucket for environment data.
INFO: Created load balancer named: awseb-e-h-AWSEBLoa-1RK79GWK4LTAX
INFO: Created security group named: awseb-e-hv2pi24s68-stack-AWSEBSecurityGroup-UM0W8K5E9CDD
INFO: Environment health has transitioned to Pending. There are no instances.
INFO: Created Auto Scaling launch configuration named: awseb-e-hv2pi24s68-stack-AWSEBAutoScalingLaunchConfiguration-1BHHKYU806ALK
INFO: Created RDS database security group named: awseb-e-hv2pi24s68-stack-awsebrdsdbsecuritygroup-1sp5ji05a0x1w
INFO: Creating RDS database named: aam287ufb56rr8. This may take a few minutes.
INFO: Created RDS database named: aam287ufb56rr8
INFO: Added instance [i-e6355521] to your environment.
INFO: Created Auto Scaling group named: awseb-e-hv2pi24s68-stack-AWSEBAutoScalingGroup-8OR3442828CO
INFO: Waiting for EC2 instances to launch. This may take a few minutes.
INFO: Created Auto Scaling group policy named: arn:aws:autoscaling:us-west-2:317994125539:scalingPolicy:b4405f9a-d873-467b-ad4f-2acb6a7e0cfc:autoScalingGroupName/awseb-e-hv2pi24s68-stack-AWSEBAutoScalingGroup-8OR3442828CO:policyName/awseb-e-hv2pi24s68-stack-AWSEBAutoScalingScaleDownPolicy-ZX5WCMKXK2NX
INFO: Created Auto Scaling group policy named: arn:aws:autoscaling:us-west-2:317994125539:scalingPolicy:cc7aa1fc-c1f2-4a37-8628-b5fba9349b81:autoScalingGroupName/awseb-e-hv2pi24s68-stack-AWSEBAutoScalingGroup-8OR3442828CO:policyName/awseb-e-hv2pi24s68-stack-AWSEBAutoScalingScaleUpPolicy-6LKCGIYSRTEH
INFO: Created CloudWatch alarm named: awseb-e-hv2pi24s68-stack-AWSEBCloudwatchAlarmLow-D9Y892YBGJMC
INFO: Created CloudWatch alarm named: awseb-e-hv2pi24s68-stack-AWSEBCloudwatchAlarmHigh-6LHXXRN6SCDA
WARN: Environment health has transitioned from Pending to Severe. Command is executing on all instances.
INFO: Successfully launched environment: test-bdobyns-php-mysql
                                
```
