#!/bin/sh

. ~/.bash_profile

if [ -z "$VUFIND_HOME" ]
then
  export VUFIND_HOME=/usr/local/vufind
fi
  
cd $VUFIND_HOME || exit

# run preprocessing
java -jar import/YorkIndexer.jar

# import catalog marc files
[ -f /tmp/catalog.mrc ] && /usr/local/vufind/import-marc.sh /tmp/catalog.mrc &>/tmp/import-catalog.log
grep -A2 -B2 -i exception /tmp/import-catalog.log
grep -A2 -B2 -i error /tmp/import-catalog.log
grep -A2 -B2 -i severe /tmp/import-catalog.log

# import SFX journals marcxml
[ -f /tmp/sfx-journals.xml ] && curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>data_source_str:SFX</query></delete>' 1>/dev/null
[ -f /tmp/sfx-journals.xml ] && /usr/local/vufind/import-sfx.sh /tmp/sfx-journals.xml &>/tmp/import-sfx-journals.log
grep -A2 -B2 -i exception /tmp/import-sfx-journals.log
grep -A2 -B2 -i error /tmp/import-sfx-journals.log
grep -A2 -B2 -i severe /tmp/import-sfx-journals.log

# import MULER marc 
[ -f /tmp/muler.mrc ] && curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>data_source_str:MULER</query></delete>' 1>/dev/null
[ -f /tmp/muler.mrc ] && /usr/local/vufind/import-muler.sh /tmp/muler.mrc &>/tmp/import-muler.log
grep -A2 -B2 -i exception /tmp/import-muler.log
grep -A2 -B2 -i error /tmp/import-muler.log
grep -A2 -B2 -i severe /tmp/import-muler.log

# cleanup and commit
curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>format:Delete</query></delete>' 1>/dev/null
curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>title:"**REQUIRED FIELD**"</query></delete>' 1>/dev/null
curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<commit/>' 1>/dev/null
