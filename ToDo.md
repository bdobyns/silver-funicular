# ElasticBeanstalk ToDo List

- show how to have other ports open than just 80 and 8080
- show how to have better security when you `eb ssh` by limiting the open port to just your public ip address
- show how to connect a debugger (like netbeans or intellij) from my laptop to the cloud instance
- handle route53 n-part names, not just three part foo.example.com
- connect to a security group that's already been created by CloudFormation
- show an example that includes an RDS (wordpress)
- show an example with bad hygiene (wordpress)
- show an example with two tiers (web, service) and multiple SOA components in each tier, plus RDS
- refactor deploy.sh into a library, so it can be "wrapped" by a project-specific deploy.sh
- refactor route53wire so we can show the current wirings

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

done 2/22/2016:
- rewrite Readme.md in ph-eb-simple to show usage of the `deploy.sh` script
- add changing instance type to ./deploy.sh
- in `new` check the app name does *not* exist before calling `eb init`