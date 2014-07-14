#!/bin/sh

if [ "$VUFIND_HOME"="" ]; then
  VUFIND_HOME=/usr/local/vufind
fi

if [ ! -d mkdir $VUFIND_HOME/solr/jetty/logs ]; then
  echo mkdir $VUFIND_HOME/solr/jetty/logs does not exist, creating it...
  mkdir -p $VUFIND_HOME/solr/jetty/logs
fi

if [ ! -d $VUFIND_HOME/web/interface/compile ]; then
  echo $VUFIND_HOME/web/interface/compile does not exist, creating it...
  mkdir -p $VUFIND_HOME/web/interface/compile
fi
chmod -R a+rw $VUFIND_HOME/web/interface/compile

if [ ! -d $VUFIND_HOME/web/interface/cache ]; then
  echo $VUFIND_HOME/web/interface/cache does not exist, creating it...
  mkdir -p $VUFIND_HOME/web/interface/cache
fi
chmod -R a+rw $VUFIND_HOME/web/interface/cache

if [ ! -d $VUFIND_HOME/web/images/covers/small ]; then
  echo $VUFIND_HOME/web/images/covers/small does not exist, creating it...
  mkdir -p $VUFIND_HOME/web/images/covers/small
fi

if [ ! -d $VUFIND_HOME/web/images/covers/medium ]; then
  echo $VUFIND_HOME/web/images/covers/medium does not exist, creating it...
  mkdir -p $VUFIND_HOME/web/images/covers/medium
fi

if [ ! -d $VUFIND_HOME/web/images/covers/large ]; then
  echo $VUFIND_HOME/web/images/covers/large does not exist, creating it...
  mkdir -p $VUFIND_HOME/web/images/covers/large
fi

if [ ! -d $VUFIND_HOME/web/images/covers/local ]; then
  echo $VUFIND_HOME/web/images/covers/local does not exist, creating it...
  mkdir -p $VUFIND_HOME/web/images/covers/local/small
  mkdir -p $VUFIND_HOME/web/images/covers/local/medium
  mkdir -p $VUFIND_HOME/web/images/covers/local/large
  mkdir -p $VUFIND_HOME/web/images/covers/local/original
fi
chmod -R a+rw $VUFIND_HOME/web/images/covers

if [ ! -d $VUFIND_HOME/openurl_cache ]; then
  echo $VUFIND_HOME/openurl_cache does not exist, creating it...
  mkdir -p $VUFIND_HOME/openurl_cache
fi
chmod -R a+rw $VUFIND_HOME/openurl_cache

if [ ! -d $VUFIND_HOME/log ]; then
  echo $VUFIND_HOME/log does not exist, creating it...
  mkdir -p $VUFIND_HOME/log
  touch $VUFIND_HOME/log/messages.log
fi
chmod -R a+rw $VUFIND_HOME/log

chmod -R a+rw $VUFIND_HOME/web/interface/themes/bootstrap/css
chmod -R a+rw $VUFIND_HOME/web/interface/themes/bootstrap/js
