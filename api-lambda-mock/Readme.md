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
      * your lambda could kick off an ansible task to do *anything you want* on first execution.  Or anything else you might fear or desire.

1. It's helpful to understand the differences between execution of a lambda in different languages

   * Lambdas written in node.js start up more quickly than either
     python or java ones.  But if the lambda itself is computationally
     expensive, you may discover that node.js takes longer (clock
     time) overall compared to java and python.

   * Lambdas written in java take the longest to start up (because you
     still have to start up a JVM).  However since you don't also have to
     start up a servlet container, or autowire beans or any of the
     other usual stuff, it's still much faster than starting up a
     traditional Spring-based REST service.

   * Lambdas written in python are intermediate between node.js and
     java for startup time, and comparable to java in execution time
     (python is pretty good at JIT compilation).  Because of the
     cheaper startup cost, you ought to consider python as the right
     target for most lambdas that do non-trivial work.

   * You should pick the language based on which language has better
     support (libraries or frameworks) for the work you need to do in
     your Lambda.

     * For instance, if you need to parse broken xml that doesn't
       conform properly to a DTD, and is missing close tags and has
       other syntax errors, then you want `beautiful soup` which is an
       incredible python library for just this purpose, and so you'd
       write in python.  By the way, you should consider all XML
       received from an outside source as probably broken, full of
       syntax errors, and likely non-conformant.

1. It's possible that the docker container with your lambda in it will
get re-used on subsequent executions.  [here's a blog post about
that](https://alestic.com/2014/12/aws-lambda-persistence/)

   * you cannot ever *depend* on this behavior.  You may be given a
     fresh container with a freshly started copy of your Lambda on any
     invocation, and for reasons outside of your control.

   * If you get the same container again you don't pay the startup
     cost again (espcially important for a Java Lambda).

   * If you get the same container again that means you may be able to
     keep intermediate state in either the filesystem, JVM or some
     other means.

   * Your Lambda cannot assume it's in a *clean* container, or freshly
     started.  So if you bind something big into your lambda, like all
     of lucene, then you can't assume it is uninitialized.

   * AWS *might* keep your container around for up to five minutes,
     just in case you invoke it again.  This means you can arragnge to
     schedule a call to your lambda every few minutes (say) to keep it
     alive.  

     * Amazingly AWS is okay with this, even though it basically gives
       you full-time docker container (approximately a t1.micro or
       t2.micro) at a much lower cost than an EC2 or ElasticBeanstalk
       version of the same.

   * This creates a potential security leakage problem, as data from a
      previous invocation of the Lambda may still be available to
      subsequent invocations.  If this is a concern, you should make
      sure you clean up properly (temp files and in-memory objects)
      after each invocation.






# FETCH AND PREPARE THE SWAGGER SPEC YOU WANT TO USE

1. You can get a copy of the petstore swagger API to use for the
   playing around.  This specification is *already* in the 2.0 format and
   ready to use.  You will, of course use your own specification once you write one.   
   `wget -O petstore.json http://petstore.swagger.io/v2/swagger.json`

1. If you are unlucky and your Swagger specification complies with
version 1.2, you *might* be able to succeed by installing
`swagger-converter` and `swagger-tools` to convert it.  This may be
easy or hard depending on how many prerequisites you already have
installed.
```
   npm install swagger-converter --save    
   npm install -g swagger-tools   
   swagger-tools convert your-api-docs.json   
```

1.  If you are fabulously unlucky, your Swagger specification complies with version 1.1, and you'll need to rewrite it manually.  Wah wah wah. 
    * most of the early Sequoia services have a Swagger 1.1 specification.








# INSTALL AND GET READY TO USE THE `aws-gateway-importer`

You need to have `aws-gateway-importer` available to use it, which
(sadly) means downloading and *building it from source* first.  I tried to use
`brew` to fetch a built version, but no. Because fail.

I found it 'easy' (for infinitesmal values of easy) to do this by
including `aws-gateway-importer` as a submodule.  Following the
submodule tutorial in the [git wiki](https://git.wiki.kernel.org/index.php/GitSubmoduleTutorial) will
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
horrified / alarmed at how many random dependencies are fetched from
who-knows-where in order to build this.

```
if [ -z `which mvm` ] ; then brew install mvn ; fi
cd aws-apigateway-importer
mvn assembly:assembly
```

You can safely go use the panini maker while `mvn` does it's job.  (In
other words, it is not quick.)








# MOCK OUT THE API USING THE INTERNAL GATEWAY MOCK

The AWS API Gateway offering has an internal loopback facility, and
you can use this to create callable versions of your API *almost*
effortlessly.

Of course, these callable things don't actually do anything useful,
and they don't return anything valuable.  But it gives you endpoints
to begin calling, which may be handy.

The API Gateway has four distinct control points inside it (and these
are represented graphically in a little picture for each endpoint in
the AWS Web Console).  For each REST endpoint/HTTP Method pair that
you define, there's two control points inbound from the caller to your
implementation, and two control points outbound from the
implementation back to the caller.  You can do some complex and
frightening transformations at some of these control points, using the
[Apache Velocity](https://velocity.apache.org) template engine.  

To really get value out of the API Gateway system, you'll eventually
need to learn all this, but not at first when you're just trying to
get anything at all going.

The work plan is pretty simple:
1. ingest the swagger using the `deploy_api.sh` script
2. mock all the endpoints in the swagger using the `deploy_api.sh` script

## INGEST THE SWAGGER

Using the `deploy_api.sh` script, we just gobble up the swagger spec.
There will be a lot of output, most of it can be ignored.  All that
matters is that you locate the name of the API so you can find it in
the web console if you want to, and can refer to it later.

```
	SWAGGER2=product-catalog.json
	APINAME=$( cat $SWAGGER2 | jq -r .info.title )
	echo $APINAME
	./deploy_api.sh import $SWAGGER2
```

Now you can use the AWS Web Console to go see that your API got
imported.  visit http//console.aws.amazon.com/apigateway/home and you
should see your API and all it's endpoints, methods and models.  

It's handy if this is your first time to click around and see what got
created.  In particular, click on 'Resources' for your API, and then
click on the HTTP method for an endpoint.  You'll see a simplified
flow diagram of how that endpoint will get processed.  At the moment,
only the *Method Request* has been defined (from the swagger spec),
although all your object models also got imported.



## MOCK ALL THE ENDPOINTS USING THE INTERNAL LOOPBACK

The `deploy_api.sh` script can take the name of the API or the id of
the API interchangeably, and will internally figure out which one you
gave it and rearrange what it does accordingly.  You are welcome.

Now you can use `deploy_api.sh` to mock all the endpoints.   

Behind the scenes, `deploy_api.sh` will

  * validate that the API you asked to mock exists
  * turn the name of the api (if that's what you gave it) into the id
  * iterate over all the endpoint/method pairs in your api  
    for each endpoint/method pair
    * define the Integration Request type and target as 'MOCK'
    * define the Integration Response sensibly so that you can return at all
    * define the Method Response sensibly so that you can return anything at all

```
	./deploy_api.sh $APINAME mockall
```

This does not take long enough for a panini-maker trip.  But it
outputs a lot of stuff, most of which you can ignore as long as none
of it is an error message.

## DEPLOY AN ENVIRONMENT (STAGE)

What we would call an environment (test or prod) the AWS API Gateway calls a stage.   

At this point you can now crate a stage (this will also force the
first deploy of your API) and expect it to work!

```
	ENVNAME=test
        ./deploy_api.sh $APINAME create $ENVNAME

	ENV_URL=$( ./deploy_api.sh $APINAME $ENVNAME uri )
	echo $ENV_URL
```

That last bit gets us the raw URL that we can begin to call our apis with.  










# SOMETIMES THINGS GO TERRIBLY WRONG 

1. I also fetched a spec that Kevin Albert had written recently   
   `wget -O api-docs.json http://kevin-demo-app01.test.cirrostratus.org/v2/api-docs`

1. Running `aws-gateway-importer` on it failed with a   
   `java.lang.StackOverflowError` (infinite recursion) error. 

1. This is because in the models, 'Product' is defined recursively as
   containing other 'Products', and the importer naievely tries to
   crawl to the bottom of the object model tree ...

1. I fixed it by creating a new model element, 'Item' (essentially the
   same as a 'Product' but without the loop) and making 'Product'
   composed of 'Item's.  This is an acceptable hack (in the API
   definition) since we now model 'Item' as a named pointer to an
   entitlement, and a 'Product' is an array of such 'Item'
   entitlements which itself also has an entitlement.

1. From the point of view of the Product Catalog API we don't expect
   anyone to need to crawl the entitlement tree from a Product like
   *Arts and Sciences 32* to an individual leaf article.  

   * Or for that matter no one expects that 'counting' the 'items' at
     the top level of a product gives a count of the leaves.  

   * So making a Product not-crawlable by the provided API model
     probably makes sense, even though as a matter of strict fact it is
     crawlable.

1. This has the side effect of forcing you to use EME to answer the
   question of whether a particular leaf item is contained in a
   particular product (e.g. "is this article in this Arts And Sciences MCMLXXVII ?").

   * You'd ask that by getting the entitlement for the Item (from it's
     individual leaf Product), and the entitlement for the Product
     you are asking the question of, and call the one of the ItemHaz
     apis on EME.

1. Once the loopy model was corrected, we discover that the way
   SpringFox annotations creates model names that are
   non-alphanumeric, like `DeferredResult«object»` and therefore
   unacceptable to `aws-gateway-importer`.
   * So I fixed those up to all be pure alphanumeric.

1. The importer also does not allow content type `*/*`. 
   * I replaced all
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