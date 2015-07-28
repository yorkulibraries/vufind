Installation (as root)
======================
cd /usr/local
git clone https://github.com/yorkulibraries/vufind.git
cd /vufind
./install-libraries.sh 
./mkdir.sh 
cp httpd-vufind.conf /etc/httpd/conf.d/

* copy /usr/local/vufind/web/conf/config.ini from dev/prod server to web/conf/config.ini
* copy /usr/local/vufind/web/conf/Unicorn.ini from dev/prod server to web/conf/Unicorn.ini
* dump mysql database from prod/dev server and import into your db


VuFind needs to be mounted on /find/, if you mount it on a different prefix, then you need to make 
appropriate changes in config.ini, httpd-vufind.conf and web/interface/themes/bootstrap/min/.htaccess

*NOTE*: you must copy httpd-vufind.conf to apache config directory and restart apache.

Setup solr master/slave (which may reside on the same machine as the vufind web app)
=====================================================================================
- setup master on localhost:8080 and slave on localhost:8081, 
- searches will be on the slave while indexing is on the master

- setup OS user for master (vufind) and user for slave (vslave)

- clone vufind to /usr/local/vufind for the master instance

cd /usr/local
git clone path-to-git-repo/vufind.git vufind
chown -R vufind:vufind vufind

- clone vufind to /home/vslave/vufind for the slave instance
cd /home/vslave 
git clone path-to-git-repo/vufind.git vufind
chown -R vslave:vufind vufind

- make necessary changes to both vufind/web/conf/config.ini

- Add the following to /home/vufind/.bash_profile for the master OS user (vufind) 

export VUFIND_HOME=/usr/local/vufind
export PATH=$JAVA_HOME/bin:$PATH
export INDEX_OPTIONS="-Xms512m -Xmx4g -DentityExpansionLimit=0"
export JETTY_CONSOLE=$VUFIND_HOME/solr/jetty/logs/console.log
export JAVA_OPTIONS="-server -Dmaster.enable=true"
export JETTY_PORT=8080
export JETTY_PID=/tmp/$USER.pid
export SOLRCORE=biblio

- Add the following to the /home/vslave/.bash_profile for the slave OS user (vslave)
export JAVA_HOME=~/java
export VUFIND_HOME=~/vufind
export JETTY_CONSOLE=$VUFIND_HOME/solr/jetty/logs/console.log
export JAVA_OPTIONS="-server -Xmx8192m -Dslave.enable=true -Dmaster.url=http://localhost:8080/solr"
export JETTY_PORT=8081
export JETTY_PID=/tmp/$USER.pid

- login as vufind (master user) to build the index 
/usr/local/vufind/vufind.sh start
/usr/local/vufind/reindex.sh

- after indexing is done, login as vslave (slave user) to start the slave instance
~/vufind/vufind.sh start

- the slave will begin replicating the index from the master instance



Files to pay attention to when merging with upstream
=======================================================
./httpd-vufind.conf
./import/marc_local.properties
./import-marc.sh
./solr/authority/conf/solrconfig.xml
./solr/biblio/conf/elevate.xml
./solr/biblio/conf/schema.xml
./solr/biblio/conf/solrconfig.xml
./solr/biblio/conf/synonyms.txt
./solr/reserves/conf/solrconfig.xml
./solr/solr.xml
./web/bookcover.php
./web/conf/config.ini
./web/conf/facets.ini
./web/conf/fulltext.ini
./web/conf/reserves.ini
./web/conf/searches.ini
./web/conf/searchspecs.yaml
./web/conf/sms.ini
./web/conf/Unicorn.ini
./web/conf/vufind.ini
./web/Drivers/Unicorn.php
./web/index.php
./web/interface/plugins/function.css.php
./web/interface/plugins/modifier.addEllipsis.php
./web/lang/en.ini
./web/lang/fr.ini
./web/RecordDrivers/Factory.php
./web/RecordDrivers/IndexRecord.php
./web/RecordDrivers/MarcRecord.php
./web/services/AJAX/JSON.php
./web/services/Cart/Email.php
./web/services/Cart/Export.php
./web/services/MyResearch/CheckedOut.php
./web/services/MyResearch/Edit.php
./web/services/MyResearch/EditList.php
./web/services/MyResearch/Fines.php
./web/services/MyResearch/Holds.php
./web/services/MyResearch/Home.php
./web/services/MyResearch/lib/FavoriteHandler.php
./web/services/MyResearch/lib/User.php
./web/services/MyResearch/Login.php
./web/services/MyResearch/Logout.php
./web/services/MyResearch/MyList.php
./web/services/MyResearch/Profile.php
./web/services/Record/Cite.php
./web/services/Record/Email.php
./web/services/Record/Hold.php
./web/services/Record/Holdings.php
./web/services/Record/Record.php
./web/services/Record/Save.php
./web/services/Record/SMS.php
./web/services/Record/UserComments.php
./web/services/Record/xsl/record-marc.xsl
./web/services/Search/Advanced.php
./web/services/Search/Email.php
./web/services/Search/Home.php
./web/services/Search/Reserves.php
./web/services/Search/Results.php
./web/services/Search/xsl/json-rss.xsl
./web/sys/authn/LDAPAuthentication.php
./web/sys/authn/LDAPConfigurationParameter.php
./web/sys/Cart_Model.php
./web/sys/HoldLogic.php
./web/sys/Interface.php
./web/sys/Logger.php
./web/sys/Mailer.php
./web/sys/Pager.php
./web/sys/Resolver/ResolverConnection.php
./web/sys/Resolver/sfx.php
./web/sys/SearchObject/Factory.php
./web/sys/SearchObject/Solr.php
./web/sys/Solr.php
./web/sys/Translator.php
./web/sys/User.php

