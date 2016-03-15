#!/bin/bash -x -e
docker build -t bdobyns/centos4.8_i386 .
docker push bdobyns/centos4.8_i386
