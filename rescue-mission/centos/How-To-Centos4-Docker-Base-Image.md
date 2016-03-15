# MINIMAL 32-BIT CENTOS 4.X FROM ISO

The objective here is to build a Centos 4.4, 4.6, 4.8 or 4.9 Docker Image with a 32-bit userland.

How I built a base linux image for several ancient distributions that are no longer supported, *and* fixed them up so that they can still `yum install` something useful.  And got `gosu` into them.cd .

Most of these techniques can be used for any linux image installable from an ISO, whether i386, i586, i686, x86_64.  



# PRE-REQUISITES

1. Docker installed and working
2. VirtualBox or VMware or something like it installed and working
3. wget 
4. a bittorrent client
5. working knowledge of basic linux command line tools including ssh, sed, tar, yum, sudo 




# GET THE INSTALLATION ISO

I got the installation ISO with:

| Centos | How |
| ------- | --- |
| 4.4 | `wget http://mirror.symnds.com/distributions/CentOS-vault/4.4/isos/i386/CentOS-4.4.ServerCD-i386.iso` |
| 4.6 | by torrenting `http://mirror.symnds.com/distributions/CentOS-vault/4.6/isos/i386/CentOS-4.6-i386-binDVD.torrent` |
| 4.8 | by torrenting `http://mirror.symnds.com/distributions/CentOS-vault/4.8/isos/i386/CentOS-4.8-i386-binDVD.torrent` |
| 4.9 | by torrenting `http://mirror.symnds.com/distributions/CentOS-vault/4.8/isos/i386/CentOS-4.8-i386-binDVD.torrent` |

Note that 4.9 was never released as ISOs, so you have to `yum update` from 4.8 to get there.  Consume the [readme](http://mirror.symnds.com/distributions/CentOS-vault/4.9/isos/i386/readme.txt) for 4.9 to grok this better. 




# INSTALL INTO AN EMPTY VIRTUAL MACHINE

(These instructions assume VirtualBox, but using qemu or VMware is basically identical).

1. I created a new VirtualBox, selecting `Other Linux 2.6/3.x`.  
   The hard disk can be small-ish, 8GB or even 4GB is more than enough.
1. I connected the downloaded ISO as a CD/optical device.
1. When the VirtualBox boots, at the grub prompt, I type 'linux text' so that I'm doing an install in text mode.
   1. I use DiskDruid to partition, since this makes things much easier later.   
      1. Make /dev/sda1 100mb
      1. Make /dev/sda2 the remainder of the free space
      1. You don't need a swap, although it will warn you.
1. Most of the settings don't matter (network, selinux, root password).  pick something sensible, so you could actually boot the image if you want or need to.
1. Unselect every package at the package selection screen (it still installs 500 to 700mb of stuff)
1. When it says "Press Enter To Reboot"
   1. Go ahead and press enter
   2. Power off the virtual machine after it shuts down and before the reboot begins




# COLLECT A TARBALL OF THE INSTALLED INSTANCE

1. Edit the settings in a different Linux VirtualBox
   1. add the disk image you just created as another drive in THIS virtual machine
   1. I just made a copy of my Docker 'default' image, and renamed it 'disk-manipulator' (copying an image is an easy thing to do in VirtualBox)
   1. It needs to be powered off when you're doing this
1. Start the 'disk-manipulator' VirtualBox
1. Login and mount the disk.  Your command may vary somewhat, depending on how many drives are in 'disk-manipulator'  
   `mount /dev/sdb2 /mnt/sdb2`
1. From outside, ssh into the VirtualBox and tar up the disk   
   `ssh -i $DOCKER_CERT_PATH/id_rsa docker@192.168.99.101 "cd /mnt/sdb2 ; sudo tar czf - *" >centos46_i386.tar.gz`
1. You can shut down the 'disk-manipulator' now.   





# BUILD A DOCKERFILE TO MAKE AN IMAGE

1. We build a Dockerfile to import the image.
   * this is not strictly necessary, since we could simply   
     `docker import centos46_i386.tar bdobyns/centos4.6_i386`       
     but that does not allow us to fixup the repo    
2. In the Dockerfile, we follow [2k0ri's recipe](https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/) for pointing the yum repos properly to the vault, which you will need to do to use yum to install anything at all.     
   Because we are trying to make a specific version of the image (4.4 or 4.6) we need to edit the 4.9 in the recipe for the version we intend.  
3. Also, following the suggestion in "Using Docker, Adrian Mouat, page 309, we install gosu as an alternative to su and sudo.
3. The example below is for Centos 4.6 

```
# this builds a Centos4.6 i386 image
# with a working yum repo pointer
FROM scratch

# self-blame
MAINTAINER <barry@productops.com>

# we did a minimal install into a vm, and then 
# mounted the disk image in a different vm to 
# capture a tarball which we add here
ADD centos46_i386.tar.gz /

# follow the recipe in 2k0ri/centos4-64-vault
# to update the repo pointers
# note we update here to 4.6, not to 4.9
RUN sed -ri -e 's/^mirrorlist/#mirrorlist/g' -e 's/#baseurl=http:\/\/mirror\.centos\.org\/centos\/\$releasever/baseurl=http:\/\/vault\.centos\.org\/4\.6/g' /etc/yum.repos.d/CentOS-Base.repo

# need these for anything useful
RUN yum install -y curl wget

# install gosu, per "Using Docker", Adrian Mouat, p309
RUN curl -k -o /usr/local/bin/gosu -fsSL "https://github.com/tianon/gosu/releases/download/1.7/gosu-i386" && chmod +x /usr/local/bin/gosu

# you still might want to run yum update, but I didn't
# RUN yum update -y

# you should also examine the shellshock repair trick shown in 2k0ri/centos4-64-vault
# https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/
```

* Build with   
   `docker build bdobyns/centos4.6_i386 .`




# SPECIAL CONSIDERATION FOR CENTOS 4.9

* There are no ISO images for 4.9, so you have to start with 4.8 first, and then `yum update` to 4.9.  

```
# Make a Centos4.9 from a Centos4.8
FROM bdobyns/centos4.8_i386

# self-blame
MAINTAINER <barry@productops.com>

# follow the recipe in 2k0ri/centos4-64-vault
# to update the repo pointers
RUN sed -ri -e 's/^mirrorlist/#mirrorlist/g' -e 's/#baseurl=http:\/\/mirror\.centos\.org\/centos\/\$releasever/baseurl=http:\/\/vault\.centos\.org\/4\.9/g' /etc/yum.repos.d/CentOS-Base.repo

# centos 4.9 is a yum update away from 4.8
RUN yum update -y

# you should also examine the shellshock repair trick shown in 2k0ri/centos4-64-vault
# https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/
```

2. However, this modifies nearly every file in the image, doubling it's size.    
   So it goes from being a 650Mb image to being a 1.2Gb image.  That's annoying.
   1. Get your image id from `docker images`
   1. `docker export d8792700441c >centos49_i386.tar`
   1. `docker import centos49_i386.tar bdobyns/centos4.9_i386`
3. now you have a smaller 4.9 image that has no (mostly wasted) 4.8 layer underneath it.
4. use `docker rm` and `docker rmi` to cleanup crap you don't need anymore.

This points out a major weakness of the Docker Layer methodology.  If most of the files in a layer are different from the ones in the layer below, then the resulting size is roughly double. 



# CLEANUP NOW THAT YOU ARE DONE

1. You should disconnect the disk image from the 'disk-manipulator'
1. You can delete the VirtualBox image you created to hold the Centos4.x
1. If you never plan to do this again (but it was so much fun!), you can delete the 'disk-manipulator' as well
1. use `docker rm` and `docker rmi` to cleanup crap you don't need anymore.
1. delete the tarball you created.  It's too big to check into github anyway.




# WHY SHOULD ANY OF THIS EVER BE NECESSARY

The reason for all this jiggery-pokey is that I (bdobyns) needed a Centos 4.x base image with a 32-bit userland to rescue some old software that I have which *must* live on this *particular* distribution of Centos and is unhappy otherwise.    Note that because docker, I still have a 64-bit kernel, but everything else inside this container is 32-bit, and shockingly, my antique application stack works just fine, thankyouverymuch.

The ability do to this kind of crazy mashup is, for me, a key value of Docker Containers - I don't care that Centos 4.x is old, unsupported, and grotty - I can provide all the pieces for my old app to run, and have them isolated from the modern substrate.

While both [fatherlinux/centos4-base](https://hub.docker.com/r/fatherlinux/centos4-base/) and the derivative [2k0ri/centos4-64-vault](https://hub.docker.com/r/2k0ri/centos4-64-vault/) are similar to what I built here, each of those have a 64-bit userland, which does not help me at all.  They are also slightly larger, no doubt bloated from those long 64bit pointers. :)



# TINFOIL HATS AND PARANOIA

1. Why should you ever build an image at all?  You *know* the [Docker Hub](https://hub.docker.com) is just *full* of tasty images, right?
2. Because you don't trust most of them.   
   You certainly don't trust them for production use.
2. The [Official Ubuntu](https://hub.docker.com/_/ubuntu/) can be trusted perhaps, or the [Official Debian](https://hub.docker.com/_/debian/) or [Official Centos](https://hub.docker.com/_/centos/), 
  1. but you should *NEVER NEVER* trust any non-official image.   
  1. It's too easy for a developer to sneak in a layer with something nasty
  1. Like, maybe sneak in a layer with a compromised executable (like apache, or an apache module) that you don't notice.
    1. It can even be a *valid* older release, just one that has a vulnerability unpatched.
  1. *Don't use untrusted images in your* `FROM`
  1. It is possible to copy, carefully review, and use someone else's `Dockerfile` safely
1. Did you know that all your favorite public keys are globally visible?  
  1. For example,  `curl https://github.com/bdobyns.keys` and see.
  1. this makes it easy to add a run line to a Dockerfile like   
     `RUN curl https://github.com/bdobyns.keys >>/home/ubuntu/.ssh/authorized.keys`    
     `RUN curl https://github.com/bdobyns.keys >>/.ssh/authorized.keys`
  1. Not useful to you?  It's useful to *attackers*.   That's in a layer you didn't notice.   
     Heck, it may be in every docker image I ever build from now on.




# OLD DISTROS HAVE SECURITY BUGS

* If you actually use one of these Centos4.x images, you may want to look carefully at [2k0ri's recipe](https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/) for updating bash to avoid the *shellshocked* bug.  Or be very very careful that you never run `bash` in your container.
* That's why they are *old* and *abandoned*.



# BARRYS RESULTS ARE VISIBLE HERE

| DISTRO | URL |
| ------ | --- |
| Centos 4.4 i386 | https://hub.docker.com/r/bdobyns/centos4.4_i386/ |
| Centos 4.4 i386 | https://hub.docker.com/r/bdobyns/centos4.4_i386/ |
| Centos 4.6 i386 | https://hub.docker.com/r/bdobyns/centos4.6_i386/ |
| Centos 4.9 i386 | https://hub.docker.com/r/bdobyns/centos4.9_i386/ |
