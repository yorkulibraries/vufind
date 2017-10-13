#!/bin/sh
DB_ROOT_PASS=12345

echo "mysql-server mysql-server/root_password password ${DB_ROOT_PASS}" | sudo debconf-set-selections 
echo "mysql-server mysql-server/root_password_again password ${DB_ROOT_PASS}" | sudo debconf-set-selections 
sudo apt-get -y install mysql-server mysql-client

sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update
sudo apt-get -y install git apache2 php5.6-cli php5.6-mysql php5.6-xml php5.6-xsl php5.6-json php5.6-ldap libapache2-mod-php5.6 php5.6-dev \
memcached php-memcache php5.6-common php-pear php5.6-soap php5.6-mbstring php5.6-tidy php5.6-gd php5.6-imagick gearman libgearman-dev supervisor

sudo pecl install gearman
sudo echo extension=gearman.so > /etc/php/5.6/cli/conf.d/20-gearman.ini
sudo echo extension=gearman.so > /etc/php/5.6/apache2/conf.d/20-gearman.ini

cat << EOF | sudo tee /etc/mysql/conf.d/disable_strict_mode.cnf
[mysqld]
sql_mode=IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
EOF

sudo systemctl stop mysql
sudo systemctl start mysql

[ ! -d /usr/local/vufind ] && ln -sf /vagrant /usr/local/vufind
cd /usr/local/vufind && sudo ./install-libraries.sh && sudo ./mkdir.sh

mysql -u root -p${DB_ROOT_PASS} -e 'create database if not exists vufind'
mysql -u root -p${DB_ROOT_PASS} -e "grant all privileges on vufind.* to vufind@localhost identified by '12345'"
mysql -u vufind -p12345 vufind < /usr/local/vufind/mysql.sql
mysql -u vufind -p12345 vufind < /usr/local/vufind/york.sql

cat /usr/local/vufind/httpd-vufind.conf | sudo tee /etc/apache2/conf-available/vufind.conf 
cat <<EOF | sudo tee /etc/apache2/sites-enabled/000-default.conf 
<VirtualHost *:80>
  ServerName vufind.local
  ServerAdmin webmaster@localhost
  DocumentRoot /var/www/html

  ErrorLog \${APACHE_LOG_DIR}/error.log
  CustomLog \${APACHE_LOG_DIR}/access.log combined

  Include conf-available/vufind.conf
</VirtualHost>
EOF

# make apache run as ubuntu user
sudo sed -i 's/www-data/ubuntu/g'  /etc/apache2/envvars

sudo a2enmod rewrite
sudo systemctl restart apache2

sudo cp /vagrant/vufind-gearman-worker.conf /etc/supervisor/conf.d/
sudo systemctl enable gearman-job-server 
sudo systemctl restart gearman-job-server 
sudo systemctl enable supervisor
sudo systemctl restart supervisor
