# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/xenial64"
  config.vm.define "vufind"
  config.vm.network :private_network, ip: "172.16.130.63"
  config.vm.hostname = "vufind.local"
  config.vm.provision :shell, path: "bootstrap.sh"
  config.ssh.forward_agent = true
  
  config.vm.provider "virtualbox" do |v|
    v.memory = 2048
  end
  
end
