# Technical Leaders Create The Project (ElasticBeanstalk "Application")

read thru the background and pre-requisites documented in the Readme.md

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

using `eb init` (or `./deploy.sh newapp`) without a keypair argument will offer to create a keypair.

### Create An Empty Project (Tech Leads)

create a directory for your project and then initialize your new project with elastic beanstalk.   

    mkdir my-excellent-project
    cd    my-excellent-project
    ./deploy.sh newapp 

The `deploy newapp` is a thin wrapper around `eb init`, but first
checks that the appname you use does not yet exist (so you don't
destroy someone else's 'most-excellent-app).  It will also try to to
guess a sensible name for your app (where sensible is `basename
$PWD`), pick up the current region as defined in your `~/.aws/config`
default region.  `eb init` will try to guess the type af app by
looking for source code in the various supported languages.

note that `eb init` relies on your `AWS_ACCESS_KEY` and
`AWS_SECRET_KEY` being set to working values.

This example project already includes a copy of Michael Fortin's
markdown viewer for PHP library and a trivial php app that displays
this readme.md file.  You can use it by:

    git clone git@github.com:productOps/best-practices.git
    cd best-practices/php-aws-eb-example
    ./deploy.sh new php-aws-eb-example -p PHP --region us-west-2 --keyname my_key

`eb init` is idempotent, meaning you can run it again and again, and
it doesn't hurt.  If there is already an application defined in AWS with the name eb
chooses, and with those options, it will do nothing.  If there is
already an application with that name, `eb init` updates the
application.  

### Convert An Existing Tree Of Code (Tech leads)

if you already have a directory full of php code, it might be
sufficient to `eb init` at the top of the hierarcy, where "top" means
the directory where the `index.html` or `index.php` is.

Note well that your ElasticBeanstalk application name will be the
directory name of the directory in which you run the `eb init` so if
your convention is to put all your code in `/var/www` or
`/opt/blargle/www` your project will get named `www` which is probably
not a very good name.  So you might want to rename the directory that
contains the root first, using `git mv` if necessary.

    cd ~/customer/excellent-project-website
    git mv var/www this-excellent-module
    git commit -am "had to rename the toplevel dir because elasticbeanstalk"
    git push
    cd this-excellent-module

    eb init -p PHP --region us-west-2 --keyname my_key

### Create An Environment (Tech Leads)

Typically, you have several environments, most commonly [ *dev* *prod*
] or [ *dev* *staging* *production* ] or [ *dev* *test* *prod* ].
Typically the tech lead will create environments for you, or if you
are the tech lead, you can create some environments.  Sadly, EB
requires environment names be at least four characters long, so *dev*
is not a legal name.  `eb create` takes a minute or two, so be
patient.

    ./deploy.sh createenv test

Here, `./deploy.sh createenv`  wraps `eb create` so that  you and your
devs can  use a  short name like  'test' and  under the hood,  it will
append the appname and create test-$appname.

With no other args, `eb create` sets up a load balancer, a web tier,
autoscaling group, security group, cloud watch alarms, and assigns an
s3 bucket for your environment, using the keyname and the region you
specified eariler with `eb init`.  This defaults are usually all
reasonable, but you can override most of them with arguments.

ALSO, this will launch an EC2 instance, and deploy everything in the
current directory into /var/www/html in your newly launched instance,
and will start up apache in that instance to run your PHP code.

`eb create` can do a lot of other dangerous things too - this is
documented in other examples, and in the aws documentation for [eb
create](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/eb3-create.html).

Probably the first options you want learn first are to specify the instance
type, and to add some tags to the launched instances.  Normally eb
launches with only one tag, `Name=$environment` and you probably want
to do something else.

    eb create test --instance_type m3.medium--tags Name=`basename $PWD`,Blame=$USER,Env=develop

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

    ./deploy.sh env itype m3.medium 

Open up the ./deploy.sh script and you can see all of these are
trivially implemented with editconfig, and it's easy to add more.

### Applications With Bad Hygiene 

Sometimes applications like wordpress come "unconfigured" and the
first time you access the application in the browser, you "configure"
it and the application writes some files "locally" inside the deployed
instance.  These files will not be present in additional instances if
you autoscale up.

Bad hygiene aside, you need to get those files out of the instance and
into your git sandbox, so you can check them in.

You can ssh into the app with `eb ssh` but that's not enough to
succeed in this case.  However, eb figures out the public ip address
of the instance when you `eb ssh` into it, and you need that.  You can
log in as *ec2-user* with the ssh key you specified earlier, and tell
eb to leave port 22 open for you to abuse.

    eb ssh -o    # type ^d to log back out but note the public ip address, leave port 22 open
    scp -r -i ~/.ssh/my_key    'ec2-user@pub.lic.ip.addr:/var/www/html/*'  .
    eb ssh       # log back in and out quickly to close port 22

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
much more secure.