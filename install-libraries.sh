#!/bin/sh

## Install PEAR Packages
pear upgrade pear
pear install --onlyreqdeps DB
pear install --onlyreqdeps DB_DataObject
pear install --onlyreqdeps Structures_DataGrid-beta
pear install --onlyreqdeps Structures_DataGrid_DataSource_DataObject-beta
pear install --onlyreqdeps Structures_DataGrid_DataSource_Array-beta
pear install --onlyreqdeps Structures_DataGrid_Renderer_HTMLTable-beta
pear install --onlyreqdeps HTTP_Client
pear install --onlyreqdeps HTTP_Request
pear install --onlyreqdeps Log
pear install --onlyreqdeps Mail
pear install --onlyreqdeps Mail_Mime
pear install --onlyreqdeps Net_SMTP
pear install --onlyreqdeps Pager
pear install --onlyreqdeps XML_Serializer-beta
pear install --onlyreqdeps Console_ProgressBar-beta
pear install --onlyreqdeps File_Marc-alpha
pear channel-discover pear.horde.org
pear channel-update pear.horde.org
pear install Horde/Horde_Yaml-beta


# Install Smarty into PHP Include Directory
PHPDIR=`pear config-get php_dir`
SMARTYDIR="$PHPDIR/Smarty"
SMARTYVER="2.6.26"
SMARTYFILE="Smarty-$SMARTYVER"

if [ ! -d $SMARTYDIR ]
then
    [ -x /usr/bin/wget ] && /usr/bin/wget http://www.smarty.net/files/$SMARTYFILE.tar.gz -O $SMARTYFILE.tar.gz
    [ -x /usr/bin/curl ] && /usr/bin/curl http://www.smarty.net/files/$SMARTYFILE.tar.gz -o $SMARTYFILE.tar.gz
    
    if [ ! -f $SMARTYFILE.tar.gz ]
    then
        echo "Unable to download Smarty templates (file=$SMARTYFILE.tar.gz)."
        exit 1
    fi
    tar -zxf $SMARTYFILE.tar.gz
    if [ "$?" -ne "0" ]
    then
        echo "Unable to extract archive $SMARTYFILE.tar.gz."
        rm $SMARTYFILE.tar.gz
        exit 1
    fi
    mkdir $SMARTYDIR
    mv $SMARTYFILE/libs/* $SMARTYDIR
    rm $SMARTYFILE.tar.gz
    rm -rf $SMARTYFILE
fi

