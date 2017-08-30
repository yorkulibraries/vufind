#!/bin/sh
DB_ROOT_PASS=12345

echo "mysql-server mysql-server/root_password password ${DB_ROOT_PASS}" | sudo debconf-set-selections 
echo "mysql-server mysql-server/root_password_again password ${DB_ROOT_PASS}" | sudo debconf-set-selections 
sudo apt-get -y install mysql-server mysql-client

sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update
sudo apt-get -y install git apache2 php5.6-cli php5.6-mysql php5.6-xml php5.6-xsl php5.6-json php5.6-ldap libapache2-mod-php5.6 \
memcached php-memcache php5.6-common php-pear php5.6-soap php5.6-mbstring php5.6-tidy php5.6-gd php5.6-imagick

cat << EOF | sudo tee /etc/mysql/conf.d/disable_strict_mode.cnf
[mysqld]
sql_mode=IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
EOF

sudo systemctl stop mysql
sudo systemctl start mysql

[ ! -d /usr/local/vufind ] && cd /usr/local/ && sudo git clone --depth 1 https://github.com/yorkulibraries/vufind.git
cd /usr/local/vufind && sudo ./install-libraries.sh && sudo ./mkdir.sh

mysql -u root -p${DB_ROOT_PASS} -e 'create database if not exists vufind'
mysql -u root -p${DB_ROOT_PASS} -e "grant all privileges on vufind.* to vufind@localhost identified by '12345'"
mysql -u vufind -p12345 vufind < /usr/local/vufind/mysql.sql
mysql -u vufind -p12345 vufind < /usr/local/vufind/york.sql

sudo cp /vagrant/web/conf/*.ini /usr/local/vufind/web/conf/

sudo chown -R www-data:www-data /usr/local/vufind/web
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

sudo a2enmod rewrite
sudo systemctl restart apache2

