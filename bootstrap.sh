#!/usr/bin/env bash

DB_ROOT_PASS=12345

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

sudo timedatectl set-timezone America/Toronto

/vagrant/setup-java.sh

/vagrant/setup-master.sh

/vagrant/setup-slave.sh

/vagrant/setup-vufind.sh
