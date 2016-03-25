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
       ``export  JAVA_HOME=`/usr/libexec/java_home -v 1.8`  ``   
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
       (see [lambdash](https://alestic.com/2015/06/aws-lambda-shell-2/) for example)

    * Because you are based on docker containers, you can payload in
      anything you want that makes sense in a container (up to the 
      [size limits](http://docs.aws.amazon.com/lambda/latest/dg/limits.html)
      defined by AWS)   

      *  This includes binary executables, so you are not limited to the officially supported languages.
      *  AWS acknowledges and supports doing so
         [Running Arbitrary Executables](http://aws.amazon.com/blogs/compute/running-executables-in-aws-lambda/)
	 (for small values of support).










# FETCH AND PREPARE THE SWAGGER SPEC YOU WANT TO USE

1. I got a copy of the petstore swagger API to use for the
   demonstration.  This specification is *already* in the 2.0 format and
   ready to use.  You will, of course use your own specification.   
   `wget -O petstore.json http://petstore.swagger.io/v2/swagger.json`

1. If you are unlucky and your Swagger specification complies with version 1.2, you *might* be able to succeed by installing `swagger-converter` and `swagger-tools to convert it.  This may be easy or hard depending on how many prerequisites you already have installed.   
 `npm install swagger-converter --save`   
 `npm install -g swagger-tools`   
 `swagger-tools convert your-api-docs.json`   

1.  If you are fabulously unlucky, your Swagger specification complies with version 1.1, and you'll need to rewrite it manually.  Wah wah wah. 








# INSTALL AND GET READY TO USE THE `aws-gateway-importer`

You need to have `aws-gateway-importer` available to use it, which
(sadly) means downloading and building it first.  I tried to use
`brew` to fetch a built version, but no. Because fail.

I found it 'easy' (for infinitesmal values of easy) to do this by
including `aws-gateway-importer` as a submodule.  Following the
submodule tutorial in the [git
wiki](https://git.wiki.kernel.org/index.php/GitSubmoduleTutorial) will
lead you down an alternative path of fail.   Do this instead:

```
   cd ~/some/path/myproject/api-lambda-mock
   git submodule add git@github.com:awslabs/aws-apigateway-importer.git 
```

I suppose you could install the `aws-gateway-importer` in `~/bin` or
`/usr/local/bin` but that has problems of it's own, because it's not
really a singular binary, as we will see.

Now you need to build `aws-gateway-importer` because you checked out
the sources.  Which may require you install maven first if you haven't
already.  If you've never used `mvn` before, you might be a little
alarmed at how many random things are fetched in order to build this.

```
if [ -z `which mvm` ] ; then brew install mvn ; fi
cd aws-apigateway-importer
mvn assembly:assembly
```

You can safely go use the panini maker while `mvn` does it's job.










# SOMETIMES THINGS GO TERRIBLY WRONG 

1. I also fetched a spec that Kevin Albert had written recently   
   `wget -O api-docs.json http://kevin-demo-app01.test.cirrostratus.org/v2/api-docs`

1. Running `aws-gateway-importer` on it failed with a   
   `java.lang.StackOverflowError` (infinite recursion) error. 

1. This is because in the models, 'Product' is defined recursively as
   containing other 'Products', and the importer is too dumb to detect
   this loop.  

1. I fixed it by creating a new model element, 'Item' (essentially the
   same as a 'Product' but without the loop) and making 'Product'
   composed of 'Item's.  This is an acceptable hack (in the API
   definition) since we now model 'Item' as a named pointer to an
   entitlement, and a 'Product' is an array of such 'Item'
   entitlements which itself also has an entitlement.

1. From the point of view of the Product Catalog API we don't expect
   anyone to need to crawl the entitlement tree from a Product like
   *Arts and Sciences 32* to an individual leaf article.  Or for that
   matter no one expects that 'counting' the 'items' at the top level
   of a product gives a count of the leaves.  So making a Product
   not-crawlable by the provided API model probably makes sense, even
   though as a matter of strict fact it is crawlable.

1. This has the side effect of forcing you to use EME to answer the
   question of whether a particular leaf item is contained in a
   particular product (e.g. "is this article in this Arts And Sciences MCMLXXVII ?").
   You'd ask that by getting the entitlement for the Item (from it's
   individual leaf Product), and the entitlement for the Product you
   are asking the question of, and call the one of the ItemHaz apis on EME.

1. Once the loopy model was corrected, we discover that the way
   SpringFox annotations creates model names that are
   non-alphanumeric, like `DeferredResult«object»` and therefore
   unacceptable to `aws-gateway-importer`

1. The importer also does not allow content type `*/*`. I replaced all
   of these with `application/json` whether it's right or not.






# REFERENCES

* Git Submodule Tutorial  
  <https://git.wiki.kernel.org/index.php/GitSubmoduleTutorial>

* Amazon API Gateway Importer   
  <https://github.com/awslabs/aws-apigateway-importer>

* Shell commands as AWS Lambdas (used to explore the execution environment)   
  <https://alestic.com/2015/06/aws-lambda-shell-2/>

* Running Arbitrary Executables in AWS Lambda   
  <http://aws.amazon.com/blogs/compute/running-executables-in-aws-lambda/>

* A different, but interesting, use case for the API Gateway   
  <https://alestic.com/2015/11/amazon-api-gateway-aws-cli-redirect/>

* Java 8   
  <http://www.oracle.com/technetwork/java/javase/downloads/jdk8-downloads-2133151.html>

* Setting up Java$N on Mac OSX properly
  <http://stackoverflow.com/a/15306564/5167278>
  The higher rated answers are older and generally wrong, while this one is the right answer.  Upvotes please.

* Switching between different Java versions on Mac OSX
  <http://mikemainguy.blogspot.com/2014/11/easily-changing-java-versions-in-osx.html>

* Swagger homepage   
  <http://swagger.io>

* Swagger 2.0 specification   
  <https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md>

* AWS Lambda size limits
  <http://docs.aws.amazon.com/lambda/latest/dg/limits.html>