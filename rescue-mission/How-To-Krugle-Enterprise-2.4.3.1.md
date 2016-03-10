# GENERAL PROCEDURES

Our starting point is a RAR file of a VMware VM instance named "Krugle Enterprise 2.4.3 32bit 40Gb", and we start this vmware instance with both VMware as well as with VirtualBox.  

# MAKE AN INITIAL CONTAINER WITH THE RAW TARBALL

First we naievely pack up all the files using the technique outlined in the How-To-Centos4-Docker-Base-Image.md, section "Collect A Tarball"

Then we import the tarball into a container which we'll use for further work.  This is not our final container.    
   `docker import ketarball.tar bdobyns/ke2431`

# FORENSICS 

A first look inside the KE makes it appear that it's running Redhat
4.4 `cat /etc/redhat-release` but a comparison of the actual version
numbers of most of the packages reveals that this is a bastard box,
updated to Centos4.6 (also as seen in /etc/yum.repos.d).

Examining the output of `ps` or `top` The application itself seems to
consist of two java containers, resin and jetty, an apache and a
mysqld.

Most of the application appears to be in `/data/krugle`

The mysql databases are in the usual location in `/var/lib/mysql`

Apache's webroot is someplace in `/data/krugle`

Lots of files in `/etc` are changed to fixup the configuration

Most of the packages seem to be stock standard packages from the Redhat or Centos repos.

# GETTING READY

The
[bdobyns/centos4.6_i386](https://hub.docker.com/r/bdobyns/centos4.6_i386/)
(which see) is substantially similar to the KE 2.4.3.1 - we need to
find out what files are different and only move those over.  

In addition the [bdobyns/centos4.6_i386](https://hub.docker.com/r/bdobyns/centos4.6_i386/) has yum installed and working
(the KE did not, as a security measure), and has
[gosu](https://github.com/tianon/gosu) installed.

The KE ran the `resin` and `jetty` containers as root, we will `gosu` them as a less priviliged user.

inside a centos46  `rpm -qa | sort >centos46base_rpm_qa.txt`
inside a ke `rpm -qa | sort >ke_rpm_qa.txt`

find the packages only in the ke

`sort -y centos46base_rpm_qa.txt ke_rpm_qa.txt | grep '>' >only-in-ke.txt`

check the only-in-ke list to be sure the exact version of those packages are available in http://vault.centos.org/4.6/os/i386/CentOS/RPMS/

apr apr-util atk cpp curl cvs distcache expect gcc gcc-java
glibc-devel glibc-headers glibc-kernheaders gtk2 httpd httpd-suexec
java-1.4.2-gcj-compat libgcj libgcj-devel neon mod_python mod_ssl
mysql mysql-server pango perl-DBD-MySQL perl-DBI perl-URI php php-pear
postgresql-libs libidn libxslt-devel mod_perl specspo zip

Packages not available in the base repo (came from who knows where)

| Package | explanation | Needed? |
| ------- | ----------- | ------- |
| jed | jed editor | no |
| jed-common | jed editor | no |
| compat-slang | jed editor | no |
| jdk-1.5.0_09-fcs | sun jdk | yes |
| subversion-1.4.6 | newer version than repo | yes |
| sysreport | - | no |
| rpmforge-release-0.3.6-1 | add the dag repo | no |
| enscript-1.6.4-2 | newer version | yes |
| apt-0.5.15lorg3.2-1.el4.rf | alternative to rpm | no |
| rsync-3.0.0 | newer version | ? |


Start the vm instance of the machine, and use rpm to ininstall and repackage each of these, e.g.    
`rpm --repackage jdk-1.5.0_09-fcs` which puts the rebundled RPM in `/var/spool/repackage/jdk-1.5.0_09-fcs.i586.rpm`    
and then copy the resulting RPMs out of the vm

docker exec 8e004dbbee00 cat /var/spool/repackage/jdk-1.5.0_09-fcs.i586.rpm >jdk-1.5.0_09-fcs.i586.rpm 