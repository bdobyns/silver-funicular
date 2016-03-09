# This is a minimal server installation of 32-bit user land Centos 4.x from the original server iso

This describes how I built a base linux image for several ancient distributions that are no longer supported, *and* fixed them up so that they can still `yum install` something useful.

Most of these techniques can be used for any linux image installable from an ISO, whether i386, i586, i686, x86_64.  




# GET THE INSTALLATION ISO

I got the installation ISO with:

| Version | How |
| ------- | --- |
| Centos 4.4 | `wget http://mirror.symnds.com/distributions/CentOS-vault/4.4/isos/i386/CentOS-4.4.ServerCD-i386.iso` |
| Centos 4.6 | by torrenting `http://mirror.symnds.com/distributions/CentOS-vault/4.6/isos/i386/CentOS-4.6-i386-binDVD.torrent` |
| Centos 4.8 | by torrenting `http://mirror.symnds.com/distributions/CentOS-vault/4.8/isos/i386/CentOS-4.8-i386-binDVD.torrent` |
| Centos 4.9 | by torrenting `http://mirror.symnds.com/distributions/CentOS-vault/4.8/isos/i386/CentOS-4.8-i386-binDVD.torrent` |

Note that 4.9 was never released as ISOs, so you have to `yum update` from 4.8 to get there.  Consume the [readme](http://mirror.symnds.com/distributions/CentOS-vault/4.9/isos/i386/readme.txt) for 4.9 to grok this better. 




# INSTALL INTO AN EMPTY VIRTUAL MACHINE

(These instructions assume VirtualBox, but using qemu or VMware is basically identical).

1. I created a new VirtualBox, selecting `Other Linux 2.6/3.x`.  The hard disk can be small-ish, 8Gb is more than enough.
1. I connected the downloaded ISO as a CD device.
1. When the VirtualBox boots, at the grub prompt, I type 'linux text' so that I'm doing an install in text mode.
   1. I use DiskDruid to partition, since this makes things much easier later.   
      1. Make /dev/sda1 100mb
      1. Make /dev/sda2 the remainder of the free space
1. unselect every package at the package selection screen (it still installs 500 to 700mb of stuff)
1. when it says "press enter to reboot"
   1. go ahead and press enter
   2. power off the virtual machine after it shuts down and before the reboot begins




# COLLECT A TARBALL OF THE INSTALLED INSTANCE

1. Edit the settings in a different Linux VirtualBox, add the disk image you just created as another drive (doesn't matter if it was scsi or ide before or now)
   1. I just made a copy of my Docker 'default' image, and renamed it 'disk-manipulator'
   1. It needs to be powered off when you're doing this
1. Start the 'disk-manipulator' VirtualBox
1. Login and mount the disk.  Your command may vary somewhat, depending on how many drives are in 'disk-manipulator'  
   `mount /dev/sdb2 /mnt/sdb2`
1. From outside, ssh into the VirtualBox and tar up the disk   
   `ssh -i ~/.docker/machine/machines/default/id_rsa docker@192.168.99.101 "cd /mnt/sdb2 ; sudo tar cf - *" >centos46_i386.tar`
   



# BUILD A DOCKERFILE TO MAKE AN IMAGE

1. We build a Dockerfile to import the image.
2. In the Dockerfile, we follow [2k0ri's recipe](https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/) for pointing the yum repos properly to the vault, which you will need to do to use yum to install anything at all.  Because we are trying to make a specific version of the image (4.4 or 4.6) we need to edit the 4.9 in the recipe for the version we intend.  
3. The example below is for Centos 4.6 

```
# this builds a Centos4.6 i386 image
# with a working yum repo pointer
FROM scratch

# self-blame
MAINTAINER <barry@productops.com>

# we did a minimal install into a vm, and then 
# mounted the disk image in a different vm to 
# capture a tarball, below
ADD centos46_i386.tar.gz /

# follow the recipe in 2k0ri/centos4-64-vault
# to update the repo pointers
# note we update here to 4.6, not to 4.9
RUN sed -ri -e 's/^mirrorlist/#mirrorlist/g' -e 's/#baseurl=http:\/\/mirror\.centos\.org\/centos\/\$releasever/baseurl=http:\/\/vault\.centos\.org\/4\.6/g' /etc/yum.repos.d/CentOS-Base.repo


# you still might want to run yum update, but I didn't
# RUN yum update -y

# you should also examine the shellshock repair trick shown in 2k0ri/centos4-64-vault
# https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/
```

If you use this container, you may want to look carefully at [2k0ri's recipe](https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/) for updating bash to avoid the *shellshocked* bug.  Or be very very careful that you never run `bash` in your container.




# SPECIAL CONSIDERATION FOR CENTOS 4.9

1. There are no ISO images for 4.9, so you have to start with 4.8 first, and then `yum update` to 4.9.  

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

1. However, this modifies nearly every file in the image, doubling it's size.   So it goes from being a 650Mb image to being a 1.2Gb image.  That's annoying.
   1. Get your image id from `docker images`
   1. `docker export d8792700441c >centos49_i386.tar`
   1. `docker import centos49_i386.tar bdobyns/centos4.9_i386`
1. now you have a smaller 4.9 image that has no (mostly wasted) 4.8 layer underneath it.
1. use `docker rm` and `docker rmi` to cleanup crap you don't need anymore.



# WHY SHOULD ANY OF THIS EVER BE NECESSARY

The reason for all this jiggery-pokey is that I (bdobyns) needed a Centos 4.x base image with a 32-bit userland to rescue some old software that I have which *must* live on this *particular* distribution of Centos and is unhappy otherwise.    Note that because docker, I still have a 64-bit kernel, but everything else inside this container is 32-bit, and shockingly, my antique application stack works just fine, thankyouverymuch.

The ability do to this kind of crazy mashup is, for me, a key value of Docker Containers - I don't care that Centos 4.x is old, unsupported, and grotty - I can provide all the pieces for my old app to run, and have them isolated from the modern substrate.

While both [fatherlinux/centos4-base](https://hub.docker.com/r/fatherlinux/centos4-base/) and the derivative [2k0ri/centos4-64-vault](https://hub.docker.com/r/2k0ri/centos4-64-vault/) are similar to what I built here, each of those have a 64-bit userland, which does not help me at all.  They are also slightly larger, no doubt bloated from those long 64bit pointers. :)



# TINFOIL HATS AND PARANOIA

* Why should you ever build an image at all? Because you know the [Docker Hub](https://hub.docker.com) is just *full* of tasty images.
* Because you don't trust most of them.  The [Official Ubuntu](https://hub.docker.com/_/ubuntu/) can be trusted perhaps, or the [Official Debian](https://hub.docker.com/_/debian/) or [Official Centos](https://hub.docker.com/_/centos/), 
  * but you should *NEVER NEVER* trust any non-official image.   It's too easy for a developer to sneak in a layer with their public ssh keys in some tasty image you want to use.  *Don't do it.*
* Did you know that all your favorite public keys are globally visible?  For example,  `curl https://github.com/bdobyns.keys` and see.
  * this makes it easy to add a run line to a Dockerfile like   
  `RUN curl https://github.com/bdobyns.keys >>/home/ubuntu/.ssh/authorized.keys`    
  Not useful to you?  it's useful to attackers.   That's in a layer you didn't notice.



# BARRYS RESULTS ARE VISIBLE HERE

| Centos 4.4 i386 | https://hub.docker.com/r/bdobyns/centos4.4_i386/ |
| Centos 4.4 i386 | https://hub.docker.com/r/bdobyns/centos4.4_i386/ |
| Centos 4.6 i386 | https://hub.docker.com/r/bdobyns/centos4.6_i386/ |
| Centos 4.9 i386 | https://hub.docker.com/r/bdobyns/centos4.9_i386/ |
