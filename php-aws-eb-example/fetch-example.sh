#!/bin/bash

# Write Some Code If Necessary
echo "The purpose of this example is not to show you how to write PHP, so we'll just fetch a very simple example application from"
echo "http://php-html.net/tutorials/model-view-controller-in-php/"

set -x
wget http://downloads.sourceforge.net/project/mvc-php/mvc.zip
unzip mvc.zip
mv mvc/* .
rm -rf mvc mvc.zip readme.txt

