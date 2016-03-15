#!/bin/bash -x -e
docker build -t bdobyns/centos4.9_i386 .
docker export d8792700441c >centos49_i386.tar
docker import centos49_i386.tar bdobyns/centos4.9_i386
docker push bdobyns/centos4.9_i386
