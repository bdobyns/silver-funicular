# HOW TO RESCUE A SERVER THAT IS TOO OLD TO JUST MIGRATE CLEANLY

*Migrate Cleanly* means you can just take the directory tree of php/perl/python or the .jar or .war file and run it in the latest release of Ubuntu, Redhat, Centos or ElasticBeanstalk.

*Too Old To Migrate Cleanly* means you've got a server or application that
is still running, but is on a distribution that is now too old to be
properly supported (say, Fedora8 or Centos4.6). It may also be the case that the
application is written using a language or components that are no
longer supported or full of security holes (PHP4, MySQL4 etc).  

In a separate companion document we show the specific steps we actually used to rescue a specific Centos4.6 application which used some nonstandard RPMS from a VMware image. 

In General, You need to

1. Work slowly and document every step carefully so you can backtrack if something goes wrong.
1. Move the application to new hardware (because the old hardware is about to fail / full of dust bunnies / on fire / too small) without disturbing the ancient software stack to provide business continuity.
   * In this document, the objective is to move the old system to a docker container.
   * Other similar procedures could be used to move it to an AMI, a VirtualBox VM or VMware VM.
2. Once moved you have time and leisure to decide how to rewrite the application to be on a more modern software stack.

# LINUX DISTRO SPECIFIC ISSUES

* Note that Debian is much more harsh about taking down public repositories of ancient distributions.  There is no way to get a copy of Debian Potato (for example) anymore.

* Centos (RedHat) is much better about making ancient distributions available still, although you may need to edit the repo urls so that you point at a different place.  See the [Centos Vault](http://vault.centos.org)

* It may be helpful (or may not) to make a base image for your distribution, but it is not strictly necessary.  A separate companion document to this one shows how.

# MAKE A TARBALL OF THE OLD SERVER

* We assume you have ssh access to the failing instance, and sudo or root credentials.  If it's a virtual machine (vmware or virtualbox) you don't actually need credentials.
* Perform enough forensic analysis so that you can figure out what version of the OS (e.g. Centos3.2) and what version of major application dependencies you rely on (Python 1.99, PHP 4.3.2, Perl 5.8.0, MySQL 3.23.58 etc).  
* If you can do an `rpm -qa` or `dpkg --get-selections` to find out what's available, do so and save the output.
*  For A Running Instance You Can't Stop:  
   * You still want to stop services like mysql so you can get a consistent copy of the database files on disk.  
   Not doing so risks getting corrupted or unreadable files.
   * Get everything inside it except /proc and /dev (the --one-file-system option may or may not be something you want).
      
```
    ssh user@failingserver 'cd / ; sudo tar cf - --exclude=/dev --exclude=/proc --one-file-system *' >failingserver.tar
```

*  For An instance You Can Stop And Tinker With (like a vmware image).   
   * make sure the instance has been properly shut down (no random files for dead processes in /var/run/ or /var/lock/ or the like) 
   * in particular, things like postgres or mysql must be properly shut down or the database files on disk can be corrupt
   * Mount the root volume  using another linux distribution (either a live-cd if it's hardware, or on another vm if it's not)
   * if it's a vm, inspect the vm metadata file (.vmx or .vbox - almost always plaintext) to make sure you get all the disk images - there may be more than one.   
   You can ignore /boot if it's on a separate partition, and you can ignore the swap partition.
   * tar it up.  note that we (likely) don't need to exclude /proc and /dev this time because they're not present (mounted) in this case  

```
     ssh -i mykey_rsa user@otherdistro 'cd /mnt/sdb2 ; sudo tar cf - *' >failingserver.tar
```



# MAKE AN INITIAL DOCKER CONTAINER

* With a tarball in hand, you will now make an inital docker image.  This is fairly easy, and even if the tarball is of a 32-bit linux, you can likely still run things inside it with a 64-bit docker container kernel.  Note that this is far from the final step, and you may end up making several Docker Containers before you're completely done.
* Make a Docker image:
 
```
    $TODAY=`date +%Y%m%d`
    docker import failingserver.tar productops/failingserver_$TODAY
```

* This command creates an image named productops/failingserver_0.1 that you can open up and inspect.   
  You will also do some work inside the image to try and figure out what can be done, and what needs to be done.
* Start a bash in the image to see if you are able to use it

```
    docker run -i -t productops/failingserver_$TODAY /bin/bash
```

* Inside a docker container, you don't really get to run init and process all the /etc/init.d scripts the way you do in a regular linux instance.   
  Typically you just run one or two processes - whatever your app needs, but not all the other bits.   
  So you need to indentify the bits you need, and ignore the bits you don't.

   * most systems start in runlevel 3, but maybe not this one.  
   Something like  `cat /etc/inittab | grep initdefault` will tell you what runlevel this system used.
   * now, you need to examine the init scripts for that runlevel, typically in `/etc/rc3.d`
      * do `cd /etc/rc3.d ; ls -1 S*`
      * comb thru the list looking for things you can ignore  
      (like `S09isdn` and `S09pcmcia` because they refer to hardware you don't have, or `S10network` which starts services you don't need and can't start anyway)
      * most things that normally run in a linux box are *totally unnecessary* in a docker container.  
      For example, in a typical Centos4 box, *all* of these are started, and *none* of these are necessary to run in a Docker container: 

```
S00microcode_ctl
S02lvm2-monitor
S05kudzu
S06cpuspeed
S08iptables
S09isdn
S09pcmcia
S10network
S12syslog
S13irqbalance
S13portmap
S14nfslock
S15mdmonitor
S18rpcidmapd
S19rpcgssd
S25netfs
S26apmd
S28autofs
S40smartd
S44acpid
S55cups
S55sshd
S56rawdevices
S56xinetd
S80sendmail
S85gpm
S90crond
S95anacron
S95atd
S97messagebus
S97rhnsd
S98haldaemon
S99local
```

   * The short list of things you MUST run is probably really short.   
     * For a PHP app like a blog it may be just `mysqld` and `httpd`.   
     * For a complicated app with a database, some stuff in java and some stuff served by apache, it might be `mysqld` and `httpd` plus `tomcat`
   * notice the order they're started in, and try to start them manually one by one from inside the running container and see if they can be made to run (the commands below are the ones to start the application in a _Krugle Enterprise 2.4.3.1_
      
```
    /etc/rc3.d/S64mysqld start 
    /etc/rc3.d/S70hub start 
    /etc/rc3.d/S85httpd-ent start 
    /etc/rc3.d/S86resin-krugle-api 
```


# UNDERSTAND WHAT'S PART OF THE OS AND WHAT'S THE APPLICATION