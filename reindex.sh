#!/bin/sh

. ~/.bash_profile

if [ -z "$VUFIND_HOME" ]
then
  export VUFIND_HOME=/usr/local/vufind
fi
  
cd $VUFIND_HOME || exit

if [ -z "$MASTER" ]; then
  echo "MASTER environment variable not set. Please set it to the URL of the master solr biblio index."
  echo "Example: export MASTER=http://localhost:8080/solr/biblio"
  exit 
fi

# run preprocessing
java $INDEX_OPTIONS -jar import/YorkIndexer.jar

[ -f /tmp/sfx-journals.xml ] && curl -s -S ${MASTER}/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>data_source_str:SFX</query></delete>' 1>/dev/null
[ -f /tmp/muler.mrc ] && curl -s -S ${MASTER}/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>data_source_str:MULER</query></delete>' 1>/dev/null

# import SFX journals marcxml
[ -f /tmp/sfx-journals.xml ] &&  ${VUFIND_HOME}/import-sfx.sh /tmp/sfx-journals.xml &>/tmp/import-sfx-journals.log &

# import MULER marc 
[ -f /tmp/muler.mrc ] &&  ${VUFIND_HOME}/import-muler.sh /tmp/muler.mrc &>/tmp/import-muler.log &

wait

# import catalog marc files
[ -f /tmp/0catalog.mrc ] &&  ${VUFIND_HOME}/import-marc.sh /tmp/0catalog.mrc &>/tmp/import-catalog0.log &
[ -f /tmp/1catalog.mrc ] &&  ${VUFIND_HOME}/import-marc.sh /tmp/1catalog.mrc &>/tmp/import-catalog1.log &
[ -f /tmp/2catalog.mrc ] &&  ${VUFIND_HOME}/import-marc.sh /tmp/2catalog.mrc &>/tmp/import-catalog2.log &
[ -f /tmp/3catalog.mrc ] &&  ${VUFIND_HOME}/import-marc.sh /tmp/3catalog.mrc &>/tmp/import-catalog3.log &

wait

# TODO check log for errors and rollback if necessary

# cleanup and commit
curl -s -S ${MASTER}/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>format:Delete</query></delete>' 1>/dev/null
curl -s -S ${MASTER}/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>title:"**REQUIRED FIELD**"</query></delete>' 1>/dev/null
curl -s -S ${MASTER}/update/ -H "Content-Type: text/xml" --data-binary '<commit/>' 1>/dev/null

