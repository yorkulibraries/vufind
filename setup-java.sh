#!/bin/sh

add-apt-repository -y ppa:webupd8team/java
apt-get update >/dev/null

echo debconf shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | /usr/bin/debconf-set-selections
apt-get -y install oracle-java8-installer
