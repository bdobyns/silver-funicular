# PHP Example for AWS Elastic Beanstalk

This is the simplest possible PHP example, with no mysql database, and no extra frills and options.

## Pre-requisites

### AWS CLI

you need to install the AWS CLI, and the AWS ElasticBeanstalk CLI and on Mac OSX this is easiest with homebrew

    brew install awscli
    brew install awsebcli

Instructions for other platforms are similar and [well documented at Amazon](https://docs.aws.amazon.com/elasticbeanstalk/latest/dg/eb-cli3-install.html)

Check that you have both installed and a sensible version of both

    aws --version
    eb --version

### AWS IAM Role and a Secret Key

make sure you have an access key and secret key for your AWS account,
these are typically tied to an IAM role that you were granted

put your AWS Access key and secret key in your environment (which is
what the AWS CLI tools want) perhaps in your `.bash_profile` (the keys
shown below are NOT working keys)

    export AWS_ACCESS_KEY=AKIAJF6PZAUYG6ASVNIL
    export AWS_SECRET_KEY=vXGKk19xV6IkVbXJ8g3ZNsBCZX7Xe5PYYaDTkeF3


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

### Locate A Sensible SSH keypair (Developers)

If your project is shared, the SSH public and private keypair will be
given to you by the tech lead.  This key has likely been alread added
to the list of keypairs in the EC2 dashboard, and you'll need the name
from the EC2 dashboard, as well as the *private* key of the keypair.

## Ready Set Code!

### Create An Empty Project (ElasticBeanstalk "Application")

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

### Convert An Existing Tree Of Code (ElasticBeanstalk "Application")

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

`eb create` is NOT idempotent, so you only get to use it once, unless you `eb terminate` and try again.
As the tech lead, you should record the arguments you gave to `eb create` in a simple bash script.

    eb terminate develop
    eb create develop [ better options here ]

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

Probably the options you want learn first are to specify the instance
type, and to add some tags to the launched instances.  Normally eb
launches with only one tag, `Name=$environment` and you probably want
to do something else.

    eb create develop --instance_type t2.micro --tags Name=`basename $PWD`,Blame=$USER,Env=develop

### Use An Environment (Regular Developerss)

when you create an environment, it's a little bit like "checking out"
a branch in git, the environment choice is persisted locally. You can
switch the active environment with `eb use` and you can see what your
choices are with `eb list`.  If your tech lead has already created 
environments for you, then you will:

    eb list
    eb use develop

if you want to see how many instances are deployed and some more
details, you can ask for that with

    eb list -v

### Change Some Code and Copy It To Your Instance - NOT PRODUCTION

After you change something (and check it in with git) you might want
to deploy it again.  If you're not deploying to production (don't need
to ensure zero-downtime), then it's pretty easy.  `eb deploy` packs
everything up again and deploys it to /var/www/html in your instance.

    emacs somecode.php
    git commit -am "I made some more changes"
    git push origin
    eb deploy

### Get Access To Your Instance

Sometimes you're unlucky and you need to get into the instance to
figure out what happened, and why your excellent code changes didn't
work as you expected.  eb understands and `eb ssh` will get you into
the instance.

    eb ssh

Remember that changes you make inside a running instance are lost in
the case of an autoscaling event, so you always want to make changes
to the source code on your development computer (laptop), and commit
those, rather than changing things in the deployed instance.

# Applications With Bad Hygiene 

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
