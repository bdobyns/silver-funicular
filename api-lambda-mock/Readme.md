# USING AWS LAMBDAS AND AWS API-GATEWAY TO MOCK A SERVICE WITH REST APIS

AWS has powerful tools that can be used to mock out a complete service
before you actually build it.  There's lots of reasons you may want to
do this, and some of those reasons might include:

1.  you are building an SOA system, and you want to mock the services
you're not building now so the services you are building have
something to call on the endpoints that they will eventually need to
call.

1. QA/Test wants to test a particular SOA component, but you don't
want to have bugs in the systems that the component under test calls
to affect the outcomes of your test, so you point the component under
test to mock systems to call.

1. You are building one particular component in an soa system and you'd like to simply make up the
syntax and semantics of the calls you *wish* the the components you
want to call *might* have in the future, so you mock them all out.

   * When those systems are actually built, you can mitigate the
     difference between apis you imagined and the actual APIS provided
     by

   * Having the API gateway you have already set up use (more
     sophisticated) lambdas to mediate the connection to whatever the
     real service provides, whether it's raw data tables, elastic
     search, SOAP, EDI, REST, Kinesis, Kafka, Flume, SNS, SQS, JBOSS
     or whatever

1.  You want to model some future service that you intend to build
later, and you want to mock it out now so that clients of your service
can begin to call something.

    * If your service is truly stateless then you can start to
      prototype the *real* functionality as simple lambdas, and worry
      later whether you lash them all together in a monolithic
      application using the usual framework (Spring).
      
      * by stateless, we mean that the code itself has no state in it.
        You can (and will) of course persist state in something like
        an RDS, DynamoDB, SimpleDB, SQS, SNS etc

    * For some services it may actually be sensible to leave the
      service as a collection of lambdas because it is actually *more
      performant AND less expensive*

      * It can be more performant because each api endpoint can be
        individually provisioned with respect to temp-disk/cpu/memory
        and is scaled separately from all other endpoints










# TECHNICAL PREREQUISITES

1.  You need to have the AWS CLI installed.    
    On a sensible development machine this is done with `brew install awscli`

1.  You need to have
    [Java8 JDK](http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html) 
    installed, and your `JAVA_HOME` needs to correctly point to Java8.

    *  on a Mac, this means you put something sensible like   
       `export  JAVA_HOME=\`/usr/libexec/java_home -v 1.8\` `   
       in your `.bash_profile` and `.bashrc`

1.  We assume you have set up your AWS credentials for your IAM role so that you can use the AWS cli sensibly.




# MENTAL PREREQUISITES

1.  We assume you've already defined the APIs you want to mock
    using [Swagger](http://swagger.io) 
    Version [2.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md)

1.  You need to have a basic understanding of what it means to execute
in a docker container.  

    * This is because AWS Lambdas are implemented with docker
      containers at AWS, but are *amazingly* even lighter weight than
      deploying a container.

    *  Your lambda can exec any binary that's in a basic Amazon Linux AMI 
       (see [lambdash](https://alestic.com/2015/06/aws-lambda-shell-2/) 

    * Because you are based on docker containers, you can payload
      anything you want that makes sense in a container (up to the 
      [size limits](http://docs.aws.amazon.com/lambda/latest/dg/limits.html)
      defined by AWS)   

      *  This includes binary executables, so you are not limited to the officially supported languages.
      *  AWS acknowledges and supports
         [Running Arbitrary Executables](http://aws.amazon.com/blogs/compute/running-executables-in-aws-lambda/)
	 (for small values of support).










# REFERENCES

* Git Submodule Tutorial  
  https://git.wiki.kernel.org/index.php/GitSubmoduleTutorial

* Amazon API Gateway Importer   
  https://github.com/awslabs/aws-apigateway-importer

* Shell commands as AWS Lambdas (used to explore the execution environment)
  https://alestic.com/2015/06/aws-lambda-shell-2/

* Running Arbitrary Executables in AWS Lambda   
  http://aws.amazon.com/blogs/compute/running-executables-in-aws-lambda/

* A different, but interesting, use case for the API Gateway
  https://alestic.com/2015/11/amazon-api-gateway-aws-cli-redirect/

* Java 8
  http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html

* Swagger homepage
  http://swagger.io

* Swagger 2.0 specification
  https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md
