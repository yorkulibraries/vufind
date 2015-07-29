# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "chef/centos-6.5"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  config.vm.network "forwarded_port", guest: 80, host: 8080

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  # config.vm.synced_folder "../data", "/vagrant_data"

  config.vm.synced_folder ".", "/usr/local/vufind-source",  :mount_options => ['dmode=777', 'fmode=777']
  config.vm.synced_folder ".", "vagrant", disabled: true

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  # config.vm.provision "shell", inline: <<-SHELL
  #   sudo apt-get update
  #   sudo apt-get install -y apache2
  # SHELL

  config.vm.provision "shell", inline: <<-SHELL
    echo "=============== HTTPD SETUP ================="

    yum -y install httpd
    chkconfig --levels 235 httpd on
    /etc/init.d/httpd start

    echo "=============== MEMCACHED SETUP ================="

    yum -y install memcached
    chkconfig --levels 235 memcached on
    /etc/rc.d/init.d/memcached start

    echo "=============== MYSQL SETUP ================="

    yum -y install mysql mysql-server
    chkconfig --levels 235 mysqld on
    /etc/init.d/mysqld start

    yum install -y git

    echo "=============== PHP SETUP ================="

    yum install -y centos-release-SCL

    yum install -y php54-php-cli php54-php-pdo php54-php-pear php54-php-pecl-memcache php54-php-xml php54-php-common php54 php54-php-mysql php54-php-pecl-memcached php54-php-mbstring php54-php-gd php54-php-pgsql php54-php-devel php54-php-magickwand php54-php-ldap php54-php
    yum install -y libevent libevent-devel

    source /opt/rh/php54/enable
    rm -fr /etc/httpd/conf.d/php.conf

    echo "=============== VUFIND SETUP ================="
    ln -s /usr/local/vufind-source /usr/local/vufind
    ln -s /usr/local/vufind-source /usr/local/vufind-preview

    cp /usr/local/vufind/httpd-vufind-vagrant.conf /etc/httpd/conf.d/httpd-vufind-vagrant.conf



    mysql -u root -e 'create database if not exists vufind'
    mysql -u root -e "grant all privileges on vufind.* to vufind@localhost identified by 'vufind'"
    mysql -u vufind -pvufind vufind < mysql.sql

    /etc/init.d/httpd restart

  SHELL

    #cd /usr/local/vufind && ./install-libraries.sh && ./mkdir.sh
end
