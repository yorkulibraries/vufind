#!/bin/sh
. `dirname ${0}`/lcp.sh
java -server -Djava.awt.headless=true -Xms1024m -Xmx1024m -XX:MaxPermSize=128m -XX:+UseParallelGC -XX:NewRatio=5 -classpath $LOCALCLASSPATH -Djava.util.logging.config.file=logging.properties org.semanticdesktop.aperture.examples.ExampleWebCrawler $*
