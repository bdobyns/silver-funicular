# PHP Example for AWS Elastic Beanstalk

This is the simplest possible PHP example, with no mysql database, and no extra frills and options.

The AWS documentation for Elastic Beanstalk talks about using both the
EB CLI `eb verb` and the AWS CLI `aws elasticbeanstalk verb`, but you
should try to limit your actual behavior to using only the EB CLI.
That's because the AWS CLI operates at a lower level of abstraction,
supplies few if any sensible default values, and is generally much
harder to use.

Conceptually, the AWS CLI for `aws elasticbeanstalk verb` simply wraps
the AWS REST API in the thinnest possible wrapper.  Nobody wants to
`curl` at raw endpoints unless they have to, and the AWS CLI is just
that.

## Pre-requisites

### AWS Tools

you need to install the AWS CLI, and the AWS ElasticBeanstalk CLI and on Mac OSX this is easiest with homebrew

    brew install awscli
    brew install awsebcli

Instructions for other platforms are similar and [well documented at Amazon](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/eb-cli3-install.html)

Check that you have both installed and a sensible version of both

    aws --version
    eb --version

### AWS IAM Role and a Secret Key

make sure you have an access key and secret key for your AWS account,
these are typically tied to an IAM role that you were granted.

put your AWS Access key and secret key in your environment (which is
what the AWS CLI tools want) perhaps in your `.bash_profile` (the keys
shown below are NOT working keys)

    export AWS_ACCESS_KEY=AKIAJF6PZAUYG6ASVNIL
    export AWS_SECRET_KEY=vXGKk19xV6IkVbXJ8g3ZNsBCZX7Xe5PYYaDTkeF3

    export AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY
    export AWS_SECRET_ACCESS_KEY=$AWS_SECRET_KEY

The reason there's two names is that some older AWS CLI tools used the
old names but the EB CLI uses the new names.

## Technical Leaders Create The Project (ElasticBeanstalk "Application")

### Locate A Sensible SSH keypair (Tech Leads)

Put the private key in your `~/.ssh` directory and name it the same as
the name from the EC2 dashboard.  So if the EC2 dashboard says the key
is named 'my_key' you should have a private key named `~/.ssh/my_key`

If you're the tech lead, look in the EC2 dashboard and pick `Key
Pairs` from the `Network & Security` section in the left-hand panel.
Try to figure out if you already have the private key for any of the
named keys there, and if so, do you want to share that key with the
whole team?  

No, I didn't think so.  Create a new keypair using `ssh-keygen` and
upload the *public* part of the key here.  You will distribute the
private part of the key to the rest of the team, along with the name
you just gave it in the dashboard.

using `eb init` without a keypair argument will offer to create a keypair.

### Create An Empty Project (Tech Leads)

create a directory for your project and then initialize your new project with elastic beanstalk.   

    mkdir my-excellent-project
    cd    my-excellent-project
    eb init -p PHP --region us-west-2 --keyname my_key

note that `eb init` relies on your `AWS_ACCESS_KEY` and
`AWS_SECRET_KEY` being set to working values.

This example project already includes a copy of Michael Fortin's
markdown viewer for PHP library and a trivial php app that displays
this readme.md file.  You can use it by:

    git clone git@github.com:productOps/best-practices.git
    cd best-practices/php-aws-eb-example
    eb init -p PHP --region us-west-2 --keyname my_key

`eb init` is idempotent, meaning you can run it again and again, and
it doesn't hurt.  If there is already an application with the name eb
chooses, and with those options, it will do nothing.  If there is
already an application with that name, `eb init` updates the
application.

### Convert An Existing Tree Of Code (Tech leads)

if you already have a directory full of php code, it might be
sufficient to `eb init` at the top of the hierarcy, where "top" means
the directory where the index.html or index.php is.

Note well that your ElasticBeanstalk application name will be the
directory name of the directory in which you run the `eb init` so if
your convention is to put all your code in `/var/www` or
`/opt/blargle/www` your project will get named `www` which is probably
not a very good name.  So you might want to rename the directory that
contains the root first, using `git mv` if necessary.

    cd ~/customer/excellent-project-website
    git mv var/www this-excellent-project
    git commit -am "had to rename the toplevel dir because elasticbeanstalk"
    cd this-excellent-project

    eb init -p PHP --region us-west-2 --keyname my_key

### Create An Environment (Tech Leads)

Typically, you have several environments, most commonly [ *dev* *prod*
] or [ *dev* *staging* *production* ] or [ *dev* *test* *prod* ].
Typically the tech lead will create environments for you, or if you
are the tech lead, you can create some environments.  Sadly, EB
requires environment names be at least four characters long, so *dev*
is not a legal name.  `eb create` takes a minute or two, so be
patient.

    eb create develop 

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

    eb create develop --instance_type m3.medium--tags Name=`basename $PWD`,Blame=$USER,Env=develop

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


As always, the [Amazon
documentation](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/environment-configuration-methods-after.html)
on this matter are lengthy and hard to grok in a single go.

## General Development

### Locate A Sensible SSH keypair (Developers)

If your project is shared, the SSH public and private keypair will be
given to you by the tech lead.  

This key has likely been alread added to the list of keypairs in the
EC2 dashboard, and you'll need the name from the EC2 dashboard, as
well as the *private* key of the keypair.  The name SHOULD be the same
as the filename of the private key.

Without renaming the private key, put it in your `~/.ssh` directory.

If you only got the private key, you can create the public key with
ssh-keygen, but you don't actually need to do so because you never
need the public key for anything.  eb already has it and stuffs it
into instances it starts so that you can ssh into them later.

    ssh-keygen -y -f some_private_key


### Join an existing project (Developers)

`eb init` with no arguments lets you choose an existing application
that has already been created, or to create a new one.  If you're
joining an existing project, then the eb "application" and
"environment" artifacts should already have been created.  You just
need to connect to them.

Lets say you've been brought onto some excellent project to work on
the cool module.

    git clone git@github.com:productOps/some-excellent-project.git 
    cd some-excellent-project/cool-module
    eb init  

You'll pick the "cool module" project that already exists.        

### Use An Environment (Developerss)

When you did `eb init` after you checked out the application, it
automatically pulled down the environments created by your tech lead.
You shouldn't need to create any.  Select the environment you want to
use by listing them, and then choosing one

    eb list
    eb use develop

### Change Some Code and Copy It To Your Instance - NOT PRODUCTION

After you change something (and check it in with git) you might want
to deploy it again.  If you're not deploying to production (don't need
to ensure zero-downtime), then it's pretty easy.  `eb deploy` packs
everything up again and deploys it to /var/www/html in your instance.

    emacs somecode.php
    git commit -am "I made some more changes again"
    git push origin
    eb deploy

This is not suitable for production because it may cause a momentary
service outage while the code is literally copied into and unzipped in
a running instance.  That's okay for development or staging instances,
probably, but not for an app that may be used by many end-users *at
this very moment*.   

AWS has several different ways to ensure that redeployment to
production doesn't cause a service outage for EBS, and your tech lead
should pick one and advise you on how it's done in your project.
Better yet, he'll encapsulate the details in a simple `deploy.sh`
script for everyone to use.

### Get SSH Access To Your Instance

Sometimes you're unlucky and you need to get into the instance to
figure out what happened, and why your excellent code changes didn't
work as you expected.  eb understands and `eb ssh` will get you into
the instance.

    eb ssh

Remember that changes you make inside a running instance are lost in
the case of an autoscaling event, so you always want to make changes
to the source code on your development computer (laptop), and commit
those, rather than changing things in the deployed instance.

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
