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
* `php-eb-wordpress` a php app with a MySQL database
   * mysql is deployed with elastic beanstalk
   * wordpress has `bad hygiene` and writes files back into the local filesystem