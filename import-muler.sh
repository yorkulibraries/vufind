#!/bin/sh

if [ -z "$VUFIND_HOME" ]
then
    echo "You need to set the VUFIND_HOME environmental variable before running this script."
    exit 1
fi

exec $VUFIND_HOME/import-marc.sh -p $VUFIND_HOME/import/import-muler.properties $1
