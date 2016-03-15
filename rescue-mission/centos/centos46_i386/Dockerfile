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

# need these for anything useful
RUN yum install -y curl wget

# install gosu, per "Using Docker", Adrian Mouat, p309
RUN curl -k -o /usr/local/bin/gosu -fsSL "https://github.com/tianon/gosu/releases/download/1.7/gosu-i386" && chmod +x /usr/local/bin/gosu

# you still might want to run yum update, but I didn't
# RUN yum update -y

# you should also examine the shellshock repair trick shown in 2k0ri/centos4-64-vault
# https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/