# assemble a working ke 2.4.3.1
FROM scratch

ADD ke243-tarball.tar.gz /

# install gosu, per "Using Docker", Adrian Mouat, p309
RUN curl -k -o /usr/local/bin/gosu -fsSL "https://github.com/tianon/gosu/releases/download/1.7/gosu-i386" && chmod +x /usr/local/bin/gosu

# 80   httpd-ent
# 9100 resin-krugle-api
# 8080 hub
# 3306 mysqld
EXPOSE 80 9100 8080 3306

# ENTRYPOINT NEEDS DO DO THIS:
#  service atd start
#  service mysqld start
#  service crond start
#  service httpd-ent start
#  service resin-krugle-api start
#  service hub stop ; service hub start
#  service krugle-monitor start
