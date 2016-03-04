# ElasticBeanstalk ToDo List

- figure out bug in deploy_eb.sh where using editconfig from a script is not the same as using it from the commandline
- show how to have other ports (on your app) open than just 80 and 8080
- show how to connect a debugger (like netbeans or intellij) from my laptop to the cloud instance
- handle route53 n-part names, not just three part foo.example.com
- show an example with bad hygiene (wordpress)
- show an example with two tiers (web, service) and multiple SOA components in each tier, plus RDS - note well, that by AWS design the worker tier in eb ONLY is allowed to communicate with other workers and with the web tier via SQS
- refactor route53wire so we can show the current wirings

done 3/4/2016:
- workaround bug in 'eb create' which reads from stdin when it should not
- add history mechanism, so deploy writes an executable history log
- show how to have better security when you `eb ssh` by limiting the open port to just your public ip address
- show an example that includes an RDS (wordpress)
- show the source not at the eb root
- show restoring a database iff the database is empty (just created by eb)
- connect to a security group that's already been created (created by someone/something else), by adding ingress rules to the other security group for the sg in this environment
- move deploy_eb.sh up, fix bugs with it executing in a dir not the same as the app
- rename deploy_eb.sh to deploy_eb.sh
- add cloudwatch logs verb, which uses 'eb labs setup-cwl' to write some configs in .ebextensions

done 2/24/2016:
- refactor deploy_eb.sh into a library, so it can be "wrapped" by a project-specific deploy_eb.sh.  
  strip all 'active' code out of deploy_eb.sh other than the big switch statements.
- add appname and listapps verbs
- add s3logs verb which turns on/off aws:elasticbeanstalk:hostmanager:  LogPublicationControl
- add nodeploy verb which allows you to mark files as "not to be deployed"
- try and figure out VPC and EB so that I can use a t2.nano or t2.micro, and many other things

done 2/23/2016:
- fixup new verb in the edge case where the TechLead gives lots of args
- fixup new verb to properly check for app already existing
- refactor check for existing app to use 'aws elasticbeanstalk describe-applicaitons' instead of '... describe-environments' which would fail if the app had no environments yet
- fix bug introduced in all commands that take the env as arg1. sigh.
- fix bug in editconfig when the KEY in question appeared at the end of the section
- fix service outage bug in setitype by changing max instance count in asg
- fiddle with max instance count when doing a scale operation
- try and detect problem 'Launching a new EC2 instance. Status Reason: The specified instance type can only be used in a VPC. A subnet ID or network interface ID is required to carry out the request. Launching EC2 instance failed.' and give a helfpul error message
- scale with no args gives current values
- count with no args gives current values
- cooldown with no args gives current values
- fiddle with asg parameters in setitype so there's no service outages
- remove getitype in favor of no-arg default for get
- setitype with no args gives current values
- refactor and move some code out of the big case statement at the bottom into library-like methods
- fix bug found by KevinA when 'region' is not in the 'default' section of ~/.aws/config
- fix bug found by KevinA when ConfigParser python module not available
- fix bug found by KevinA in the test for 'is your python broken'
- fix bug in 'sgn' and add 'sgid' command
- fix bug in setitype which persistently increased the maxsize every run
- add 'swap' verb, which also required refactoring the test for 'does this env exist'

done 2/22/2016:
- rewrite Readme.md in ph-eb-simple to show usage of the `deploy_eb.sh` script
- add changing instance type to ./deploy_eb.sh
- in `new` check the app name does *not* exist before calling `eb init`