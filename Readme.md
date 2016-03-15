# best-practices
best practices and basics for starting projects

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

# Projects

* `php-eb-simple` a trivial php app deployable with elastic beanstalk
   * including instructions for a tech lead to set a new one up
   * instructions for developers to join the project
   * a working `deploy.sh` showing the common verbs and some project-specific verbs

* `php-mysql-eb` a php app with a MySQL database
   * mysql is deployed with elastic beanstalk
   * has the code *not* at the root of the eb project (scripts move it during deployment)
   * has code to initialize the database from a sql dump
   * cleans up some files that don't need to stay in the deployed targetrescue-mission/How-To-Centos4-Docker-Base-Image.md

* rescue-mission/centos
   * shows how to build a custom base image for an ancient unsupported linux
   * starts with an ISO image and makes a docker image
   * fixes up image (for RHEL derived ones) so `yum install` still works
   * examples cover 32-bit OS
   * Centos 4.4, Centos 4.6, Centos 4.8, Centos 4.9

* rescue-mission/Readme-Rescue-To-Docker.md
   * general instructions for rescuing a stranded application

* rescue-mission/ke-docker
   * specific details on how we rescued *Krugle Enterprise 2.4.3.1* from an abandonded vmware
   * rescuing the contents of the vmware image into a docker image
   * forensics about which parts of the image are app, and which are not necesssary
   * starting the app locally and exposing all the right ports
   * starting the app in elastic-beanstalk and exposing the right ports
