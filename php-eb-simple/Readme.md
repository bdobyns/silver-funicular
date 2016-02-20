# PHP Example for AWS Elastic Beanstalk

This is the simplest possible PHP example, with no mysql database, and no extra frills and options.

## Deployment Tools Why

The purpose of deployment tools is to ensure that we have complete
provenance for all the bits that make our applications work right in
production.  

We should be able to have *every* instance that we've launched fail or
catch fire, and be able to completely redeploy from saved artifacts
(in S3, git etc) without manual configuration.

Generally, if you *must* ssh into an application after it's deployed
in order to accomplish a task, then you've not done your job.  Those
things that you would have done manually need to be converted into
scripted instructions which can be persisted outside of the running
instances (in S3, Git, etc) .

----

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

It's also convenient to run `aws configure`

    $ aws configure
    AWS Access Key ID [None]: AKIAJF6PZAUYG6ASVNIL
    AWS Secret Access Key [None]: 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
    Default region name [None]: us-west-2
    Default output format [None]: 

---

Instructions for technical leads to begin a new project are in `Readme.TechLead.md`

----

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

    ssh-keygen -y -f ~/.ssh/some_key >~/.ssh/some_key.pub


### Join an existing project (Developers)

`eb init` with no arguments lets you choose an existing application
that has already been created, or to create a new one.  If you're
joining an existing project, then the eb "application" and
"environment" artifacts should already have been created.  You just
need to connect to them.

Lets say you've been brought onto "some excellent project" to work on
the "cool module".  Lucky you!

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

### Change Some Code And Deploy To Production

AWS has several different ways to ensure that redeployment to
production doesn't cause a service outage for EBS, and your tech lead
should pick one and advise you on how it's done in your project.
Better yet, he or she will encapsulate the details in a simple `deploy.sh`
script for everyone to use.

### Get SSH Access To Your Instance

Sometimes you're unlucky and you need to get into the instance to
figure out what happened, and why your excellent code changes didn't
work as you expected.  eb understands and `eb ssh` will get you into
the instance.

    eb ssh

Remember that changes you make inside a running instance are lost in
the case of an autoscaling event or redeployment, so you always want
to make changes to the source code on your development computer
(laptop), and commit those, rather than changing things in the
deployed instance.

