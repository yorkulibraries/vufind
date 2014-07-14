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
[ -f /tmp/sfx.xml ] && cat /tmp/sfx.xml | php util/dump_isn_from_marc.php marcxml 090a > /tmp/sfx-issns.txt
[ -f /tmp/sfx-issns.txt ] && cat /tmp/sfx-issns.txt | php util/load_isns.php issns sfx

# import marc files
[ -f /tmp/catalog.mrc ] && /usr/local/vufind/import-marc.sh /tmp/catalog.mrc &>/tmp/import-marc.log

