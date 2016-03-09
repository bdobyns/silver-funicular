# build a Centos 4.9 image
# with a working yum repo pointer
FROM bdobyns/centos4.8_i386

# self-blame
MAINTAINER <barry@productops.com>

# follow the recipe in 2k0ri/centos4-64-vault
# to update the repo pointers
# note we update here to 4.6, not to 4.9
RUN sed -ri -e 's/^mirrorlist/#mirrorlist/g' -e 's/#baseurl=http:\/\/mirror\.centos\.org\/centos\/\$releasever/baseurl=http:\/\/vault\.centos\.org\/4\.9/g' /etc/yum.repos.d/CentOS-Base.repo

# don't need to get curl, wget, or gosu, since those are in the 4.8 image

# you still might want to run yum update, but I didn't
RUN yum update -y

# you should also examine the shellshock repair trick shown in 2k0ri/centos4-64-vault
# https://hub.docker.com/r/2k0ri/centos4-64-vault/~/dockerfile/
