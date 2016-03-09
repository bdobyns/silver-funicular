inside the instance `rpm --repackage jdk-1.5.0_09-fcs` which puts the rebundled RPM in `/var/spool/repackage/jdk-1.5.0_09-fcs.i586.rpm`

docker exec 8e004dbbee00 cat /var/spool/repackage/jdk-1.5.0_09-fcs.i586.rpm >jdk-1.5.0_09-fcs.i586.rpm 
