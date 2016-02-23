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

done 2/22/2016:
- rewrite Readme.md in ph-eb-simple to show usage of the `deploy.sh` script
- add changing instance type to ./deploy.sh
- in `new` check the app name does *not* exist before calling `eb init`