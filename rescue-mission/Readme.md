# THIS IS HOW TO RESCUE A SERVER THAT IS TOO OLD TO JUST MIGRATE CLEANLY

So the backstory here is that you've got a server or application that
is still running, but is on a distribution that is now too old to be
properly supported (say, Fedora8 or Centos4.6). Or, worse, the
application us written using a language or components that are no
longer supported or full of security holes (PHP4, MySQL4 etc).

You need to

1.  move the application to new hardware (because the old hardware is about to fail / full of dust bunnies / on fire / too small) without disturbing the ancient software stack to provide business continuity 
2.  while you separtely: update the application to be on a more modern software stack

# GET A TARBALL OF THE OLD SERVER

1. We assume you have ssh access to the failing instance, and sudo or root credentials.  
1. Perform enough forensic analysis so that you can figure out what version of the OS (e.g. Centos3.2) and what version of major application dependencies you rely on (Python 1.99, PHP 4.3.2, Perl 5.8.0, MySQL 3.23.58 etc).  if you can do an `rpm -qa` or `dpkg --get-selections` to find out what's available, do so and save the output.
1. Make a tarball of the running instance.  There are two versions of this - one applies if you only have access to a running instance that you can't take down or stop.
   *  A Running Instance you can't stop:  
      * Get everything inside it except /proc and /dev (the --one-file-system option may or may not be something you want).

    ssh -i mykey_rsa user@failingserver 'cd / ; sudo tar cf - --exclude=/dev --exclude=/proc --one-file-system *' >failingserver.tar

   *  An instance you can stop and tinker with (like a vmware image).   
      * make sure the instance has been properly shut down (no random files for dead processes in /var/run/ or /var/lock/ or the like) 
      * Mount the disk using another linux distribution (either a live-cd if it's hardware, or on another vm if it's not)
      * tar it up.  note that we don't need to exclude /proc and /dev because they're not present in this case

     ssh -i mykey_rsa user@otherdistro 'cd /mnt/sdb2 ; sudo tar cf - *' >failingserver.tar

      * if it's a vm, inspect the vm metadata file (.vmx or .vbox - almost always plaintext) to make sure you get all the disk images.

# MAKE A DOCKER CONTAINER

1. With a tarball, you will now make a docker container.  This is fairly easy, and even if the tarball is of a 32-bit linux, you can still run things inside it with a 64-bit docker container kernel.
1. Make a Docker image:

    $TODAY=`date +%Y%m%d`
    docker import failingserver.tar productops/failingserver_$$TODAY

2. This command creates an image named productops/failingserver_0.1 that you can open up and inspect.   You can also do some work inside the image to try and figure out what can be done, and what needs to be done.
3. Start a bash in the image to see if you are able to use it

    docker run -i -t productops/failingserver_$$TODAY /bin/bash

4. Inside a docker container, you don't really get to run init and process all the /etc/init.d scripts the way you do in a regular linux instance.  Typically you just run one or two processes - whatever your app needs, but not all the other bits.   So you need to indentify the bits you need, and ignore the bits you don't.

   * most systems start in runlevel 3, but maybe not this one.  Something like  `cat /etc/inittab | grep initdefault` will tell you what runlevel this system used.
   * now, you need to examine the init scripts for that runlevel, typically in `/etc/rc3.d`
      * do `cd /etc/rc3.d ; ls -1 S*`
      * comb thru the list looking for things you can ignore (like `S09isdn` and `S09pcmcia` because they refer to hardware you don't have, or `S10network` which starts services you don't need and can't start anyway)
      * most things that normally run in a linux box are unnecessary in a docker container.  For example, in a typical Centos4 box, none of these are necessary to run in a Docker container: `S00microcode_ctl S02lvm2-monitor S05kudzu S06cpuspeed S08iptables S09isdn S09pcmcia S10network S12syslog S13irqbalance S13portmap S14nfslock S15mdmonitor S18rpcidmapd S19rpcgssd S25netfs S26apmd S28autofs S40smartd S44acpid S55cups S55sshd S56rawdevices S56xinetd S80sendmail S85gpm S90crond S95anacron S95atd S97messagebus S97rhnsd S98haldaemon S99local`
      * The short list of things you MUST run probably really is short.   For a PHP app it may be just `mysqld` and `httpd`.   For a complicated app with some stuff in java and some stuff served by apache, it might be `mysqld` and `httpd` plus `tomcat`
      * notice the order they're started in, and try to start them one by one from inside the box and see if they can be made to run (the commands below are the ones to start the application in a _Krugle Enterprise 2.4.3.1_

    /etc/rc3.d/S64mysqld start 
    /etc/rc3.d/S70hub start 
    /etc/rc3.d/S85httpd-ent start 
    /etc/rc3.d/S86resin-krugle-api 

