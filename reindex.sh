#!/bin/sh

. ~/.bash_profile

if [ -z "$VUFIND_HOME" ]
then
  export VUFIND_HOME=/usr/local/vufind
fi
  
cd $VUFIND_HOME || exit

# update ISSNs database with ISSNs from catalog dump
[ -f /tmp/catalog.mrc ] && cat /tmp/catalog.mrc | php util/dump_isn_from_marc.php marc 035a /tmp/catalog-issns.mrc > /tmp/catalog-issns.txt
[ -f /tmp/catalog-issns.txt ] && cat /tmp/catalog-issns.txt | php util/load_isns.php issns sirsi

# update ISSNs database with ISSNs from MULER dump
[ -f /tmp/muler.mrc ] && cat /tmp/muler.mrc | php util/dump_isn_from_marc.php marc 035a > /tmp/muler-issns.txt
[ -f /tmp/muler-issns.txt ] && cat /tmp/muler-issns.txt | php util/load_isns.php issns muler

# update ISSNs database with ISSNs from SFX dump
[ -f /tmp/sfx-journals.xml ] && cat /tmp/sfx-journals.xml | php util/dump_isn_from_marc.php marcxml 090a > /tmp/sfx-issns.txt
[ -f /tmp/sfx-issns.txt ] && cat /tmp/sfx-issns.txt | php util/load_isns.php issns sfx

# import catalog marc files
[ -f /tmp/catalog.mrc ] && /usr/local/vufind/import-marc.sh /tmp/catalog.mrc &>/tmp/import-catalog.log

# import SFX journals marcxml
[ -f /tmp/sfx-journals.xml ] && curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>data_source_str:SFX</query></delete>'
[ -f /tmp/sfx-journals.xml ] && /usr/local/vufind/import-sfx.sh /tmp/sfx-journals.xml &>/tmp/import-sfx-journals.log

# import MULER marc 
[ -f /tmp/muler.mrc ] && curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>data_source_str:MULER</query></delete>'
[ -f /tmp/muler.mrc ] && /usr/local/vufind/import-muler.sh /tmp/muler.mrc &>/tmp/import-muler.log

# cleanup and commit
curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>format:Delete</query></delete>'
curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<delete><query>title:"**REQUIRED FIELD**"</query></delete>'
curl -s -S http://localhost:$JETTY_PORT/solr/$SOLRCORE/update/ -H "Content-Type: text/xml" --data-binary '<commit/>'
